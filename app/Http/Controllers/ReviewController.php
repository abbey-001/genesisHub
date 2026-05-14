<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ReviewImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ReviewController extends Controller
{
    /**
     * Check if user can review a specific product
     */
    public function checkEligibility(Request $request, $productId)
    {
        $user = auth()->user();
        
        // Find delivered orders containing this product
        $eligibleItems = OrderItem::whereHas('order', function($q) use ($user) {
            $q->where('user_id', $user->id)
              ->where('status', 'delivered')
              ->where('updated_at', '>=', now()->subDays(config('reviews.review_window_days', 90)));
        })
        ->where('product_id', $productId)
        ->whereDoesntHave('reviews', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })
        ->with('order')
        ->get();
        
        return response()->json([
            'can_review' => $eligibleItems->isNotEmpty(),
            'eligible_items' => $eligibleItems,
            'message' => $eligibleItems->isEmpty() 
                ? 'You can only review products you have purchased and received.'
                : 'You can submit a review for this product.'
        ]);
    }
    
    /**
     * Submit a new review
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'order_id' => 'required|exists:orders,id',
            'order_item_id' => 'required|exists:order_items,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|min:' . config('reviews.min_comment_length', 10) . '|max:' . config('reviews.max_comment_length', 1000),
            'images.*' => 'nullable|image|max:' . config('reviews.max_image_size', 2048),
        ]);
        
        $user = auth()->user();
        
        try {
            DB::beginTransaction();
            
            // Verify eligibility
            $orderItem = OrderItem::with('order')->findOrFail($validated['order_item_id']);
            
            // Security checks
            if ($orderItem->order->user_id !== $user->id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            
            if ($orderItem->order->status !== 'delivered') {
                return response()->json(['error' => 'Can only review delivered orders'], 400);
            }
            
            // Check delivery date is within review window
            $reviewWindowDays = config('reviews.review_window_days', 90);
            if ($orderItem->order->updated_at < now()->subDays($reviewWindowDays)) {
                return response()->json([
                    'error' => "Review window has expired. You can only review products within {$reviewWindowDays} days of delivery."
                ], 400);
            }
            
            // Check for duplicate review
            $existingReview = Review::where('user_id', $user->id)
                ->where('product_id', $validated['product_id'])
                ->where('order_item_id', $validated['order_item_id'])
                ->first();
            
            if ($existingReview) {
                return response()->json(['error' => 'You have already reviewed this product from this order'], 400);
            }
            
            // Create review
            $review = Review::create([
                'product_id' => $validated['product_id'],
                'user_id' => $user->id,
                'order_id' => $validated['order_id'],
                'order_item_id' => $validated['order_item_id'],
                'rating' => $validated['rating'],
                'comment' => $validated['comment'],
                'is_verified_purchase' => true,
                'is_approved' => false,
                'status' => 'pending',
            ]);
            
            // Handle image uploads
            if ($request->hasFile('images')) {
                $images = $request->file('images');
                $maxImages = config('reviews.max_images', 5);
                $uploadedCount = 0;
                
                foreach ($images as $image) {
                    if ($uploadedCount >= $maxImages) {
                        break;
                    }
                    
                    $path = $image->store('reviews', 'public');
                    ReviewImage::create([
                        'review_id' => $review->id,
                        'image_path' => $path,
                    ]);
                    
                    $uploadedCount++;
                }
            }
            
            DB::commit();
            
            try {
                app(\App\Services\Telegram\AdminTelegramService::class)
                    ->notifyNewReviewPending($review->loadMissing(['product.shop', 'user']));
            } catch (\Exception $e) {
                Log::warning('Admin Telegram review alert failed', [
                    'review_id' => $review->id,
                    'error'     => $e->getMessage(),
                ]);
            }
            
            Log::info('Review submitted successfully', [
                'review_id' => $review->id,
                'user_id' => $user->id,
                'product_id' => $validated['product_id']
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Thank you! Your review has been submitted and is pending approval.',
                'review' => $review->load('images')
            ], 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Review submission failed', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'product_id' => $validated['product_id']
            ]);
            
            return response()->json([
                'error' => 'Failed to submit review. Please try again.'
            ], 500);
        }
    }
    
    /**
     * Get user's reviews
     */
    public function myReviews(Request $request)
    {
        $user = auth()->user();
        
        $query = Review::where('user_id', $user->id)
            ->with(['product', 'images', 'orderItem.order']);
        
        // Filter by status
        if ($request->status) {
            $query->where('status', $request->status);
        }
        
        // Sort
        $sortBy = $request->get('sort', 'latest');
        switch ($sortBy) {
            case 'oldest':
                $query->oldest();
                break;
            case 'highest_rated':
                $query->orderByDesc('rating');
                break;
            case 'lowest_rated':
                $query->orderBy('rating');
                break;
            default:
                $query->latest();
        }
        
        $reviews = $query->paginate(10);
        
        return response()->json($reviews);
    }
    
    /**
     * Update review (only pending reviews within edit window)
     */
    public function update(Request $request, Review $review)
    {
        $user = auth()->user();
        
        if (!$review->canBeEditedByUser($user->id)) {
            return response()->json([
                'error' => 'Cannot edit this review. Reviews can only be edited within ' . 
                          config('reviews.edit_window_hours', 24) . ' hours of submission and while pending approval.'
            ], 403);
        }
        
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|min:' . config('reviews.min_comment_length', 10) . '|max:' . config('reviews.max_comment_length', 1000),
        ]);
        
        $review->update($validated);
        
        Log::info('Review updated', [
            'review_id' => $review->id,
            'user_id' => $user->id
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Review updated successfully',
            'review' => $review->load('images')
        ]);
    }
    
    /**
     * Mark review as helpful or not helpful
     */
    public function markHelpful(Request $request, Review $review)
    {
        $user = auth()->user();
        
        $validated = $request->validate([
            'helpful' => 'required|boolean',
        ]);
        
        $voteType = $validated['helpful'] ? 'helpful' : 'not_helpful';
        
        try {
            // Update or create vote
            $vote = $review->helpfulVotes()->updateOrCreate(
                ['user_id' => $user->id],
                ['vote_type' => $voteType]
            );
            
            // Recalculate counts
            $review->updateVoteCounts();
            
            return response()->json([
                'success' => true,
                'helpful_count' => $review->helpful_count,
                'not_helpful_count' => $review->not_helpful_count,
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to record helpful vote', [
                'error' => $e->getMessage(),
                'review_id' => $review->id,
                'user_id' => $user->id
            ]);
            
            return response()->json([
                'error' => 'Failed to record vote. Please try again.'
            ], 500);
        }
    }
    
    /**
     * Delete review (only user's own pending reviews)
     */
    public function destroy(Review $review)
    {
        $user = auth()->user();
        
        if ($review->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        if ($review->status !== 'pending') {
            return response()->json(['error' => 'Cannot delete approved or rejected reviews'], 400);
        }
        
        try {
            // Delete associated images
            foreach ($review->images as $image) {
                Storage::disk('public')->delete($image->image_path);
            }
            
            $review->delete();
            
            Log::info('Review deleted by user', [
                'review_id' => $review->id,
                'user_id' => $user->id
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Review deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to delete review', [
                'error' => $e->getMessage(),
                'review_id' => $review->id,
                'user_id' => $user->id
            ]);
            
            return response()->json([
                'error' => 'Failed to delete review. Please try again.'
            ], 500);
        }
    }
}
