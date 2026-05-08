<?php

namespace App\Services;

use App\Models\Brand;
use Illuminate\Support\Str;

class BrandService
{
    /**
     * Similarity threshold (0–100). Matches scoring ABOVE this are considered
     * "too similar" to an existing brand and will be flagged.
     *
     * 80 → catches "Nike" vs "Nike", "NIKE", "Nikee", "N1ke"
     * 70 → also catches "Adiddas" vs "Adidas"
     * Tune down if too aggressive for your catalogue.
     */
    private const SIMILARITY_THRESHOLD = 72;

    // -------------------------------------------------------------------------
    // PUBLIC API
    // -------------------------------------------------------------------------

    /**
     * Normalize a raw brand name submitted by a seller.
     *
     * Rules applied (in order):
     *  1. Trim surrounding whitespace and collapse internal runs of spaces.
     *  2. Strip non-printable / non-ASCII control characters.
     *  3. Remove characters that are not letters, digits, spaces, hyphens,
     *     ampersands, dots, or apostrophes (the set that legitimately appears in
     *     brand names).
     *  4. Convert to Title Case — but preserve known all-caps abbreviations
     *     (e.g. "LG", "HP", "BMW", "ASUS").
     *  5. Collapse any whitespace introduced by the cleaning steps.
     */
    public function normalize(string $raw): string
    {
        // 1 & 2 — strip control chars, trim
        $name = preg_replace('/[\x00-\x1F\x7F]/u', '', $raw);
        $name = trim($name);

        // 3 — allow only safe characters
        $name = preg_replace("/[^\p{L}\p{N}\s\-&.'']/u", '', $name);

        // 4 — Title Case with abbreviation preservation
        $name = $this->toSmartTitleCase($name);

        // 5 — collapse internal whitespace
        $name = preg_replace('/\s+/', ' ', $name);

        return trim($name);
    }

    /**
     * Search existing brands for near-duplicates of $candidateName.
     *
     * Returns an array of Brand models whose normalised names are "too similar"
     * to the candidate, ordered by descending similarity score.
     *
     * An empty array means the candidate is safe to create.
     *
     * @return Brand[]
     */
    public function findSimilar(string $candidateName): array
    {
        $candidate  = $this->normalizeForComparison($candidateName);
        $allBrands  = Brand::select('id', 'name', 'slug')->get();
        $matches    = [];

        foreach ($allBrands as $brand) {
            $existing = $this->normalizeForComparison($brand->name);
            $score    = $this->computeSimilarity($candidate, $existing);

            if ($score >= self::SIMILARITY_THRESHOLD) {
                $matches[] = [
                    'brand' => $brand,
                    'score' => $score,
                ];
            }
        }

        // Sort descending by score so the closest match comes first
        usort($matches, fn($a, $b) => $b['score'] <=> $a['score']);

        return array_map(fn($m) => $m['brand'], $matches);
    }

    /**
     * Attempt to create a new brand after normalizing and checking for
     * near-duplicates.
     *
     * Returns one of:
     *   ['status' => 'created',    'brand' => Brand]
     *   ['status' => 'exists',     'brand' => Brand]   ← exact slug match
     *   ['status' => 'similar',    'brands' => Brand[]] ← fuzzy matches found
     *
     * The caller decides how to present 'similar' results to the user.
     * Pass $force = true to bypass the similarity check (user confirmed intent).
     */
    public function createBrand(string $rawName, bool $force = false): array
    {
        $normalized = $this->normalize($rawName);
        $slug       = Str::slug($normalized);

        // 1. Exact slug match — return the existing brand silently
        $existing = Brand::where('slug', $slug)->first();
        if ($existing) {
            return ['status' => 'exists', 'brand' => $existing];
        }

        // 2. Fuzzy duplicate check (unless user explicitly confirmed)
        if (!$force) {
            $similar = $this->findSimilar($normalized);
            if (!empty($similar)) {
                return ['status' => 'similar', 'brands' => $similar];
            }
        }

        // 3. Safe to create
        $brand = Brand::create(['name' => $normalized]);

        return ['status' => 'created', 'brand' => $brand];
    }

    // -------------------------------------------------------------------------
    // PRIVATE HELPERS
    // -------------------------------------------------------------------------

    /**
     * Title-Case that preserves known abbreviations (all-uppercase short tokens).
     *
     * Heuristic: if a word is 1–4 chars and entirely uppercase in the ORIGINAL
     * input, keep it uppercase (LG, HP, BMW, ASUS, etc.).
     * Everything else gets ucfirst(strtolower()).
     */
    private function toSmartTitleCase(string $name): string
    {
        $words = explode(' ', $name);

        return implode(' ', array_map(function (string $word) {
            // Preserve short all-caps tokens (abbreviations)
            if (strlen($word) <= 4 && $word === strtoupper($word) && ctype_alpha($word)) {
                return $word;
            }
            // Preserve tokens that look like model numbers (mixed alpha+digit, e.g. "S21")
            if (preg_match('/^[A-Z0-9\-]+$/', $word) && preg_match('/\d/', $word)) {
                return $word;
            }
            return ucfirst(strtolower($word));
        }, $words));
    }

    /**
     * Flatten a brand name to a bare comparison string:
     * lowercase, no spaces, no punctuation, no diacritics.
     * This is used ONLY internally for similarity scoring.
     */
    private function normalizeForComparison(string $name): string
    {
        // Transliterate diacritics (é → e, ü → u, etc.)
        $name = transliterator_transliterate('Any-Latin; Latin-ASCII', $name) ?? $name;
        $name = strtolower($name);
        // Strip everything except alphanumeric
        $name = preg_replace('/[^a-z0-9]/', '', $name);
        return $name;
    }

    /**
     * Composite similarity score (0–100) combining:
     *   - similar_text() percentage  (weight 0.5)
     *   - Levenshtein-based score    (weight 0.5)
     *
     * Using two algorithms makes the score robust across different error types:
     * similar_text catches transpositions well; Levenshtein catches insertions /
     * deletions better.
     */
    private function computeSimilarity(string $a, string $b): float
    {
        if ($a === $b) return 100.0;
        if ($a === '' || $b === '') return 0.0;

        // similar_text percentage
        similar_text($a, $b, $stPct);

        // Levenshtein-based percentage
        $maxLen  = max(strlen($a), strlen($b));
        $levDist = levenshtein($a, $b);
        $levPct  = (1 - ($levDist / $maxLen)) * 100;

        return ($stPct * 0.5) + ($levPct * 0.5);
    }
}