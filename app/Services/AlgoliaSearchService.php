<?php

namespace App\Services;

use Algolia\AlgoliaSearch\Api\SearchClient;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * AlgoliaSearchService  —  v4 client (algolia/algoliasearch-client-php ^4.0)
 *
 * Drop-in replacement for TypesenseSearchService.
 * SearchController and ProductListingService call the same public methods:
 *   ->suggest(string $query): array
 *   ->search(string $query, array $filters, string $sortBy, int $page, int $perPage): array
 *
 * KEY v4 CHANGES vs v3 / scout-extended:
 *  - SearchClient::create(appId, apiKey)  instead of  Algolia::client() / initIndex()
 *  - No more $index object — every call goes through $this->client directly
 *  - search()      → $client->searchSingleIndex(indexName, searchParams)
 *  - setSettings() → $client->setSettings(indexName, settingsRequest)
 *  - saveSynonym() → $client->saveSynonym(indexName, objectID, synonymHit, forwardToReplicas)
 *  - All results are typed objects but implement ArrayAccess, so ['key'] syntax still works.
 *
 * .env keys required:
 *   ALGOLIA_APP_ID=your_app_id
 *   ALGOLIA_SECRET=your_admin_api_key
 *
 * scout.php keys used:
 *   algolia.id      → ALGOLIA_APP_ID
 *   algolia.secret  → ALGOLIA_SECRET
 *   prefix          → optional index prefix
 */
class AlgoliaSearchService
{
    private SearchClient $client;
    private string $indexName;

    public function __construct()
    {
        $this->client    = SearchClient::create(
            config('scout.algolia.id'),      // reads ALGOLIA_APP_ID from .env
            config('scout.algolia.secret')   // reads ALGOLIA_SECRET from .env
        );
        $this->indexName = config('scout.prefix', '') . 'products';
    }

    // =========================================================================
    // MAIN SEARCH  (used by ProductListingService)
    // =========================================================================

    /**
     * Execute a full search with filters, facets, and pagination.
     *
     * Returns the same shape as the old TypesenseSearchService so
     * ProductListingService needs zero changes.
     *
     * @param  string  $query
     * @param  array   $filters   Keys: category_slug, brand_slug, min_price,
     *                            max_price, min_rating, on_sale, in_stock_only,
     *                            condition, target_audience, new, featured
     * @param  string  $sortBy    One of: default|price_low|price_high|rating|bestseller|newest|name
     * @param  int     $page      1-based
     * @param  int     $perPage
     * @return array   { hits, total, facets, took_ms, page }
     */
    public function search(
        string $query,
        array  $filters  = [],
        string $sortBy   = 'default',
        int    $page     = 1,
        int    $perPage  = 24
    ): array {
        $targetIndex = $this->resolveIndex($sortBy);

        // ── Build filter string ───────────────────────────────────────────────
        $filterParts = ['is_active:true'];

        if (!empty($filters['in_stock_only'])) {
            $filterParts[] = 'in_stock:true';
        }
        if (!empty($filters['category_slug'])) {
            $slugs = array_map(fn($s) => 'category_slug:"' . addslashes($s) . '"', (array) $filters['category_slug']);
            $filterParts[] = '(' . implode(' OR ', $slugs) . ')';
        }
        if (!empty($filters['brand_slug'])) {
            $slugs = array_map(fn($s) => 'brand_slug:"' . addslashes($s) . '"', (array) $filters['brand_slug']);
            $filterParts[] = '(' . implode(' OR ', $slugs) . ')';
        }
        if (!empty($filters['min_price']) || !empty($filters['max_price'])) {
            $min = isset($filters['min_price']) ? (float) $filters['min_price'] : 0;
            $max = isset($filters['max_price']) ? (float) $filters['max_price'] : PHP_INT_MAX;
            $filterParts[] = "effective_price >= {$min} AND effective_price <= {$max}";
        }
        if (!empty($filters['min_rating'])) {
            $filterParts[] = 'rating >= ' . (float) $filters['min_rating'];
        }
        if (!empty($filters['on_sale'])) {
            $filterParts[] = 'on_sale:true';
        }
        if (!empty($filters['condition'])) {
            $values = array_map(fn($v) => 'condition:"' . addslashes($v) . '"', (array) $filters['condition']);
            $filterParts[] = '(' . implode(' OR ', $values) . ')';
        }
        if (!empty($filters['target_audience'])) {
            $values = array_map(fn($v) => 'target_audience:"' . addslashes($v) . '"', (array) $filters['target_audience']);
            $filterParts[] = '(' . implode(' OR ', $values) . ')';
        }
        if (!empty($filters['new'])) {
            $filterParts[] = 'created_at_ts >= ' . now()->subDays(30)->timestamp;
        }
        if (!empty($filters['featured'])) {
            $filterParts[] = 'is_featured:true';
        }

        // ── Build SearchParamsObject ──────────────────────────────────────────
        // v4 accepts a plain associative array or a SearchParamsObject.
        // Plain array is simpler and fully supported.
        $searchParams = [
            'query'                 => $query ?: '',
            'hitsPerPage'           => $perPage,
            'page'                  => $page - 1,   // Algolia is 0-based
            'facets'                => ['category_slug', 'brand_slug', 'condition', 'target_audience'],
            'attributesToRetrieve'  => [
                'objectID', 'name', 'slug', 'price', 'sale_price', 'effective_price',
                'brand_name', 'brand_slug', 'category_name', 'category_slug',
                'rating', 'review_count', 'sold_count', 'is_featured',
                'in_stock', 'on_sale', 'image_url', 'created_at_ts', 'popularity_score',
            ],
            'attributesToHighlight' => ['name'],
            'highlightPreTag'       => '<mark>',
            'highlightPostTag'      => '</mark>',
            'filters'               => implode(' AND ', $filterParts),
        ];

        // ── Execute ──────────────────────────────────────────────────────────
        try {
            $start  = microtime(true);

            // v4: searchSingleIndex(indexName, searchParams)
            // Returns a SearchResponse object that implements ArrayAccess.
            $result = $this->client->searchSingleIndex($targetIndex, $searchParams);

            $ms = (int) round((microtime(true) - $start) * 1000);

            return $this->formatSearchResult($result, $ms);

        } catch (\Exception $e) {
            Log::warning('Algolia search failed', [
                'error' => $e->getMessage(),
                'query' => $query,
            ]);
            return $this->fallbackResult();
        }
    }

    // =========================================================================
    // SUGGEST  (autocomplete — called by SearchController::suggest)
    // =========================================================================

    /**
     * Autocomplete suggestions with the same shape as the old Typesense version:
     *
     *  Row 1 — exact_search   : the raw query itself + total result count
     *  Rows 2-6 — autocomplete : deduplicated product name predictions
     *  Rows 7-8 — category_search : "X in Electronics"
     *  Rows 9-10 — brand_search   : "X in Samsung"
     *
     * Results are cached for 30 seconds (same as Typesense version).
     */
    public function suggest(string $rawQuery): array
    {
        $cacheKey = 'alg_suggest_' . md5(strtolower(trim($rawQuery)));
        return Cache::remember($cacheKey, 30, fn () => $this->generateSuggestions($rawQuery));
    }

    private function generateSuggestions(string $rawQuery): array
    {
        $suggestions = [];

        try {
            // v4: searchSingleIndex(indexName, searchParams)
            $result = $this->client->searchSingleIndex($this->indexName, [
                'query'                 => $rawQuery,
                'hitsPerPage'           => 15,
                'attributesToRetrieve'  => ['name', 'category_name', 'category_slug', 'brand_name', 'brand_slug'],
                'attributesToHighlight' => ['name'],
                'highlightPreTag'       => '<mark>',
                'highlightPostTag'      => '</mark>',
                'filters'               => 'is_active:true',
                'facets'                => ['category_slug', 'brand_slug'],
            ]);

            $totalFound = $result['nbHits'] ?? 0;
            $hits       = $result['hits']   ?? [];

            // ── Row 1: the exact query — always first ─────────────────────────
            $suggestions[] = [
                'text'         => $rawQuery,
                'type'         => 'exact_search',
                'icon'         => 'search',
                'action_type'  => 'search',
                'action_param' => $rawQuery,
                'count'        => $totalFound,
                'label'        => null,
                'highlight'    => null,
            ];

            // ── Rows 2-6: deduplicated name predictions ───────────────────────
            $seenNames   = [strtolower(trim($rawQuery))];
            $predictions = [];

            foreach ($hits as $hit) {
                $name       = trim($hit['name'] ?? '');
                $normalised = strtolower($name);

                if (!$name || in_array($normalised, $seenNames)) continue;
                $seenNames[] = $normalised;

                $highlight = $hit['_highlightResult']['name']['value'] ?? null;

                $predictions[] = [
                    'text'         => $name,
                    'highlight'    => $highlight,
                    'type'         => 'autocomplete',
                    'icon'         => 'search',
                    'action_type'  => 'search',
                    'action_param' => $name,
                    'label'        => null,
                    'count'        => null,
                ];
            }

            $suggestions = array_merge($suggestions, array_slice($predictions, 0, 5));

            // ── Category-scoped rows ──────────────────────────────────────────
            $categoryFacets = array_slice(
                (array) ($result['facets']['category_slug'] ?? []),
                0, 2
            );
            foreach ($categoryFacets as $facetValue => $facetCount) {
                $suggestions[] = [
                    'text'          => $rawQuery,
                    'label'         => $this->titleCase($facetValue),
                    'type'          => 'category_search',
                    'icon'          => 'tag',
                    'action_type'   => 'search_in_category',
                    'action_param'  => $rawQuery,
                    'category_slug' => $facetValue,
                    'count'         => $facetCount,
                    'highlight'     => null,
                ];
            }

            // ── Brand-scoped rows ─────────────────────────────────────────────
            $brandFacets = array_slice(
                (array) ($result['facets']['brand_slug'] ?? []),
                0, 2
            );
            foreach ($brandFacets as $facetValue => $facetCount) {
                $suggestions[] = [
                    'text'         => $rawQuery,
                    'label'        => $this->titleCase($facetValue),
                    'type'         => 'brand_search',
                    'icon'         => 'store',
                    'action_type'  => 'search_in_brand',
                    'action_param' => $rawQuery,
                    'brand_slug'   => $facetValue,
                    'brand_name'   => $this->titleCase($facetValue),
                    'count'        => $facetCount,
                    'highlight'    => null,
                ];
            }

        } catch (\Exception $e) {
            Log::warning('Algolia suggest failed', ['error' => $e->getMessage()]);
            $suggestions = [[
                'text'         => $rawQuery,
                'type'         => 'exact_search',
                'icon'         => 'search',
                'action_type'  => 'search',
                'action_param' => $rawQuery,
                'count'        => 0,
                'label'        => null,
                'highlight'    => null,
            ]];
        }

        return $suggestions;
    }

    // =========================================================================
    // SYNONYM MANAGEMENT
    // =========================================================================

    /**
     * Upsert a synonym rule into Algolia.
     *
     * Multi-way (default): all words are interchangeable.
     * One-way: searching a synonym returns the root, not vice-versa.
     *
     * Usage:
     *   $service->addSynonym('shoe-synonyms', ['shoe','sneaker','trainer']);
     *   $service->addSynonym('iphone-synonyms', ['iphone','apple phone'], oneWay: true);
     *
     * v4: saveSynonym() expects a plain array for synonymHit, not a SynonymHit object.
     *     Passing an object causes array_merge() to blow up inside ApiWrapper.
     */
    public function addSynonym(string $id, array $synonyms, bool $oneWay = false): void
    {
        if (count($synonyms) < 2) {
            throw new \InvalidArgumentException("Synonym group '{$id}' needs at least 2 words.");
        }

        if ($oneWay) {
            $synonymHit = [
                'objectID' => $id,
                'type'     => 'oneWaySynonym',
                'input'    => $synonyms[0],
                'synonyms' => array_values(array_slice($synonyms, 1)),
            ];
        } else {
            $synonymHit = [
                'objectID' => $id,
                'type'     => 'synonym',
                'synonyms' => array_values($synonyms),
            ];
        }

        // v4 signature: saveSynonym(indexName, objectID, synonymHit, forwardToReplicas)
        $this->client->saveSynonym(
            indexName:         $this->indexName,
            objectID:          $id,
            synonymHit:        $synonymHit,
            forwardToReplicas: true
        );
    }

    /**
     * Seed common ecommerce synonyms.
     * Run once: php artisan algolia:setup
     */
public function seedDefaultSynonyms(): void
{
    $synonymGroups = [
 
    // ── Electronics ──
    'electronics-smartphone-synonyms' => ['phone', 'mobile', 'cell phone', 'cellphone', 'handset', 'android', 'iphone', 'device', 'smartphone', 'smart phone', 'mobile phone', 'fone', 'phon', 'smarthphone', 'androi'],
    'electronics-laptop-synonyms' => ['notebook', 'computer', 'pc', 'portable computer', 'laptops', 'lappy', 'lapy', 'notbook', 'computor', 'macbook', 'chromebook', 'ultrabook', 'netbook'],
    'electronics-tablet-synonyms' => ['ipad', 'tab', 'tablet pc', 'android tablet', 'slate', 'tablete', 'e-reader', 'reading tablet', 'drawing tablet', 'pad'],
    'electronics-smart-tv-synonyms' => ['tv', 'television', 'smart television', 'led tv', 'oled tv', 'qled tv', 'flat screen', 'flat-screen', 'flatscreen', 'tele', 'tellyvision', 'plasma tv', 'android tv', 'fire tv'],
    'electronics-headphones-synonyms' => ['earphone', 'earphones', 'headset', 'earbud', 'earbuds', 'airpods', 'wireless earphones', 'bluetooth headset', 'headfone', 'earfone', 'headphone', 'pods', 'buds', 'in-ear', 'over-ear'],
    'electronics-speaker-synonyms' => ['bluetooth speaker', 'wireless speaker', 'portable speaker', 'soundbar', 'sound bar', 'speaker box', 'subwoofer', 'home theatre', 'home theater', 'boombox', 'boom box'],
    'electronics-camera-synonyms' => ['dslr', 'mirrorless', 'digital camera', 'camcorder', 'action camera', 'gopro', 'webcam', 'security camera', 'cctv camera', 'photo camera', 'camara'],
    'electronics-smartwatch-synonyms' => ['smart watch', 'wristwatch', 'fitness watch', 'apple watch', 'galaxy watch', 'fitness tracker', 'wearable', 'activity tracker', 'smart band', 'fitbit'],
    'electronics-power-bank-synonyms' => ['portable charger', 'power pack', 'battery pack', 'mobile charger', 'travel charger', 'backup battery', 'pawa bank', 'pawer bank', 'powerbank'],
    'electronics-charger-synonyms' => ['adapter', 'phone charger', 'fast charger', 'quick charger', 'wireless charger', 'usb charger', 'wall charger', 'charging brick', 'chaja', 'charging adapter'],
    'electronics-cable-synonyms' => ['usb cable', 'charging cable', 'data cable', 'type-c cable', 'lightning cable', 'micro usb', 'usb-c', 'phone cable', 'transfer cable'],
    'electronics-router-synonyms' => ['wifi router', 'wireless router', 'modem', 'internet router', 'broadband router', 'network router', 'wifi modem', 'hotspot device', 'mifi'],
    'electronics-gaming-console-synonyms' => ['console', 'playstation', 'ps5', 'ps4', 'xbox', 'nintendo', 'game console', 'gaming system', 'ps', 'play station', 'x-box', 'switch'],
    'electronics-drone-synonyms' => ['quadcopter', 'uav', 'unmanned aerial vehicle', 'dji', 'flying camera', 'aerial drone', 'mini drone', 'fpv drone'],
    'electronics-projector-synonyms' => ['beamer', 'home projector', 'portable projector', 'mini projector', 'movie projector', 'pico projector', 'proyector'],
 
    // ── Appliances ──
    'appliances-refrigerator-synonyms' => ['fridge', 'freezer', 'refrigerator', 'cold room', 'cold box', 'double door fridge', 'single door fridge', 'mini fridge', 'side by side fridge', 'fridgerator', 'refridgerator', 'refrijerator', 'frig', 'deep freezer', 'chest freezer'],
    'appliances-washing-machine-synonyms' => ['washer', 'laundry machine', 'clothes washer', 'automatic washer', 'front load washer', 'top load washer', 'washing machin', 'washing masheen', 'washin machine', 'spin dryer', 'twin tub'],
    'appliances-air-conditioner-synonyms' => ['ac', 'a/c', 'air con', 'air conditioning', 'split unit', 'split ac', 'window ac', 'portable ac', 'inverter ac', 'standing ac', 'ceiling ac', 'aircon', 'airconditioner', 'air-conditioner', 'cool air', '1.5hp ac', '1hp ac', '2hp ac'],
    'appliances-generator-synonyms' => ['gen', 'genset', 'gen set', 'generator set', 'standby generator', 'portable generator', 'inverter generator', 'petrol generator', 'diesel generator', 'i-better-pass-my-neighbour', 'nepa alternative', 'light gen', 'small gen', 'big gen'],
    'appliances-microwave-synonyms' => ['microwave oven', 'micro wave', 'microwav', 'oven microwave', 'countertop microwave', 'built-in microwave', 'convection microwave', 'microoven'],
    'appliances-blender-synonyms' => ['juicer', 'smoothie maker', 'food blender', 'hand blender', 'immersion blender', 'mixer', 'grinder', 'blenda', 'food processor', 'liquidizer', 'fruit blender'],
    'appliances-fan-synonyms' => ['standing fan', 'ceiling fan', 'table fan', 'wall fan', 'rechargeable fan', 'pedestal fan', 'tower fan', 'desk fan', 'electric fan', 'solar fan', 'rechargeable standing fan'],
    'appliances-iron-synonyms' => ['clothes iron', 'electric iron', 'steam iron', 'dry iron', 'pressing iron', 'travel iron', 'cordless iron', 'pressing cloth', 'cloth iron'],
    'appliances-water-dispenser-synonyms' => ['dispenser', 'water cooler', 'hot and cold dispenser', 'water heater dispenser', 'standing dispenser', 'table dispenser', 'desktop dispenser', 'water machine'],
    'appliances-gas-cooker-synonyms' => ['cooker', 'gas stove', 'cooking stove', 'gas burner', 'cooker stove', 'table cooker', 'standing cooker', 'oven cooker', 'gas oven', 'electric cooker', 'induction cooker', '4 burner cooker', '2 burner cooker'],
    'appliances-water-heater-synonyms' => ['boiler', 'electric water heater', 'solar water heater', 'instant water heater', 'geyser', 'hot water heater', 'storage water heater'],
    'appliances-vacuum-cleaner-synonyms' => ['hoover', 'vaccum', 'vacum cleaner', 'floor cleaner', 'carpet cleaner', 'robot vacuum', 'handheld vacuum', 'wet and dry vacuum'],
 
    // ── Fashion Men ──
    'fashion_men-shirt-synonyms' => ['polo shirt', 't-shirt', 'tshirt', 'tee', 'button-down shirt', 'dress shirt', 'casual shirt', 'native shirt', 'senator shirt', 'kaftan', 'agbada top', 'corporate shirt', 'office shirt', 'long sleeve shirt', 'short sleeve shirt'],
    'fashion_men-trousers-synonyms' => ['pants', 'chinos', 'jeans', 'slacks', 'dress pants', 'casual pants', 'native trousers', 'senator trousers', 'joggers', 'cargo pants', 'pallazo', 'bottom wear', 'trouser'],
    'fashion_men-suit-synonyms' => ['men suit', '2 piece suit', '3 piece suit', 'blazer', 'corporate suit', 'tuxedo', 'office suit', 'wedding suit', 'suit jacket', 'suit trousers', 'men formal'],
    'fashion_men-shoes-synonyms' => ['men shoes', 'dress shoes', 'oxford shoes', 'loafers', 'brogues', 'formal shoes', 'leather shoes', 'men footwear', 'corporate shoes', 'office shoes'],
    'fashion_men-sneakers-synonyms' => ['canvas', 'trainers', 'sports shoes', 'running shoes', 'casual shoes', 'gym shoes', 'tennis shoes', 'kicks', 'basketball shoes', 'nike', 'adidas', 'puma', 'converse', 'vans', 'snickers', 'sneackers'],
    'fashion_men-cap-synonyms' => ['hat', 'baseball cap', 'snapback', 'fitted cap', 'bucket hat', 'beanie', 'face cap', 'trucker hat', 'golf cap', 'men cap'],
    'fashion_men-belt-synonyms' => ['leather belt', 'men belt', 'trouser belt', 'waist belt', 'designer belt', 'casual belt', 'formal belt', 'canvas belt'],
    'fashion_men-native-wear-synonyms' => ['agbada', 'babariga', 'senator', 'kaftan', 'dashiki', 'ankara shirt', 'yoruba attire', 'igbo attire', 'hausa attire', 'native attire', 'traditional wear', 'guinea brocade', 'aso-oke top'],
    'fashion_men-underwear-synonyms' => ['boxers', 'briefs', 'boxer briefs', 'underpants', 'pants', 'singlet', 'vest', 'inner wear', 'men underwear', 'undershirt'],
    'fashion_men-watch-synonyms' => ['wristwatch', 'men watch', 'analog watch', 'digital watch', 'luxury watch', 'casual watch', 'smart watch', 'timepiece', 'chronograph'],
 
    // ── Fashion Women ──
    'fashion_women-dress-synonyms' => ['gown', 'frock', 'maxi dress', 'midi dress', 'mini dress', 'party dress', 'office dress', 'casual dress', 'ankara dress', 'lace dress', 'evening dress', 'cocktail dress', 'bodycon dress', 'shift dress', 'wrapper'],
    'fashion_women-blouse-synonyms' => ['top', 'women top', 'ladies top', 'chiffon top', 'peplum top', 'off shoulder top', 'crop top', 'women blouse', 'ladies blouse', 'work top', 'cami top'],
    'fashion_women-skirt-synonyms' => ['pencil skirt', 'maxi skirt', 'midi skirt', 'mini skirt', 'flared skirt', 'a-line skirt', 'ankara skirt', 'pleated skirt', 'wrap skirt'],
    'fashion_women-heels-synonyms' => ['high heels', 'stilettos', 'pumps', 'wedge heels', 'block heels', 'platform heels', 'court shoes', 'pointed heels', 'kitten heels', 'women heels', 'ladies heels'],
    'fashion_women-bag-synonyms' => ['handbag', 'purse', 'shoulder bag', 'tote bag', 'clutch bag', 'crossbody bag', 'backpack', 'mini bag', 'ladies bag', 'women bag', 'sling bag', 'hand bag'],
    'fashion_women-wig-synonyms' => ['human hair', 'lace wig', 'lace front', 'frontal', 'closure', 'hair extensions', 'weave', 'hair weave', 'synthetic wig', 'bob wig', 'straight hair', 'curly hair', 'kinky hair', 'braids', 'weavon'],
    'fashion_women-lingerie-synonyms' => ['bra', 'panty', 'underwear', 'panties', 'brassiere', 'women underwear', 'ladies underwear', 'inner wear', 'intimates', 'nightgown', 'nightwear', 'sleepwear'],
    'fashion_women-ankara-synonyms' => ['african print', 'african fabric', 'kente', 'wax print', 'chitenge', 'ankara fabric', 'african attire', 'aso-ebi', 'lace fabric', 'george fabric', 'native attire', 'traditional attire'],
    'fashion_women-jewellery-synonyms' => ['jewelry', 'necklace', 'bracelet', 'earring', 'ring', 'anklet', 'bangles', 'chain', 'gold jewellery', 'silver jewellery', 'fashion jewellery', 'adornment'],
    'fashion_women-sandals-synonyms' => ['women sandals', 'ladies sandals', 'flat sandals', 'flip flops', 'slides', 'slippers', 'mules', 'strappy sandals', 'beach sandals'],
 
    // ── Beauty ──
    'beauty-skincare-synonyms' => ['face cream', 'moisturizer', 'serum', 'face wash', 'cleanser', 'toner', 'sunscreen', 'spf', 'skin cream', 'body cream', 'lotion', 'face lotion', 'brightening cream', 'skin lightening', 'glow cream', 'anti-aging cream'],
    'beauty-hair-product-synonyms' => ['hair cream', 'hair oil', 'hair serum', 'hair relaxer', 'hair dye', 'shampoo', 'conditioner', 'hair mask', 'edge control', 'hair gel', 'hair spray', 'hair growth oil', 'scalp treatment'],
    'beauty-makeup-synonyms' => ['foundation', 'powder', 'lipstick', 'lip gloss', 'mascara', 'eyeliner', 'eyeshadow', 'blush', 'highlighter', 'contour', 'bb cream', 'cc cream', 'primer', 'concealer', 'setting spray', 'makeover'],
    'beauty-perfume-synonyms' => ['fragrance', 'cologne', 'body spray', 'eau de parfum', 'edp', 'edt', 'eau de toilette', 'scent', 'deodorant spray', 'body mist', 'arabian perfume', 'oud', 'perfum'],
    'beauty-deodorant-synonyms' => ['antiperspirant', 'roll on', 'roll-on', 'body spray', 'underarm spray', 'deo', 'anti-perspirant', 'armpit spray'],
    'beauty-body-lotion-synonyms' => ['body cream', 'moisturizer', 'skin lotion', 'body butter', 'shea butter', 'cocoa butter', 'body oil', 'skin moisturizer', 'after bath lotion'],
    'beauty-nail-care-synonyms' => ['nail polish', 'nail varnish', 'nail art', 'gel nails', 'acrylic nails', 'nail kit', 'nail file', 'nail clipper', 'cuticle oil', 'nail treatment'],
    'beauty-face-mask-synonyms' => ['sheet mask', 'clay mask', 'peel off mask', 'sleeping mask', 'facial mask', 'charcoal mask', 'mud mask', 'face pack'],
    'beauty-lip-care-synonyms' => ['lip balm', 'chapstick', 'lip butter', 'lip treatment', 'lip gloss', 'lip oil', 'vaseline lip', 'lip moisturizer'],
 
    // ── Food ──
    'food-rice-synonyms' => ['long grain rice', 'parboiled rice', 'basmati rice', 'ofada rice', 'brown rice', 'white rice', 'jollof rice', 'local rice', 'foreign rice', 'rice grain', 'bag of rice', '50kg rice', '25kg rice'],
    'food-cooking-oil-synonyms' => ['vegetable oil', 'palm oil', 'groundnut oil', 'sunflower oil', 'olive oil', 'canola oil', 'coconut oil', 'frying oil', 'red oil', 'palm kernel oil'],
    'food-seasoning-synonyms' => ['maggi', 'knorr', 'royco', 'seasoning cube', 'stock cube', 'bouillon cube', 'curry powder', 'thyme', 'pepper', 'suya spice', 'egusi', 'crayfish', 'uziza', 'ogiri'],
    'food-noodles-synonyms' => ['indomie', 'pasta', 'spaghetti', 'noodle', 'instant noodles', 'cup noodles', 'macaroni', 'penne', 'fettuccine', 'vermicelli', 'golden morn noodles'],
    'food-beverages-synonyms' => ['juice', 'soft drink', 'mineral water', 'energy drink', 'malt', 'milk', 'ovaltine', 'milo', 'bournvita', 'horlicks', 'tea', 'coffee', 'zobo', 'kunu', 'chapman'],
    'food-snacks-synonyms' => ['biscuit', 'cracker', 'chin chin', 'puff puff', 'cake', 'cookies', 'wafer', 'crisps', 'popcorn', 'groundnut', 'peanuts', 'cashew nuts', 'chips', 'pringles'],
    'food-canned-food-synonyms' => ['tin tomatoes', 'tomato paste', 'sardines', 'tuna', 'corned beef', 'baked beans', 'sweet corn', 'canned beans', 'tinned fish', 'tin food'],
    'food-flour-synonyms' => ['wheat flour', 'semolina', 'cornmeal', 'garri', 'fufu', 'pounded yam flour', 'oat flour', 'bread flour', 'all purpose flour', 'amala flour'],
    'food-sugar-synonyms' => ['white sugar', 'brown sugar', 'raw sugar', 'caster sugar', 'icing sugar', 'granulated sugar', 'sweetener', 'honey'],
 
    // ── Furniture ──
    'furniture-sofa-synonyms' => ['couch', 'settee', 'loveseat', '3 seater sofa', '2 seater sofa', 'corner sofa', 'l-shaped sofa', 'sectional sofa', 'fabric sofa', 'leather sofa', 'sofa set', 'living room chair', 'center sofa'],
    'furniture-bed-synonyms' => ['bed frame', 'double bed', 'queen bed', 'king bed', 'single bed', 'bunk bed', 'divan bed', 'storage bed', 'wooden bed', 'metal bed', 'bed base', 'bedframe'],
    'furniture-mattress-synonyms' => ['foam mattress', 'spring mattress', 'memory foam', 'orthopaedic mattress', 'king size mattress', 'queen size mattress', 'single mattress', 'mattres', 'matrass', 'dunlop foam'],
    'furniture-wardrobe-synonyms' => ['closet', 'clothes cabinet', 'armoire', 'sliding wardrobe', 'fitted wardrobe', 'wooden wardrobe', 'bedroom cabinet', 'standing wardrobe', 'cloth cabinet'],
    'furniture-dining-set-synonyms' => ['dining table', 'dining chairs', '4 seater dining', '6 seater dining', 'dining room set', 'kitchen table', 'breakfast table', 'dining furniture'],
    'furniture-office-chair-synonyms' => ['desk chair', 'computer chair', 'swivel chair', 'ergonomic chair', 'executive chair', 'task chair', 'study chair', 'gaming chair', 'work chair'],
    'furniture-table-synonyms' => ['center table', 'coffee table', 'side table', 'console table', 'study table', 'writing desk', 'computer desk', 'bedside table', 'end table', 'hall table'],
    'furniture-curtains-synonyms' => ['window curtain', 'drapes', 'blinds', 'voile', 'sheer curtain', 'blackout curtain', 'roller blind', 'venetian blind', 'curtain panel', 'window treatment'],
    'furniture-rug-synonyms' => ['carpet', 'floor mat', 'area rug', 'door mat', 'centre rug', 'living room rug', 'turkish rug', 'persian rug', 'shaggy rug', 'bedside rug'],
 
    // ── Phone Accessories ──
    'phone_accessories-phone-case-synonyms' => ['back cover', 'phone cover', 'protective case', 'silicone case', 'clear case', 'leather case', 'flip case', 'wallet case', 'armor case', 'shockproof case', 'phone protection'],
    'phone_accessories-screen-protector-synonyms' => ['tempered glass', 'screen guard', 'glass protector', 'privacy screen', 'anti-glare screen', 'screen protector glass', 'phone screen guard', 'lcd protector'],
    'phone_accessories-power-bank-synonyms' => ['portable charger', 'power pack', 'backup battery', 'mobile power', 'travel charger', 'pawa bank', '20000mah', '10000mah', 'fast charging power bank'],
    'phone_accessories-earphones-synonyms' => ['earbuds', 'headphones', 'headset', 'airpods', 'wireless earbuds', 'bluetooth earphones', 'in-ear headphones', 'gaming headset', 'stereo earphone', 'earfone'],
    'phone_accessories-charger-synonyms' => ['phone charger', 'fast charger', 'wall charger', 'usb charger', 'type c charger', 'lightning charger', 'wireless charger', 'car charger', 'travel adapter', '65w charger', '33w charger'],
    'phone_accessories-cable-synonyms' => ['usb cable', 'charging cable', 'data cable', 'type-c cable', 'micro usb cable', 'lightning cable', '3-in-1 cable', 'braided cable', 'fast charging cable'],
    'phone_accessories-memory-card-synonyms' => ['sd card', 'microsd', 'flash card', 'storage card', 'memory stick', 'class 10 card', '256gb card', '128gb card', '64gb card', '32gb card'],
    'phone_accessories-phone-holder-synonyms' => ['phone stand', 'ring holder', 'pop socket', 'phone grip', 'desk stand', 'car phone holder', 'pop socket grip', 'popsocket', 'fidget ring'],
 
    // ── Computing ──
    'computing-printer-synonyms' => ['inkjet printer', 'laser printer', 'all in one printer', 'wireless printer', 'photo printer', 'hp printer', 'canon printer', 'epson printer', 'office printer', 'home printer'],
    'computing-monitor-synonyms' => ['computer monitor', 'pc monitor', 'led monitor', 'curved monitor', 'gaming monitor', '4k monitor', 'display screen', 'desktop monitor', '27 inch monitor', '24 inch monitor'],
    'computing-keyboard-synonyms' => ['computer keyboard', 'wireless keyboard', 'mechanical keyboard', 'gaming keyboard', 'laptop keyboard', 'bluetooth keyboard', 'usb keyboard', 'typing keyboard'],
    'computing-mouse-synonyms' => ['computer mouse', 'wireless mouse', 'gaming mouse', 'optical mouse', 'bluetooth mouse', 'usb mouse', 'silent mouse', 'ergonomic mouse'],
    'computing-hard-drive-synonyms' => ['external hard drive', 'hdd', 'ssd', 'solid state drive', 'external hdd', 'portable hard drive', 'storage drive', '1tb hard drive', '2tb hard drive', 'seagate', 'western digital'],
    'computing-flash-drive-synonyms' => ['usb drive', 'thumb drive', 'pen drive', 'usb stick', 'flash disk', 'memory stick', 'usb storage', 'data stick', '32gb flash', '64gb flash'],
    'computing-ups-synonyms' => ['uninterruptible power supply', 'backup power', 'power backup', 'battery backup', 'surge protector ups', 'offline ups', 'online ups'],
    'computing-laptop-bag-synonyms' => ['computer bag', 'laptop backpack', 'laptop sleeve', 'laptop case', 'notebook bag', 'business bag', 'laptop carrying case'],
 
    // ── Health ──
    'health-blood-pressure-monitor-synonyms' => ['bp monitor', 'bp machine', 'blood pressure machine', 'sphygmomanometer', 'digital bp monitor', 'automatic bp monitor', 'heart rate monitor', 'bp checker'],
    'health-thermometer-synonyms' => ['temperature meter', 'digital thermometer', 'forehead thermometer', 'infrared thermometer', 'ear thermometer', 'non-contact thermometer', 'baby thermometer', 'fever thermometer'],
    'health-glucometer-synonyms' => ['blood sugar monitor', 'glucose meter', 'diabetes monitor', 'blood glucose machine', 'blood sugar machine', 'sugar level monitor'],
    'health-face-mask-synonyms' => ['nose mask', 'surgical mask', 'n95 mask', 'kn95 mask', 'disposable mask', 'reusable mask', 'protective mask', 'medical mask', 'breathing mask'],
    'health-vitamins-synonyms' => ['supplements', 'multivitamin', 'vitamin c', 'vitamin d', 'zinc supplement', 'iron supplement', 'fish oil', 'omega 3', 'calcium supplement', 'health supplement'],
    'health-weighing-scale-synonyms' => ['body scale', 'bathroom scale', 'digital scale', 'weight scale', 'personal scale', 'bmi scale', 'body fat scale', 'weight machine'],
    'health-pulse-oximeter-synonyms' => ['oxygen meter', 'oxygen monitor', 'spo2 monitor', 'blood oxygen monitor', 'finger pulse oximeter', 'oxygen level checker'],
    'health-first-aid-synonyms' => ['first aid kit', 'bandage', 'plaster', 'wound care', 'antiseptic', 'cotton wool', 'gauze', 'first aid box', 'medical kit', 'emergency kit'],
 
    // ── Automotive ──
    'automotive-car-battery-synonyms' => ['battery', 'automotive battery', '12v battery', 'vehicle battery', 'car battery', 'truck battery', 'deep cycle battery'],
    'automotive-engine-oil-synonyms' => ['motor oil', 'car oil', 'lubricant', 'synthetic oil', '5w30', '10w40', 'engine lubricant', 'vehicle oil', 'transmission fluid'],
    'automotive-tyre-synonyms' => ['tire', 'car tyre', 'vehicle tyre', 'truck tyre', 'motorcycle tyre', 'tubeless tyre', 'radial tyre', 'all season tyre', 'spare tyre'],
    'automotive-car-audio-synonyms' => ['car stereo', 'car speaker', 'car radio', 'head unit', 'car amplifier', 'subwoofer', 'car sound system', 'car music', 'bluetooth car radio'],
    'automotive-dashcam-synonyms' => ['dash camera', 'car camera', 'driving recorder', 'car dvr', 'front camera', 'rear view camera', 'reverse camera', 'parking camera'],
    'automotive-car-perfume-synonyms' => ['car air freshener', 'car fragrance', 'car deodorizer', 'vehicle air freshener', 'hanging air freshener', 'vent clip freshener'],
    'automotive-car-charger-synonyms' => ['vehicle charger', 'usb car charger', 'fast car charger', 'dual car charger', 'phone car charger'],
    'automotive-seat-cover-synonyms' => ['car seat cover', 'leather seat cover', 'vehicle seat cover', 'car interior cover', 'seat protector'],
 
    // ── Sports ──
    'sports-football-synonyms' => ['soccer ball', 'football ball', 'match ball', 'training ball', 'size 5 ball', 'indoor football', 'futsal ball', 'official match ball'],
    'sports-jersey-synonyms' => ['football jersey', 'sports jersey', 'team kit', 'sports shirt', 'football shirt', 'training jersey', 'basketball jersey', 'cycling jersey'],
    'sports-football-boots-synonyms' => ['cleats', 'soccer boots', 'football shoes', 'astro boots', 'indoor boots', 'turf boots', 'fg boots', 'ag boots', 'sport boots'],
    'sports-gym-equipment-synonyms' => ['dumbbell', 'barbell', 'weight plates', 'kettlebell', 'resistance bands', 'pull up bar', 'bench press', 'home gym', 'weight set', 'exercise equipment'],
    'sports-running-shoes-synonyms' => ['jogging shoes', 'athletic shoes', 'sports shoes', 'training shoes', 'marathon shoes', 'track shoes', 'workout shoes', 'fitness shoes'],
    'sports-yoga-mat-synonyms' => ['exercise mat', 'fitness mat', 'gym mat', 'non-slip mat', 'pilates mat', 'workout mat', 'foam mat', 'stretch mat'],
    'sports-bicycle-synonyms' => ['bike', 'mountain bike', 'road bike', 'cycling bicycle', 'exercise bike', 'bmx', 'kids bike', 'folding bike', 'electric bike'],
    'sports-swimming-synonyms' => ['swimwear', 'swim suit', 'swimming costume', 'swim cap', 'swimming goggles', 'swim shorts', 'bikini', 'swimming trunk'],
 
    // ── Baby ──
    'baby-diapers-synonyms' => ['nappies', 'disposable diapers', 'pampers', 'huggies', 'baby nappies', 'pull-up diapers', 'overnight diapers', 'newborn diapers', 'diaper pants', 'baby diapers'],
    'baby-baby-food-synonyms' => ['infant formula', 'baby milk', 'breast milk substitute', 'nan', 'aptamil', 'sma', 'baby cereal', 'pap', 'akamu', 'ogi', 'baby porridge', 'weaning food'],
    'baby-pram-synonyms' => ['stroller', 'baby stroller', 'pushchair', 'baby carriage', 'pram stroller', 'travel system', 'buggy', 'baby trolley'],
    'baby-feeding-bottle-synonyms' => ['baby bottle', 'nursing bottle', 'infant bottle', 'sippy cup', 'anti-colic bottle', 'glass bottle', 'avent bottle', 'tommee tippee'],
    'baby-baby-carrier-synonyms' => ['baby wrap', 'baby sling', 'kangaroo carrier', 'ergonomic carrier', 'baby backpack carrier', 'hip seat carrier'],
    'baby-cot-synonyms' => ['baby crib', 'crib', 'moses basket', 'bassinet', 'baby bed', 'infant bed', 'rocking cot', 'portable cot', 'travel cot'],
    'baby-baby-wipes-synonyms' => ['wet wipes', 'baby tissues', 'cleansing wipes', 'diaper wipes', 'sensitive wipes', 'alcohol free wipes', 'waterwipes'],
    'baby-toy-synonyms' => ['baby toy', 'infant toy', 'toddler toy', 'educational toy', 'teether', 'rattle', 'soft toy', 'stuffed animal', 'plush toy', 'learning toy', 'fidget toy'],
 
    // ── Solar Power ──
    'solar_power-solar-panel-synonyms' => ['photovoltaic panel', 'pv panel', 'solar plate', 'solar module', 'monocrystalline panel', 'polycrystalline panel', '400w solar panel', '200w solar panel', 'solar electricity'],
    'solar_power-inverter-synonyms' => ['solar inverter', 'power inverter', 'hybrid inverter', 'pure sine wave inverter', 'modified sine wave', 'mppt inverter', 'off grid inverter', 'grid tie inverter'],
    'solar_power-battery-storage-synonyms' => ['solar battery', 'lithium battery', 'deep cycle battery', 'lead acid battery', 'lifepo4 battery', 'storage battery', 'backup battery', '100ah battery', '200ah battery'],
    'solar_power-stabilizer-synonyms' => ['voltage stabilizer', 'automatic voltage regulator', 'avr', 'voltage regulator', 'power stabilizer', 'current stabilizer', 'servo stabilizer'],
    'solar_power-solar-light-synonyms' => ['outdoor solar light', 'garden solar light', 'street light', 'solar bulb', 'solar flood light', 'motion sensor light', 'solar security light'],
    'solar_power-extension-board-synonyms' => ['extension cord', 'power strip', 'multiple socket', 'trailing socket', 'extension lead', 'multi-plug', 'power extension', '4-way socket'],
 
    // ── Security ──
    'security-cctv-synonyms' => ['security camera', 'surveillance camera', 'ip camera', 'outdoor camera', 'indoor camera', 'night vision camera', 'wifi camera', '4k camera', 'bullet camera', 'dome camera', 'cctv camera', 'nvr camera'],
    'security-door-lock-synonyms' => ['smart lock', 'electronic lock', 'digital lock', 'fingerprint lock', 'biometric lock', 'keypad lock', 'deadbolt', 'padlock', 'door knob lock', 'security lock'],
    'security-alarm-synonyms' => ['burglar alarm', 'security alarm', 'motion sensor alarm', 'door alarm', 'window alarm', 'home alarm system', 'intruder alarm', 'security siren'],
    'security-safe-synonyms' => ['security safe', 'fireproof safe', 'digital safe', 'cash safe', 'document safe', 'gun safe', 'hotel safe', 'wall safe', 'floor safe'],
    'security-electric-fence-synonyms' => ['perimeter fence', 'electric fencing', 'solar electric fence', 'security fence', 'electrified fence', 'fence energizer'],
    'security-access-control-synonyms' => ['biometric machine', 'attendance machine', 'fingerprint scanner', 'face recognition', 'access card', 'rfid system', 'time attendance'],
 
    // ── Agriculture ──
    'agriculture-fertilizer-synonyms' => ['npk fertilizer', 'urea fertilizer', 'organic fertilizer', 'compost', 'manure', 'plant food', 'soil conditioner', 'foliar fertilizer', 'basal fertilizer', 'top dressing'],
    'agriculture-seeds-synonyms' => ['crop seeds', 'vegetable seeds', 'maize seeds', 'tomato seeds', 'pepper seeds', 'hybrid seeds', 'improved seeds', 'planting seeds', 'farm seeds'],
    'agriculture-pesticide-synonyms' => ['insecticide', 'herbicide', 'fungicide', 'weedicide', 'pest control', 'crop protection', 'farm spray', 'agrochemical', 'weed killer'],
    'agriculture-farming-tools-synonyms' => ['hoe', 'cutlass', 'machete', 'shovel', 'rake', 'trowel', 'watering can', 'wheelbarrow', 'hand tiller', 'plough'],
    'agriculture-poultry-equipment-synonyms' => ['poultry cage', 'chicken cage', 'layer cage', 'broiler cage', 'incubator', 'egg incubator', 'poultry feeder', 'poultry drinker', 'brooding equipment'],
    'agriculture-irrigation-synonyms' => ['drip irrigation', 'sprinkler', 'water pump', 'irrigation hose', 'soaker hose', 'irrigation system', 'borehole pump', 'submersible pump'],
 
    // ── Networking ──
    'networking-wifi-router-synonyms' => ['router', 'wireless router', 'wifi modem', 'internet router', 'mesh router', '4g router', '5g router', 'tp-link', 'netgear', 'dlink', 'ubiquiti'],
    'networking-smart-bulb-synonyms' => ['led bulb', 'smart light', 'wifi bulb', 'rgb bulb', 'color changing bulb', 'energy saving bulb', 'tuya bulb', 'smart lighting', 'led lamp'],
    'networking-smart-plug-synonyms' => ['wifi plug', 'smart socket', 'smart switch', 'remote control socket', 'power monitor plug', 'automation plug', 'alexa plug', 'google home plug'],
    'networking-network-cable-synonyms' => ['ethernet cable', 'lan cable', 'rj45 cable', 'cat5 cable', 'cat6 cable', 'patch cable', 'network wire', 'internet cable'],
    'networking-nas-synonyms' => ['network attached storage', 'nas drive', 'home server', 'media server', 'cloud storage device', 'shared storage', 'synology', 'qnap'],
 
    // ── Gaming ──
    'gaming-playstation-synonyms' => ['ps5', 'ps4', 'ps3', 'sony playstation', 'play station', 'ps console', 'playstation 5', 'playstation 4', 'sony console'],
    'gaming-xbox-synonyms' => ['xbox series x', 'xbox series s', 'xbox one', 'microsoft xbox', 'x-box', 'xbox controller', 'xbox game pass'],
    'gaming-nintendo-synonyms' => ['nintendo switch', 'switch lite', 'switch oled', 'wii', '3ds', 'ds', 'handheld console', 'portable console'],
    'gaming-game-controller-synonyms' => ['gamepad', 'joystick', 'controller', 'wireless controller', 'bluetooth controller', 'gaming controller', 'pad', 'analog', 'analog controller'],
    'gaming-gaming-chair-synonyms' => ['racing chair', 'ergonomic gaming chair', 'pc gaming chair', 'esports chair', 'gamer chair', 'computer gaming chair'],
    'gaming-gaming-headset-synonyms' => ['gaming headphones', 'gaming audio', 'surround sound headset', 'ps headset', 'xbox headset', 'wireless gaming headset', '7.1 headset'],
    'gaming-game-voucher-synonyms' => ['psn card', 'xbox gift card', 'playstation card', 'game card', 'psn voucher', 'game credits', 'playstation network card', 'xbox live'],
    ];
 
    $total = count($synonymGroups);
    $done  = 0;
 
    foreach ($synonymGroups as $id => $words) {
        $this->addSynonym($id, $words);
        $done++;
        echo sprintf("  [%d/%d] ✓ %s\n", $done, $total, $id);
    }
 
    echo "\n  Done — {$total} synonym groups seeded.\n";
}

    // =========================================================================
    // INDEX SETTINGS
    // =========================================================================

    /**
     * Push index settings and replica configuration to Algolia.
     *
     * Run once (or after changing settings):
     *   php artisan algolia:configure
     *   php artisan scout:import "App\Models\Product"
     *
     * v4 change: setSettings(indexName, settingsRequest, forwardToReplicas)
     *            Accepts a plain array — no need to instantiate SetSettingsRequest manually.
     */
    public function configureIndex(): void
    {
        $settings = [
            'searchableAttributes' => [
                'unordered(name)',
                'model_number',
                'brand_name',
                'category_name',
                'tags,search_keywords',
                'short_description',
                'subcategory_name',
            ],
            'attributesForFaceting' => [
                'filterOnly(is_active)',
                'filterOnly(in_stock)',
                'filterOnly(on_sale)',
                'filterOnly(is_featured)',
                'category_slug',
                'brand_slug',
                'condition',
                'target_audience',
                'filterOnly(shop_id)',
                'filterOnly(category_id)',
                'filterOnly(brand_id)',
            ],
            'numericAttributesForFiltering' => [
                'effective_price', 'price', 'rating',
                'created_at_ts', 'popularity_score', 'sold_count', 'stock',
            ],
            'customRanking' => [
                'desc(popularity_score)',
                'desc(rating)',
                'desc(sold_count)',
            ],
            'minWordSizefor1Typo'     => 4,
            'minWordSizefor2Typos'    => 7,
            'typoTolerance'           => true,
            'queryType'               => 'prefixLast',
            'unretrievableAttributes' => ['search_vector'],
            'replicas'                => [
                $this->indexName . '_price_asc',
                $this->indexName . '_price_desc',
                $this->indexName . '_rating_desc',
                $this->indexName . '_sold_desc',
                $this->indexName . '_newest',
                $this->indexName . '_name_asc',
            ],
        ];

        // v4: setSettings(indexName, settingsAsArray, forwardToReplicas)
        $this->client->setSettings(
            indexName:        $this->indexName,
            indexSettings:    $settings,
            forwardToReplicas: true
        );

        echo "  ✓ Primary index settings applied.\n";

        // Configure custom ranking on each replica.
        $replicaSettings = [
            $this->indexName . '_price_asc'   => ['asc(effective_price)', 'desc(popularity_score)'],
            $this->indexName . '_price_desc'  => ['desc(effective_price)', 'desc(popularity_score)'],
            $this->indexName . '_rating_desc' => ['desc(rating)', 'desc(review_count)'],
            $this->indexName . '_sold_desc'   => ['desc(sold_count)'],
            $this->indexName . '_newest'      => ['desc(created_at_ts)'],
            $this->indexName . '_name_asc'    => ['asc(name)'],
        ];

        foreach ($replicaSettings as $replicaName => $replicaRanking) {
            $this->client->setSettings(
                indexName:     $replicaName,
                indexSettings: ['customRanking' => $replicaRanking]
            );
            echo "  ✓ Replica configured: {$replicaName}\n";
        }

        echo "  ✓ All done.\n";
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    /**
     * Map sort_by param → correct Algolia index name.
     * Algolia changes sort order by querying a different (replica) index.
     */
    private function resolveIndex(string $sortBy): string
    {
        return match ($sortBy) {
            'price_low'  => $this->indexName . '_price_asc',
            'price_high' => $this->indexName . '_price_desc',
            'rating'     => $this->indexName . '_rating_desc',
            'bestseller' => $this->indexName . '_sold_desc',
            'newest'     => $this->indexName . '_newest',
            'name'       => $this->indexName . '_name_asc',
            default      => $this->indexName,
        };
    }

    /**
     * Map Algolia SearchResponse → same shape ProductListingService expects.
     * v4 returns objects implementing ArrayAccess, so ['key'] syntax works fine.
     */
    private function formatSearchResult(mixed $result, int $ms): array
    {
        $hits = array_map(function ($hit) {
            // Ensure $hit is a plain array (v4 may return an object)
            $hit = is_array($hit) ? $hit : (array) $hit;

            // Map objectID → id to match Typesense shape
            $hit['document'] = array_merge($hit, ['id' => $hit['objectID'] ?? null]);

            // Normalise highlight to same structure Typesense produced
            if (isset($hit['_highlightResult']['name']['value'])) {
                $hit['highlights'] = [
                    ['field' => 'name', 'snippet' => $hit['_highlightResult']['name']['value']]
                ];
            }
            return $hit;
        }, (array) ($result['hits'] ?? []));

        return [
            'hits'    => $hits,
            'total'   => $result['nbHits']  ?? 0,
            'facets'  => $this->parseFacets((array) ($result['facets'] ?? [])),
            'took_ms' => $ms,
            'page'    => ($result['page'] ?? 0) + 1,  // back to 1-based
        ];
    }

    /**
     * Algolia returns facets as { "category_slug": { "electronics": 42, ... } }
     * Normalise to the same shape TypesenseSearchService returned.
     */
    private function parseFacets(array $algoliaFacets): array
    {
        $facets = [];
        foreach ($algoliaFacets as $fieldName => $counts) {
            $facets[$fieldName] = array_map(
                fn($value, $count) => ['value' => $value, 'count' => $count],
                array_keys((array) $counts),
                array_values((array) $counts)
            );
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