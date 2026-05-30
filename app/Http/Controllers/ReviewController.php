<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductReview;
use App\Models\User;
use App\Notifications\NewProductReviewNotification;
use App\Notifications\ReviewReplyNotification;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * Store a new review for a product.
     */
    public function store(Request $request, $productId)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'nullable|string|max:150',
            'comment' => 'nullable|string|max:1000',
        ]);

        $product = Product::findOrFail($productId);

        // Check if user already reviewed this product
        $existing = ProductReview::where('product_id', $productId)
            ->where('user_id', auth()->id())
            ->first();

        if ($existing) {
            // Update existing review
            $existing->update([
                'rating' => $request->rating,
                'title' => $request->title,
                'comment' => $request->comment,
            ]);

            // Reload with relationships
            $existing->load('product', 'user');

            // Notify seller and admin about the updated review
            if ($product->seller_id) {
                $seller = User::find($product->seller_id);
                if ($seller) {
                    $seller->notify(new NewProductReviewNotification($existing));
                }
            }

            if ($product->admin_id) {
                $admin = User::find($product->admin_id);
                if ($admin) {
                    $admin->notify(new NewProductReviewNotification($existing));
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Review updated successfully!',
                'review' => $existing->load('user'),
            ]);
        }

        $review = ProductReview::create([
            'product_id' => $productId,
            'user_id' => auth()->id(),
            'rating' => $request->rating,
            'title' => $request->title,
            'comment' => $request->comment,
        ]);

        $review->load('product', 'user');

        // Notify seller and admin about the new review
        if ($product->seller_id) {
            $seller = User::find($product->seller_id);
            if ($seller) {
                $seller->notify(new NewProductReviewNotification($review));
            }
        }

        if ($product->admin_id) {
            $admin = User::find($product->admin_id);
            if ($admin) {
                $admin->notify(new NewProductReviewNotification($review));
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Review submitted successfully!',
            'review' => $review->load('user'),
        ]);
    }

    /**
     * Delete a review.
     */
    public function destroy($id)
    {
        $review = ProductReview::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $review->delete();

        return response()->json([
            'success' => true,
            'message' => 'Review deleted successfully!',
        ]);
    }

    /**
     * Store a reply to a product review (Admin/Seller only).
     */
    public function storeReply(Request $request, $reviewId)
    {
        $request->validate([
            'reply' => 'required|string|max:1000',
        ]);

        $review = ProductReview::with(['product'])->findOrFail($reviewId);
        $user = auth()->user();

        // Check if user is authorized to reply (must be admin or product seller/admin)
        $isAdmin = $user->role === 'admin';
        $isSeller = $user->id === $review->product->seller_id;
        $isProductAdmin = $user->id === $review->product->admin_id;

        if (!($isAdmin || $isSeller || $isProductAdmin)) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to reply to this review.',
            ], 403);
        }

        $review->update([
            'reply' => $request->reply,
            'replied_by' => $user->id,
            'replied_at' => now(),
        ]);

        // Notify the reviewer
        $review->user->notify(new \App\Notifications\ReviewReplyNotification($review));

        return response()->json([
            'success' => true,
            'message' => 'Reply posted successfully!',
            'review' => $review->load('repliedByUser'),
        ]);
    }

    /**
     * Get reviews for a product (for admin/seller panel).
     */
    public function getProductReviews($productId)
    {
        $product = Product::findOrFail($productId);
        $user = auth()->user();

        // Check authorization
        $isAdmin = $user->role === 'admin';
        $isSeller = $user->id === $product->seller_id;
        $isProductAdmin = $user->id === $product->admin_id;

        if (!($isAdmin || $isSeller || $isProductAdmin)) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to view these reviews.',
            ], 403);
        }

        $reviews = $product->reviews()->with(['user', 'repliedByUser'])->latest()->get();

        return response()->json([
            'success' => true,
            'reviews' => $reviews,
        ]);
    }

    /**
     * Get all reviews for admin panel with buyer verification.
     */
    public function getAllReviewsForAdmin()
    {
        $user = auth()->user();

        // Only admin can access
        if ($user->role !== 'admin' && $user->role !== 'seller') {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized.',
            ], 403);
        }

        // Get reviews
        if ($user->role === 'admin') {
            // Admin sees all reviews
            $reviews = ProductReview::with(['product', 'user', 'repliedByUser'])
                ->latest()
                ->get();
        } else {
            // Seller sees only their product reviews
            $reviews = ProductReview::whereHas('product', function ($query) use ($user) {
                $query->where('seller_id', $user->id)->orWhere('admin_id', $user->id);
            })
            ->with(['product', 'user', 'repliedByUser'])
            ->latest()
            ->get();
        }

        // Add buyer verification info
        $reviews = $reviews->map(function ($review) {
            // Check if user has purchased this product by looking in orders items_json
            $hasPurchased = \App\Models\Order::where('user_id', $review->user_id)
                ->whereJsonContains('items_json', [
                    'id' => $review->product_id
                ])
                ->exists();
            
            return array_merge($review->toArray(), ['has_purchased' => $hasPurchased]);
        });

        return response()->json([
            'success' => true,
            'reviews' => $reviews->values(),
        ]);
    }
}
