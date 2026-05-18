<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductReview;
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
}
