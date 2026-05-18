<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

use App\Traits\CanProcessImages;

class CategoryController extends Controller
{
    use CanProcessImages;

    public function index()
    {
        $adminId = auth()->user()->role === 'admin' ? auth()->id() : auth()->user()->admin_id;
        $categories = Category::withCount('products')->where('admin_id', $adminId)->orderBy('created_at', 'desc')->get();
        $viewPrefix = auth()->user()->role === 'seller' ? 'seller' : 'admin'; 
        return view("$viewPrefix.categories", compact('categories'));
    }

    public function publicIndex()
    {
        $categories = Category::withCount('products')->get();
        return view('categories', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $imageUrl = $this->processImage($request->file('image'), 'categories/img', $name);

        $adminId = auth()->user()->role === 'admin' ? auth()->id() : auth()->user()->admin_id;

        $category = Category::create([
            'admin_id' => $adminId,
            'name' => $name,
            'dp_img_url' => $imageUrl,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Category added successfully!',
            'category' => $category
        ]);
    }
}
