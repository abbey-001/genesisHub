<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReviewHelpfulVote extends Model
{
    use HasFactory;

    protected $fillable = [
        'review_id',
        'user_id',
        'vote_type',
    ];

    /**
     * Get the review that owns the vote
     */
    public function review()
    {
        return $this->belongsTo(Review::class);
    }

    /**
     * Get the user who cast the vote
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if vote is helpful
     */
    public function isHelpful()
    {
        return $this->vote_type === 'helpful';
    }

    /**
     * Check if vote is not helpful
     */
    public function isNotHelpful()
    {
        return $this->vote_type === 'not_helpful';
    }
}