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
        }

        .ad-banner {
            position: relative;
            width: 100%;
            height: 450px;
            background: var(--color-bg-light);
            border-radius: var(--radius-md);
            overflow: hidden;
            display: flex;
            align-items: center;
            padding: 0 8%;
            box-shadow: var(--shadow-sm);
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

        @keyframes slideRight { to { opacity: 1; transform: translateX(0); } }
        @keyframes slideLeft { to { opacity: 1; transform: translateX(0); } }

        @media (max-width: 992px) {
            .ad-title { font-size: 2.8rem; }
            .ad-image-wrapper { opacity: 0.3; right: -10%; }
            .ad-content { max-width: 100%; z-index: 20; }
        }

        @media (max-width: 768px) {
            .ad-banner { height: 380px; padding: 0 5%; }
            .ad-title { font-size: 2.2rem; }
            .section-title { font-size: 1.8rem; }
        }
    </style>
@endsection

@section('content')
    <!-- ========== AD BANNER ========== -->
    <section class="ad-banner-section">
        <div class="ad-banner">
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
    </section>

    <!-- ========== PRODUCTS SECTION ========== -->
    <section class="products-section">
        <div class="section-header">
            <h2 class="section-title">New Arrivals</h2>
            <a href="{{ route('products') }}" class="view-all">View Collection <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg></a>
        </div>
        
        <div class="product-grid">
            <!-- Product 1 -->
            <div class="product-card" data-product-id="p1">
                <div class="product-badge">New</div>
                <div class="product-image">
                    <img src="https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&q=80&w=600" alt="Nike Shoes">
                </div>
                <div class="product-info">
                    <span class="product-category">Footwear</span>
                    <h3 class="product-title">Nike Revolution 5 Running Shoes</h3>
                    <div class="product-footer">
                        <div class="product-price">$120.00</div>
                        <div class="product-actions">
                            <button class="btn-add">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                            </button>
                            <div class="qty-selector">
                                <button class="qty-btn minus">−</button>
                                <span class="qty-value">1</span>
                                <button class="qty-btn plus">+</button>
                                <button class="btn-confirm-add">ADD TO CART (1)</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Product 2 -->
            <div class="product-card" data-product-id="p4">
                <div class="product-badge" style="background: #28a745;">-15%</div>
                <div class="product-image">
                    <img src="https://images.unsplash.com/photo-1583394838336-acd977736f90?auto=format&fit=crop&q=80&w=600" alt="Headphones">
                </div>
                <div class="product-info">
                    <span class="product-category">Audio</span>
                    <h3 class="product-title">Beats Solo Pro Wireless On-Ear</h3>
                    <div class="product-footer">
                        <div class="product-price">$249.99</div>
                        <div class="product-actions">
                            <button class="btn-add">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                            </button>
                            <div class="qty-selector">
                                <button class="qty-btn minus">−</button>
                                <span class="qty-value">1</span>
                                <button class="qty-btn plus">+</button>
                                <button class="btn-confirm-add">ADD TO CART (1)</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Product 3 -->
            <div class="product-card" data-product-id="p2">
                <div class="product-image">
                    <img src="https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&q=80&w=600" alt="Watch">
                </div>
                <div class="product-info">
                    <span class="product-category">Accessories</span>
                    <h3 class="product-title">Apple Watch Series 8 GPS</h3>
                    <div class="product-footer">
                        <div class="product-price">$399.00</div>
                        <div class="product-actions">
                            <button class="btn-add">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                            </button>
                            <div class="qty-selector">
                                <button class="qty-btn minus">−</button>
                                <span class="qty-value">1</span>
                                <button class="qty-btn plus">+</button>
                                <button class="btn-confirm-add">ADD TO CART (1)</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Product 4 -->
            <div class="product-card" data-product-id="p3">
                <div class="product-badge" style="background: #1A1A1A;">Hot</div>
                <div class="product-image">
                    <img src="https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&q=80&w=800" alt="Headphones">
                </div>
                <div class="product-info">
                    <span class="product-category">Audio</span>
                    <h3 class="product-title">Sony WH-1000XM4 Noise Cancelling</h3>
                    <div class="product-footer">
                        <div class="product-price">$298.00</div>
                        <div class="product-actions">
                            <button class="btn-add">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                            </button>
                            <div class="qty-selector">
                                <button class="qty-btn minus">−</button>
                                <span class="qty-value">1</span>
                                <button class="qty-btn plus">+</button>
                                <button class="btn-confirm-add">ADD TO CART (1)</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
