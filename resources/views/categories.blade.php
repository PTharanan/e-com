@extends('layouts.master')

@section('title', 'Categories')

@section('styles')
    <style>
        /* ========== CATEGORIES SECTION ========== */
        .categories-section { padding: 60px 5%; max-width: 1500px; margin: 0 auto; }
        .section-header { margin-bottom: 40px; text-align: center; }
        .section-title { font-size: 2.2rem; font-weight: 700; color: var(--color-text-dark); margin-bottom: 10px; }
        
        .category-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px; }
        .category-card { position: relative; border-radius: var(--radius-md); overflow: hidden; height: 300px; display: flex; align-items: center; justify-content: center; text-decoration: none; }
        .category-card::before { content: ''; position: absolute; inset: 0; background: rgba(0,0,0,0.4); z-index: 1; transition: background 0.3s; }
        .category-card:hover::before { background: rgba(242, 92, 59, 0.7); }
        .category-image { position: absolute; width: 100%; height: 100%; object-fit: cover; transition: transform 0.6s ease; }
        .category-card:hover .category-image { transform: scale(1.1); }
        .category-content { position: relative; z-index: 2; text-align: center; color: white; padding: 20px; }
        .category-title { font-size: 1.8rem; font-weight: 700; margin-bottom: 10px; letter-spacing: 1px; }
        .category-count { font-size: 0.9rem; background: rgba(255,255,255,0.2); padding: 5px 15px; border-radius: 20px; backdrop-filter: blur(5px); }
    </style>
@endsection

@section('content')
    <!-- ========== CATEGORIES SECTION ========== -->
    <section class="categories-section">
        <div class="section-header">
            <h2 class="section-title">Shop by Category</h2>
            <p style="color: var(--color-text-medium);">Find exactly what you are looking for.</p>
        </div>
        
        <div class="category-grid">
            <a href="#" class="category-card">
                <img class="category-image" src="https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&q=80&w=800" alt="Electronics">
                <div class="category-content">
                    <h3 class="category-title">Electronics</h3>
                    <span class="category-count">124 Items</span>
                </div>
            </a>
            <a href="#" class="category-card">
                <img class="category-image" src="https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&q=80&w=800" alt="Footwear">
                <div class="category-content">
                    <h3 class="category-title">Footwear</h3>
                    <span class="category-count">86 Items</span>
                </div>
            </a>
            <a href="#" class="category-card">
                <img class="category-image" src="https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&q=80&w=800" alt="Accessories">
                <div class="category-content">
                    <h3 class="category-title">Accessories</h3>
                    <span class="category-count">52 Items</span>
                </div>
            </a>
        </div>
    </section>
@endsection
