@extends('layouts.delivery')

@section('title', 'Work History')

@section('styles')
<style>
    .history-card { background: var(--partner-white); padding: 20px; border-radius: 12px; box-shadow: var(--shadow); margin-bottom: 15px; display: grid; grid-template-columns: 100px 1fr 120px 100px; align-items: center; gap: 20px; border: 1px solid #eee; }
    .hist-id { font-weight: 700; color: #777; }
    .hist-main h4 { margin: 0 0 4px; font-size: 15px; }
    .hist-main p { margin: 0; font-size: 12px; color: #888; }
    .hist-price { font-weight: 700; color: #4CAF50; text-align: right; }
    .hist-status { text-align: center; }
    .badge { padding: 4px 10px; border-radius: 50px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
    .badge-delivered { background: #E8F5E9; color: #4CAF50; }

    @media (max-width: 768px) {
        .history-card {
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            padding: 15px;
        }
        .hist-id { order: 1; font-size: 13px; }
        .hist-status { order: 2; text-align: right; }
        .hist-main { order: 3; grid-column: span 2; }
        .hist-price { order: 4; grid-column: span 2; text-align: left; border-top: 1px dashed #eee; padding-top: 10px; margin-top: 5px; }
    }
</style>
@endsection

@section('content')
    <h2 style="margin-bottom: 25px; color: var(--partner-dark);">Completed Deliveries</h2>

    @forelse($completedOrders as $order)
        <div class="history-card">
            <div class="hist-id">#ORD-{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</div>
            <div class="hist-main">
                <h4>{{ $order->user->name ?? 'Guest' }}</h4>
                <p>{{ $order->updated_at->format('d M Y, h:i A') }}</p>
            </div>
            <div class="hist-status">
                <span class="badge badge-delivered">Delivered</span>
            </div>
            <div class="hist-price">+ {{ currency_symbol() }}{{ number_format($order->total_price, 2) }}</div>
        </div>
    @empty
        <div style="text-align: center; padding: 60px 20px; background: white; border-radius: 16px; border: 1px dashed #ccc;">
            <p style="color: #999;">No delivery history found.</p>
        </div>
    @endforelse
@endsection
