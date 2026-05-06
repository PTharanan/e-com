@extends('layouts.master')

@section('title', 'Home')

@section('styles')
    <style>
        /* ========== Design Tokens (Specific to Index) ========== */
        :root {
            --shadow-md: 0 8px 24px rgba(242, 92, 59, 0.12);
        }

        /* ========== AD BANNER ========== */
        .ad-banner-section {
            padding: 30px 5%;
            margin-top: 10px;
            position: relative;
        }

        .ad-banner-container {
            position: relative;
            height: 450px;
            width: 100%;
            overflow: hidden;
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-sm);
        }

        .banner-track {
            display: flex;
            height: 100%;
            width: 100%;
            transition: transform 0.8s cubic-bezier(0.2, 0.8, 0.2, 1);
        }

        .ad-banner {
            flex: 0 0 100%;
            width: 100%;
            height: 100%;
            background: var(--color-bg-light);
            display: flex;
            align-items: center;
            padding: 0 8%;
            position: relative;
            overflow: hidden;
        }

        .banner-dots {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
        }

        .dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #E2E8F0;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .dot.active {
            background: var(--color-primary);
            width: 25px;
            border-radius: 10px;
        }

        .ad-content {
            position: relative;
            z-index: 10;
            max-width: 500px;
            animation: slideRight 0.8s cubic-bezier(0.2, 0.8, 0.2, 1) forwards;
            opacity: 0;
            transform: translateX(-30px);
        }

        .ad-badge {
            display: inline-block;
            background: rgba(242, 92, 59, 0.1);
            color: var(--color-primary);
            padding: 6px 16px;
            border-radius: var(--radius-pill);
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 20px;
        }

        .ad-title {
            font-size: 3.5rem;
            font-weight: 800;
            color: var(--color-text-dark);
            line-height: 1.1;
            margin-bottom: 20px;
            letter-spacing: -1px;
        }

        .ad-subtitle {
            font-size: 1.1rem;
            color: var(--color-text-medium);
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .ad-image-wrapper {
            position: absolute;
            right: 5%;
            bottom: 0;
            height: 110%;
            width: 50%;
            display: flex;
            align-items: flex-end;
            justify-content: center;
            animation: slideLeft 1s cubic-bezier(0.2, 0.8, 0.2, 1) 0.2s forwards;
            opacity: 0;
            transform: translateX(30px);
        }

        .ad-image-wrapper img {
            max-height: 100%;
            max-width: 100%;
            object-fit: contain;
            filter: drop-shadow(0 20px 30px rgba(0,0,0,0.15));
        }

        /* ========== PRODUCTS SECTION OVERRIDES ========== */
        .products-section {
            padding: 60px 5%;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-bottom: 40px;
        }

        .section-title {
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--color-text-dark);
            position: relative;
            display: inline-block;
        }

        .section-title::after {
            content: '';
            position: absolute;
            width: 40%;
            height: 4px;
            bottom: -8px;
            left: 0;
            background-color: var(--color-primary);
            border-radius: 2px;
        }

        .view-all {
            color: var(--color-primary);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: var(--transition-fast);
        }

        .view-all:hover {
            color: var(--color-primary-hover);
            transform: translateX(4px);
        }

        /* Badge System */
        .badge-container {
            position: absolute;
            top: 20px;
            left: 20px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            z-index: 10;
        }

        .product-badge {
            position: static !important; /* Override absolute from master */
            padding: 6px 14px !important;
            font-size: 0.75rem !important;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .badge-offer {
            background: #10B981 !important;
        }

        .badge-new {
            background: var(--color-primary) !important;
        }

        .badge-sold-out {
            background: #64748B !important;
        }

        /* Price Styling */
        .price-wrapper {
            display: flex;
            flex-direction: column;
        }

        .old-price {
            font-size: 0.9rem;
            color: #9CA3AF;
            text-decoration: line-through;
            font-weight: 500;
            margin-bottom: -2px;
        }

        .new-price {
            color: #10B981;
            font-weight: 800;
        }

        @keyframes slideRight { to { opacity: 1; transform: translateX(0); } }
        @keyframes slideLeft { to { opacity: 1; transform: translateX(0); } }

        @media (max-width: 992px) {
            .ad-title { font-size: 2.8rem; }
            .ad-image-wrapper { opacity: 0.3; right: -10%; }
            .ad-content { max-width: 100%; z-index: 20; }
        }

        @media (max-width: 768px) {
            .ad-banner-container { height: 520px; }
            .ad-banner { flex-direction: column-reverse; padding: 30px 5%; text-align: center; justify-content: center; }
            .ad-content { display: flex; flex-direction: column; align-items: center; max-width: 100%; z-index: 20; transform: none; animation: none; opacity: 1; margin-top: 20px; }
            .ad-image-wrapper { position: relative; width: 100%; height: 220px; opacity: 1; right: auto; bottom: auto; transform: none; animation: none; }
            .ad-title { font-size: 2.2rem; }
            .section-title { font-size: 1.8rem; }
        }

        @media (max-width: 480px) {
            .ad-banner-section { padding: 15px 5%; margin-top: 5px; }
            .ad-banner-container { height: 500px; }
            .ad-title { font-size: 1.8rem; margin-bottom: 10px; }
            .ad-subtitle { font-size: 0.95rem; margin-bottom: 20px; line-height: 1.5; }
            .btn-primary-custom { padding: 12px 28px !important; font-size: 0.95rem !important; }
            .ad-badge { font-size: 0.75rem; padding: 6px 14px; margin-bottom: 15px; }
            .ad-image-wrapper { width: 90%; height: 180px; }
            .products-section { padding: 40px 5%; }
            .section-header { flex-direction: column; align-items: flex-start; gap: 12px; margin-bottom: 25px; }
            .section-title { font-size: 1.5rem; }
        }
    </style>
@endsection

@section('content')
    <!-- ========== AD BANNER ========== -->
    <section class="ad-banner-section">
        <div class="ad-banner-container">
            <div class="banner-track">
                @forelse($banners as $index => $banner)
                    <div class="ad-banner {{ $index === 0 ? 'active' : '' }}" id="banner-{{ $index }}">
                        <div class="ad-content">
                            @if($banner->badge_text)
                                <div class="ad-badge">{{ $banner->badge_text }}</div>
                            @endif
                            <h1 class="ad-title">{{ $banner->title }}</h1>
                            @if($banner->subtitle)
                                <p class="ad-subtitle">{{ $banner->subtitle }}</p>
                            @endif
                            <a href="{{ $banner->button_link }}" class="btn-primary-custom" style="display: inline-flex; align-items: center; justify-content: center; padding: 14px 32px; font-size: 1.05rem; background: var(--color-primary); color: white; border-radius: 50px; text-decoration: none; font-weight: 600; box-shadow: 0 4px 15px rgba(242, 92, 59, 0.3); transition: 0.3s;">
                                {{ $banner->button_text }}
                                <svg style="margin-left: 8px;" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                            </a>
                        </div>
                        <div class="ad-image-wrapper">
                            <img src="{{ asset($banner->image_url) }}" alt="{{ $banner->title }}">
                        </div>
                    </div>
                @empty
                    <!-- Fallback if no banners -->
                    <div class="ad-banner active">
                        <div class="ad-content">
                            <div class="ad-badge">Special Promo</div>
                            <h1 class="ad-title">Upgrade Your Lifestyle</h1>
                            <p class="ad-subtitle">Discover our exclusive collection of premium gadgets. Unbeatable prices, stunning quality, and free shipping on all orders over $50.</p>
                            <a href="#" class="btn-primary-custom" style="display: inline-flex; align-items: center; justify-content: center; padding: 14px 32px; font-size: 1.05rem; background: var(--color-primary); color: white; border-radius: 50px; text-decoration: none; font-weight: 600; box-shadow: 0 4px 15px rgba(242, 92, 59, 0.3); transition: 0.3s;">
                                Shop Now
                                <svg style="margin-left: 8px;" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                            </a>
                        </div>
                        <div class="ad-image-wrapper">
                            <img src="https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&q=80&w=800" alt="Headphones Banner">
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
        
        @if($banners->count() > 1)
            <div class="banner-dots">
                @foreach($banners as $index => $banner)
                    <div class="dot {{ $index === 0 ? 'active' : '' }}" onclick="showBanner({{ $index }})"></div>
                @endforeach
            </div>
        @endif
    </section>

    <!-- ========== PRODUCTS SECTION ========== -->
    <section class="products-section">
        <div class="section-header">
            <h2 class="section-title">New Arrivals</h2>
            <a href="{{ route('products') }}" class="view-all">View Collection <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg></a>
        </div>
        
        <div class="product-grid">
            @forelse($products as $product)
                <div class="product-card" data-product-id="{{ $product->id }}" data-stock="{{ $product->stock_quantity }}">
                    <div class="badge-container">
                        @if($product->discount_percentage)
                            <div class="product-badge badge-offer">-{{ $product->discount_percentage }}%</div>
                        @endif
                        @if($product->is_new)
                            <div class="product-badge badge-new">NEW</div>
                        @endif
                        @if($product->stock_status == 'not' || $product->stock_quantity == 0)
                            <div class="product-badge badge-sold-out">SOLD OUT</div>
                        @endif
                    </div>

                    <div class="product-image">
                        <img src="{{ asset($product->main_image_url) }}" alt="{{ $product->name }}">
                    </div>
                    <div class="product-info">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                            <span class="product-category">{{ $product->category->name }}</span>
                            <span style="font-size: 0.7rem; color: var(--color-primary); font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; background: rgba(242, 92, 59, 0.1); padding: 2px 8px; border-radius: 4px;">
                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 3px; vertical-align: middle;"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                                {{ $product->seller->name ?? ($product->admin->name ?? 'E-Shop') }}
                            </span>
                        </div>
                        <h3 class="product-title">{{ $product->name }}</h3>
                        <div class="product-footer">
                            <div class="product-price">
                                @if($product->discount_percentage)
                                    <div class="price-wrapper">
                                        <span class="old-price">${{ number_format($product->price, 2) }}</span>
                                        <span class="new-price">${{ number_format($product->final_price, 2) }}</span>
                                    </div>
                                @else
                                    ${{ number_format($product->price, 2) }}
                                @endif
                            </div>
                            <div class="product-actions">
                                @if($product->stock_status == 'available' && $product->stock_quantity > 0)
                                    <button class="btn-add">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                    </button>
                                    <div class="qty-selector">
                                        <button class="qty-btn minus">−</button>
                                        <span class="qty-value">1</span>
                                        <button class="qty-btn plus">+</button>
                                        <button class="btn-confirm-add">ADD TO CART (1)</button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div style="grid-column: 1/-1; text-align: center; padding: 100px 0; color: var(--color-text-medium);">
                    <p>No products found in our catalog yet.</p>
                </div>
            @endforelse
        </div>
    </section>
    @if($banners->count() > 1)
    <script>
        let currentBanner = 0;
        const banners = document.querySelectorAll('.ad-banner');
        const dots = document.querySelectorAll('.dot');
        const track = document.querySelector('.banner-track');
        const totalBanners = banners.length;

        function showBanner(index) {
            banners.forEach(b => b.classList.remove('active'));
            dots.forEach(d => d.classList.remove('active'));
            
            banners[index].classList.add('active');
            dots[index].classList.add('active');
            
            track.style.transform = `translateX(-${index * 100}%)`;
            currentBanner = index;
        }

        function nextBanner() {
            let next = (currentBanner + 1) % totalBanners;
            showBanner(next);
        }

        let bannerInterval = setInterval(nextBanner, 5000); // 5 seconds

        // Reset interval on manual click
        document.querySelector('.banner-dots').onclick = () => {
            clearInterval(bannerInterval);
            bannerInterval = setInterval(nextBanner, 5000);
        };
    </script>
    @endif
@endsection
