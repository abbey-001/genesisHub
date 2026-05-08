<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'user_id',
        'order_id',
        'order_item_id',
        'rating',
        'comment',
        'is_verified_purchase',
        'is_approved',
        'status',
        'admin_notes',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
        'seller_response',
        'seller_responded_at',
        'helpful_count',
        'not_helpful_count'
    ];

    protected $casts = [
        'is_verified_purchase' => 'boolean',
        'is_approved' => 'boolean',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'seller_responded_at' => 'datetime',
    ];

    // ============================================
    // RELATIONSHIPS
    // ============================================

    /**
     * Get the product that owns the review
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the user who wrote the review
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the order associated with the review
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the specific order item reviewed
     */
    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    /**
     * Get the admin who approved the review
     */
    public function approvedBy()
    {
        return $this->belongsTo(Admin::class, 'approved_by');
    }

    /**
     * Get the admin who rejected the review
     */
    public function rejectedBy()
    {
        return $this->belongsTo(Admin::class, 'rejected_by');
    }

    /**
     * Get the images for the review
     */
    public function images()
    {
        return $this->hasMany(ReviewImage::class);
    }

    /**
     * Get the helpful votes for the review
     */
    public function helpfulVotes()
    {
        return $this->hasMany(ReviewHelpfulVote::class);
    }

    // ============================================
    // QUERY SCOPES
    // ============================================

    /**
     * Scope a query to only include approved reviews
     */
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true)
                     ->where('status', 'approved');
    }

    /**
     * Scope a query to only include pending reviews
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include rejected reviews
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Scope a query to reviews for a specific product
     */
    public function scopeForProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Scope a query to only verified purchase reviews
     */
    public function scopeVerifiedPurchase($query)
    {
        return $query->where('is_verified_purchase', true);
    }

    /**
     * Scope a query to reviews by rating
     */
    public function scopeByRating($query, $rating)
    {
        return $query->where('rating', $rating);
    }

    /**
     * Scope a query to reviews with seller responses
     */
    public function scopeWithSellerResponse($query)
    {
        return $query->whereNotNull('seller_response');
    }

    /**
     * Scope a query to reviews without seller responses
     */
    public function scopeWithoutSellerResponse($query)
    {
        return $query->whereNull('seller_response');
    }

    // ============================================
    // HELPER METHODS
    // ============================================

    /**
     * Approve the review
     */
    public function approve($adminId, $notes = null)
    {
        $this->update([
            'status' => 'approved',
            'is_approved' => true,
            'approved_by' => $adminId,
            'approved_at' => now(),
            'admin_notes' => $notes,
        ]);

        // Update product rating
        $this->product->recalculateRating();

        // Trigger notification to user
        // event(new ReviewApproved($this));

        return $this;
    }

    /**
     * Reject the review
     */
    public function reject($adminId, $reason)
    {
        $this->update([
            'status' => 'rejected',
            'is_approved' => false,
            'rejected_by' => $adminId,
            'rejected_at' => now(),
            'rejection_reason' => $reason,
        ]);

        // Trigger notification to user
        // event(new ReviewRejected($this));

        return $this;
    }

    /**
     * Add seller response to the review
     */
    public function addSellerResponse($response)
    {
        $this->update([
            'seller_response' => $response,
            'seller_responded_at' => now(),
        ]);

        // Trigger notification to customer
        // event(new SellerRespondedToReview($this));

        return $this;
    }

    /**
     * Check if review has a seller response
     */
    public function hasSellerResponse()
    {
        return !empty($this->seller_response);
    }

    /**
     * Check if review can be edited by user
     */
    public function canBeEditedByUser($userId)
    {
        return $this->user_id === $userId 
               && $this->status === 'pending' 
               && $this->created_at->diffInHours(now()) < 24;
    }

    /**
     * Check if user has voted on this review
     */
    public function hasUserVoted($userId)
    {
        return $this->helpfulVotes()->where('user_id', $userId)->exists();
    }

    /**
     * Get user's vote on this review
     */
    public function getUserVote($userId)
    {
        return $this->helpfulVotes()->where('user_id', $userId)->first();
    }

    /**
     * Update helpful vote counts
     */
    public function updateVoteCounts()
    {
        $this->update([
            'helpful_count' => $this->helpfulVotes()->where('vote_type', 'helpful')->count(),
            'not_helpful_count' => $this->helpfulVotes()->where('vote_type', 'not_helpful')->count(),
        ]);

        return $this;
    }

    // ============================================
    // ACCESSORS
    // ============================================

    /**
     * Get the review status badge color
     */
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
        ];

        return $badges[$this->status] ?? 'secondary';
    }

    /**
     * Get formatted rating
     */
    public function getFormattedRatingAttribute()
    {
        return number_format($this->rating, 1);
    }

    /**
     * Get star rating HTML
     */
    public function getStarRatingHtmlAttribute()
    {
        $html = '';
        for ($i = 1; $i <= 5; $i++) {
            $class = $i <= $this->rating ? 'text-warning' : 'text-muted';
            $html .= '<i class="fa fa-star ' . $class . '"></i>';
        }
        return $html;
    }

    /**
     * Check if review is from verified purchase
     */
    public function isVerifiedPurchase()
    {
        return $this->is_verified_purchase === true;
    }

    /**
     * Check if review is approved
     */
    public function isApproved()
    {
        return $this->is_approved === true && $this->status === 'approved';
    }

    /**
     * Check if review is pending
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }

    /**
     * Check if review is rejected
     */
    public function isRejected()
    {
        return $this->status === 'rejected';
    }
}