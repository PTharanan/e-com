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
    .badge-returned { background: #EEF2FF; color: #6366F1; }

    /* Toggle Switch Styles */
    .toggle-container {
        display: flex;
        background: #F1F5F9;
        padding: 5px;
        border-radius: 12px;
        margin-bottom: 30px;
        width: fit-content;
        position: relative;
    }
    .toggle-option {
        padding: 10px 25px;
        font-size: 14px;
        font-weight: 600;
        color: #64748B;
        cursor: pointer;
        border-radius: 8px;
        transition: 0.3s;
        position: relative;
        z-index: 2;
    }
    .toggle-option.active {
        color: white;
    }
    .toggle-slider {
        position: absolute;
        top: 5px;
        left: 5px;
        height: calc(100% - 10px);
        width: calc(50% - 5px);
        background: var(--partner-primary);
        border-radius: 8px;
        transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        z-index: 1;
    }
    .toggle-container.returns-active .toggle-slider {
        left: 50%;
    }

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
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
        <h2 style="margin: 0; color: var(--partner-dark);">Work History</h2>
    </div>

    <div class="toggle-container" id="history-toggle">
        <div class="toggle-slider"></div>
        <div class="toggle-option active" onclick="switchHistory('delivery', this)">Delivery Works</div>
        <div class="toggle-option" onclick="switchHistory('returns', this)">Return Works</div>
    </div>

    {{-- Delivery History List --}}
    <div id="delivery-history-list">
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
        <div style="margin-top: 20px;">
            {{ $completedOrders->links() }}
        </div>
    </div>

    {{-- Returns History List --}}
    <div id="returns-history-list" style="display: none;">
        @forelse($completedReturns as $return)
            <div class="history-card" style="border-left: 4px solid #6366F1;">
                <div class="hist-id">#RET-{{ str_pad($return->id, 5, '0', STR_PAD_LEFT) }}</div>
                <div class="hist-main">
                    <h4>{{ $return->user->name ?? 'Guest' }}</h4>
                    <p>{{ $return->updated_at->format('d M Y, h:i A') }}</p>
                </div>
                <div class="hist-status">
                    <span class="badge badge-returned">Returned</span>
                </div>
                <div class="hist-price" style="color: #6366F1;">COMPLETED</div>
            </div>
        @empty
            <div style="text-align: center; padding: 60px 20px; background: white; border-radius: 16px; border: 1px dashed #ccc;">
                <p style="color: #999;">No return history found.</p>
            </div>
        @endforelse
        <div style="margin-top: 20px;">
            {{ $completedReturns->links() }}
        </div>
    </div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('returns_page')) {
            switchHistory('returns', document.querySelectorAll('.toggle-option')[1]);
        }
    });

    function switchHistory(type, el) {
        const container = document.getElementById('history-toggle');
        const deliveryList = document.getElementById('delivery-history-list');
        const returnsList = document.getElementById('returns-history-list');
        const options = container.querySelectorAll('.toggle-option');

        options.forEach(opt => opt.classList.remove('active'));
        el.classList.add('active');

        if (type === 'returns') {
            container.classList.add('returns-active');
            deliveryList.style.display = 'none';
            returnsList.style.display = 'block';
        } else {
            container.classList.remove('returns-active');
            deliveryList.style.display = 'block';
            returnsList.style.display = 'none';
        }
    }
</script>
@endsection
