<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ReviewController extends Controller
{
    /**
     * Review management dashboard
     */
    public function index(Request $request)
    {
        $query = Review::with(['product.shop', 'user', 'order']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('rating')) {
            $query->where('rating', $request->rating);
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->has('verified_only')) {
            $query->where('is_verified_purchase', true);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', fn ($q2) => $q2->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('product', fn ($q2) => $q2->where('name', 'like', "%{$search}%"))
                  ->orWhere('comment', 'like', "%{$search}%");
            });
        }

        $sortBy = $request->get('sort', 'latest');
        switch ($sortBy) {
            case 'oldest':        $query->oldest();                  break;
            case 'highest_rated': $query->orderByDesc('rating');     break;
            case 'lowest_rated':  $query->orderBy('rating');         break;
            case 'most_helpful':  $query->orderByDesc('helpful_count'); break;
            default:              $query->latest();
        }

        $reviews = $query->paginate(20);

        $stats = [
            'total'    => Review::count(),
            'pending'  => Review::pending()->count(),
            'approved' => Review::approved()->count(),
            'rejected' => Review::rejected()->count(),
            'today'    => Review::whereDate('created_at', today())->count(),
            'this_week'=> Review::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
        ];

        return view('admin.reviews.index', compact('reviews', 'stats'));
    }

    /**
     * Show single review details
     */
    public function show(Review $review)
    {
        $review->load([
            'product.shop',
            'product.images',
            'user',
            'order',
            'orderItem',
            'images',
            'approvedBy',
            'rejectedBy',
            'helpfulVotes.user',
        ]);

        $userReviews = Review::where('user_id', $review->user_id)
            ->where('id', '!=', $review->id)
            ->with('product')
            ->latest()
            ->take(5)
            ->get();

        return view('admin.reviews.show', compact('review', 'userReviews'));
    }

    /**
     * Approve review
     */
    public function approve(Request $request, Review $review)
    {
        if ($review->isApproved()) {
            return back()->with('info', 'This review is already approved.');
        }

        $request->validate([
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            // Use the admin guard so the correct authenticated user ID is stored
            $adminId = auth()->guard('admin')->id();

            $review->approve($adminId, $request->notes);

            Log::info('Review approved by admin', [
                'review_id'  => $review->id,
                'admin_id'   => $adminId,
                'product_id' => $review->product_id,
            ]);

            return back()->with('success', 'Review approved! It is now visible on the product page.');

        } catch (\Exception $e) {
            Log::error('Failed to approve review', [
                'error'     => $e->getMessage(),
                'review_id' => $review->id,
            ]);

            return back()->with('error', 'Failed to approve review. Please try again.');
        }
    }

    /**
     * Reject review
     */
    public function reject(Request $request, Review $review)
    {
        if ($review->isRejected()) {
            return back()->with('info', 'This review is already rejected.');
        }

        $request->validate([
            'reason' => 'required|string|max:500',
        ], [
            'reason.required' => 'Please provide a reason for rejecting this review.',
        ]);

        try {
            $adminId = auth()->guard('admin')->id();

            $review->reject($adminId, $request->reason);

            Log::info('Review rejected by admin', [
                'review_id' => $review->id,
                'admin_id'  => $adminId,
                'reason'    => $request->reason,
            ]);

            return back()->with('success', 'Review rejected. The customer has been notified.');

        } catch (\Exception $e) {
            Log::error('Failed to reject review', [
                'error'     => $e->getMessage(),
                'review_id' => $review->id,
            ]);

            return back()->with('error', 'Failed to reject review. Please try again.');
        }
    }

    /**
     * Bulk approve reviews
     */
    public function bulkApprove(Request $request)
    {
        $request->validate([
            'review_ids'   => 'required|array|min:1',
            'review_ids.*' => 'exists:reviews,id',
        ], [
            'review_ids.required' => 'Please select at least one review.',
        ]);

        $adminId      = auth()->guard('admin')->id();
        $successCount = 0;
        $skippedCount = 0;

        try {
            foreach ($request->review_ids as $reviewId) {
                $review = Review::find($reviewId);

                if ($review && $review->isPending()) {
                    $review->approve($adminId);
                    $successCount++;
                } else {
                    $skippedCount++;
                }
            }

            Log::info('Bulk approve completed', [
                'admin_id'       => $adminId,
                'approved_count' => $successCount,
                'skipped_count'  => $skippedCount,
            ]);

            $message = "{$successCount} review(s) approved successfully!";
            if ($skippedCount > 0) {
                $message .= " ({$skippedCount} skipped — already approved or rejected)";
            }

            return back()->with('success', $message);

        } catch (\Exception $e) {
            Log::error('Bulk approve failed', [
                'error'    => $e->getMessage(),
                'admin_id' => $adminId,
            ]);

            return back()->with('error', 'Failed to approve reviews. Please try again.');
        }
    }

    /**
     * Delete review permanently
     */
    public function destroy(Review $review)
    {
        try {
            $productId   = $review->product_id;
            $wasApproved = $review->isApproved();

            foreach ($review->images as $image) {
                if (Storage::disk('public')->exists($image->image_path)) {
                    Storage::disk('public')->delete($image->image_path);
                }
            }

            $review->delete();

            if ($wasApproved) {
                $product = Product::find($productId);
                $product?->recalculateRating();
            }

            Log::info('Review deleted by admin', [
                'review_id'  => $review->id,
                'admin_id'   => auth()->guard('admin')->id(),
                'product_id' => $productId,
            ]);

            return redirect()
                ->route('admin.reviews.index')
                ->with('success', 'Review deleted successfully.');

        } catch (\Exception $e) {
            Log::error('Failed to delete review', [
                'error'     => $e->getMessage(),
                'review_id' => $review->id,
            ]);

            return back()->with('error', 'Failed to delete review. Please try again.');
        }
    }

    /**
     * Update review content (admin editing)
     */
    public function update(Request $request, Review $review)
    {
        $request->validate([
            'rating'      => 'required|integer|min:1|max:5',
            'comment'     => 'required|string|min:10|max:1000',
            'admin_notes' => 'nullable|string|max:500',
        ]);

        try {
            $review->update([
                'rating'      => $request->rating,
                'comment'     => $request->comment,
                'admin_notes' => $request->admin_notes,
            ]);

            if ($review->isApproved()) {
                $review->product->recalculateRating();
            }

            Log::info('Review updated by admin', [
                'review_id' => $review->id,
                'admin_id'  => auth()->guard('admin')->id(),
            ]);

            return back()->with('success', 'Review updated successfully.');

        } catch (\Exception $e) {
            Log::error('Failed to update review', [
                'error'     => $e->getMessage(),
                'review_id' => $review->id,
            ]);

            return back()->with('error', 'Failed to update review. Please try again.');
        }
    }

    /**
     * Toggle review status — approved ↔ pending
     */
    public function toggleStatus(Review $review)
    {
        $adminId = auth()->guard('admin')->id();

        try {
            if ($review->isApproved()) {
                $review->update([
                    'status'      => 'pending',
                    'is_approved' => false,
                    'approved_by' => null,
                    'approved_at' => null,
                ]);

                // Recalculate rating since an approved review was removed
                $review->product->recalculateRating();

                $message = 'Review reverted to pending.';
            } else {
                $review->approve($adminId);
                $message = 'Review approved.';
            }

            Log::info('Review status toggled', [
                'review_id'  => $review->id,
                'admin_id'   => $adminId,
                'new_status' => $review->fresh()->status,
            ]);

            return back()->with('success', $message);

        } catch (\Exception $e) {
            Log::error('Failed to toggle review status', [
                'error'     => $e->getMessage(),
                'review_id' => $review->id,
            ]);

            return back()->with('error', 'Failed to update status. Please try again.');
        }
    }

    /**
     * Export reviews to CSV
     */
    public function export(Request $request)
    {
        $query = Review::with(['product', 'user']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('rating')) {
            $query->where('rating', $request->rating);
        }

        $reviews = $query->latest()->get();

        $filename = 'reviews_' . now()->format('Y-m-d_His') . '.csv';
        $headers  = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($reviews) {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'ID', 'Date', 'Product', 'Shop', 'Customer', 'Email',
                'Rating', 'Comment', 'Status', 'Verified Purchase', 'Has Seller Response',
            ]);

            foreach ($reviews as $review) {
                fputcsv($file, [
                    $review->id,
                    $review->created_at->format('Y-m-d H:i:s'),
                    $review->product->name ?? 'N/A',
                    $review->product->shop->shop_name ?? 'N/A',
                    $review->user->name ?? 'N/A',
                    $review->user->email ?? 'N/A',
                    $review->rating,
                    $review->comment,
                    ucfirst($review->status),
                    $review->is_verified_purchase ? 'Yes' : 'No',
                    $review->hasSellerResponse() ? 'Yes' : 'No',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Review analytics
     */
    public function analytics()
    {
        $analytics = [
            'total_reviews'        => Review::count(),
            'approved'             => Review::approved()->count(),
            'pending'              => Review::pending()->count(),
            'rejected'             => Review::rejected()->count(),
            // avg() returns null when there are no rows — cast safely
            'average_rating'       => (float) (Review::approved()->avg('rating') ?? 0),
            'verified_purchases'   => Review::where('is_verified_purchase', true)->count(),
            'with_images'          => Review::has('images')->count(),
            'with_seller_response' => Review::whereNotNull('seller_response')->count(),
        ];

        // Reviews by month (last 12 months)
        $monthlyReviews = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthlyReviews[$month->format('M Y')] = Review::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();
        }

        // Rating distribution (approved reviews only)
        $ratingDistribution = [];
        for ($i = 5; $i >= 1; $i--) {
            $ratingDistribution[$i] = Review::approved()->where('rating', $i)->count();
        }

        return view('admin.reviews.analytics', compact('analytics', 'monthlyReviews', 'ratingDistribution'));
    }
}