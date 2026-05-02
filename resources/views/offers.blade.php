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
        
        <div class="offer-card">
            <div class="offer-image">
                <img src="https://images.unsplash.com/photo-1607082348824-0a96f2a4b9da?auto=format&fit=crop&q=80&w=800" alt="Summer Sale">
            </div>
            <div class="offer-details">
                <div class="offer-badge">Save 30%</div>
                <h3 class="offer-title">Summer Electronics Sale</h3>
                <p class="offer-desc">Upgrade your tech this summer. Get up to 30% off on all premium headphones, smartwatches, and speakers. Limited time only!</p>
                <a href="#" class="btn-claim">Claim Offer</a>
            </div>
        </div>

        <div class="offer-card" style="flex-direction: row-reverse;">
            <div class="offer-image">
                <img src="https://images.unsplash.com/photo-1606220838315-056192d5e927?auto=format&fit=crop&q=80&w=800" alt="BOGO Offer">
            </div>
            <div class="offer-details">
                <div class="offer-badge" style="background: #28a745;">BOGO Free</div>
                <h3 class="offer-title">Buy One Get One Free</h3>
                <p class="offer-desc">Buy any pair of running shoes and get the second pair absolutely free. Mix and match styles and sizes.</p>
                <a href="#" class="btn-claim">Shop Sneakers</a>
            </div>
        </div>
    </section>
@endsection
