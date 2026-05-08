<?php

namespace App\Services;

use Typesense\Client as TypesenseClient;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * TypesenseSearchService
 *
 * Wraps all Typesense query logic. Called by SearchController and
 * ProductListingService. Keeps controller/service code clean.
 *
 * Features delivered:
 *  - Typo-tolerant full-text search (Typesense native)
 *  - Faceted filtering (category, brand, price, rating, condition, audience)
 *  - Multi-sort (relevance, price, rating, bestseller, newest)
 *  - Instant autocomplete suggestions (separate lightweight query)
 *  - Synonym support via Typesense Synonyms API
 *  - Graceful MySQL fallback if Typesense is unreachable
 */
class TypesenseSearchService
{
    private TypesenseClient $client;
    private string $collection = 'products';

    public function __construct()
    {
        // Use scout.php as single source of truth — same credentials Scout uses
        $this->client = new TypesenseClient(
            config('scout.typesense.client-settings')
        );
    }

    // =========================================================================
    // MAIN SEARCH  (used by ProductListingService)
    // =========================================================================

    /**
     * Execute a full search with filters, facets, and pagination.
     *
     * Returns a structured array that ProductListingService can use directly,
     * keeping the same shape as the MySQL version so views need no changes.
     *
     * @param  string  $query       Raw user query (can be empty for browse)
     * @param  array   $filters     Parsed filters from request
     * @param  string  $sortBy      Sort key matching your existing sort_by param
     * @param  int     $page        1-based page number
     * @param  int     $perPage     Items per page (default 24)
     * @return array   { hits, total, facets, took_ms }
     */
    public function search(
        string $query,
        array  $filters  = [],
        string $sortBy   = 'default',
        int    $page     = 1,
        int    $perPage  = 24
    ): array {
        $params = [
            'q'                    => $query ?: '*',   // '*' = browse all
            'query_by'             => 'name,brand_name,category_name,tags,search_keywords,model_number,short_description',
            'query_by_weights'     => '10,6,5,4,4,8,2',  // importance per field
            'typo_tokens_threshold'=> 1,               // allow typos after 1+ tokens
            'num_typos'            => 2,               // tolerate up to 2 typos
            'min_len_1typo'        => 4,               // allow 1 typo for 4+ char words
            'min_len_2typo'        => 7,               // allow 2 typos for 7+ char words
            'prefix'               => true,            // prefix matching for autocomplete feel
            'split_join_tokens'    => 'always',        // handles "iphone14" vs "iphone 14"
            'infix'                => 'off',           // keep fast; enable only if needed
            'page'                 => $page,
            'per_page'             => $perPage,
            'highlight_full_fields'=> 'name',
            'highlight_affix_num_tokens' => 4,
            'include_fields'       => implode(',', [
                'id','name','slug','price','sale_price','effective_price',
                'brand_name','brand_slug','category_name','category_slug',
                'rating','review_count','sold_count','is_featured',
                'in_stock','on_sale','image_url','created_at_ts','popularity_score',
            ]),
            // Facets returned with result counts — powers your sidebar filters
            'facet_by'             => 'category_slug,brand_slug,condition,target_audience',
            'max_facet_values'     => 20,
        ];

        // ── Filter string ────────────────────────────────────────────
        $filterParts = ['is_active:=true'];

        if (!empty($filters['in_stock_only'])) {
            $filterParts[] = 'in_stock:=true';
        }
        if (!empty($filters['category_slug'])) {
            $slugs = (array) $filters['category_slug'];
            $filterParts[] = 'category_slug:[' . implode(',', array_map('addslashes', $slugs)) . ']';
        }
        if (!empty($filters['brand_slug'])) {
            $slugs = (array) $filters['brand_slug'];
            $filterParts[] = 'brand_slug:[' . implode(',', array_map('addslashes', $slugs)) . ']';
        }
        if (!empty($filters['min_price']) || !empty($filters['max_price'])) {
            $min = isset($filters['min_price']) ? (float) $filters['min_price'] : 0;
            $max = isset($filters['max_price']) ? (float) $filters['max_price'] : PHP_INT_MAX;
            $filterParts[] = "effective_price:[{$min}..{$max}]";
        }
        if (!empty($filters['min_rating'])) {
            $filterParts[] = 'rating:>=' . (float) $filters['min_rating'];
        }
        if (!empty($filters['on_sale'])) {
            $filterParts[] = 'on_sale:=true';
        }
        if (!empty($filters['condition'])) {
            $values = array_map('addslashes', (array) $filters['condition']);
            $filterParts[] = count($values) === 1
                ? 'condition:=' . $values[0]
                : 'condition:[' . implode(',', $values) . ']';
        }
        if (!empty($filters['target_audience'])) {
            $values = array_map('addslashes', (array) $filters['target_audience']);
            $filterParts[] = count($values) === 1
                ? 'target_audience:=' . $values[0]
                : 'target_audience:[' . implode(',', $values) . ']';
        }
        if (!empty($filters['seller_type'])) {
            $values = array_map('addslashes', (array) $filters['seller_type']);
            $filterParts[] = count($values) === 1
                ? 'business_type:=' . $values[0]
                : 'business_type:[' . implode(',', $values) . ']';
        }
        if (!empty($filters['delivery_zone'])) {
            $values = array_map('addslashes', (array) $filters['delivery_zone']);
            $filterParts[] = count($values) === 1
                ? 'delivery_zone:=' . $values[0]
                : 'delivery_zone:[' . implode(',', $values) . ']';
        }
        // 'new' arrivals filter — last 30 days
        if (!empty($filters['new'])) {
            $filterParts[] = 'created_at_ts:>=' . now()->subDays(30)->timestamp;
        }
        if (!empty($filters['featured'])) {
            $filterParts[] = 'is_featured:=true';
        }

        $params['filter_by'] = implode(' && ', $filterParts);

        // ── Sort ─────────────────────────────────────────────────────
        $params['sort_by'] = $this->buildSortString($sortBy, $query);

        // ── Execute ──────────────────────────────────────────────────
        try {
            $result = $this->client->collections[$this->collection]->documents->search($params);
            return $this->formatSearchResult($result);
        } catch (\Exception $e) {
            Log::warning('Typesense search failed, falling back to MySQL', [
                'error' => $e->getMessage(),
                'query' => $query,
            ]);
            return $this->fallbackResult();
        }
    }

    // =========================================================================
    // SUGGEST  (autocomplete dropdown — used by SearchController::suggest)
    // =========================================================================

    /**
     * Marketplace-safe autocomplete suggestions.
     *
     * Design: no individual products are shown — only search predictions.
     * This gives every seller an equal chance when the user lands on results.
     *
     * Suggestion order:
     *  1. Exact query row   — "samsung" → search all matching products
     *  2. Query predictions — deduplicated product names as search terms
     *                         e.g. "Samsung S8", "Samsung S9", "Samsung Tab"
     *  3. Category scoped   — "samsung in Electronics" (filters to category)
     *  4. Brand scoped      — "samsung in Samsung" (filters to brand)
     */
    public function suggest(string $rawQuery): array
    {
        $cacheKey = 'ts_suggest_' . md5(strtolower(trim($rawQuery)));
        return Cache::remember($cacheKey, 30, fn () => $this->generateSuggestions($rawQuery));
    }

    private function generateSuggestions(string $rawQuery): array
    {
        $suggestions = [];

        try {
            // ── Multi-search: 3 queries in 1 HTTP round-trip ─────────────
            $searches = [

                // 1. Name predictions — fetch matching product names to use as
                //    query predictions. Sorted by popularity so most-sold terms
                //    appear first. We fetch 15 and deduplicate below.
                [
                    'collection'       => $this->collection,
                    'q'                => $rawQuery,
                    'query_by'         => 'name,tags,search_keywords',
                    'query_by_weights' => '10,4,3',
                    'filter_by'        => 'is_active:=true',
                    'sort_by'          => 'popularity_score:desc',
                    'per_page'         => 15,
                    'num_typos'        => 1,
                    'prefix'           => 'true',
                    'include_fields'   => 'name,category_name,sold_count',
                    'highlight_full_fields' => 'name',
                ],

                // 2. Category predictions — which categories have products
                //    matching this query (for "X in Category" rows)
                [
                    'collection'   => $this->collection,
                    'q'            => $rawQuery,
                    'query_by'     => 'name,tags,search_keywords,category_name',
                    'filter_by'    => 'is_active:=true',
                    'facet_by'     => 'category_slug',
                    'per_page'     => 0,
                    'num_typos'    => 1,
                    'prefix'       => 'true',
                    'include_fields' => 'id',
                ],

                // 3. Brand predictions — which brands have matching products
                [
                    'collection'   => $this->collection,
                    'q'            => $rawQuery,
                    'query_by'     => 'name,tags,search_keywords,brand_name',
                    'filter_by'    => 'is_active:=true',
                    'facet_by'     => 'brand_slug',
                    'per_page'     => 0,
                    'num_typos'    => 1,
                    'prefix'       => 'true',
                    'include_fields' => 'id',
                ],
            ];

            $results = $this->client->multiSearch->perform(
                ['searches' => $searches],
                ['collection' => $this->collection]
            );

            $nameResults     = $results['results'][0] ?? [];
            $categoryResults = $results['results'][1] ?? [];
            $brandResults    = $results['results'][2] ?? [];

            $totalFound = $nameResults['found'] ?? 0;

            // ── Row 1: exact query — always first ─────────────────────────
            $suggestions[] = [
                'text'         => $rawQuery,
                'type'         => 'exact_search',
                'icon'         => 'search',
                'action_type'  => 'search',
                'action_param' => $rawQuery,
                'count'        => $totalFound,
                'label'        => null,
            ];

            // ── Rows 2+: query predictions from product names ─────────────
            // Deduplicate: if multiple products share the same name (different
            // sellers), only one prediction row is shown for that name.
            // Also deduplicate by normalised lowercase to avoid "Samsung S8"
            // and "samsung s8" appearing as separate rows.
            $hits = $nameResults['hits'] ?? [];
            $seenNames = [strtolower(trim($rawQuery))]; // skip exact query duplicate
            $predictions = [];

            foreach ($hits as $hit) {
                $doc        = $hit['document'];
                $name       = trim($doc['name']);
                $normalised = strtolower($name);

                // Skip duplicates (case-insensitive exact match)
                if (in_array($normalised, $seenNames)) continue;

                // Also skip if this name is just the query with a space/suffix
                // e.g. query="samsung" and name="samsung" would duplicate row 1
                if ($normalised === strtolower(trim($rawQuery))) continue;

                $seenNames[] = $normalised;

                // Use Typesense highlight on the name field if available
                $highlight = null;
                foreach (($hit['highlights'] ?? []) as $h) {
                    if ($h['field'] === 'name') { $highlight = $h['snippet']; break; }
                }

                $predictions[] = [
                    'text'         => $name,
                    'highlight'    => $highlight,
                    'type'         => 'autocomplete',
                    'icon'         => 'search',          // search icon, not arrow — it IS a search
                    'action_type'  => 'search',
                    'action_param' => $name,
                    'label'        => null,
                    'count'        => null,
                ];
            }

            // Limit to 5 predictions
            $suggestions = array_merge($suggestions, array_slice($predictions, 0, 5));

            // ── Category scoped rows: "X in Electronics" ──────────────────
            $catFacets = array_slice($categoryResults['facet_counts'][0]['counts'] ?? [], 0, 2);
            foreach ($catFacets as $facet) {
                $suggestions[] = [
                    'text'          => $rawQuery,
                    'label'         => $this->titleCase($facet['value']),
                    'type'          => 'category_search',
                    'icon'          => 'tag',
                    'action_type'   => 'search_in_category',
                    'action_param'  => $rawQuery,
                    'category_slug' => $facet['value'],
                    'count'         => $facet['count'],
                ];
            }

            // ── Brand scoped rows: "X in Apple" ───────────────────────────
            $brandFacets = array_slice($brandResults['facet_counts'][0]['counts'] ?? [], 0, 2);
            foreach ($brandFacets as $facet) {
                $suggestions[] = [
                    'text'         => $rawQuery,
                    'label'        => $this->titleCase($facet['value']),
                    'type'         => 'brand_search',
                    'icon'         => 'store',
                    'action_type'  => 'search_in_brand',
                    'action_param' => $rawQuery,
                    'brand_slug'   => $facet['value'],
                    'brand_name'   => $this->titleCase($facet['value']),
                    'count'        => $facet['count'],
                ];
            }

        } catch (\Exception $e) {
            Log::warning('Typesense suggest failed', ['error' => $e->getMessage()]);
            $suggestions = [[
                'text'         => $rawQuery,
                'type'         => 'exact_search',
                'icon'         => 'search',
                'action_type'  => 'search',
                'action_param' => $rawQuery,
                'count'        => 0,
                'label'        => null,
            ]];
        }

        return $suggestions;
    }

    // =========================================================================
    // SYNONYM MANAGEMENT
    // =========================================================================

    /**
     * Upsert a synonym set into Typesense.
     *
     * Multi-way (default): all words are interchangeable.
     *   addSynonym('shoe-synonyms', ['shoe','sneaker','trainer'])
     *
     * One-way: searching any synonym returns results for the root word,
     * but searching the root does NOT return synonym results.
     *   addSynonym('iphone-synonyms', ['iphone','apple phone'], oneWay: true)
     */
    public function addSynonym(string $id, array $synonyms, bool $oneWay = false): array
    {
        if (count($synonyms) < 2) {
            throw new \InvalidArgumentException("Synonym group '{$id}' needs at least 2 words.");
        }

        // Typesense v27+ uses global SynonymSets API (not per-collection)
        // Format: { items: [{ id: string, synonyms: [], root?: string }] }
        $item = ['id' => $id . '-1', 'synonyms' => array_values($synonyms)];
        if ($oneWay) {
            $item['root'] = $synonyms[0];
            $item['synonyms'] = array_values(array_slice($synonyms, 1));
        }

        return $this->client->synonymSets->upsert($id, ['items' => [$item]]);
    }

    /**
     * Seed common ecommerce synonyms.
     * Run once: php artisan typesense:seed-synonyms
     * Add your own domain-specific synonyms here (Nigerian brands, local terms, etc.)
     */
    public function seedDefaultSynonyms(): void
    {
        // Resolve the actual collection name Scout created
        // (Scout prefixes with the app name in some versions)
        $collectionName = (new \App\Models\Product)->searchableAs();
        $this->collection = $collectionName;

        $synonymGroups = [
            'phone-synonyms'     => ['phone', 'mobile', 'smartphone', 'handset', 'cellphone'],
            'laptop-synonyms'    => ['laptop', 'notebook', 'portable computer'],
            'tv-synonyms'        => ['tv', 'television', 'smart tv', 'flat screen'],
            'fridge-synonyms'    => ['fridge', 'refrigerator', 'freezer combo'],
            'sofa-synonyms'      => ['sofa', 'couch', 'settee', 'loveseat'],
            'shirt-synonyms'     => ['shirt', 'top', 'blouse', 'tee', 't-shirt'],
            'shoe-synonyms'      => ['shoe', 'sneaker', 'trainer', 'footwear'],
            'generator-synonyms' => ['generator', 'gen set', 'genset', 'inverter'],
            'earphone-synonyms'  => ['earphone', 'earbuds', 'headphone', 'headset', 'airpods'],
            'ac-synonyms'        => ['ac', 'air conditioner', 'air conditioning', 'split unit'],
        ];

        foreach ($synonymGroups as $id => $words) {
            $this->addSynonym($id, $words);
            echo "  ✓ {$id}\n";
        }
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    private function buildSortString(string $sortBy, string $query): string
    {
        // When there's a real query, default to relevance (Typesense handles this
        // automatically with _text_match) then break ties with popularity.
        return match ($sortBy) {
            'price_low'   => 'effective_price:asc,popularity_score:desc',
            'price_high'  => 'effective_price:desc,popularity_score:desc',
            'name'        => 'name:asc',
            'rating'      => 'rating:desc,review_count:desc',
            'bestseller'  => 'sold_count:desc',
            'newest'      => 'created_at_ts:desc',
            default       => $query
                ? '_text_match:desc,popularity_score:desc'
                : 'popularity_score:desc',
        };
    }

    private function formatSearchResult(array $result): array
    {
        return [
            'hits'    => $result['hits']    ?? [],
            'total'   => $result['found']   ?? 0,
            'facets'  => $this->parseFacets($result['facet_counts'] ?? []),
            'took_ms' => $result['search_time_ms'] ?? 0,
            'page'    => $result['page']    ?? 1,
        ];
    }

    private function parseFacets(array $facetCounts): array
    {
        $facets = [];
        foreach ($facetCounts as $facetGroup) {
            $fieldName = $facetGroup['field_name'];
            $facets[$fieldName] = array_map(fn($c) => [
                'value' => $c['value'],
                'count' => $c['count'],
            ], $facetGroup['counts']);
        }
        return $facets;
    }

    private function fallbackResult(): array
    {
        return ['hits' => [], 'total' => 0, 'facets' => [], 'took_ms' => 0, 'page' => 1, '_fallback' => true];
    }

    private function titleCase(string $str): string
    {
        return ucwords(str_replace(['-', '_'], ' ', $str));
    }
}