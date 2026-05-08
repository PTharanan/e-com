@extends('layouts.master')
@section('title', $product->name)
@section('styles')
<style>
/* Preload Hint */
</style>
<link rel="preload" as="image" href="{{ asset($product->main_image_url) }}" fetchpriority="high">
<style>
.pd-wrap{max-width:1300px;margin:0 auto;padding:40px 5%}
.pd-main{display:grid;grid-template-columns:1fr 1fr;gap:50px;margin-bottom:60px}
.pd-gallery{position:relative}
.pd-main-img{width:100%;aspect-ratio:1;background:#F8F9FA;border-radius:var(--radius-md);display:flex;align-items:center;justify-content:center;overflow:visible;border:1px solid var(--color-border);cursor:crosshair;position:relative}
.pd-main-img img{pointer-events:none;width:100%;height:100%;object-fit:cover;transition:.4s ease}
.zoom-lens{position:absolute;width:100px;height:100px;border:2px solid var(--color-primary);border-radius:50%;pointer-events:none;opacity:0;transition:opacity .2s;z-index:10;background:rgba(242,92,59,.08)}
.zoom-result{position:absolute;left:calc(100% + 20px);top:0;width:400px;height:400px;border:1px solid var(--color-border);border-radius:var(--radius-md);background-repeat:no-repeat;background-color:#fff;opacity:0;pointer-events:none;transition:opacity .3s;z-index:20;box-shadow:0 10px 40px rgba(0,0,0,.12);overflow:hidden}
.pd-main-img:hover .zoom-lens,.pd-main-img:hover .zoom-result{opacity:1}
@media(max-width:768px){.zoom-lens,.zoom-result{display:none!important}}
.pd-thumbs{display:flex;gap:12px;margin-top:16px;overflow-x:auto;padding-bottom:5px}
.pd-thumb{width:72px;height:72px;border-radius:var(--radius-sm);border:2px solid var(--color-border);cursor:pointer;overflow:hidden;display:flex;align-items:center;justify-content:center;background:#F8F9FA;transition:.3s;flex-shrink:0}
.pd-thumb:hover,.pd-thumb.active{border-color:var(--color-primary);box-shadow:0 0 0 2px rgba(242,92,59,.15)}
.pd-thumb img{width:100%;height:100%;object-fit:cover}
.pd-info{display:flex;flex-direction:column;gap:10px}
.pd-cat{font-size:.8rem;color:var(--color-primary);text-transform:uppercase;font-weight:700;letter-spacing:1px;background:rgba(242,92,59,.08);display:inline-block;padding:5px 14px;border-radius:var(--radius-pill)}
.pd-name{font-size:2.2rem;font-weight:800;color:var(--color-text-dark);line-height:1.2;letter-spacing:-.5px}
.pd-stars{display:flex;align-items:center;gap:8px;margin:5px 0}
.pd-stars .stars{color:#F59E0B;font-size:1.1rem;letter-spacing:2px}
.pd-stars .count{font-size:.85rem;color:var(--color-text-light)}
.pd-desc{font-size:.95rem;color:var(--color-text-medium);line-height:1.7}
.pd-seller{display:flex;align-items:center;gap:8px;font-size:.85rem;color:var(--color-text-medium);padding:10px 0;border-top:1px solid var(--color-border);border-bottom:1px solid var(--color-border);margin:5px 0}
.pd-seller svg{color:var(--color-primary)}
.pd-price-row{display:flex;align-items:baseline;gap:14px;margin:10px 0}
.pd-final{font-size:2.4rem;font-weight:800;color:var(--color-text-dark)}
.pd-old{font-size:1.2rem;color:#9CA3AF;text-decoration:line-through}
.pd-discount{background:#10B981;color:#fff;font-size:.8rem;font-weight:700;padding:4px 12px;border-radius:var(--radius-pill)}
.pd-stock{font-size:.85rem;font-weight:600}
.pd-stock.in{color:#10B981}
.pd-stock.out{color:#EF4444}
.pd-actions{display:flex;gap:12px;margin-top:10px}
.pd-qty{display:flex;align-items:center;border:1.5px solid var(--color-border);border-radius:var(--radius-sm);overflow:hidden}
.pd-qty button{width:42px;height:46px;border:none;background:#F8F9FA;cursor:pointer;font-size:1.2rem;font-weight:700;color:var(--color-text-dark);transition:.2s}
.pd-qty button:hover{background:var(--color-primary);color:#fff}
.pd-qty span{width:45px;text-align:center;font-weight:700;font-size:1rem}
.pd-buy{flex:1;height:46px;background:var(--color-primary);color:#fff;border:none;border-radius:var(--radius-sm);font-weight:700;font-size:1rem;cursor:pointer;transition:.3s;display:flex;align-items:center;justify-content:center;gap:8px;box-shadow:0 4px 15px rgba(242,92,59,.25)}
.pd-buy:hover{background:var(--color-primary-hover);transform:translateY(-2px);box-shadow:0 8px 20px rgba(242,92,59,.35)}
.pd-buy:disabled{opacity:.5;cursor:not-allowed;transform:none}

/* Reviews */
.rv-section{max-width:1300px;margin:0 auto;padding:0 5% 60px}
.rv-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:30px}
.rv-title{font-size:1.6rem;font-weight:700;color:var(--color-text-dark)}
.rv-summary{display:grid;grid-template-columns:200px 1fr;gap:40px;background:#fff;border-radius:var(--radius-md);padding:30px;border:1px solid var(--color-border);margin-bottom:30px}
.rv-avg{text-align:center}
.rv-avg .big{font-size:3.5rem;font-weight:800;color:var(--color-text-dark);line-height:1}
.rv-avg .stars{color:#F59E0B;font-size:1.3rem;letter-spacing:2px;margin:8px 0}
.rv-avg .total{font-size:.85rem;color:var(--color-text-light)}
.rv-bars{display:flex;flex-direction:column;gap:8px;justify-content:center}
.rv-bar{display:flex;align-items:center;gap:10px;font-size:.85rem;color:var(--color-text-medium)}
.rv-bar .track{flex:1;height:8px;background:#F1F5F9;border-radius:4px;overflow:hidden}
.rv-bar .fill{height:100%;background:#F59E0B;border-radius:4px;transition:.6s ease}
.rv-bar .num{min-width:28px;text-align:right;font-weight:600}

/* Review Form */
.rv-form-card{background:#fff;border-radius:var(--radius-md);padding:25px;border:1px solid var(--color-border);margin-bottom:30px}
.rv-form-card h3{font-size:1.1rem;font-weight:700;margin-bottom:15px;color:var(--color-text-dark)}
.rv-star-input{display:flex;gap:4px;margin-bottom:15px;direction:rtl;justify-content:flex-end}
.rv-star-input input{display:none}
.rv-star-input label{font-size:1.8rem;color:#D1D5DB;cursor:pointer;transition:.2s}
.rv-star-input label:hover,.rv-star-input label:hover~label,.rv-star-input input:checked~label{color:#F59E0B}
.rv-form-card input[type=text],.rv-form-card textarea{width:100%;padding:12px 15px;border:1.5px solid #E2E8F0;border-radius:var(--radius-sm);font-family:inherit;font-size:.9rem;margin-bottom:12px;outline:none;transition:.2s}
.rv-form-card input[type=text]:focus,.rv-form-card textarea:focus{border-color:var(--color-primary);box-shadow:0 0 0 3px rgba(242,92,59,.08)}
.rv-form-card textarea{resize:vertical;min-height:80px}
.rv-submit{background:var(--color-primary);color:#fff;border:none;padding:12px 28px;border-radius:var(--radius-sm);font-weight:600;cursor:pointer;transition:.3s}
.rv-submit:hover{background:var(--color-primary-hover)}

/* Review List */
.rv-item{background:#fff;border-radius:var(--radius-md);padding:20px;border:1px solid var(--color-border);margin-bottom:15px;transition:.2s}
.rv-item:hover{border-color:rgba(242,92,59,.2)}
.rv-item-header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:10px}
.rv-user{display:flex;align-items:center;gap:12px}
.rv-avatar{width:40px;height:40px;background:var(--color-bg-light);color:var(--color-primary);border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.9rem}
.rv-user-info .name{font-weight:600;color:var(--color-text-dark);font-size:.95rem}
.rv-user-info .date{font-size:.75rem;color:var(--color-text-light)}
.rv-item .stars{color:#F59E0B;font-size:.95rem;letter-spacing:1px}
.rv-item .rv-title-text{font-weight:600;color:var(--color-text-dark);margin-bottom:5px}
.rv-item .rv-comment{font-size:.9rem;color:var(--color-text-medium);line-height:1.6}
.rv-delete{background:none;border:none;color:#EF4444;font-size:.8rem;cursor:pointer;font-weight:600;padding:4px 8px;border-radius:4px;transition:.2s}
.rv-delete:hover{background:rgba(239,68,68,.08)}

/* Related */
.related-section{max-width:1300px;margin:0 auto;padding:0 5% 80px}
.related-title{font-size:1.6rem;font-weight:700;color:var(--color-text-dark);margin-bottom:25px;position:relative;display:inline-block}
.related-title::after{content:'';position:absolute;width:40%;height:4px;bottom:-8px;left:0;background:var(--color-primary);border-radius:2px}

/* Toast */
.toast{position:fixed;top:20px;right:20px;background:#10B981;color:#fff;padding:14px 24px;border-radius:var(--radius-sm);font-weight:600;font-size:.9rem;z-index:9999;transform:translateX(120%);opacity:0;visibility:hidden;transition:.4s cubic-bezier(.4,0,.2,1);box-shadow:0 8px 25px rgba(0,0,0,.15)}
.toast.show{transform:translateX(0);opacity:1;visibility:visible}
.toast.error{background:#EF4444}

@media (max-width: 992px) {
    .pd-main { gap: 30px; }
    .pd-name { font-size: 1.8rem; }
}

@media (max-width: 768px) {
    .pd-wrap { padding: 20px 5%; }
    .pd-main { grid-template-columns: 1fr; gap: 30px; margin-bottom: 40px; }
    .pd-name { font-size: 1.6rem; }
    .pd-final { font-size: 1.8rem; }
    
    .rv-summary { grid-template-columns: 1fr; gap: 25px; padding: 20px; }
    .rv-avg { display: flex; align-items: center; gap: 20px; text-align: left; justify-content: center; border-bottom: 1px solid var(--color-border); padding-bottom: 20px; }
    .rv-avg .big { font-size: 2.8rem; }
    .rv-avg .stars { margin: 4px 0; }
    
    .rv-header { flex-direction: column; align-items: flex-start; gap: 10px; }
    .rv-item-header { flex-direction: column; gap: 8px; }
    .rv-item-header > div:last-child { align-self: flex-start; }
    
    .pd-actions { 
        position: fixed; 
        bottom: 70px; 
        left: 0; 
        width: 100%; 
        background: #fff; 
        padding: 12px 20px; 
        box-shadow: 0 -5px 20px rgba(0,0,0,0.08); 
        z-index: 1000; 
    }
    body { padding-bottom: 150px; }
}

@media (max-width: 480px) {
    .pd-name { font-size: 1.4rem; }
    .pd-thumbs { gap: 8px; }
    .pd-thumb { width: 60px; height: 60px; }
}

/* Lightbox Styles */
.lb-modal {
    position: fixed;
    top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(0,0,0,0.98);
    z-index: 10000;
    display: none;
    align-items: center;
    justify-content: center;
    user-select: none;
    backdrop-filter: blur(10px);
}
.lb-modal.active { display: flex; }
.lb-close {
    position: absolute;
    top: 25px; right: 25px;
    color: var(--color-primary);
    font-size: 35px;
    cursor: pointer;
    z-index: 10001;
    text-shadow: 0 2px 10px rgba(0,0,0,0.5);
}
.lb-main-img {
    max-width: 95%;
    max-height: 85%;
    object-fit: contain;
}
.lb-nav {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 50px;
    height: 50px;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    color: var(--color-primary);
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    cursor: pointer;
    z-index: 10001;
    transition: all .3s ease;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
}
.lb-nav:hover { 
    background: rgba(0, 0, 0, 0.8); 
    transform: translateY(-50%) scale(1.1);
}
.lb-prev { left: 15px; }
.lb-next { right: 15px; }
</style>
@endsection

@section('content')
<div class="toast" id="toast"></div>
<div class="pd-wrap">
    <div class="pd-main">
        <div class="pd-gallery">
            <div class="pd-main-img" id="mainImgWrap" onmousemove="zoomMove(event)" onmouseleave="zoomOut()" onclick="openLightbox()">
                <img src="{{ asset($product->main_image_url) }}" alt="{{ $product->name }}" id="mainImg" fetchpriority="high">
                <div class="zoom-lens" id="zoomLens"></div>
                <div class="zoom-result" id="zoomResult"></div>
            </div>
            @if($product->image_urls && count($product->image_urls) > 1)
            <div class="pd-thumbs">
                @foreach($product->image_urls as $i => $img)
                <div class="pd-thumb {{ $img == $product->main_image_url ? 'active' : '' }}" onclick="changeImg(this,'{{ asset($img) }}')">
                    <img src="{{ asset($img) }}" alt="thumb" loading="lazy">
                </div>
                @endforeach
            </div>
            @endif
        </div>
        <div class="pd-info">
            <span class="pd-cat">{{ $product->category->name }}</span>
            <h1 class="pd-name">{{ $product->name }}</h1>
            <div class="pd-stars">
                <span class="stars">{!! str_repeat('★', floor($product->average_rating)) . str_repeat('☆', 5 - floor($product->average_rating)) !!}</span>
                <span class="count">{{ $product->average_rating }} ({{ $product->reviews_count }} {{ Str::plural('review', $product->reviews_count) }})</span>
            </div>
            @if($product->description)
            <p class="pd-desc">{{ $product->description }}</p>
            @endif
            <div class="pd-seller">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                Sold by <strong>{{ $product->seller_id ? $product->seller->name : 'E-Shop' }}</strong>
            </div>
            <div class="pd-price-row">
                <span class="pd-final">{{ currency_symbol() }}{{ number_format($product->final_price, 2) }}</span>
                @if($product->discount_percentage)
                <span class="pd-old">{{ currency_symbol() }}{{ number_format($product->price, 2) }}</span>
                <span class="pd-discount">-{{ $product->discount_percentage }}% OFF</span>
                @endif
            </div>
            @if($product->stock_quantity > 0)
            <span class="pd-stock in">✓ In Stock ({{ $product->stock_quantity }} available)</span>
            @else
            <span class="pd-stock out">✕ Out of Stock</span>
            @endif
            <div class="pd-actions">
                @if($product->stock_quantity > 0)
                <div class="pd-qty">
                    <button type="button" onclick="changeQty(-1)">−</button>
                    <span id="pdQty">1</span>
                    <button type="button" onclick="changeQty(1)">+</button>
                </div>
                <button class="pd-buy" id="pdBuyBtn" onclick="addToCartDetail()">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg>
                    ADD TO CART
                </button>
                @else
                <button class="pd-buy" disabled style="opacity:.5;cursor:not-allowed;width:100%">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"></line></svg>
                    OUT OF STOCK
                </button>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="rv-section">
    <div class="rv-header"><h2 class="rv-title">Customer Reviews</h2></div>
    @if($product->reviews->count() > 0)
    <div class="rv-summary">
        <div class="rv-avg">
            <div class="big">{{ $product->average_rating }}</div>
            <div class="stars">{!! str_repeat('★', floor($product->average_rating)) . str_repeat('☆', 5 - floor($product->average_rating)) !!}</div>
            <div class="total">{{ $product->reviews_count }} {{ Str::plural('review', $product->reviews_count) }}</div>
        </div>
        <div class="rv-bars">
            @for($i = 5; $i >= 1; $i--)
            <div class="rv-bar">
                <span>{{ $i }}★</span>
                <div class="track"><div class="fill" style="width:{{ $product->reviews_count > 0 ? ($ratingBreakdown[$i] / $product->reviews_count * 100) : 0 }}%"></div></div>
                <span class="num">{{ $ratingBreakdown[$i] }}</span>
            </div>
            @endfor
        </div>
    </div>
    @endif

    @auth
    <div class="rv-form-card">
        <h3>{{ $userReview ? 'Update Your Review' : 'Write a Review' }}</h3>
        <form id="reviewForm">
            @csrf
            <div class="rv-star-input">
                @for($i = 5; $i >= 1; $i--)
                <input type="radio" name="rating" id="star{{ $i }}" value="{{ $i }}" {{ ($userReview && $userReview->rating == $i) ? 'checked' : '' }}>
                <label for="star{{ $i }}">★</label>
                @endfor
            </div>
            <input type="text" name="title" placeholder="Review title (optional)" value="{{ $userReview->title ?? '' }}">
            <textarea name="comment" placeholder="Share your experience...">{{ $userReview->comment ?? '' }}</textarea>
            <button type="submit" class="rv-submit">{{ $userReview ? 'Update Review' : 'Submit Review' }}</button>
        </form>
    </div>
    @else
    <div class="rv-form-card" style="text-align:center"><p>Please <a href="{{ route('sign-in') }}" style="color:var(--color-primary);font-weight:600">sign in</a> to write a review.</p></div>
    @endauth

    <div id="reviewList">
        @foreach($product->reviews->sortByDesc('created_at') as $review)
        <div class="rv-item" id="review-{{ $review->id }}">
            <div class="rv-item-header">
                <div class="rv-user">
                    <div class="rv-avatar">{{ strtoupper(substr($review->user->name, 0, 1)) }}</div>
                    <div class="rv-user-info"><div class="name">{{ $review->user->name }}</div><div class="date">{{ $review->created_at->diffForHumans() }}</div></div>
                </div>
                <div style="display:flex;align-items:center;gap:10px">
                    <span class="stars">{!! str_repeat('★', $review->rating) . str_repeat('☆', 5 - $review->rating) !!}</span>
                    @if(auth()->check() && auth()->id() == $review->user_id)
                    <button class="rv-delete" onclick="deleteReview({{ $review->id }})">Delete</button>
                    @endif
                </div>
            </div>
            @if($review->title)<div class="rv-title-text">{{ $review->title }}</div>@endif
            @if($review->comment)<div class="rv-comment">{{ $review->comment }}</div>@endif
        </div>
        @endforeach
    </div>
</div>

@if($relatedProducts->count() > 0)
<div class="related-section">
    <h2 class="related-title">You May Also Like</h2>
    <div class="product-grid" style="margin-top:30px">
        @foreach($relatedProducts as $rp)
        <div class="product-card" onclick="window.location='{{ route('product.show', $rp->id) }}'" data-product-id="{{ $rp->id }}" data-stock="{{ $rp->stock_quantity }}">
            <div class="product-image"><img src="{{ asset($rp->main_image_url) }}" alt="{{ $rp->name }}" loading="lazy"></div>
            <div class="product-info">
                <span class="product-category">{{ $rp->category->name }}</span>
                <h3 class="product-title">{{ $rp->name }}</h3>
                <div class="product-footer">
                    <div class="product-price">{{ currency_symbol() }}{{ number_format($rp->final_price, 2) }}</div>
                    <div class="product-actions">
                        @if($rp->stock_quantity > 0)
                            <button class="btn-add">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

<div class="lb-modal" id="lbModal">
    <span class="lb-close" onclick="closeLightbox()">&times;</span>
    <button class="lb-nav lb-prev" onclick="changeLbImg(-1)">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--color-primary)" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
    </button>
    <img src="" id="lbImg" class="lb-main-img">
    <button class="lb-nav lb-next" onclick="changeLbImg(1)">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--color-primary)" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
    </button>
</div>

<script>
const productId = {{ $product->id }};
const stockLimit = {{ $product->stock_quantity }};
const productImages = @json($product->image_urls ?? [$product->main_image_url]);
let currentImgIndex = productImages.indexOf('{{ $product->main_image_url }}');
if(currentImgIndex === -1) currentImgIndex = 0;

function openLightbox(){
    if(window.innerWidth > 768) return; 
    document.getElementById('lbImg').src = '{{ asset("") }}' + productImages[currentImgIndex];
    document.getElementById('lbModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}
function closeLightbox(){
    document.getElementById('lbModal').classList.remove('active');
    document.body.style.overflow = 'auto';
}
function changeLbImg(dir){
    currentImgIndex += dir;
    if(currentImgIndex >= productImages.length) currentImgIndex = 0;
    if(currentImgIndex < 0) currentImgIndex = productImages.length - 1;
    const path = '{{ asset("") }}' + productImages[currentImgIndex];
    document.getElementById('lbImg').src = path;
    document.getElementById('mainImg').src = path;
    document.querySelectorAll('.pd-thumb').forEach((t, i) => {
        if(i === currentImgIndex) t.classList.add('active');
        else t.classList.remove('active');
    });
}
document.getElementById('lbModal').addEventListener('click', e => {
    if(e.target === document.getElementById('lbModal')) closeLightbox();
});
function changeImg(el, src){
    document.getElementById('mainImg').src = src;
    document.querySelectorAll('.pd-thumb').forEach((t, i) => {
        t.classList.remove('active');
        if(t === el) currentImgIndex = i;
    });
    el.classList.add('active');
}
function zoomMove(e){
    if(window.innerWidth <= 768) return;
    const wrap=document.getElementById('mainImgWrap'), lens=document.getElementById('zoomLens'), result=document.getElementById('zoomResult'), img=document.getElementById('mainImg');
    const rect=wrap.getBoundingClientRect(), x=e.clientX-rect.left, y=e.clientY-rect.top;
    lens.style.left=(x-50)+'px'; lens.style.top=(y-50)+'px';
    const zoomLevel=3; result.style.backgroundImage='url('+img.src+')';
    const imgRect=img.getBoundingClientRect(), bgW=imgRect.width*zoomLevel, bgH=imgRect.height*zoomLevel;
    const posX=-((x-(imgRect.left-rect.left))/imgRect.width*bgW-200), posY=-((y-(imgRect.top-rect.top))/imgRect.height*bgH-200);
    result.style.backgroundSize=bgW+'px '+bgH+'px'; result.style.backgroundPosition=posX+'px '+posY+'px';
}
function zoomOut(){}
function changeQty(d){
    const el = document.getElementById('pdQty');
    let v = parseInt(el.textContent) + d;
    if(v < 1) v = 1; if(v > stockLimit) v = stockLimit;
    el.textContent = v;
}
function showToast(msg, err){
    const t = document.getElementById('toast');
    t.textContent = msg; t.className = 'toast show' + (err ? ' error' : '');
    setTimeout(()=>t.className='toast', 3000);
}
async function addToCartDetail(){
    const isAuth = {{ Auth::check() ? 'true' : 'false' }};
    if(!isAuth){window.location.href="{{ route('sign-in') }}";return;}
    const qty = parseInt(document.getElementById('pdQty').textContent), btn = document.getElementById('pdBuyBtn');
    btn.disabled = true; btn.innerHTML = 'Adding...';
    try{
        const r = await fetch('{{ route("products.add-to-cart") }}',{
            method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'},
            body:JSON.stringify({product_id:productId,quantity:qty})
        });
        const d = await r.json();
        if(d.success){
            if(window.flyToCart) window.flyToCart(btn, qty, String(productId));
            showToast('Added to cart!');
            btn.innerHTML='✓ Added!'; setTimeout(()=>{btn.disabled=false;btn.innerHTML='ADD TO CART';},1500);
        } else { showToast(d.message||'Failed',true); btn.disabled=false; btn.innerHTML='ADD TO CART'; }
    }catch(e){showToast('Error',true); btn.disabled=false; btn.innerHTML='ADD TO CART';}
}
document.getElementById('reviewForm')?.addEventListener('submit', async function(e){
    e.preventDefault(); const fd = new FormData(this), rating = fd.get('rating');
    if(!rating){showToast('Please select a rating',true);return;}
    const btn = this.querySelector('.rv-submit'); btn.disabled=true; btn.textContent='Submitting...';
    try{
        const r = await fetch('{{ route("product.review.store", $product->id) }}',{
            method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'},
            body:JSON.stringify({rating:parseInt(rating),title:fd.get('title'),comment:fd.get('comment')})
        });
        const d = await r.json(); if(d.success){showToast(d.message);setTimeout(()=>location.reload(),1000);}
    }catch(e){showToast('Error',true);}
    btn.disabled=false; btn.textContent='Submit Review';
});
async function deleteReview(id){
    if(!confirm('Delete?'))return;
    try{ const r = await fetch('/webbuilders/e-com/public/review/'+id,{method:'DELETE',headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'}});
    const d = await r.json(); if(d.success){document.getElementById('review-'+id)?.remove();showToast('Deleted');}
    }catch(e){showToast('Error',true);}
}
</script>
@endsection