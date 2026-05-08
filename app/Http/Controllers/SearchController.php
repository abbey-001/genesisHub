<?php

namespace App\Http\Controllers;

use App\Services\ProductListingService;
use App\Services\AlgoliaSearchService;
use Illuminate\Http\Request;

/**
 * SearchController — now powered by Algolia.
 *
 * The only change from the Typesense version:
 *  - TypesenseSearchService → AlgoliaSearchService
 *
 * Public method signatures are identical, so the rest of your app
 * (routes, views, fastsearch.js) needs zero changes.
 */
class SearchController extends Controller
{
    public function __construct(
        protected ProductListingService $listing,
        protected AlgoliaSearchService  $algolia
    ) {}

    /**
     * Autocomplete suggestions endpoint.
     * Called by fastsearch.js on every keystroke (debounced).
     *
     * GET /search/suggest?q=iphone
     */
    public function suggest(Request $request)
    {
        $query = trim($request->input('q', ''));

        if (strlen($query) < 2) {
            return response()->json(['suggestions' => []]);
        }

        $suggestions = $this->algolia->suggest($query);

        return response()->json(['suggestions' => $suggestions]);
    }

    /**
     * Full search results page.
     *
     * GET /products?search=iphone
     * GET /search?q=iphone  (redirects here)
     */
    public function index(Request $request)
    {
        $query = trim($request->input('q', $request->input('search', '')));

        if (strlen($query) < 2) {
            return redirect()->route('home');
        }

        $request->merge(['search' => $query]);
        $data = $this->listing->build($request);

        return view('products.index', $data);
    }
}