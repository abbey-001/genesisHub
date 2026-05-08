<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReviewImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'review_id',
        'image_path',
    ];

    /**
     * Get the review that owns the image
     */
    public function review()
    {
        return $this->belongsTo(Review::class);
    }

    /**
     * Get the full URL for the image
     */
    public function getImageUrlAttribute()
    {
        return asset('storage/' . $this->image_path);
    }

    /**
     * Get the full path for the image
     */
    public function getFullPathAttribute()
    {
        return storage_path('app/public/' . $this->image_path);
    }
}