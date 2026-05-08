<?php

/**
 * config/typesense.php
 *
 * Typesense connection + collection schema for GenesisHub.
 * Copy this file to your Laravel config/ directory.
 *
 * ENV variables to add to your .env:
 *   TYPESENSE_API_KEY=your_admin_key_here
 *   TYPESENSE_HOST=xxx.a1.typesense.net
 *   TYPESENSE_PORT=443
 *   TYPESENSE_PROTOCOL=https
 *   SCOUT_DRIVER=typesense
 */

return [

    'api_key' => env('TYPESENSE_API_KEY', ''),

    'nodes' => [
        [
            'host'     => env('TYPESENSE_HOST', 'localhost'),
            'port'     => env('TYPESENSE_PORT', '8108'),
            'protocol' => env('TYPESENSE_PROTOCOL', 'http'),
        ],
    ],

    'nearest_node' => null,

    'connection_timeout_seconds'    => 2,
    'healthcheck_interval_seconds'  => 60,
    'num_retries'                   => 3,
    'retry_interval_seconds'        => 0.1,

    'collection_settings' => [
        // ── Product collection ──────────────────────────────────────
        'products' => [
            'schema' => [
                'name'   => 'products',
                'fields' => [
                    // Core identifiers
                    ['name' => 'id',              'type' => 'string'],
                    ['name' => 'name',            'type' => 'string'],
                    ['name' => 'slug',            'type' => 'string', 'index' => false],

                    // Searchable text fields
                    ['name' => 'short_description','type' => 'string',  'optional' => true],
                    ['name' => 'tags',             'type' => 'string',  'optional' => true],
                    ['name' => 'search_keywords',  'type' => 'string',  'optional' => true],
                    ['name' => 'model_number',     'type' => 'string',  'optional' => true],
                    ['name' => 'brand_name',       'type' => 'string',  'optional' => true],
                    ['name' => 'category_name',    'type' => 'string',  'optional' => true],
                    ['name' => 'subcategory_name', 'type' => 'string',  'optional' => true],

                    // Numeric facets / filters
                    ['name' => 'price',            'type' => 'float'],
                    ['name' => 'sale_price',       'type' => 'float',   'optional' => true],
                    ['name' => 'effective_price',  'type' => 'float'],   // COALESCE(sale_price, price)
                    ['name' => 'stock',            'type' => 'int32'],
                    ['name' => 'rating',           'type' => 'float',   'optional' => true],
                    ['name' => 'review_count',     'type' => 'int32',   'optional' => true],
                    ['name' => 'sold_count',       'type' => 'int32',   'optional' => true],

                    // Boolean facets
                    ['name' => 'is_active',        'type' => 'bool'],
                    ['name' => 'is_featured',      'type' => 'bool'],
                    ['name' => 'in_stock',         'type' => 'bool'],
                    ['name' => 'on_sale',          'type' => 'bool'],

                    // Facetable string IDs / slugs
                    ['name' => 'category_id',      'type' => 'string',  'facet' => true, 'optional' => true],
                    ['name' => 'category_slug',    'type' => 'string',  'facet' => true, 'optional' => true],
                    ['name' => 'subcategory_id',   'type' => 'string',  'facet' => true, 'optional' => true],
                    ['name' => 'brand_id',         'type' => 'string',  'facet' => true, 'optional' => true],
                    ['name' => 'brand_slug',       'type' => 'string',  'facet' => true, 'optional' => true],
                    ['name' => 'shop_id',          'type' => 'string',  'facet' => true, 'optional' => true],
                    ['name' => 'condition',        'type' => 'string',  'facet' => true, 'optional' => true],
                    ['name' => 'target_audience',  'type' => 'string',  'facet' => true, 'optional' => true],

                    // Timestamp for sort
                    ['name' => 'created_at_ts',    'type' => 'int64'],

                    // Weighted popularity score for default ranking
                    ['name' => 'popularity_score', 'type' => 'float'],

                    // Primary image URL (returned directly — avoids extra DB join)
                    ['name' => 'image_url',        'type' => 'string',  'optional' => true, 'index' => false],
                ],

                // Default sort: popularity descending (featured products naturally score higher)
                'default_sorting_field' => 'popularity_score',
            ],
        ],
    ],
];