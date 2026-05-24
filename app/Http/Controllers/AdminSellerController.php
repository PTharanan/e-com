<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class AdminSellerController extends Controller
{
    public function index()
    {
        $sellers = User::withTrashed()
            ->where('role', 'seller')
            ->with(['approver', 'lastEditor'])
            ->withCount('products')
            ->orderBy('created_at', 'desc')
            ->get();
        return view('admin.sellers', compact('sellers'));
    }

    public function adminIndex()
    {
        $admins = User::where('role', 'admin')
            ->orderBy('created_at', 'desc')
            ->get();
        return view('admin.accounts', compact('admins'));
    }


    public function destroySeller(Request $request, $id)
    {
        $seller = User::where('role', 'seller')->findOrFail($id);

        $request->validate([
            'reason' => 'required|string|max:1000'
        ]);

        // 1. Delete all products and their images
        $products = \App\Models\Product::where('seller_id', $id)->get();
        foreach ($products as $product) {
            // Delete main image
            if ($product->main_image_url) {
                $path = public_path($product->main_image_url);
                if (file_exists($path)) {
                    @unlink($path);
                }
            }

            // Delete gallery images
            if (is_array($product->image_urls)) {
                foreach ($product->image_urls as $imgUrl) {
                    $path = public_path($imgUrl);
                    if (file_exists($path)) {
                        @unlink($path);
                    }
                }
            }

            // Delete variant images
            foreach ($product->variants as $variant) {
                if ($variant->image_url) {
                    $path = public_path($variant->image_url);
                    if (file_exists($path)) {
                        @unlink($path);
                    }
                }
            }

            $product->delete();
        }

        // 2. Perform Soft Delete on Seller with reason
        $seller->update([
            'deletion_reason' => $request->reason,
            'last_edited_by' => auth()->id()
        ]);
        $seller->delete();

        return response()->json(['success' => true, 'message' => 'Seller account deleted and files cleared.']);
    }

    public function toggleBlock($id)
    {
        $seller = User::where('role', 'seller')->findOrFail($id);
        $seller->is_blocked = !$seller->is_blocked;
        $seller->save();

        return response()->json(['success' => true, 'is_blocked' => $seller->is_blocked]);
    }

    public function storeAdmin(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => 'admin',
            'is_verified' => true,
        ]);

        return response()->json(['success' => true, 'message' => 'Admin created successfully.']);
    }

    public function updateAdmin(Request $request, $id)
    {
        $admin = User::where('role', 'admin')->findOrFail($id);

        $rules = [
            'name' => 'required|string|max:255',
            'current_password' => 'required',
        ];

        if ($request->filled('password')) {
            $rules['password'] = 'required|string|min:8';
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()]);
        }

        // Verify the logged-in admin's current password
        if (!\Illuminate\Support\Facades\Hash::check($request->current_password, auth()->user()->password)) {
            return response()->json(['success' => false, 'message' => 'The provided current password does not match our records.']);
        }

        $admin->name = $request->name;
        // Email is readonly on frontend and should not be changed for security
        if ($request->filled('password')) {
            $admin->password = \Illuminate\Support\Facades\Hash::make($request->password);
        }
        $admin->last_edited_by = auth()->id();
        $admin->save();

        return response()->json(['success' => true, 'message' => 'Admin account updated successfully.']);
    }

    public function destroyAdmin(Request $request, $id)
    {
        if (auth()->id() == $id) {
            return response()->json(['success' => false, 'message' => 'You cannot delete yourself.']);
        }

        $admin = User::where('role', 'admin')->findOrFail($id);

        \Illuminate\Support\Facades\DB::transaction(function () use ($admin) {
            // Nullify admin_id in related tables to prevent ON DELETE CASCADE 
            // from unintentionally wiping out all content managed by this admin
            \App\Models\User::where('admin_id', $admin->id)->update(['admin_id' => null]);
            \App\Models\Category::where('admin_id', $admin->id)->update(['admin_id' => null]);
            \App\Models\Product::where('admin_id', $admin->id)->update(['admin_id' => null]);
            \App\Models\Order::where('admin_id', $admin->id)->update(['admin_id' => null]);
            \App\Models\Banner::where('admin_id', $admin->id)->update(['admin_id' => null]);

            // Delete admin's address from user_info table
            \App\Models\UserInfo::where('user_id', $admin->id)->delete();

            // Hard delete - permanently remove from users table
            $admin->forceDelete();
        });

        return response()->json(['success' => true, 'message' => 'Admin account permanently deleted.']);
    }

    public function toggleAdminBlock($id)
    {
        if (auth()->id() == $id) {
            return response()->json(['success' => false, 'message' => 'You cannot block yourself.']);
        }

        $admin = User::where('role', 'admin')->findOrFail($id);
        $admin->is_blocked = !$admin->is_blocked;
        $admin->save();

        return response()->json(['success' => true, 'is_blocked' => $admin->is_blocked]);
    }
}
