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
    @if($isJoined && $availableOrders->count() > 0)
        <h3 style="margin: 30px 0 20px; color: var(--partner-dark); display: flex; align-items: center; gap: 10px;">
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
                        <button class="btn-action btn-pickup" onclick="takeOrder({{ $order->id }})">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><polyline points="17 11 19 13 23 9"></polyline></svg>
                            TAKE JOB
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <div class="header-flex" style="display: flex; justify-content: space-between; align-items: center; margin: 40px 0 25px;">
        <h2 style="margin: 0; color: var(--partner-dark); display: flex; align-items: center; gap: 10px;">
            <span style="width: 10px; height: 10px; background: #4CAF50; border-radius: 50%;"></span>
            My Active Tasks
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
        @forelse($activeOrders as $order)
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
                        <button class="btn-action btn-pickup" onclick="triggerPickup({{ $order->id }})">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path></svg>
                            PICK UP (Take Photo)
                        </button>
                        <input type="file" id="pickup-input-{{ $order->id }}" style="display: none;" accept="image/*" onchange="handlePickup({{ $order->id }}, this)">
                    @endif

                    @if($order->status == 'shipped')
                        <button class="btn-action btn-deliver" onclick="triggerDelivery({{ $order->id }})">
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
        @empty
            <div style="text-align: center; padding: 60px 20px; background: white; border-radius: 16px; border: 1px dashed #ccc;">
                <p style="color: #999;">No active tasks assigned to you.</p>
            </div>
        @endforelse
    @endif
@endsection

@section('scripts')
<script>
    function triggerPickup(orderId) {
        document.getElementById(`pickup-input-${orderId}`).click();
    }

    async function handlePickup(orderId, input) {
        if (!input.files || !input.files[0]) return;
        
        const formData = new FormData();
        formData.append('pickup_image', input.files[0]);
        formData.append('_token', '{{ csrf_token() }}');

        try {
            const response = await fetch(`{{ url('delivery/take-order') }}/${orderId}`, {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json'
                }
            });
            const result = await response.json();
            if (result.success) {
                alert('Order picked up successfully! The customer has been notified and sent a secret code.');
                location.reload();
            } else {
                alert(result.message);
            }
        } catch (error) {
            console.error(error);
            alert('An error occurred during pickup.');
        }
    }

    function triggerDelivery(orderId) {
        document.getElementById(`delivery-input-${orderId}`).click();
    }

    async function handleDelivery(orderId, input) {
        if (!input.files || !input.files[0]) return;
        
        const code = prompt('Please enter the 6-digit Secret Code provided by the customer:');
        if (!code) {
            input.value = ''; // Reset input
            return;
        }
        if (code.length !== 6) {
            alert('The code must be 6 digits.');
            input.value = '';
            return;
        }

        const formData = new FormData();
        formData.append('delivery_image', input.files[0]);
        formData.append('code', code);
        formData.append('_token', '{{ csrf_token() }}');

        try {
            const response = await fetch(`{{ url('delivery/verify-delivery') }}/${orderId}`, {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json'
                }
            });
            const result = await response.json();
            if (result.success) {
                alert(result.message);
                location.reload();
            } else {
                alert(result.message);
                input.value = '';
            }
        } catch (error) {
            console.error(error);
            alert('An error occurred during delivery verification.');
            input.value = '';
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
