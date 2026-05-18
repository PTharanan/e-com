<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminSellerController extends Controller
{
    public function index()
    {
        $sellers = auth()->user()->subSellers()->orderBy('created_at', 'desc')->get();
        return view('admin.sellers', compact('sellers'));
    }

    public function toggleBlock($id)
    {
        $seller = auth()->user()->subSellers()->findOrFail($id);
        $seller->is_blocked = !$seller->is_blocked;
        $seller->save();

        return response()->json(['success' => true, 'is_blocked' => $seller->is_blocked]);
    }
}
