<?php

use Illuminate\Support\Facades\Route;

Route::get('/500', function () {
    return view('errors.500');
});

Route::get('/', function () {
    $products = \App\Models\Product::with(['category', 'seller', 'admin'])->where('is_new', true)->latest()->take(4)->get();
    if($products->isEmpty()) {
        $products = \App\Models\Product::with(['category', 'seller', 'admin'])->latest()->take(4)->get();
    }
    $banners = \App\Models\Banner::where('is_active', true)->orderBy('order')->get();
    return view('index', compact('products', 'banners'));
})->name('home');

Route::get('/products', [\App\Http\Controllers\ProductController::class, 'publicIndex'])->name('products');
Route::post('/products/add-to-cart', [\App\Http\Controllers\ProductController::class, 'deductStock'])->name('products.add-to-cart');
Route::post('/products/remove-from-cart', [\App\Http\Controllers\ProductController::class, 'returnStock'])->name('products.remove-from-cart');
Route::get('/cart', function () { 
    $products = \App\Models\Product::with('category')->get();
    return view('cart', compact('products')); 
})->name('cart');

Route::post('/checkout', [\App\Http\Controllers\StripeController::class, 'checkout'])->name('checkout');
Route::get('/checkout/success', [\App\Http\Controllers\StripeController::class, 'success'])->name('checkout.success');
Route::get('/checkout/cancel', [\App\Http\Controllers\StripeController::class, 'cancel'])->name('checkout.cancel');

Route::get('/categories', [\App\Http\Controllers\CategoryController::class, 'publicIndex'])->name('categories');

Route::get('/offers', function () {
    $products = \App\Models\Product::with('category')->whereNotNull('discount_percentage')->where('discount_percentage', '>', 0)->latest()->get();
    return view('offers', compact('products'));
})->name('offers');

// Product Detail Page
Route::get('/product/{id}', [\App\Http\Controllers\ProductController::class, 'show'])->name('product.show');

// Product Reviews (authenticated users only)
Route::middleware('auth')->group(function () {
    Route::post('/product/{id}/review', [\App\Http\Controllers\ReviewController::class, 'store'])->name('product.review.store');
    Route::delete('/review/{id}', [\App\Http\Controllers\ReviewController::class, 'destroy'])->name('product.review.delete');
});

use App\Http\Controllers\AuthController;

// User Routes
Route::get('/sign-in', function () {
    return view('login');
})->name('sign-in');

Route::get('/login', function () {
    return redirect()->route('sign-in');
})->name('login');

Route::post('/sign-in', [AuthController::class, 'signin'])->name('sign-in.post');

// Sign Up
Route::get('/sign-up', function () {
    return view('register');
})->name('sign-up');

Route::post('/sign-up', [AuthController::class, 'signup'])->name('sign-up.post');
Route::post('/check-email', [AuthController::class, 'checkEmail']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/resend-otp', [AuthController::class, 'resendOtp']);

// Forgot Password Routes
Route::get('/forgot-password', function () {
    return view('auth.forgot-password');
})->name('password.request');

Route::post('/forgot-password/check', [AuthController::class, 'forgotPasswordCheck'])->name('password.email');

Route::get('/forgot-password/verify', function () {
    return view('auth.verify-reset-otp');
})->name('password.verify');

Route::post('/forgot-password/verify', [AuthController::class, 'verifyResetOtp'])->name('password.verify.post');

Route::get('/forgot-password/reset', function () {
    $verifiedAt = session('reset_otp_verified_at');
    if (!session('reset_otp_verified') || !$verifiedAt || (now()->timestamp - $verifiedAt > 300)) {
        session()->forget(['reset_data', 'reset_otp_verified', 'reset_otp_verified_at']);
        abort(404);
    }
    return view('auth.reset-password');
})->name('password.reset');

Route::post('/forgot-password/reset', [AuthController::class, 'resetPassword'])->name('password.update');

Route::post('/forgot-password/resend-otp', [AuthController::class, 'resendResetOtp'])->name('password.resend');

Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard')->middleware('auth');
Route::post('/dashboard/orders/{id}/cancel', [\App\Http\Controllers\DashboardController::class, 'cancelOrder'])->name('orders.cancel')->middleware('auth');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/sse/stream', [\App\Http\Controllers\SSEController::class, 'stream'])->name('sse.stream')->middleware('auth');

// Delivery Routes
Route::prefix('delivery')->name('delivery.')->group(function () {
    Route::get('/', function () {
        if (auth()->check() && auth()->user()->role === 'delivery_boy') {
            return redirect()->route('delivery.dashboard');
        }
        return redirect()->route('delivery.login');
    });

    Route::get('/sign-in', function () {
        return view('delivery.login');
    })->name('login');

    Route::get('/sign-up', function () {
        return view('delivery.register');
    })->name('register');

    Route::post('/sign-in', [\App\Http\Controllers\DeliveryController::class, 'login'])->name('login.post');
    Route::post('/sign-up', [\App\Http\Controllers\AuthController::class, 'deliveryRegister'])->name('register.post');
    Route::post('/verify-otp', [\App\Http\Controllers\AuthController::class, 'deliveryVerifyOtp'])->name('verify-otp.post');
    Route::post('/resend-otp', [\App\Http\Controllers\AuthController::class, 'deliveryResendOtp'])->name('resend-otp.post');

    Route::middleware('auth')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\DeliveryController::class, 'dashboard'])->name('dashboard');
        Route::get('/work', [\App\Http\Controllers\DeliveryController::class, 'work'])->name('work');
        Route::get('/stores', [\App\Http\Controllers\DeliveryController::class, 'stores'])->name('stores');
        Route::get('/history', [\App\Http\Controllers\DeliveryController::class, 'history'])->name('history');
        Route::post('/apply/{storeOwnerId}', [\App\Http\Controllers\DeliveryController::class, 'apply'])->name('apply');
        Route::post('/take-order/{id}', [\App\Http\Controllers\DeliveryController::class, 'takeOrder'])->name('take-order');
        Route::post('/verify-delivery/{id}', [\App\Http\Controllers\DeliveryController::class, 'verifyDelivery'])->name('verify-delivery');
        Route::get('/notifications/poll', [\App\Http\Controllers\NotificationController::class, 'poll'])->name('notifications.poll');
    });
});

// Admin Routes
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/', function () {
        if (auth()->check() && auth()->user()->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }
        return redirect()->route('admin.login');
    });

    Route::get('/sign-in', function () {
        return view('admin.login');
    })->name('login');

    Route::post('/sign-in', [\App\Http\Controllers\AuthController::class, 'adminSignin'])->name('login.post');

    Route::get('/sign-up', function () {
        return view('admin.register');
    })->name('register');
    
    Route::post('/sign-up', [\App\Http\Controllers\AuthController::class, 'adminRegister'])->name('register.post');
    Route::post('/verify-otp', [\App\Http\Controllers\AuthController::class, 'adminVerifyOtp'])->name('verify-otp.post');
    Route::post('/resend-otp', [\App\Http\Controllers\AuthController::class, 'adminResendOtp'])->name('resend-otp.post');

    // Protected Admin Routes
    Route::middleware(['admin'])->group(function () {
        Route::get('/settings/sellers', [\App\Http\Controllers\AdminSellerController::class, 'index'])->name('sellers');
        Route::patch('/settings/sellers/{id}/toggle-block', [\App\Http\Controllers\AdminSellerController::class, 'toggleBlock'])->name('sellers.toggle-block');
        Route::get('/dashboard', function () {
            $adminId = auth()->id();
            $totalOrders = \App\Models\Order::where('admin_id', $adminId)->where('status', '!=', 'cancelled')->count();
            $totalUsers = \App\Models\User::where('role', 'client')->count(); // Users are shared for now
            // Revenue only from successful/shipped/delivered orders
            $totalRevenue = \App\Models\Order::where('admin_id', $adminId)->whereIn('status', ['completed', 'processing', 'shipped', 'delivered'])->sum('total_price');
            $totalProducts = \App\Models\Product::where('admin_id', $adminId)->count();
            $totalRefunds = \App\Models\Order::where('admin_id', $adminId)->where('status', 'refunded')->count();
            
            return view('admin.dashboard', compact('totalOrders', 'totalUsers', 'totalRevenue', 'totalProducts', 'totalRefunds'));
        })->name('dashboard');

        Route::get('/products', [\App\Http\Controllers\ProductController::class, 'index'])->name('products');
        Route::post('/products', [\App\Http\Controllers\ProductController::class, 'store'])->name('products.store');
        Route::put('/products/{id}', [\App\Http\Controllers\ProductController::class, 'update'])->name('products.update');
        Route::patch('/products/{id}/quick', [\App\Http\Controllers\ProductController::class, 'quickUpdate'])->name('products.quick-update');
        Route::delete('/products/{id}', [\App\Http\Controllers\ProductController::class, 'destroy'])->name('products.delete');

        Route::get('/orders', [\App\Http\Controllers\OrderController::class, 'index'])->name('orders');
        Route::patch('/orders/{id}/status', [\App\Http\Controllers\OrderController::class, 'updateStatus'])->name('orders.status');
        Route::post('/orders/{id}/assign', [\App\Http\Controllers\OrderController::class, 'assignPartner'])->name('orders.assign');
        Route::post('/orders/{id}/refund', [\App\Http\Controllers\OrderController::class, 'refundOrder'])->name('orders.refund');

        Route::get('/customers', [\App\Http\Controllers\CustomerController::class, 'index'])->name('customers');

        Route::get('/delivery-partners', [\App\Http\Controllers\AdminDeliveryController::class, 'index'])->name('delivery');
        Route::patch('/delivery-partners/{id}/status', [\App\Http\Controllers\AdminDeliveryController::class, 'updateStatus'])->name('delivery.status');
        Route::get('/delivery/assign-work', [\App\Http\Controllers\AdminDeliveryController::class, 'assignWork'])->name('delivery.assign-work');
        Route::patch('/delivery/fee/{id}', [\App\Http\Controllers\AdminDeliveryController::class, 'updateFee'])->name('delivery.update-fee');
        Route::delete('/delivery/release/{id}', [\App\Http\Controllers\AdminDeliveryController::class, 'releasePartner'])->name('delivery.release');

        Route::get('/notifications/poll', [\App\Http\Controllers\NotificationController::class, 'poll'])->name('notifications.poll');
        Route::patch('/notifications/{id}/dismiss', [\App\Http\Controllers\NotificationController::class, 'dismiss'])->name('notifications.dismiss');
        // SSE route moved to general auth group below


        Route::get('/settings', function () {
            return view('admin.settings');
        })->name('settings');

        Route::get('/settings/auto-delete', [\App\Http\Controllers\SiteSettingController::class, 'autoDeleteIndex'])->name('settings.auto-delete');
        Route::post('/settings/auto-delete', [\App\Http\Controllers\SiteSettingController::class, 'updateAutoDelete'])->name('settings.auto-delete.update');
        Route::post('/settings/auto-delete/run', [\App\Http\Controllers\SiteSettingController::class, 'runCleanup'])->name('settings.auto-delete.run');

        Route::get('/settings/categories', [\App\Http\Controllers\CategoryController::class, 'index'])->name('categories');
        Route::post('/settings/categories', [\App\Http\Controllers\CategoryController::class, 'store'])->name('categories.store');

        Route::get('/settings/currency', [\App\Http\Controllers\CurrencyController::class, 'index'])->name('currency');
        Route::post('/settings/currency', [\App\Http\Controllers\CurrencyController::class, 'update'])->name('currency.update');

        Route::get('/settings/banners', [\App\Http\Controllers\BannerController::class, 'index'])->name('banners');
        Route::post('/settings/banners', [\App\Http\Controllers\BannerController::class, 'store'])->name('banners.store');
        Route::put('/settings/banners/{id}', [\App\Http\Controllers\BannerController::class, 'update'])->name('banners.update');
        Route::delete('/settings/banners/{id}', [\App\Http\Controllers\BannerController::class, 'destroy'])->name('banners.delete');
    });
});

// Seller Routes
Route::prefix('seller')->name('seller.')->group(function () {
    Route::get('/', function () {
        if (auth()->check() && auth()->user()->role === 'seller') {
            return redirect()->route('seller.dashboard');
        }
        return redirect()->route('seller.login');
    });

    Route::get('/sign-in', function () {
        return view('seller.login');
    })->name('login');

    Route::post('/sign-in', [\App\Http\Controllers\AuthController::class, 'sellerSignin'])->name('login.post');

    Route::get('/sign-up', function () {
        $admins = \App\Models\User::where('role', 'admin')->get();
        return view('seller.register', compact('admins'));
    })->name('register');

    Route::post('/sign-up', [\App\Http\Controllers\AuthController::class, 'sellerRegister'])->name('register.post');
    Route::post('/verify-otp', [\App\Http\Controllers\AuthController::class, 'sellerVerifyOtp'])->name('verify-otp.post');
    Route::post('/resend-otp', [\App\Http\Controllers\AuthController::class, 'sellerResendOtp'])->name('resend-otp.post');

    Route::middleware(['seller'])->group(function () {
        Route::get('/dashboard', function () {
            $sellerId = auth()->id();
            
            $sellerOrdersQuery = \App\Models\Order::whereJsonContains('items_json', ['seller_id' => (int)$sellerId]);
            
            $totalOrders = (clone $sellerOrdersQuery)->where('status', '!=', 'cancelled')->count();
            $totalUsers = \App\Models\User::where('role', 'client')->count(); // Users are shared
            $totalRevenue = (clone $sellerOrdersQuery)->whereIn('status', ['completed', 'processing', 'shipped', 'delivered'])->sum('total_price');
            $totalProducts = \App\Models\Product::where('seller_id', $sellerId)->count();
            $totalRefunds = (clone $sellerOrdersQuery)->where('status', 'refunded')->count();
            
            return view('seller.dashboard', compact('totalOrders', 'totalUsers', 'totalRevenue', 'totalProducts', 'totalRefunds'));
        })->name('dashboard');

        Route::get('/products', [\App\Http\Controllers\ProductController::class, 'index'])->name('products');
        Route::post('/join-store', [\App\Http\Controllers\ProductController::class, 'joinStore'])->name('join-store');
        Route::post('/products', [\App\Http\Controllers\ProductController::class, 'store'])->name('products.store');
        Route::put('/products/{id}', [\App\Http\Controllers\ProductController::class, 'update'])->name('products.update');
        Route::patch('/products/{id}/quick', [\App\Http\Controllers\ProductController::class, 'quickUpdate'])->name('products.quick-update');
        Route::delete('/products/{id}', [\App\Http\Controllers\ProductController::class, 'destroy'])->name('products.delete');

        Route::get('/orders', [\App\Http\Controllers\OrderController::class, 'index'])->name('orders');
        Route::patch('/orders/{id}/status', [\App\Http\Controllers\OrderController::class, 'updateStatus'])->name('orders.status');
        Route::post('/orders/{id}/assign', [\App\Http\Controllers\OrderController::class, 'assignPartner'])->name('orders.assign');
        Route::post('/orders/{id}/refund', [\App\Http\Controllers\OrderController::class, 'refundOrder'])->name('orders.refund');

        Route::get('/delivery-partners', [\App\Http\Controllers\AdminDeliveryController::class, 'index'])->name('delivery');
        Route::patch('/delivery-partners/{id}/status', [\App\Http\Controllers\AdminDeliveryController::class, 'updateStatus'])->name('delivery.status');
        Route::get('/delivery/assign-work', [\App\Http\Controllers\AdminDeliveryController::class, 'assignWork'])->name('delivery.assign-work');
        Route::patch('/delivery/fee/{id}', [\App\Http\Controllers\AdminDeliveryController::class, 'updateFee'])->name('delivery.update-fee');
        Route::delete('/delivery/release/{id}', [\App\Http\Controllers\AdminDeliveryController::class, 'releasePartner'])->name('delivery.release');

        Route::get('/notifications/poll', [\App\Http\Controllers\NotificationController::class, 'poll'])->name('notifications.poll');
        Route::patch('/notifications/{id}/dismiss', [\App\Http\Controllers\NotificationController::class, 'dismiss'])->name('notifications.dismiss');
        
        Route::get('/customers', [\App\Http\Controllers\CustomerController::class, 'index'])->name('customers');

        Route::get('/settings', function () {
            return view('seller.settings');
        })->name('settings');

        Route::get('/settings/auto-delete', [\App\Http\Controllers\SiteSettingController::class, 'autoDeleteIndex'])->name('settings.auto-delete');
        Route::post('/settings/auto-delete', [\App\Http\Controllers\SiteSettingController::class, 'updateAutoDelete'])->name('settings.auto-delete.update');
        Route::post('/settings/auto-delete/run', [\App\Http\Controllers\SiteSettingController::class, 'runCleanup'])->name('settings.auto-delete.run');





    });
});
