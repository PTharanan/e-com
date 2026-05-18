<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

use App\Traits\CanProcessImages;

class BannerController extends Controller
{
    use CanProcessImages;

    public function index()
    {
        $adminId = auth()->id();
        $banners = Banner::where('admin_id', $adminId)->orderBy('order')->get();
        return view('admin.banners', compact('banners'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:10240',
            'title' => 'required|string|max:255',
            'badge_text' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string',
            'button_text' => 'nullable|string|max:255',
            'button_link' => 'nullable|string|max:255',
            'order' => 'nullable|integer',
        ]);

        $imageUrl = '';
        if ($request->hasFile('image')) {
            $imageUrl = $this->processImage($request->file('image'), 'banners', $request->title);
        }

        Banner::create([
            'admin_id' => auth()->id(),
            'image_url' => $imageUrl,
            'title' => $request->title,
            'badge_text' => $request->badge_text,
            'subtitle' => $request->subtitle,
            'button_text' => $request->button_text ?? 'Shop Now',
            'button_link' => $request->button_link ?? '#',
            'order' => $request->order ?? 0,
            'is_active' => true,
        ]);

        return response()->json(['success' => true, 'message' => 'Banner added successfully!']);
    }

    public function update(Request $request, $id)
    {
        $banner = Banner::findOrFail($id);
        
        $request->validate([
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'title' => 'required|string|max:255',
            'badge_text' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string',
            'button_text' => 'nullable|string|max:255',
            'button_link' => 'nullable|string|max:255',
            'order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $imageUrl = $banner->image_url;
        if ($request->hasFile('image')) {
            // Delete old image
            if ($banner->image_url && File::exists(public_path($banner->image_url))) {
                File::delete(public_path($banner->image_url));
            }
            $imageUrl = $this->processImage($request->file('image'), 'banners', $request->title);
        }

        $banner->update([
            'image_url' => $imageUrl,
            'title' => $request->title,
            'badge_text' => $request->badge_text,
            'subtitle' => $request->subtitle,
            'button_text' => $request->button_text ?? 'Shop Now',
            'button_link' => $request->button_link ?? '#',
            'order' => $request->order ?? 0,
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : $banner->is_active,
        ]);

        return response()->json(['success' => true, 'message' => 'Banner updated successfully!']);
    }

    public function destroy($id)
    {
        $banner = Banner::findOrFail($id);
        
        if (File::exists(public_path($banner->image_url))) {
            File::delete(public_path($banner->image_url));
        }

        $banner->delete();
        return response()->json(['success' => true, 'message' => 'Banner deleted successfully!']);
    }
}
