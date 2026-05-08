<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $table = 'product_variants';

    protected $fillable = [
        'product_id',
        'variant_name',
        'variant_value',
        'sku',
        'price_adjustment',
        'stock',
    ];
    
    protected $casts = [
    'price_adjustment' => 'decimal:2',
    'stock' => 'integer',
];

    /**
     * Relationship: A variant belongs to a product
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}