<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ReviewController extends Controller
{
    /**
     * Show all reviews for seller's products
     */
    public function index(Request $request)
    {
        $seller = Auth::guard('seller')->user()->seller;
        $shop = $seller->shop;
        
        if (!$shop) {
            return redirect()->route('seller.dashboard')
                ->with('error', 'Please complete your shop setup first.');
        }
        
        $query = Review::whereHas('product', function($q) use ($shop) {
            $q->where('shop_id', $shop->id);
        })
        ->with(['product', 'user', 'order', 'images']);
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter by rating
        if ($request->filled('rating')) {
            $query->where('rating', $request->rating);
        }
        
        // Filter by product
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }
        
        // Filter by responded/not responded
        if ($request->has('has_response')) {
            if ($request->has_response == '1') {
                $query->whereNotNull('seller_response');
            } else {
                $query->whereNull('seller_response');
            }
        }
        
        // Search in comment
        if ($request->filled('search')) {
            $query->where('comment', 'like', '%' . $request->search . '%');
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
            case 'most_helpful':
                $query->orderByDesc('helpful_count');
                break;
            default:
                $query->latest();
        }
        
        $reviews = $query->paginate(20);
        
        // Calculate statistics
        $stats = [
            'total' => Review::whereHas('product', fn($q) => $q->where('shop_id', $shop->id))->count(),
            'pending' => Review::whereHas('product', fn($q) => $q->where('shop_id', $shop->id))->pending()->count(),
            'approved' => Review::whereHas('product', fn($q) => $q->where('shop_id', $shop->id))->approved()->count(),
            'unanswered' => Review::whereHas('product', fn($q) => $q->where('shop_id', $shop->id))
                ->approved()
                ->whereNull('seller_response')
                ->count(),
            'average_rating' => Review::whereHas('product', fn($q) => $q->where('shop_id', $shop->id))
                ->approved()
                ->avg('rating') ?? 0,
        ];
        
        // Get products for filter dropdown
        $products = $shop->products()
            ->select('id', 'name')
            ->whereHas('reviews')
            ->get();
        
        return view('seller.reviews.index', compact('reviews', 'stats', 'products'));
    }
    
    /**
     * Respond to a review
     */
    public function respond(Request $request, Review $review)
    {
        $seller = Auth::guard('seller')->user()->seller;
        $shop = $seller->shop;
        
        // Verify ownership - check if review is for seller's product
        if ($review->product->shop_id !== $shop->id) {
            return back()->with('error', 'Unauthorized action.');
        }
        
        // Can only respond to approved reviews
        if (!$review->isApproved()) {
            return back()->with('error', 'Can only respond to approved reviews.');
        }
        
        // Check if already responded
        if ($review->hasSellerResponse()) {
            return back()->with('error', 'You have already responded to this review.');
        }
        
        $validated = $request->validate([
            'response' => [
                'required',
                'string',
                'min:10',
                'max:' . config('reviews.max_seller_response_length', 1000)
            ],
        ], [
            'response.required' => 'Please enter a response.',
            'response.min' => 'Response must be at least 10 characters.',
            'response.max' => 'Response cannot exceed ' . config('reviews.max_seller_response_length', 1000) . ' characters.',
        ]);
        
        try {
            $review->addSellerResponse($validated['response']);
            
            Log::info('Seller responded to review', [
                'review_id' => $review->id,
                'seller_id' => $seller->id,
                'shop_id' => $shop->id
            ]);
            
            // Trigger notification to customer (uncomment when implemented)
            // event(new SellerRespondedToReview($review));
            
            return back()->with('success', 'Response added successfully! Your response is now visible to customers.');
            
        } catch (\Exception $e) {
            Log::error('Failed to add seller response', [
                'error' => $e->getMessage(),
                'review_id' => $review->id,
                'seller_id' => $seller->id
            ]);
            
            return back()->with('error', 'Failed to add response. Please try again.');
        }
    }
    
    /**
     * Update seller response
     */
    public function updateResponse(Request $request, Review $review)
    {
        $seller = Auth::guard('seller')->user()->seller;
        $shop = $seller->shop;
        
        // Verify ownership
        if ($review->product->shop_id !== $shop->id) {
            return back()->with('error', 'Unauthorized action.');
        }
        
        // Check if has response to update
        if (!$review->hasSellerResponse()) {
            return back()->with('error', 'No response to update.');
        }
        
        $validated = $request->validate([
            'response' => [
                'required',
                'string',
                'min:10',
                'max:' . config('reviews.max_seller_response_length', 1000)
            ],
        ]);
        
        try {
            $review->update([
                'seller_response' => $validated['response'],
            ]);
            
            Log::info('Seller updated response', [
                'review_id' => $review->id,
                'seller_id' => $seller->id
            ]);
            
            return back()->with('success', 'Response updated successfully!');
            
        } catch (\Exception $e) {
            Log::error('Failed to update seller response', [
                'error' => $e->getMessage(),
                'review_id' => $review->id,
                'seller_id' => $seller->id
            ]);
            
            return back()->with('error', 'Failed to update response. Please try again.');
        }
    }
    
    /**
     * Delete seller response
     */
    public function deleteResponse(Review $review)
    {
        $seller = Auth::guard('seller')->user()->seller;
        $shop = $seller->shop;
        
        // Verify ownership
        if ($review->product->shop_id !== $shop->id) {
            return back()->with('error', 'Unauthorized action.');
        }
        
        // Check if has response to delete
        if (!$review->hasSellerResponse()) {
            return back()->with('error', 'No response to delete.');
        }
        
        try {
            $review->update([
                'seller_response' => null,
                'seller_responded_at' => null,
            ]);
            
            Log::info('Seller deleted response', [
                'review_id' => $review->id,
                'seller_id' => $seller->id
            ]);
            
            return back()->with('success', 'Response deleted successfully.');
            
        } catch (\Exception $e) {
            Log::error('Failed to delete seller response', [
                'error' => $e->getMessage(),
                'review_id' => $review->id,
                'seller_id' => $seller->id
            ]);
            
            return back()->with('error', 'Failed to delete response. Please try again.');
        }
    }
    
    /**
     * Get review analytics for seller
     */
    public function analytics()
    {
        $seller = Auth::guard('seller')->user()->seller;
        $shop = $seller->shop;
        
        $analytics = [
            'total_reviews' => Review::whereHas('product', fn($q) => $q->where('shop_id', $shop->id))->count(),
            'approved_reviews' => Review::whereHas('product', fn($q) => $q->where('shop_id', $shop->id))->approved()->count(),
            'average_rating' => Review::whereHas('product', fn($q) => $q->where('shop_id', $shop->id))->approved()->avg('rating'),
            'response_rate' => 0,
            'rating_breakdown' => [],
            'recent_reviews' => [],
        ];
        
        // Calculate response rate
        $approvedCount = $analytics['approved_reviews'];
        if ($approvedCount > 0) {
            $respondedCount = Review::whereHas('product', fn($q) => $q->where('shop_id', $shop->id))
                ->approved()
                ->whereNotNull('seller_response')
                ->count();
            $analytics['response_rate'] = round(($respondedCount / $approvedCount) * 100, 1);
        }
        
        // Rating breakdown
        for ($i = 5; $i >= 1; $i--) {
            $count = Review::whereHas('product', fn($q) => $q->where('shop_id', $shop->id))
                ->approved()
                ->where('rating', $i)
                ->count();
            $analytics['rating_breakdown'][$i] = $count;
        }
        
        // Recent reviews
        $analytics['recent_reviews'] = Review::whereHas('product', fn($q) => $q->where('shop_id', $shop->id))
            ->with(['product', 'user'])
            ->latest()
            ->take(10)
            ->get();
        
        return view('seller.reviews.analytics', compact('analytics'));
    }
}