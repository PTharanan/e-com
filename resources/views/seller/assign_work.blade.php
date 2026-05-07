@extends('layouts.seller')

@section('title', 'Assign Work')

@section('styles')
<style>
    .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
    .page-title h1 { font-size: 24px; font-weight: 700; color: var(--admin-dark); }
    
    .assign-container { display: grid; grid-template-columns: 1fr 350px; gap: 30px; }
    
    .card { background: white; border-radius: 20px; padding: 25px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); }
    .card-title { font-size: 18px; font-weight: 700; color: #1E293B; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
    
    .order-item { 
        background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px; padding: 15px; margin-bottom: 15px;
        display: flex; justify-content: space-between; align-items: center; transition: 0.2s;
    }
    .order-item:hover { border-color: var(--admin-primary); transform: translateX(5px); }
    .order-info h4 { margin: 0 0 4px; font-size: 14px; }
    .order-info p { margin: 0; font-size: 12px; color: #64748B; }
    
    .partner-card { 
        border: 1px solid #E2E8F0; border-radius: 12px; padding: 15px; margin-bottom: 15px;
    }
    .partner-header { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; }
    .partner-avatar { width: 36px; height: 36px; background: #E0E7FF; color: #4338CA; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-weight: 700; }
    .partner-name { font-weight: 600; font-size: 14px; }
    
    .load-badge { padding: 4px 10px; border-radius: 50px; font-size: 11px; font-weight: 700; }
    .load-low { background: #DCFCE7; color: #10B981; }
    .load-medium { background: #FEF3C7; color: #D97706; }
    .load-high { background: #FEE2E2; color: #EF4444; }

    .btn-release { background: #FEE2E2; color: #EF4444; border: none; padding: 6px 12px; border-radius: 8px; font-size: 12px; font-weight: 600; cursor: pointer; transition: 0.2s; }
    .btn-release:hover { background: #EF4444; color: white; }

    .assign-select {
        width: 100%; padding: 8px; border-radius: 8px; border: 1px solid #E2E8F0; font-size: 13px; outline: none; margin-bottom: 10px;
    }
</style>
@endsection

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>Work Management</h1>
        <p>Assign pending orders to your delivery team.</p>
    </div>
    <a href="{{ route('seller.delivery') }}" class="btn-release" style="background: white; border: 1px solid #E2E8F0; color: #64748B; padding: 10px 20px;">Back to Applications</a>
</div>

<div class="assign-container">
    <div class="card">
        <div class="card-title">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"></path><path d="M3 6h18"></path><path d="M16 10a4 4 0 0 1-8 0"></path></svg>
            Pending Orders ({{ $unassignedOrders->count() }})
        </div>
        
        @forelse($unassignedOrders as $order)
            <div class="order-item">
                <div class="order-info">
                    <h4>#ORD-{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }} - {{ $order->user->name }}</h4>
                    <p>{{ $order->total_items }} items | {{ currency_symbol() }}{{ number_format($order->total_price, 2) }} | {{ $order->created_at->diffForHumans() }}</p>
                </div>
                <div style="display: flex; gap: 10px; align-items: center;">
                    <select class="assign-select" style="width: 180px; margin-bottom: 0;" id="assign-{{ $order->id }}">
                        <option value="">Select Partner...</option>
                        @foreach($deliveryBoys as $boy)
                            <option value="{{ $boy->id }}">{{ $boy->name }} ({{ $boy->active_orders_count }} active)</option>
                        @endforeach
                    </select>
                    <button class="btn-release" style="background: var(--admin-primary); color: white; padding: 8px 15px;" onclick="assignWork({{ $order->id }})">Assign</button>
                </div>
            </div>
        @empty
            <div style="text-align: center; padding: 40px; color: #94A3B8;">
                <p>All clear! No pending orders for assignment.</p>
            </div>
        @endforelse
    </div>

    <div class="card">
        <div class="card-title">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
            My Team ({{ $deliveryBoys->count() }})
        </div>

        @foreach($deliveryBoys as $boy)
            <div class="partner-card">
                <div class="partner-header">
                    <div class="partner-avatar">{{ substr($boy->name, 0, 1) }}</div>
                    <div style="flex: 1;">
                        <div class="partner-name">{{ $boy->name }}</div>
                        <span class="load-badge {{ $boy->active_orders_count > 5 ? 'load-high' : ($boy->active_orders_count > 2 ? 'load-medium' : 'load-low') }}">
                            {{ $boy->active_orders_count }} Active Tasks
                        </span>
                    </div>
                    <form action="{{ route('seller.delivery.release', $boy->id) }}" method="POST" onsubmit="return confirm('Release this partner? They will be removed from your store.')">
                        @csrf
                        @method('DELETE')
                        <button class="btn-release" title="Release Partner">Release</button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
</div>

<script>
    async function assignWork(orderId) {
        const boyId = document.getElementById(`assign-${orderId}`).value;
        if (!boyId) {
            alert('Please select a delivery partner.');
            return;
        }

        try {
            const response = await fetch(`{{ url('seller/orders') }}/${orderId}/assign`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ delivery_boy_id: boyId })
            });
            const result = await response.json();
            if (result.success) {
                location.reload();
            } else {
                alert(result.message);
            }
        } catch (error) {
            console.error(error);
            alert('An error occurred.');
        }
    }
</script>
@endsection
