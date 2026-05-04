@extends('layouts.master')

@section('title', 'Special Offers')

@section('styles')
    <style>
        /* ========== OFFERS SECTION ========== */
        .offers-section { padding: 60px 5%; max-width: 1500px; margin: 0 auto; }
        .section-header { margin-bottom: 40px; text-align: center; }
        .section-title { font-size: 2.2rem; font-weight: 700; color: var(--color-text-dark); margin-bottom: 10px; }
        
        .offer-card { background: var(--color-bg-light); border-radius: var(--radius-md); padding: 40px; display: flex; align-items: center; gap: 40px; margin-bottom: 30px; box-shadow: var(--shadow-sm); overflow: hidden; position: relative; }
        .offer-card::before { content: ''; position: absolute; top: -50px; right: -50px; width: 200px; height: 200px; background: rgba(242, 92, 59, 0.1); border-radius: 50%; }
        .offer-image { width: 300px; height: 200px; border-radius: var(--radius-sm); overflow: hidden; flex-shrink: 0; box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
        .offer-image img { width: 100%; height: 100%; object-fit: cover; }
        .offer-details { flex: 1; z-index: 1; }
        .offer-badge { display: inline-block; background: var(--color-primary); color: white; padding: 5px 15px; border-radius: var(--radius-pill); font-weight: bold; margin-bottom: 15px; font-size: 0.9rem; }
        .offer-title { font-size: 2rem; font-weight: 800; margin-bottom: 15px; color: var(--color-text-dark); }
        .offer-desc { font-size: 1.1rem; color: var(--color-text-medium); margin-bottom: 25px; }

        .btn-claim {
            padding: 12px 30px;
            background: var(--color-primary);
            color: white;
            text-decoration: none;
            border-radius: var(--radius-pill);
            font-weight: 600;
            transition: 0.3s;
            display: inline-block;
        }

        .btn-claim:hover {
            background: var(--color-primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(242, 92, 59, 0.3);
        }

        @media (max-width: 768px) {
            .offer-card { flex-direction: column; padding: 25px; }
            .offer-image { width: 100%; height: 180px; }
            .offer-title { font-size: 1.5rem; }
        }
    </style>
@endsection

@section('content')
    <!-- ========== OFFERS SECTION ========== -->
    <section class="offers-section">
        <div class="section-header">
            <h2 class="section-title">Special Offers</h2>
            <p style="color: var(--color-text-medium);">Don't miss out on these exclusive deals.</p>
        </div>
        
        @forelse($products as $product)
            <div class="offer-card" @if($loop->iteration % 2 == 0) style="flex-direction: row-reverse;" @endif>
                <div class="offer-image">
                    <img src="{{ asset($product->main_image_url) }}" alt="{{ $product->name }}">
                </div>
                <div class="offer-details">
                    <div class="offer-badge" @if($loop->iteration % 2 == 0) style="background: #28a745;" @endif>Save {{ $product->discount_percentage }}%</div>
                    <h3 class="offer-title">{{ $product->name }}</h3>
                    <p class="offer-desc">{{ Str::limit($product->description, 150) }}</p>
                    <div style="margin-bottom: 20px;">
                        <span style="font-size: 1.1rem; color: #9CA3AF; text-decoration: line-through; margin-right: 10px;">${{ number_format($product->price, 2) }}</span>
                        <span style="color: #10B981; font-weight: 800; font-size: 1.8rem;">${{ number_format($product->final_price, 2) }}</span>
                    </div>
                    <a href="{{ route('products') }}" class="btn-claim">Shop Now</a>
                </div>
            </div>
        @empty
            <div style="text-align: center; padding: 100px 0; color: var(--color-text-medium);">
                <p>No special offers at the moment. Check back later!</p>
            </div>
        @endforelse
    </section>
@endsection
