@extends('layouts.master')

@section('title', 'Products')

@section('styles')
    <style>
        /* ========== PRODUCTS SECTION LAYOUT ========== */
        .products-section {
            padding: 60px 5%;
            max-width: 1500px;
            margin: 0 auto;
        }

        .section-header {
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
    </style>
@endsection

@section('content')
    <section class="products-section">
        <div class="section-header">
            <h2 class="section-title">All Products</h2>
        </div>

        <div class="product-grid">
            <!-- Product Card 1 -->
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

            <!-- Product Card 2 -->
            <div class="product-card" data-product-id="p2">
                <div class="product-image">
                    <img src="https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&q=80&w=600" alt="Apple Watch">
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

            <!-- Product Card 3 -->
            <div class="product-card" data-product-id="p3">
                <div class="product-image">
                    <img src="https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&q=80&w=600" alt="Sony Headphones">
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
            
            <!-- Product Card 4 -->
            <div class="product-card" data-product-id="p4">
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
        </div>
    </section>
@endsection
