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

        /* Floating Filter Button */
        .floating-filter-btn {
            position: fixed;
            right: 20px;
            top: 140px; /* Above mobile bottom nav */
            width: 55px;
            height: 55px;
            background: var(--color-primary);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 15px rgba(242, 92, 59, 0.4);
            cursor: pointer;
            z-index: 1000;
            border: none;
            transition: var(--transition-fast);
        }

        .floating-filter-btn:hover {
            transform: scale(1.1);
        }

        /* Filter Drawer */
        .filter-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0,0,0,0.5);
            z-index: 1999;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
        }

        .filter-overlay.active {
            opacity: 1;
            pointer-events: all;
        }

        .filter-drawer {
            position: fixed;
            top: 0;
            right: -100%;
            width: 350px;
            max-width: 100%;
            height: 100vh;
            background: white;
            z-index: 2000;
            box-shadow: -5px 0 30px rgba(0,0,0,0.1);
            transition: right 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
        }

        .filter-drawer.active {
            right: 0;
        }

        .filter-drawer-header {
            padding: 20px;
            border-bottom: 1px solid var(--color-border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .filter-drawer-header h3 {
            font-size: 1.2rem;
            color: var(--color-text-dark);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .close-filter {
            background: none;
            border: none;
            color: var(--color-text-medium);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 5px;
            transition: var(--transition-fast);
        }

        .close-filter:hover {
            color: var(--color-primary);
            transform: rotate(90deg);
        }

        .filter-drawer-body {
            padding: 25px;
            overflow-y: auto;
            flex: 1;
        }

        .filter-bar {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .filter-bar input,
        .filter-bar select {
            padding: 12px 15px;
            border: 1px solid #E2E8F0;
            border-radius: var(--radius-sm);
            outline: none;
            width: 100%;
            font-size: 0.95rem;
            color: var(--color-text-dark);
            background: #fff;
        }

        .filter-bar input:focus,
        .filter-bar select:focus {
            border-color: var(--color-primary);
            box-shadow: 0 0 0 2px rgba(242, 92, 59, 0.1);
        }

        .filter-bar button {
            background: var(--color-primary);
            color: white;
            border: none;
            padding: 14px;
            border-radius: var(--radius-sm);
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition-fast);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            margin-top: 10px;
        }

        .filter-bar button:hover {
            background: var(--color-primary-hover);
        }

        .filter-bar a.btn-clear {
            background: #E2E8F0;
            color: var(--color-text-dark);
            text-decoration: none;
            padding: 14px;
            border-radius: var(--radius-sm);
            font-weight: 600;
            transition: var(--transition-fast);
            text-align: center;
            display: block;
            width: 100%;
        }

        .filter-bar a.btn-clear:hover {
            background: #cbd5e1;
        }

        @media (max-width: 480px) {
            .products-section {
                padding: 30px 5%;
            }
            .section-title {
                font-size: 1.5rem;
            }
            .floating-filter-btn {
                position: sticky;
                top: 10px; /* 10px below the 75px navbar */
                float: right;
                margin-top: 2px; /* Align with "All Products" title */
                margin-right: 0;
                width: 46px;
                height: 46px;
                z-index: 1001;
                box-shadow: 0 8px 20px rgba(242, 92, 59, 0.2);
            }
        }
    </style>
@section('content')
    <div class="filter-overlay" id="filterOverlay"></div>

    <div class="filter-drawer" id="filterDrawer">
        <div class="filter-drawer-header">
            <h3>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
                Filters
            </h3>
            <button class="close-filter" id="closeFilterBtn">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>
        <div class="filter-drawer-body">
            <form action="{{ route('products') }}" method="GET" class="filter-bar">
                <div>
                    <label style="display:block; margin-bottom: 5px; font-weight: 600; font-size: 0.9rem;">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search products...">
                </div>
                <div>
                    <label style="display:block; margin-bottom: 5px; font-weight: 600; font-size: 0.9rem;">Category</label>
                    <select name="category">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="display:block; margin-bottom: 5px; font-weight: 600; font-size: 0.9rem;">Min Price</label>
                    <input type="number" name="min_price" value="{{ request('min_price') }}" placeholder="0" min="0">
                </div>
                <div>
                    <label style="display:block; margin-bottom: 5px; font-weight: 600; font-size: 0.9rem;">Max Price</label>
                    <input type="number" name="max_price" value="{{ request('max_price') }}" placeholder="Any" min="0">
                </div>
                
                <button type="submit">Apply Filters</button>
                @if(request('search') || request('category') || request('min_price') || request('max_price'))
                    <a href="{{ route('products') }}" class="btn-clear">Clear Filters</a>
                @endif
            </form>
        </div>
    </div>

    <section class="products-section">
        <button class="floating-filter-btn" id="openFilterBtn" title="Filter Products">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
        </button>

        <div class="section-header" style="margin-bottom: 40px;">
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
                                        <span class="old-price">{{ currency_symbol() }}{{ number_format($product->price, 2) }}</span>
                                        <span class="new-price">{{ currency_symbol() }}{{ number_format($product->final_price, 2) }}</span>
                                    </div>
                                @else
                                    {{ currency_symbol() }}{{ number_format($product->price, 2) }}
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('.filter-bar');
            if (!form) return;

            const searchInput = form.querySelector('input[name="search"]');
            const otherInputs = form.querySelectorAll('select, input[type="number"]');

            // Auto-submit when select or number inputs change
            otherInputs.forEach(input => {
                input.addEventListener('change', () => {
                    form.submit();
                });
            });

            // Debounce function for text search so it doesn't submit on every single keystroke
            let timeout = null;
            searchInput.addEventListener('input', () => {
                clearTimeout(timeout);
                timeout = setTimeout(() => {
                    // Remember focus so we can restore it after page reload
                    sessionStorage.setItem('search_focused', 'true');
                    form.submit();
                }, 600); // Wait 600ms after user stops typing
            });

            // Focus logic
            if (sessionStorage.getItem('filter_drawer_open') === 'true') {
                const drawer = document.getElementById('filterDrawer');
                const overlay = document.getElementById('filterOverlay');
                if(drawer && overlay) {
                    drawer.classList.add('active');
                    overlay.classList.add('active');
                    document.body.style.overflow = 'hidden';
                }
                
                if (sessionStorage.getItem('search_focused') === 'true') {
                    sessionStorage.removeItem('search_focused');
                    searchInput.focus();
                    if (searchInput.value) {
                        const val = searchInput.value;
                        searchInput.value = '';
                        searchInput.value = val;
                    }
                }
            }

            // Drawer Toggle Logic
            const openBtn = document.getElementById('openFilterBtn');
            const closeBtn = document.getElementById('closeFilterBtn');
            const drawer = document.getElementById('filterDrawer');
            const overlay = document.getElementById('filterOverlay');

            function openDrawer() {
                drawer.classList.add('active');
                overlay.classList.add('active');
                sessionStorage.setItem('filter_drawer_open', 'true');
                document.body.style.overflow = 'hidden';
            }

            function closeDrawer() {
                drawer.classList.remove('active');
                overlay.classList.remove('active');
                sessionStorage.setItem('filter_drawer_open', 'false');
                document.body.style.overflow = '';
            }

            if (openBtn) openBtn.addEventListener('click', openDrawer);
            if (closeBtn) closeBtn.addEventListener('click', closeDrawer);
            if (overlay) overlay.addEventListener('click', closeDrawer);
        });
    </script>
@endsection
