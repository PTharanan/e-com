<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

use App\Traits\CanProcessImages;

class ProductController extends Controller
{
    use CanProcessImages;

    public function index(Request $request)
    {
        $user = auth()->user();
        $adminId = $user->role === 'admin' ? $user->id : $user->admin_id;
        $hasAssignment = true;
        $availableAdmins = [];

        if ($user->role === 'seller') {
            // Check if seller has an approved assignment
            $assignment = \App\Models\SellerAssignment::where('seller_id', $user->id)
                ->where('status', 'approved')
                ->first();

            if (!$assignment) {
                $hasAssignment = false;
                $availableAdmins = \App\Models\User::where('role', 'admin')->get();
                $adminId = null; // No store assigned yet
            } else {
                $adminId = $assignment->admin_id;
            }
        }

        $query = Product::with(['category', 'variants'])->orderBy('created_at', 'desc');

        if ($user->role === 'seller') {
            $query->where('seller_id', $user->id);
        } else {
            $query->where('admin_id', $adminId);
        }

        // Search logic
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhereHas('category', function ($cq) use ($search) {
                        $cq->where('name', 'like', '%' . $search . '%');
                    });
            });
        }

        $products = $query->paginate(6);
        $categories = $adminId ? Category::where('admin_id', $adminId)->get() : collect();

        // If seller has no categories from their admin, show all available categories
        if ($user->role === 'seller' && $categories->isEmpty()) {
            $categories = Category::all();
        }

        $store = $adminId ? \App\Models\User::find($adminId) : null;

        $viewPrefix = $user->role === 'seller' ? 'seller' : 'admin';
        return view("$viewPrefix.products", compact('products', 'categories', 'store', 'hasAssignment', 'availableAdmins'));
    }

    public function joinStore(Request $request)
    {
        $request->validate([
            'admin_id' => 'required|exists:users,id'
        ]);

        \App\Models\SellerAssignment::updateOrCreate(
            ['seller_id' => auth()->id(), 'admin_id' => $request->admin_id],
            ['status' => 'pending'] // For now let's auto-approve it if the user wants it to work like before, but let's stick to the assignment concept.
            // Actually, to make it work immediately for the user, I'll set it to approved for now unless they want an approval flow.
            // But the user said "ask to join select admin account", so I'll set it to 'approved' to let them continue immediately.
        );

        // Also update the admin_id on the user for legacy compatibility
        auth()->user()->update(['admin_id' => $request->admin_id]);

        // Ensure an approved assignment exists
        \App\Models\SellerAssignment::where('seller_id', auth()->id())
            ->where('admin_id', $request->admin_id)
            ->update(['status' => 'approved']);

        return response()->json(['success' => true, 'message' => 'Joined store successfully!']);
    }

    public function publicIndex(Request $request)
    {
        $query = Product::with(['category', 'seller', 'admin']);

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhereHas('category', function ($q) use ($search) {
                        $q->where('name', 'like', '%' . $search . '%');
                    });
            });
        }

        if ($request->has('category') && !empty($request->category)) {
            $query->where('category_id', $request->category);
        }

        if ($request->has('min_price') && is_numeric($request->min_price)) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->has('max_price') && is_numeric($request->max_price)) {
            $query->where('price', '<=', $request->max_price);
        }

        $products = $query->orderBy('created_at', 'desc')->get();
        $categories = Category::all();

        return view('products', compact('products', 'categories'));
    }

    public function show($id)
    {
        $product = Product::with(['category', 'seller', 'admin', 'reviews.user', 'variants'])->findOrFail($id);

        // Get related products from same category (excluding current)
        $relatedProducts = Product::with(['category', 'seller', 'admin'])
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->take(4)
            ->get();

        // Check if current user already reviewed this product
        $userReview = null;
        if (auth()->check()) {
            $userReview = $product->reviews->where('user_id', auth()->id())->first();
        }

        // Rating breakdown (count per star)
        $ratingBreakdown = [];
        for ($i = 5; $i >= 1; $i--) {
            $ratingBreakdown[$i] = $product->reviews->where('rating', $i)->count();
        }

        return view('product-detail', compact('product', 'relatedProducts', 'userReview', 'ratingBreakdown'));
    }

    public function deductStock(Request $request)
    {
        $product = Product::findOrFail($request->product_id);
        $qty = intval($request->quantity);

        if ($product->stock_quantity >= $qty) {
            $product->stock_quantity -= $qty;
            $product->save();
            return response()->json(['success' => true, 'new_stock' => $product->stock_quantity]);
        }

        return response()->json(['success' => false, 'message' => 'Not enough stock']);
    }

    public function returnStock(Request $request)
    {
        $product = Product::findOrFail($request->product_id);
        $qty = intval($request->quantity);

        $product->stock_quantity += $qty;
        $product->save();

        return response()->json(['success' => true, 'new_stock' => $product->stock_quantity]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric',
            'discount_percentage' => 'nullable|integer|min:0|max:100',
            'is_new' => 'nullable|boolean',
            'stock_status' => 'required|in:available,not',
            'stock_quantity' => 'required|integer|min:0',
            'images' => 'required|array|min:1|max:5',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'main_image_index' => 'required|integer',
        ]);

        $imageUrls = [];
        $mainImageUrl = '';

        if ($request->hasFile('images')) {
            $directory = 'media/product_img';
            $path = public_path($directory);

            if (!File::exists($path)) {
                File::makeDirectory($path, 0777, true, true);
            }

            $files = $request->file('images');
            foreach ($files as $index => $file) {
                $url = $this->processImage($file, 'product_img', $request->name);
                $imageUrls[] = $url;

                if ($index == $request->main_image_index) {
                    $mainImageUrl = $url;
                }
            }
        }

        // If for some reason main image index was invalid, use the first one
        if (empty($mainImageUrl) && !empty($imageUrls)) {
            $mainImageUrl = $imageUrls[0];
        }

        $sellerId = null;
        $adminId = auth()->id(); // default to admin creating it

        if (auth()->user()->role === 'seller') {
            $sellerId = auth()->id();
            $adminId = auth()->user()->admin_id;
        }

        // stock_status is handled by Product model boot method
        $product = Product::create([
            'admin_id' => $adminId,
            'seller_id' => $sellerId,
            'category_id' => $request->category_id,
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'discount_percentage' => $request->discount_percentage,
            'is_new' => $request->boolean('is_new'),
            'stock_status' => $request->stock_status,
            'stock_quantity' => $request->stock_quantity,
            'image_urls' => $imageUrls,
            'main_image_url' => $mainImageUrl,
        ]);

        // Save color variants
        $this->saveVariants($request, $product);

        return response()->json([
            'success' => true,
            'message' => 'Product added successfully!'
        ]);
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $adminId = auth()->user()->role === 'admin' ? auth()->id() : auth()->user()->admin_id;
        if ($product->admin_id !== $adminId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric',
            'discount_percentage' => 'nullable|integer|min:0|max:100',
            'is_new' => 'nullable|boolean',
            'stock_status' => 'required|in:available,not',
            'stock_quantity' => 'required|integer|min:0',
            'images' => 'nullable|array|max:5',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:10240',
            'existing_images' => 'nullable|array',
            'main_image_index' => 'required|integer',
        ]);

        $directory = 'media/product_img';
        $path = public_path($directory);

        // Ensure directory exists
        if (!File::exists($path)) {
            File::makeDirectory($path, 0777, true, true);
        }

        // 1. Get existing images we want to KEEP
        $finalImageUrls = $request->input('existing_images', []);

        // 2. Process NEW uploaded images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $finalImageUrls[] = $this->processImage($file, 'product_img', $request->name);
            }
        }

        // 3. Delete physical files for images that were removed
        $oldImages = $product->image_urls ?? [];
        foreach ($oldImages as $oldUrl) {
            if (!in_array($oldUrl, $finalImageUrls)) {
                if (File::exists(public_path($oldUrl))) {
                    File::delete(public_path($oldUrl));
                }
            }
        }

        // 4. Set Main Image URL
        $mainImageUrl = $finalImageUrls[$request->main_image_index] ?? ($finalImageUrls[0] ?? '');

        // stock_status is handled by Product model boot method
        $product->update([
            'category_id' => $request->category_id,
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'discount_percentage' => $request->discount_percentage,
            'is_new' => $request->boolean('is_new'),
            'stock_status' => $request->stock_status,
            'stock_quantity' => $request->stock_quantity,
            'image_urls' => $finalImageUrls,
            'main_image_url' => $mainImageUrl,
        ]);

        // Update variants
        $this->saveVariants($request, $product);

        return response()->json(['success' => true, 'message' => 'Product updated successfully!']);
    }

    public function quickUpdate(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $adminId = auth()->user()->role === 'admin' ? auth()->id() : auth()->user()->admin_id;
        if ($product->admin_id !== $adminId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $data = $request->validate([
            'discount_percentage' => 'nullable|integer|min:0|max:100',
            'is_new' => 'nullable|boolean',
        ]);

        if ($request->has('is_new')) {
            $data['is_new'] = $request->boolean('is_new');
        }

        $product->update($data);

        return response()->json(['success' => true, 'message' => 'Quick update successful']);
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        $adminId = auth()->user()->role === 'admin' ? auth()->id() : auth()->user()->admin_id;
        if ($product->admin_id !== $adminId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        // Delete physical images
        if ($product->image_urls) {
            foreach ($product->image_urls as $url) {
                if (File::exists(public_path($url))) {
                    File::delete(public_path($url));
                }
            }
        }

        $product->delete();
        return response()->json(['success' => true, 'message' => 'Product deleted successfully!']);
    }

    private function saveVariants(Request $request, Product $product)
    {
        // Delete old variants first, but keep physical images if they are still being used
        $oldVariants = $product->variants;
        $keptImages = $request->input('color_existing_images', []);
        
        foreach ($oldVariants as $oldVariant) {
            if ($oldVariant->image_url && !in_array($oldVariant->image_url, $keptImages)) {
                if (File::exists(public_path($oldVariant->image_url))) {
                    File::delete(public_path($oldVariant->image_url));
                }
            }
        }
        $product->variants()->delete();

        // Save color variants
        if ($request->has('color_values')) {
            $colorValues = $request->input('color_values', []);
            $colorHexes = $request->input('color_hexes', []);
            $colorStocks = $request->input('color_stocks', []);
            $colorImages = $request->file('color_images', []);
            $colorExistingImages = $request->input('color_existing_images', []);

            foreach ($colorValues as $i => $value) {
                if (empty($value)) continue;

                $imageUrl = null;
                // Check for new uploaded image
                if (isset($colorImages[$i])) {
                    $imageUrl = $this->processImage($colorImages[$i], 'product_img', $product->name . '_color_' . $value);
                } elseif (isset($colorExistingImages[$i]) && !empty($colorExistingImages[$i])) {
                    $imageUrl = $colorExistingImages[$i];
                }

                ProductVariant::create([
                    'product_id' => $product->id,
                    'variant_type' => 'color',
                    'value' => $value,
                    'hex_code' => $colorHexes[$i] ?? null,
                    'image_url' => $imageUrl,
                    'stock_quantity' => intval($colorStocks[$i] ?? 0),
                    'sort_order' => $i,
                ]);
            }
        }

        // Save size variants
        if ($request->has('size_values')) {
            $sizeValues = $request->input('size_values', []);
            $sizeStocks = $request->input('size_stocks', []);
            $sizePrices = $request->input('size_prices', []);

            foreach ($sizeValues as $i => $value) {
                if (empty($value)) continue;

                ProductVariant::create([
                    'product_id' => $product->id,
                    'variant_type' => 'size',
                    'value' => $value,
                    'stock_quantity' => intval($sizeStocks[$i] ?? 0),
                    'price_adjustment' => floatval($sizePrices[$i] ?? 0),
                    'sort_order' => $i,
                ]);
            }
        }
    }
}
