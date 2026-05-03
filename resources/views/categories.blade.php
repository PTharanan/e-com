@extends('layouts.master')

@section('title', 'Categories')

@section('styles')
    <style>
        /* ========== CATEGORIES SECTION ========== */
        .categories-section { padding: 60px 5%; max-width: 1500px; margin: 0 auto; }
        .section-header { margin-bottom: 40px; text-align: center; }
        .section-title { font-size: 2.2rem; font-weight: 700; color: var(--color-text-dark); margin-bottom: 10px; }
        
        .category-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 20px; }
        .category-card { 
            position: relative; 
            border-radius: 20px; 
            overflow: hidden; 
            height: 280px; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            text-decoration: none; 
            transition: var(--transition);
        }
        
        .category-card::before { 
            content: ''; 
            position: absolute; 
            inset: 0; 
            background: rgba(0,0,0,0.3); 
            z-index: 1; 
            transition: background 0.3s; 
        }
        
        .category-card:hover::before { background: rgba(0,0,0,0.5); }
        
        .category-image { 
            position: absolute; 
            width: 100%; 
            height: 100%; 
            object-fit: cover; 
            transition: transform 0.8s cubic-bezier(0.4, 0, 0.2, 1); 
        }
        
        .category-card:hover .category-image { transform: scale(1.1); }
        
        .category-content { 
            position: relative; 
            z-index: 2; 
            text-align: center; 
            color: white; 
            padding: 20px; 
        }
        
        .category-title { 
            font-size: 2rem; 
            font-weight: 800; 
            margin-bottom: 12px; 
            letter-spacing: 0.5px; 
            text-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }

        .category-count {
            display: inline-block;
            padding: 8px 16px;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 50px;
            font-size: 0.9rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: var(--transition);
        }

        .category-card:hover .category-count {
            background: var(--color-primary);
            border-color: var(--color-primary);
            color: white;
        }
        
        .category-count { 
            font-size: 0.85rem; 
            font-weight: 600;
            background: rgba(255,255,255,0.25); 
            padding: 8px 20px; 
            border-radius: 50px; 
            backdrop-filter: blur(10px); 
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.1);
            display: inline-block;
        }

        @media (max-width: 768px) {
            .category-grid { grid-template-columns: 1fr; }
            .category-title { font-size: 1.6rem; }
        }
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
            @forelse($categories as $category)
                <a href="#" class="category-card">
                    <img class="category-image" src="{{ asset($category->dp_img_url) }}" alt="{{ $category->name }}">
                    <div class="category-content">
                        <h3 class="category-title">{{ $category->name }}</h3>
                        <span class="category-count">{{ $category->products_count }} {{ $category->products_count == 1 ? 'Item' : 'Items' }}</span>
                    </div>
                </a>
            @empty
                <div style="grid-column: 1/-1; text-align: center; padding: 100px 0; color: var(--color-text-medium);">
                    <p>No categories found yet.</p>
                </div>
            @endforelse
        </div>
    </section>
@endsection
