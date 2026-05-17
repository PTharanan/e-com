@extends('layouts.delivery')

@section('title', 'Active Work')

@section('styles')
<style>
    .work-card { background: var(--partner-white); border-radius: 16px; box-shadow: var(--shadow); margin-bottom: 25px; overflow: hidden; border: 1px solid #eee; }
    .work-header { padding: 20px; background: #fafafa; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; }
    .work-body { padding: 25px; }
    .work-footer { padding: 15px 25px; background: #fdfdfd; border-top: 1px solid #eee; display: flex; gap: 10px; }
    
    .order-id { font-weight: 700; color: var(--partner-dark); font-size: 16px; }
    .order-status { padding: 4px 12px; border-radius: 50px; font-size: 12px; font-weight: 600; text-transform: uppercase; }
    .status-processing { background: #E3F2FD; color: #2196F3; }
    .status-shipped { background: #FFF3E0; color: #FF9800; }
    
    .customer-info { display: flex; gap: 15px; margin-bottom: 20px; }
    .avatar { width: 45px; height: 45px; background: #eee; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; color: #666; }
    .details h4 { margin: 0 0 4px; font-size: 15px; }
    .details p { margin: 0; font-size: 13px; color: #777; }

    .btn-action { flex: 1; padding: 12px; border-radius: 8px; border: none; font-weight: 600; cursor: pointer; transition: 0.2s; font-family: 'Poppins', sans-serif; display: flex; align-items: center; justify-content: center; gap: 8px; font-size: 14px; }
    .btn-pickup { background: #2196F3; color: white; }
    .btn-deliver { background: #4CAF50; color: white; }
    .btn-return { background: #F44336; color: white; }
    .btn-cash { background: #FF9800; color: white; }

    /* Upload Progress Ring */
    .progress-ring-container { display: flex; align-items: center; justify-content: center; width: 24px; height: 24px; position: relative; margin-right: 8px; }
    .progress-ring { transform: rotate(-90deg); width: 100%; height: 100%; }
    .progress-ring__circle { transition: stroke-dashoffset 0.1s linear; stroke: #ffffff; stroke-width: 4; fill: transparent; }
    .progress-ring__circle--progress { stroke: #0033cc; stroke-dasharray: 62.8318; stroke-dashoffset: 62.8318; stroke-linecap: round; }
    .progress-ring__circle--deliver { stroke: #1b5e20; }


    @media (max-width: 768px) {
        .work-header { flex-direction: column; align-items: flex-start; gap: 8px; }
        .work-footer { flex-direction: column; }
        .btn-action { width: 100%; }
        .header-flex { flex-direction: column; align-items: flex-start !important; gap: 15px; }
        .available-grid { grid-template-columns: 1fr !important; }
        .work-body { padding: 15px; }
    }
</style>
@endsection

@section('content')
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 30px;">
        <div onclick="document.getElementById('available-section')?.scrollIntoView({behavior: 'smooth'})" style="background: white; padding: 20px; border-radius: 12px; border-left: 4px solid #FF9800; box-shadow: var(--shadow); cursor: pointer;">
            <p style="margin: 0; font-size: 13px; color: #666; font-weight: 600;">AVAILABLE PICKUPS</p>
            <h2 style="margin: 5px 0 0; color: var(--partner-dark);">{{ $availableOrders->count() }} Tasks</h2>
        </div>
        <div onclick="document.getElementById('returns-section')?.scrollIntoView({behavior: 'smooth'})" style="background: white; padding: 20px; border-radius: 12px; border-left: 4px solid #6366F1; box-shadow: var(--shadow); cursor: pointer;">
            <p style="margin: 0; font-size: 13px; color: #666; font-weight: 600;">NEW RETURNS</p>
            <h2 style="margin: 5px 0 0; color: var(--partner-dark);">{{ $availableReturns->count() }} Tasks</h2>
        </div>
        <div onclick="document.getElementById('active-section')?.scrollIntoView({behavior: 'smooth'})" style="background: white; padding: 20px; border-radius: 12px; border-left: 4px solid var(--partner-primary); box-shadow: var(--shadow); cursor: pointer;">
            <p style="margin: 0; font-size: 13px; color: #666; font-weight: 600;">MY ACTIVE TASKS</p>
            <h2 style="margin: 5px 0 0; color: var(--partner-dark);">{{ $activeOrders->count() + $acceptedReturns->count() }} Tasks</h2>
        </div>
    </div>

    {{-- 1st: Available for Pickup --}}
    @if($isJoined && $availableOrders->count() > 0)
        <h3 id="available-section" style="margin: 30px 0 20px; color: var(--partner-dark); display: flex; align-items: center; gap: 10px;">
            <span style="width: 10px; height: 10px; background: #FF9800; border-radius: 50%;"></span>
            Available for Pickup ({{ $availableOrders->count() }})
        </h3>
        <div class="available-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
            @foreach($availableOrders as $order)
                <div class="work-card" style="margin-bottom: 0; border-top: 3px solid #FF9800;">
                    <div class="work-header">
                        <span class="order-id">#ORD-{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</span>
                        <span class="order-status" style="background: #FFF3E0; color: #FF9800;">READY</span>
                    </div>
                    <div class="work-body">
                        <div class="customer-info">
                            <div class="avatar">{{ substr($order->user->name ?? 'G', 0, 1) }}</div>
                            <div class="details">
                                <h4>{{ $order->user->name ?? 'Guest Customer' }}</h4>
                                <p>{{ $order->address ?? 'No address provided' }}</p>
                            </div>
                        </div>
                        <div style="background: #f9f9f9; padding: 12px; border-radius: 8px; font-size: 13px;">
                            <p style="margin: 0;">Items: <strong>{{ $order->total_items }}</strong> | Amount: <strong>{{ currency_symbol() }}{{ number_format($order->total_price, 2) }}</strong></p>
                        </div>
                    </div>
                    <div class="work-footer">
                        <button class="btn-action btn-pickup" id="pickup-btn-{{ $order->id }}" onclick="takeOrder({{ $order->id }})">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><polyline points="17 11 19 13 23 9"></polyline></svg>
                            TAKE JOB
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    @if($isJoined)
        {{-- 2nd: Return Work Section (ONLY PENDING) --}}
        <h3 id="returns-section" style="margin: 40px 0 20px; color: #6366F1; display: flex; align-items: center; gap: 10px;">
            <span style="width: 10px; height: 10px; background: #6366F1; border-radius: 50%;"></span>
            New Return Tasks ({{ $availableReturns->count() }})
        </h3>

        @forelse($availableReturns as $return)
            <div class="work-card" style="border-left: 4px solid #6366F1;">
                <div class="work-header">
                    <span class="order-id">#RET-{{ str_pad($return->id, 5, '0', STR_PAD_LEFT) }}</span>
                    <span class="order-status" style="background: #EEF2FF; color: #6366F1;">PENDING</span>
                </div>
                <div class="work-body">
                    <div class="customer-info">
                        <div class="avatar" style="background: #EEF2FF; color: #6366F1;">{{ substr($return->user->name ?? 'G', 0, 1) }}</div>
                        <div class="details">
                            <h4>{{ $return->user->name ?? 'Guest Customer' }}</h4>
                            <p><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg> {{ $return->user->info->address ?? 'No address provided' }}</p>
                        </div>
                    </div>
                    
                    <div style="background: #f9f9f9; padding: 15px; border-radius: 10px; border-left: 4px solid #6366F1;">
                        <p style="margin: 0; font-size: 14px; font-weight: 600;">Reason: <span style="font-weight: 400; color: #475569;">{{ $return->reason }}</span></p>
                        <p style="margin: 5px 0 0; font-size: 13px; color: #64748B;">Order: <strong>#ORD-{{ str_pad($return->order_id, 5, '0', STR_PAD_LEFT) }}</strong></p>
                    </div>
                </div>
                <div class="work-footer">
                    <button class="btn-action" style="background: #FF9800; color: white;" onclick="acceptReturn({{ $return->id }})">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><polyline points="17 11 19 13 23 9"></polyline></svg>
                        TAKE RETURN
                    </button>
                </div>
            </div>
        @empty
            <div style="text-align: center; padding: 40px 20px; background: white; border-radius: 16px; border: 1px dashed #ccc;">
                <p style="color: #999;">No new return tasks available.</p>
            </div>
        @endforelse

        {{-- 3rd: My Active Deliveries + Accepted Returns --}}
        <div class="header-flex" style="display: flex; justify-content: space-between; align-items: center; margin: 40px 0 25px;">
            <h2 id="active-section" style="margin: 0; color: var(--partner-dark); display: flex; align-items: center; gap: 10px;">
                <span style="width: 10px; height: 10px; background: #4CAF50; border-radius: 50%;"></span>
                My Active Tasks ({{ $activeOrders->count() + $acceptedReturns->count() }})
            </h2>
            @if($storeName)
                <div style="background: #E0E7FF; color: #4338CA; padding: 6px 15px; border-radius: 50px; font-size: 13px; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                    STORE: {{ strtoupper($storeName) }}
                </div>
            @endif
        </div>

        @if(!$isJoined)
            <div style="text-align: center; padding: 60px 20px; background: white; border-radius: 16px; border: 1px dashed #ccc; display: flex; flex-direction: column; align-items: center; gap: 15px;">
                <div style="width: 60px; height: 60px; background: #FFF3E0; color: #FF9800; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                    <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path></svg>
                </div>
                <div>
                    <h3 style="margin: 0 0 10px; color: var(--partner-dark);">No Active Job</h3>
                    <p style="color: #666; margin: 0;">You have not joined any store yet. Please apply for a job to start receiving tasks.</p>
                </div>
                <a href="{{ route('delivery.stores') }}" class="btn-action btn-pickup" style="max-width: 200px; text-decoration: none;">Explore Stores</a>
            </div>
        @else
            {{-- Show Active Orders --}}
            @foreach($activeOrders as $order)
                <div class="work-card">
                    <div class="work-header">
                        <span class="order-id">#ORD-{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</span>
                        <span class="order-status status-{{ strtolower($order->status) }}">{{ $order->status }}</span>
                    </div>
                    <div class="work-body">
                        <div class="customer-info">
                            <div class="avatar">{{ substr($order->user->name ?? 'G', 0, 1) }}</div>
                            <div class="details">
                                <h4>{{ $order->user->name ?? 'Guest Customer' }}</h4>
                                <p><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg> {{ $order->address ?? 'No address provided' }}</p>
                            </div>
                        </div>
                        
                        <div style="background: #f9f9f9; padding: 15px; border-radius: 10px; border-left: 4px solid var(--partner-primary);">
                            <p style="margin: 0; font-size: 14px; font-weight: 600;">Payment: <span style="color: {{ ($order->payment_method ?? '') == 'COD' ? '#FF9800' : '#4CAF50' }}">{{ $order->payment_method ?? 'Prepaid' }}</span></p>
                            <p style="margin: 5px 0 0; font-size: 14px;">Total Amount: <strong>{{ currency_symbol() }}{{ number_format($order->total_price, 2) }}</strong></p>
                        </div>
                    </div>
                    <div class="work-footer">
                        @if($order->status == 'processing')
                            <button class="btn-action btn-pickup" id="pickup-btn-{{ $order->id }}" onclick="triggerPickup({{ $order->id }})">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path></svg>
                                PICK UP (Take Photo)
                            </button>
                            <input type="file" id="pickup-input-{{ $order->id }}" style="display: none;" accept="image/*" onchange="handlePickup({{ $order->id }}, this)">
                        @endif

                        @if($order->status == 'shipped')
                            <button class="btn-action btn-deliver" id="deliver-btn-{{ $order->id }}" onclick="triggerDelivery({{ $order->id }})">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                DELIVER (Code & Photo)
                            </button>
                            <input type="file" id="delivery-input-{{ $order->id }}" style="display: none;" accept="image/*" onchange="handleDelivery({{ $order->id }}, this)">
                            @if(($order->payment_method ?? '') == 'COD')
                                <button class="btn-action btn-cash">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                                    GET CASH
                                </button>
                            @endif
                        @endif
                    </div>
                </div>
            @endforeach

            {{-- Show Accepted Returns --}}
            @foreach($acceptedReturns as $return)
                <div class="work-card" style="border-left: 4px solid #6366F1;">
                    <div class="work-header">
                        <span class="order-id">#RET-{{ str_pad($return->id, 5, '0', STR_PAD_LEFT) }}</span>
                        <span class="order-status" style="background: {{ $return->status == 'accepted' ? '#FEF3C7' : '#D1FAE5' }}; color: {{ $return->status == 'accepted' ? '#92400E' : '#065F46' }};">
                            {{ $return->status == 'accepted' ? 'IN TRANSIT' : 'PICKED UP' }}
                        </span>
                    </div>
                    <div class="work-body">
                        <div class="customer-info">
                            <div class="avatar" style="background: #EEF2FF; color: #6366F1;">{{ substr($return->user->name ?? 'G', 0, 1) }}</div>
                            <div class="details">
                                <h4>{{ $return->user->name ?? 'Guest Customer' }}</h4>
                                <p><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg> {{ $return->user->info->address ?? 'No address provided' }}</p>
                            </div>
                        </div>
                        
                        <div style="background: #f9f9f9; padding: 15px; border-radius: 10px; border-left: 4px solid #6366F1;">
                            <p style="margin: 0; font-size: 14px; font-weight: 600;">Action: <span style="font-weight: 400; color: #475569;">{{ $return->status == 'accepted' ? 'Go to buyer to pick up product' : 'Deliver product back to store' }}</span></p>
                        </div>
                    </div>
                    <div class="work-footer">
                        @if($return->status == 'accepted')
                            <button class="btn-action" style="background: #6366F1; color: white;" onclick="document.getElementById('return-pickup-input-{{ $return->id }}').click()">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path><circle cx="12" cy="13" r="4"></circle></svg>
                                PICK UP (Take Pic)
                            </button>
                            <input type="file" id="return-pickup-input-{{ $return->id }}" style="display: none;" accept="image/*" onchange="handleReturnPickup({{ $return->id }}, this)">
                        @elseif($return->status == 'picked_up')
                            <button class="btn-action" style="background: #10B981; color: white;" onclick="document.getElementById('return-store-input-{{ $return->id }}').click()">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                                DROP OFF (Store Pic)
                            </button>
                            <input type="file" id="return-store-input-{{ $return->id }}" style="display: none;" accept="image/*" onchange="handleReturnStore({{ $return->id }}, this)">
                        @endif
                    </div>
                </div>
            @endforeach

            @if($activeOrders->isEmpty() && $acceptedReturns->isEmpty())
                <div style="text-align: center; padding: 60px 20px; background: white; border-radius: 16px; border: 1px dashed #ccc;">
                    <p style="color: #999;">No active tasks assigned to you.</p>
                </div>
            @endif
        @endif
    @endif

    <!-- Secure Code Modal -->
    <div id="secure-code-modal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
        <div style="background: white; padding: 30px; border-radius: 16px; width: 90%; max-width: 400px; text-align: center; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
            <h3 style="margin: 0 0 15px; color: #1E293B; font-family: 'Poppins', sans-serif;">Verify Delivery</h3>
            <p style="color: #64748B; font-size: 14px; margin-bottom: 20px;">Please enter the 6-digit Secret Code provided by the customer.</p>
            
            <input type="number" id="secure-code-input" pattern="\d*" inputmode="numeric" maxlength="6" 
                   style="width: 100%; padding: 15px; font-size: 24px; text-align: center; letter-spacing: 5px; font-weight: 700; border: 2px solid #E2E8F0; border-radius: 10px; outline: none; margin-bottom: 20px; font-family: 'Poppins', sans-serif;"
                   oninput="if(this.value.length > 6) this.value = this.value.slice(0, 6);"
                   placeholder="000000">
            
            <div style="display: flex; gap: 15px;">
                <button id="cancel-code-btn" style="flex: 1; padding: 12px; border-radius: 8px; border: none; background: #F1F5F9; color: #475569; font-weight: 600; cursor: pointer; font-family: 'Poppins', sans-serif;">Cancel</button>
                <button id="submit-code-btn" style="flex: 1; padding: 12px; border-radius: 8px; border: none; background: #4CAF50; color: white; font-weight: 600; cursor: pointer; font-family: 'Poppins', sans-serif;">Verify & Deliver</button>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    function promptSecureCode() {
        return new Promise((resolve) => {
            const modal = document.getElementById('secure-code-modal');
            const codeInput = document.getElementById('secure-code-input');
            const cancelBtn = document.getElementById('cancel-code-btn');
            const submitBtn = document.getElementById('submit-code-btn');
            
            codeInput.value = '';
            modal.style.display = 'flex';
            setTimeout(() => codeInput.focus(), 100);

            cancelBtn.onclick = () => {
                modal.style.display = 'none';
                resolve(null);
            };

            submitBtn.onclick = () => {
                const code = codeInput.value;
                if (!code || code.length !== 6 || isNaN(code)) {
                    alert('The code must be exactly 6 numeric digits.');
                    return;
                }
                modal.style.display = 'none';
                resolve(code);
            };
        });
    }

    async function compressImage(file, maxWidth = 1920, quality = 0.8) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.readAsDataURL(file);
            reader.onload = event => {
                const img = new Image();
                img.src = event.target.result;
                img.onload = () => {
                    let width = img.width;
                    let height = img.height;
                    
                    if (width > maxWidth) {
                        height = Math.round((height * maxWidth) / width);
                        width = maxWidth;
                    }
                    
                    const canvas = document.createElement('canvas');
                    canvas.width = width;
                    canvas.height = height;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, width, height);
                    
                    canvas.toBlob((blob) => {
                        if (!blob) {
                            reject(new Error('Canvas empty'));
                            return;
                        }
                        resolve(new File([blob], file.name.replace(/\.[^/.]+$/, "") + ".jpg", {
                            type: 'image/jpeg',
                            lastModified: Date.now()
                        }));
                    }, 'image/jpeg', quality);
                };
                img.onerror = error => reject(error);
            };
            reader.onerror = error => reject(error);
        });
    }

    function triggerPickup(orderId) {
        document.getElementById(`pickup-input-${orderId}`).click();
    }

    async function handlePickup(orderId, input) {
        if (!input.files || !input.files[0]) return;
        const file = input.files[0];
        
        if (file.size > 10 * 1024 * 1024) {
            alert('File is too large! Please select an image under 10MB.');
            input.value = '';
            return;
        }
        
        const btn = document.getElementById(`pickup-btn-${orderId}`);
        const originalHtml = btn ? btn.innerHTML : 'PICK UP (Take Photo)';
        
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = `
                <div class="progress-ring-container">
                    <svg class="progress-ring" viewBox="0 0 24 24">
                        <circle class="progress-ring__circle" cx="12" cy="12" r="10"></circle>
                        <circle class="progress-ring__circle progress-ring__circle--progress" cx="12" cy="12" r="10"></circle>
                    </svg>
                </div>
                <span class="btn-text">UPLOADING...</span>
            `;
        }

        try {
            const compressedFile = await compressImage(file);
            
            const formData = new FormData();
            formData.append('pickup_image', compressedFile);
            formData.append('_token', '{{ csrf_token() }}');

            const xhr = new XMLHttpRequest();
            xhr.open('POST', `{{ url('delivery/take-order') }}/${orderId}`, true);
            xhr.setRequestHeader('Accept', 'application/json');

        xhr.upload.onprogress = function(e) {
            if (e.lengthComputable && btn) {
                const percentComplete = Math.round((e.loaded / e.total) * 100);
                const circle = btn.querySelector('.progress-ring__circle--progress');
                const textSpan = btn.querySelector('.btn-text');
                
                if (circle) {
                    const circumference = 62.8318; // 2 * pi * r (10)
                    const offset = circumference - (percentComplete / 100) * circumference;
                    circle.style.strokeDashoffset = offset;
                }
                
                if (percentComplete === 100 && textSpan) {
                    textSpan.innerText = 'PROCESSING...';
                }
            }
        };

        xhr.onload = function() {
            if (btn) {
                btn.innerHTML = originalHtml;
                btn.disabled = false;
            }
            if (xhr.status >= 200 && xhr.status < 300) {
                try {
                    const result = JSON.parse(xhr.responseText);
                    if (result.success) {
                        alert('Order picked up successfully! The customer has been notified and sent a secret code.');
                        location.reload();
                    } else {
                        alert(result.message);
                    }
                } catch(err) {
                    alert('An error occurred. Invalid response format.');
                }
            } else if (xhr.status === 422) {
                try {
                    const errorResponse = JSON.parse(xhr.responseText);
                    let errorMsg = errorResponse.message || 'Validation failed.';
                    if (errorResponse.errors) {
                        const firstErrorKey = Object.keys(errorResponse.errors)[0];
                        errorMsg = errorResponse.errors[firstErrorKey][0];
                    }
                    alert('Error: ' + errorMsg);
                } catch(err) {
                    alert('Validation error occurred.');
                }
                input.value = ''; // Reset input so they can select a different file
            } else {
                alert('Upload failed. Server returned status: ' + xhr.status);
                input.value = '';
            }
        };

        xhr.onerror = function() {
            if (btn) {
                btn.innerHTML = originalHtml;
                btn.disabled = false;
            }
            alert('An error occurred during pickup.');
        };

        xhr.send(formData);
        } catch (error) {
            if (btn) {
                btn.innerHTML = originalHtml;
                btn.disabled = false;
            }
            alert('Failed to compress image.');
            input.value = '';
        }
    }

    function triggerDelivery(orderId) {
        document.getElementById(`delivery-input-${orderId}`).click();
    }

    async function handleDelivery(orderId, input) {
        if (!input.files || !input.files[0]) return;
        const file = input.files[0];
        
        if (file.size > 10 * 1024 * 1024) {
            alert('File is too large! Please select an image under 10MB.');
            input.value = '';
            return;
        }
        
        const code = await promptSecureCode();
        if (!code) {
            input.value = ''; // Reset input
            return;
        }
        if (code.length !== 6) {
            alert('The code must be 6 digits.');
            input.value = '';
            return;
        }

        const btn = document.getElementById(`deliver-btn-${orderId}`);
        const originalHtml = btn ? btn.innerHTML : 'DELIVER (Code & Photo)';
        
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = `
                <div class="progress-ring-container">
                    <svg class="progress-ring" viewBox="0 0 24 24">
                        <circle class="progress-ring__circle" cx="12" cy="12" r="10"></circle>
                        <circle class="progress-ring__circle progress-ring__circle--progress progress-ring__circle--deliver" cx="12" cy="12" r="10"></circle>
                    </svg>
                </div>
                <span class="btn-text">UPLOADING...</span>
            `;
        }

        try {
            const compressedFile = await compressImage(file);

            const formData = new FormData();
            formData.append('delivery_image', compressedFile);
            formData.append('code', code);
            formData.append('_token', '{{ csrf_token() }}');

            const xhr = new XMLHttpRequest();
            xhr.open('POST', `{{ url('delivery/verify-delivery') }}/${orderId}`, true);
            xhr.setRequestHeader('Accept', 'application/json');

        xhr.upload.onprogress = function(e) {
            if (e.lengthComputable && btn) {
                const percentComplete = Math.round((e.loaded / e.total) * 100);
                const circle = btn.querySelector('.progress-ring__circle--progress');
                const textSpan = btn.querySelector('.btn-text');
                
                if (circle) {
                    const circumference = 62.8318;
                    const offset = circumference - (percentComplete / 100) * circumference;
                    circle.style.strokeDashoffset = offset;
                }
                
                if (percentComplete === 100 && textSpan) {
                    textSpan.innerText = 'PROCESSING...';
                }
            }
        };

        xhr.onload = function() {
            if (btn) {
                btn.innerHTML = originalHtml;
                btn.disabled = false;
            }
            if (xhr.status >= 200 && xhr.status < 300) {
                try {
                    const result = JSON.parse(xhr.responseText);
                    if (result.success) {
                        alert(result.message);
                        location.reload();
                    } else {
                        alert(result.message);
                        input.value = '';
                    }
                } catch(err) {
                    alert('An error occurred. Invalid response format.');
                    input.value = '';
                }
            } else if (xhr.status === 422) {
                try {
                    const errorResponse = JSON.parse(xhr.responseText);
                    let errorMsg = errorResponse.message || 'Validation failed.';
                    if (errorResponse.errors) {
                        const firstErrorKey = Object.keys(errorResponse.errors)[0];
                        errorMsg = errorResponse.errors[firstErrorKey][0];
                    }
                    alert('Error: ' + errorMsg);
                } catch(err) {
                    alert('Validation error occurred.');
                }
                input.value = '';
            } else {
                alert('Upload failed. Server returned status: ' + xhr.status);
                input.value = '';
            }
        };

        xhr.onerror = function() {
            if (btn) {
                btn.innerHTML = originalHtml;
                btn.disabled = false;
            }
            alert('An error occurred during delivery verification.');
            input.value = '';
        };

        xhr.send(formData);
        } catch (error) {
            if (btn) {
                btn.innerHTML = originalHtml;
                btn.disabled = false;
            }
            alert('Failed to compress image.');
            input.value = '';
        }
    }

    async function acceptReturn(id) {
        if (!confirm('Start this return task?')) return;

        try {
            const response = await fetch(`{{ url('delivery/accept-return') }}/${id}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            });
            
            const result = await response.json();
            if (result.success) {
                alert(result.message);
                location.reload();
            } else {
                alert(result.message || 'Failed to accept return');
            }
        } catch (error) {
            console.error(error);
            alert('An error occurred');
        }
    }

    async function handleReturnPickup(id, input) {
        if (!input.files || !input.files[0]) return;
        const file = input.files[0];
        
        try {
            const compressedFile = await compressImage(file);
            const formData = new FormData();
            formData.append('pickup_image', compressedFile);
            formData.append('_token', '{{ csrf_token() }}');

            const response = await fetch(`{{ url('delivery/pickup-return') }}/${id}`, {
                method: 'POST',
                body: formData,
                headers: { 'Accept': 'application/json' }
            });
            
            const result = await response.json();
            if (result.success) {
                alert(result.message);
                location.reload();
            } else {
                alert(result.message || 'Failed to pick up return');
            }
        } catch (error) {
            console.error(error);
            alert('An error occurred during pickup');
        }
    }

    async function handleReturnStore(id, input) {
        if (!input.files || !input.files[0]) return;
        const file = input.files[0];
        
        try {
            const compressedFile = await compressImage(file);
            const formData = new FormData();
            formData.append('store_image', compressedFile);
            formData.append('_token', '{{ csrf_token() }}');

            const response = await fetch(`{{ url('delivery/dropoff-return') }}/${id}`, {
                method: 'POST',
                body: formData,
                headers: { 'Accept': 'application/json' }
            });
            
            const result = await response.json();
            if (result.success) {
                alert(result.message);
                location.reload();
            } else {
                alert(result.message || 'Failed to drop off return');
            }
        } catch (error) {
            console.error(error);
            alert('An error occurred during drop off');
        }
    }

    // Existing takeOrder for unassigned (now needs image too)
    async function takeOrder(id) {
        // We'll redirect to the active tasks section or just reuse handlePickup logic
        // For simplicity, let's just trigger a file picker for the unassigned ones too
        const input = document.createElement('input');
        input.type = 'file';
        input.accept = 'image/*';
        input.onchange = (e) => handlePickup(id, e.target);
        input.click();
    }

    // Real-time updates for assigned work
    const evtSource = new EventSource("{{ route('sse.stream') }}");
    evtSource.addEventListener("update", function(event) {
        const data = JSON.parse(event.data);
        if (data.assigned_work) {
            console.log("New work assigned! Reloading...");
            location.reload();
        }
    });
</script>
@endsection
