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
    </style>
@endsection

@section('content')
    <section class="products-section">
        <div class="section-header">
            <h2 class="section-title">All Products</h2>
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
                        <span class="product-category">{{ $product->category->name }}</span>
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
@endsection
