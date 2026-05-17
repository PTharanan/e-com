@extends('layouts.master')

@section('title', 'My Account Dashboard')

@section('styles')
<style>
    .dashboard-container {
        padding: 50px 5% 60px;
        max-width: 1300px;
        margin: 0 auto;
    }

    .dashboard-header {
        margin-bottom: 40px;
    }

    .welcome-text {
        font-size: 2.2rem;
        font-weight: 800;
        color: var(--color-text-dark);
        margin-bottom: 8px;
    }

    .welcome-text span {
        color: var(--color-primary);
    }

    .dashboard-subtitle {
        color: var(--color-text-medium);
        font-size: 1rem;
        font-weight: 500;
    }

    /* STATS GRID */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 24px;
        margin: -45px auto 40px;
        position: relative;
        z-index: 2;
    }

    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: 1fr;
            margin-top: -30px;
        }
    }

    .stat-card {
        background: var(--color-white);
        padding: 28px;
        border-radius: 16px;
        box-shadow: 0 4px 24px rgba(0,0,0,0.06);
        border: 1px solid rgba(0,0,0,0.04);
        display: flex;
        align-items: center;
        gap: 20px;
        transition: all 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 32px rgba(0,0,0,0.1);
    }

    .stat-icon {
        width: 56px;
        height: 56px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .icon-products { background: linear-gradient(135deg, #EEF2FF, #E0E7FF); color: #4F46E5; }
    .icon-cash { background: linear-gradient(135deg, #ECFDF5, #D1FAE5); color: #059669; }
    .icon-items { background: linear-gradient(135deg, #FFF7ED, #FFEDD5); color: #EA580C; }

    .stat-info {
        display: flex;
        flex-direction: column;
    }

    .stat-value {
        font-size: 1.6rem;
        font-weight: 800;
        color: var(--color-text-dark);
        line-height: 1.2;
    }

    .stat-label {
        font-size: 0.85rem;
        color: var(--color-text-medium);
        font-weight: 500;
        margin-top: 4px;
    }

    /* RECENT ORDERS TABLE */
    .orders-section {
        background: var(--color-white);
        border-radius: 20px;
        padding: 32px;
        box-shadow: 0 4px 24px rgba(0,0,0,0.06);
        border: 1px solid rgba(0,0,0,0.04);
    }

    .section-title {
        font-size: 1.25rem;
        font-weight: 700;
        margin-bottom: 24px;
        color: var(--color-text-dark);
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .order-table-wrapper {
        overflow-x: auto;
    }

    .order-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .order-table th {
        text-align: left;
        padding: 12px 16px;
        background: #F8FAFC;
        color: #64748B;
        font-weight: 600;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        border-bottom: 1px solid #E2E8F0;
    }

    .order-table th:first-child { border-radius: 10px 0 0 0; }
    .order-table th:last-child { border-radius: 0 10px 0 0; }

    .order-table td {
        padding: 16px;
        border-bottom: 1px solid #F1F5F9;
        color: var(--color-text-dark);
        font-weight: 500;
        font-size: 0.9rem;
    }

    .order-table tr:last-child td {
        border-bottom: none;
    }

    .order-table tbody tr {
        transition: background 0.2s ease;
    }

    .order-table tbody tr:hover {
        background: #FAFBFD;
    }

    .status-badge {
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: inline-block;
    }

    .status-completed { background: #DCFCE7; color: #16A34A; }
    .status-shipped { background: #DBEAFE; color: #2563EB; }
    .status-delivered { background: #E0E7FF; color: #4338CA; }
    .status-returning { background: #F3E8FF; color: #7E22CE; }
    .status-processing { background: #FEF3C7; color: #B45309; }
    .status-cancelled { background: #FEE2E2; color: #DC2626; }
    .status-refunded { background: #F0FDF4; color: #15803D; }

    .empty-state {
        text-align: center;
        padding: 60px 0;
        color: var(--color-text-medium);
    }

    .btn-cancel {
        background: #FEF2F2;
        color: #DC2626;
        border: 1px solid #FECACA;
        padding: 7px 14px;
        border-radius: 8px;
        font-size: 0.78rem;
        font-weight: 600;
        cursor: pointer;
        transition: 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .btn-cancel:hover {
        background: #DC2626;
        color: white;
        border-color: #DC2626;
    }

    .btn-return {
        background: #EEF2FF;
        color: #4338CA;
        border: 1px solid #C7D2FE;
        padding: 7px 14px;
        border-radius: 8px;
        font-size: 0.78rem;
        font-weight: 600;
        cursor: pointer;
        transition: 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .btn-return:hover {
        background: #4338CA;
        color: white;
        border-color: #4338CA;
    }

    .alert {
        padding: 14px 20px;
        border-radius: 12px;
        margin-bottom: 24px;
        font-weight: 600;
        font-size: 0.9rem;
    }
    .alert-success { background: #F0FDF4; color: #16A34A; border: 1px solid #BBF7D0; }
    .alert-error { background: #FEF2F2; color: #DC2626; border: 1px solid #FECACA; }

    /* MODAL STYLES */
    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        backdrop-filter: blur(5px);
    }

    .modal-content.order-modal {
        background: var(--color-white);
        width: 90%;
        max-width: 600px;
        max-height: 90vh;
        overflow-y: auto;
        border-radius: var(--radius-lg);
        overflow-x: hidden;
        animation: modalSlideUp 0.3s ease-out;
    }

    @keyframes modalSlideUp {
        from { transform: translateY(30px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }

    .modal-header {
        padding: 20px 30px;
        border-bottom: 1px solid var(--color-border);
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #F8F9FA;
    }

    .modal-header h3 {
        margin: 0;
        font-size: 1.3rem;
        color: var(--color-text-dark);
    }

    .close-modal {
        background: none;
        border: none;
        font-size: 1.8rem;
        cursor: pointer;
        color: var(--color-text-light);
    }

    .modal-body {
        padding: 30px;
    }

    .order-info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 30px;
    }

    .info-item label {
        display: block;
        font-size: 0.8rem;
        color: var(--color-text-light);
        text-transform: uppercase;
        font-weight: 700;
        margin-bottom: 5px;
    }

    .info-item p {
        font-weight: 600;
        color: var(--color-text-dark);
    }

    .items-list-container h4 {
        margin-bottom: 15px;
        font-size: 1rem;
        color: var(--color-text-dark);
        border-bottom: 2px solid var(--color-bg-light);
        padding-bottom: 10px;
    }

    .modal-items-list {
        max-height: 250px;
        overflow-y: auto;
        margin-bottom: 20px;
    }

    .modal-item-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px solid #F1F5F9;
    }

    .modal-item-row:last-child {
        border-bottom: none;
    }

    .item-main {
        display: flex;
        gap: 15px;
        align-items: center;
    }

    .item-img {
        width: 50px;
        height: 50px;
        border-radius: 8px;
        object-fit: cover;
    }

    .item-details h5 {
        margin: 0;
        font-size: 0.95rem;
    }

    .item-details span {
        font-size: 0.85rem;
        color: var(--color-text-medium);
    }

    .item-price-info {
        text-align: right;
    }

    .item-total {
        font-weight: 700;
        color: var(--color-text-dark);
    }

    .order-summary-footer {
        border-top: 2px solid #F8F9FA;
        padding-top: 20px;
    }

    .summary-row.total {
        display: flex;
        justify-content: space-between;
        font-size: 1.2rem;
        font-weight: 800;
        color: var(--color-primary);
    }

    .btn-view-details {
        background: transparent;
        color: #64748B;
        border: 1px solid #E2E8F0;
        padding: 7px 14px;
        border-radius: 8px;
        font-size: 0.78rem;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        transition: 0.2s;
    }

    .btn-view-details:hover {
        background: #1E293B;
        color: #fff;
        border-color: #1E293B;
    }

    /* MOBILE RESPONSIVE OVERRIDES */
    @media (max-width: 768px) {
        .dashboard-container {
            padding: 30px 15px 40px;
        }

        .dashboard-header {
            margin-bottom: 30px;
        }

        .welcome-text {
            font-size: 1.5rem;
        }

        .dashboard-subtitle {
            font-size: 0.85rem;
        }

        .stat-card {
            padding: 20px;
            gap: 14px;
        }

        .stat-icon {
            width: 44px;
            height: 44px;
        }

        .stat-icon svg {
            width: 22px;
            height: 22px;
        }

        .stat-value {
            font-size: 1.3rem;
        }

        .orders-section {
            padding: 20px 16px;
            border-radius: 16px;
        }

        .order-table thead {
            display: none;
        }

        .order-table tr {
            display: block;
            margin-bottom: 16px;
            padding: 16px;
            background: #FAFBFD;
            border-radius: 12px;
            border: 1px solid #F1F5F9;
        }

        .order-table td {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border: none;
            text-align: right;
            font-size: 0.85rem;
        }

        .order-table td::before {
            content: attr(data-label);
            font-weight: 600;
            color: #64748B;
            font-size: 0.75rem;
            text-transform: uppercase;
            text-align: left;
            letter-spacing: 0.3px;
        }

        .order-table td:last-child {
            justify-content: center;
            padding-top: 12px;
            margin-top: 4px;
            border-top: 1px solid #E2E8F0;
        }
    }
    /* HORIZONTAL TIMELINE */
    .tracking-section {
        margin-top: 25px;
        padding-top: 25px;
        border-top: 2px solid #F1F5F9;
    }

    .tracking-section h4 {
        font-size: 0.95rem;
        font-weight: 700;
        margin-bottom: 25px;
        color: var(--color-text-dark);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .timeline {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        position: relative;
        padding: 0;
        margin: 0 10px;
    }

    .timeline-step {
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
        flex: 1;
        z-index: 1;
    }

    .timeline-step:not(:last-child)::after {
        content: '';
        position: absolute;
        top: 17px;
        left: 50%;
        width: 100%;
        height: 3px;
        background: #E2E8F0;
        z-index: 0;
        transition: background 0.6s ease;
    }

    .timeline-step.completed:not(:last-child)::after {
        background: var(--color-primary, #F25C3B);
    }

    .timeline-step.active:not(:last-child)::after {
        background: linear-gradient(90deg, var(--color-primary, #F25C3B) 0%, #E2E8F0 100%);
        background-size: 200% 100%;
        animation: lineFlow 1.2s ease-in-out infinite alternate;
    }

    @keyframes lineFlow {
        0% { background-position: 0% 0; }
        100% { background-position: 100% 0; }
    }

    .step-circle {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        background: #F1F5F9;
        border: 3px solid #E2E8F0;
        transition: all 0.5s ease;
        z-index: 2;
        position: relative;
        color: #94A3B8;
    }

    .timeline-step.completed .step-circle {
        background: var(--color-primary, #F25C3B);
        border-color: var(--color-primary, #F25C3B);
        color: #fff;
        animation: stepPop 0.4s ease;
    }

    .timeline-step.active .step-circle {
        background: var(--color-primary, #F25C3B);
        border-color: var(--color-primary, #F25C3B);
        color: #fff;
        box-shadow: 0 0 0 5px rgba(242, 92, 59, 0.15);
        animation: stepPulse 2s ease-in-out infinite;
    }

    .timeline-step.rejected .step-circle {
        background: #EF4444;
        border-color: #EF4444;
        color: #fff;
    }

    .timeline-step.pending .step-circle {
        background: #F8FAFC;
        border-color: #CBD5E1;
        color: #CBD5E1;
    }

    @keyframes stepPop {
        0% { transform: scale(0.6); }
        60% { transform: scale(1.15); }
        100% { transform: scale(1); }
    }

    @keyframes stepPulse {
        0%, 100% { box-shadow: 0 0 0 5px rgba(242, 92, 59, 0.15); }
        50% { box-shadow: 0 0 0 10px rgba(242, 92, 59, 0.05); }
    }

    .step-label {
        margin-top: 10px;
        text-align: center;
        font-size: 0.72rem;
        font-weight: 700;
        color: #1E293B;
        line-height: 1.3;
        max-width: 80px;
    }

    .timeline-step.completed .step-label { color: var(--color-primary, #F25C3B); }
    .timeline-step.active .step-label { color: var(--color-primary, #F25C3B); }
    .timeline-step.pending .step-label { color: #94A3B8; }
    .timeline-step.rejected .step-label { color: #EF4444; }

    .timeline-divider {
        display: flex;
        align-items: center;
        gap: 12px;
        margin: 28px 0 20px;
    }

    .timeline-divider span {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #7E22CE;
        white-space: nowrap;
    }

    .timeline-divider::before,
    .timeline-divider::after {
        content: '';
        flex: 1;
        height: 1px;
        background: linear-gradient(to right, transparent, #DDD6FE, transparent);
    }

    @media (max-width: 480px) {
        .step-circle { width: 28px; height: 28px; }
        .timeline-step:not(:last-child)::after { top: 14px; height: 2px; }
        .step-label { font-size: 0.6rem; max-width: 55px; }
    }
</style>
@endsection

@section('content')
<div class="dashboard-container">
    <div class="dashboard-header">
        <h1 class="welcome-text">Hello, <span>{{ Auth::user()->name }}!</span></h1>
        <p class="dashboard-subtitle">Welcome back! Here's an overview of your shopping journey.</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-error">
            {{ session('error') }}
        </div>
    @endif

    <!-- STATS GRID -->
    <div class="stats-grid">
        <!-- Unique Products -->
        <div class="stat-card">
            <div class="stat-icon icon-products">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <path d="M16 10a4 4 0 0 1-8 0"></path>
                </svg>
            </div>
            <div class="stat-info">
                <div class="stat-value">{{ number_format($totalProductsCount) }}</div>
                <div class="stat-label">Products Purchased</div>
            </div>
        </div>

        <!-- Total Spent -->
        <div class="stat-card">
            <div class="stat-icon icon-cash">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="1" x2="12" y2="23"></line>
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                </svg>
            </div>
            <div class="stat-info">
                <div class="stat-value">{{ currency_symbol() }}{{ number_format($totalCashSpent, 2) }}</div>
                <div class="stat-label">Total Cash Spent</div>
            </div>
        </div>

        <!-- Total Items -->
        <div class="stat-card">
            <div class="stat-icon icon-items">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="9" cy="21" r="1"></circle>
                    <circle cx="20" cy="21" r="1"></circle>
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                </svg>
            </div>
            <div class="stat-info">
                <div class="stat-value">{{ number_format($totalItemsBought) }}</div>
                <div class="stat-label">No. of Items Bought</div>
            </div>
        </div>
    </div>

    <!-- RECENT ORDERS -->
    <div class="orders-section">
        <h2 class="section-title">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
                <line x1="16" y1="13" x2="8" y2="13"></line>
                <line x1="16" y1="17" x2="8" y2="17"></line>
                <polyline points="10 9 9 9 8 9"></polyline>
            </svg>
            My Recent Orders
        </h2>

        <div class="order-table-wrapper">
            @if(count($orders) > 0)
                <table class="order-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Date</th>
                            <th>Items</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                            <th>Secret Code</th>
                            <th>Proof</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders->sortByDesc('created_at') as $order)
                        <tr id="order-row-{{ $order->id }}">
                            <td data-label="Order ID" style="font-weight: 600;">#ORD-{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</td>
                            <td data-label="Date">{{ $order->created_at->format('M d, Y') }}</td>
                            <td data-label="Items">{{ $order->total_items }} Items</td>
                            <td data-label="Total Amount" style="font-weight: 700;">{{ currency_symbol() }}{{ number_format($order->total_price, 2) }}</td>
                            <td data-label="Status" id="status-container-{{ $order->id }}">
                                @php
                                    $displayStatus = $order->status == 'completed' ? 'payment complet' : ($order->status == 'refunded' ? 'Refund' : $order->status);
                                    $badgeClass = $order->status;
                                    
                                    if ($order->status == 'returning') {
                                        $latestReturn = $order->returns->sortByDesc('created_at')->first();
                                        if ($latestReturn) {
                                            $displayStatus = 'Return: ' . ucfirst(str_replace('_', ' ', $latestReturn->status));
                                            $badgeClass = 'returning return-' . $latestReturn->status;
                                        }
                                    }
                                @endphp
                                <span id="badge-{{ $order->id }}" class="status-badge status-{{ $badgeClass }}">
                                     {{ $displayStatus }}
                                </span>
                            </td>
                            <td data-label="Secret Code" id="code-container-{{ $order->id }}">
                                @if($order->status == 'shipped' && $order->delivery?->secret_code)
                                    <div style="background: #FFFBEB; color: #B45309; padding: 6px 12px; border-radius: 8px; font-weight: 800; font-family: monospace; border: 1px dashed #F59E0B; display: inline-block;">
                                        {{ $order->delivery->secret_code }}
                                    </div>
                                @else
                                    <span style="color: #94A3B8; font-size: 0.85rem;">---</span>
                                @endif
                            </td>
                            <td data-label="Proof">
                                @if($order->status == 'returning')
                                    @php
                                        $latestReturn = $order->returns->sortByDesc('created_at')->first();
                                    @endphp
                                    @if($latestReturn && ($latestReturn->pickup_image || $latestReturn->store_image))
                                        <div style="display: flex; gap: 4px;">
                                            @if($latestReturn->pickup_image)
                                                <a href="{{ asset($latestReturn->pickup_image) }}" target="_blank" title="Pickup Proof">
                                                    <img src="{{ asset($latestReturn->pickup_image) }}" style="width: 30px; height: 30px; border-radius: 4px; object-fit: cover; border: 1px solid #eee;">
                                                </a>
                                            @endif
                                            @if($latestReturn->store_image)
                                                <a href="{{ asset($latestReturn->store_image) }}" target="_blank" title="Store Return Proof">
                                                    <img src="{{ asset($latestReturn->store_image) }}" style="width: 30px; height: 30px; border-radius: 4px; object-fit: cover; border: 1px solid #eee;">
                                                </a>
                                            @endif
                                        </div>
                                    @else
                                        <span style="color: #94A3B8; font-size: 0.85rem;">---</span>
                                    @endif
                                @elseif($order->delivery?->delivery_image)
                                    <a href="{{ asset($order->delivery->delivery_image) }}" target="_blank">
                                        <img src="{{ asset($order->delivery->delivery_image) }}" style="width: 40px; height: 40px; border-radius: 8px; object-fit: cover; border: 1px solid #eee;">
                                    </a>
                                @else
                                    <span style="color: #94A3B8; font-size: 0.85rem;">---</span>
                                @endif
                            </td>
                            <td data-label="Action" id="action-container-{{ $order->id }}">
                                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                                    <button type="button" class="btn-view-details" onclick="showOrderDetails({{ json_encode($order) }})">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                        Details
                                    </button>

                                    @if($order->status == 'completed')
                                        <button type="button" class="btn-cancel" onclick="cancelOrder({{ $order->id }})">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                                <line x1="6" y1="6" x2="18" y2="18"></line>
                                            </svg>
                                            Cancel
                                        </button>
                                    @elseif($order->status == 'delivered' && ($order->delivered_at ?? $order->updated_at)->diffInDays(now()) <= 14)
                                        <button type="button" class="btn-return" onclick="returnOrder({{ $order->id }})">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                                <path d="M15 10l-5 5 5 5"></path>
                                                <path d="M4 4v7a4 4 0 0 0 4 4h12"></path>
                                            </svg>
                                            Return
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="empty-state">
                    <p>You haven't placed any orders yet. Start shopping to see your history here!</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- ORDER DETAILS MODAL -->
<div id="order-details-modal" class="modal-overlay" style="display: none;">
    <div class="modal-content order-modal">
        <div class="modal-header">
            <h3 id="modal-order-id">Order Details</h3>
            <button class="close-modal" onclick="closeOrderModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div class="order-info-grid">
                <div class="info-item">
                    <label>Order Date</label>
                    <p id="modal-date"></p>
                </div>
                <div class="info-item">
                    <label>Order Status</label>
                    <p id="modal-status"></p>
                </div>
                <div class="info-item">
                    <label>Items Count</label>
                    <p id="modal-total-items"></p>
                </div>
                <div class="info-item">
                    <label>Total Price</label>
                    <p id="modal-total-amount-info" style="color: var(--color-primary); font-weight: 800;"></p>
                </div>
            </div>

            <div class="items-list-container">
                <h4>Purchased Items</h4>
                <div id="modal-items-list" class="modal-items-list">
                    <!-- Items injected via JS -->
                </div>
            </div>

            <div class="order-summary-footer">
                <div class="summary-row total">
                    <span>Grand Total</span>
                    <span id="modal-total-amount-footer"></span>
                </div>
            </div>

            <!-- ORDER TRACKING TIMELINE -->
            <div class="tracking-section" id="modal-tracking-section">
                <h4>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg>
                    Order Tracking
                </h4>
                <div id="order-timeline" class="timeline">
                    <!-- Rendered via JS -->
                </div>

                <!-- Return timeline divider (shown only when returning) -->
                <div id="return-timeline-container" style="display: none;">
                    <div class="timeline-divider">
                        <span>Return Tracking</span>
                    </div>
                    <div id="return-timeline" class="timeline">
                        <!-- Rendered via JS -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@section('scripts')
<script>
    // Track currently open modal order for live timeline updates
    let currentModalOrderId = null;
    let currentModalOrderData = null;

    // SVG icons for timeline steps
    const icons = {
        check: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>',
        cart: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>',
        gear: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>',
        truck: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg>',
        home: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>',
        x: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>',
        returnIcon: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 10l-5 5 5 5"></path><path d="M4 4v7a4 4 0 0 0 4 4h12"></path></svg>',
        pickup: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path></svg>',
        store: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path></svg>'
    };

    function getOrderStepState(stepKey, orderStatus) {
        const flow = ['completed', 'processing', 'shipped', 'delivered'];
        const stepIndex = flow.indexOf(stepKey);
        const currentIndex = flow.indexOf(orderStatus);
        if (orderStatus === 'cancelled' || orderStatus === 'refunded') {
            if (stepKey === 'completed') return 'completed';
            return 'pending';
        }
        if (orderStatus === 'returning') {
            return 'completed'; // all order steps done
        }
        if (currentIndex === -1) return 'pending';
        if (stepIndex < currentIndex) return 'completed';
        if (stepIndex === currentIndex) {
            // If it's the final step (delivered), mark as completed instead of active
            return (stepIndex === flow.length - 1) ? 'completed' : 'active';
        }
        return 'pending';
    }

    function getReturnStepState(stepKey, returnStatus) {
        if (!returnStatus) return 'pending';
        if (returnStatus === 'rejected') {
            if (stepKey === 'pending') return 'completed';
            if (stepKey === 'rejected') return 'rejected';
            return 'pending';
        }
        const flow = ['pending', 'accepted', 'picked_up', 'completed'];
        const stepIndex = flow.indexOf(stepKey);
        const currentIndex = flow.indexOf(returnStatus);
        if (currentIndex === -1) return 'pending';
        if (stepIndex < currentIndex) return 'completed';
        if (stepIndex === currentIndex) {
            // If it's the final step (returned to store), mark as completed instead of active
            return (stepIndex === flow.length - 1) ? 'completed' : 'active';
        }
        return 'pending';
    }

    function renderStep(state, icon, title) {
        const circleIcon = state === 'completed' ? icons.check : (state === 'rejected' ? icons.x : icon);
        return `<div class="timeline-step ${state}">
            <div class="step-circle">${circleIcon}</div>
            <div class="step-label">${title}</div>
        </div>`;
    }

    function renderOrderTimeline(orderStatus) {
        const el = document.getElementById('order-timeline');
        if (!el) return;
        let html = '';
        html += renderStep(getOrderStepState('completed', orderStatus), icons.cart, 'Order Placed');
        html += renderStep(getOrderStepState('processing', orderStatus), icons.gear, 'Processing');
        html += renderStep(getOrderStepState('shipped', orderStatus), icons.truck, 'In Transit');
        html += renderStep(getOrderStepState('delivered', orderStatus), icons.home, 'Delivered');
        if (orderStatus === 'cancelled') {
            html += renderStep('rejected', icons.x, 'Cancelled');
        }
        if (orderStatus === 'refunded') {
            html += renderStep('completed', icons.check, 'Refunded');
        }
        el.innerHTML = html;
    }

    function renderReturnTimeline(returnStatus) {
        const container = document.getElementById('return-timeline-container');
        const el = document.getElementById('return-timeline');
        if (!container || !el) return;
        if (!returnStatus) { container.style.display = 'none'; return; }
        container.style.display = 'block';
        let html = '';
        html += renderStep(getReturnStepState('pending', returnStatus), icons.returnIcon, 'Requested');
        if (returnStatus === 'rejected') {
            html += renderStep('rejected', icons.x, 'Rejected');
        } else {
            html += renderStep(getReturnStepState('accepted', returnStatus), icons.check, 'Accepted');
            html += renderStep(getReturnStepState('picked_up', returnStatus), icons.pickup, 'Picked Up');
            html += renderStep(getReturnStepState('completed', returnStatus), icons.store, 'Returned');
        }
        el.innerHTML = html;
    }

    // Real-time status updates via SSE
    const evtSource = new EventSource("{{ route('sse.stream') }}");
    
    evtSource.onopen = function() {
        console.log("SSE Connection established.");
    };

    evtSource.addEventListener("update", function(event) {
        console.log("SSE Update received:", event.data);
        const data = JSON.parse(event.data);
        
        if (data.user_orders) {
            data.user_orders.forEach(order => {
                const statusContainer = document.getElementById(`status-container-${order.id}`);
                const actionContainer = document.getElementById(`action-container-${order.id}`);
                
                if (statusContainer) {
                    const currentBadge = statusContainer.querySelector('.status-badge');
                    const currentStatusStr = currentBadge ? currentBadge.textContent.trim().toLowerCase() : '';
                    const newStatusStr = order.display_status.toLowerCase();
                    
                    // Only update if status changed
                    if (currentStatusStr !== newStatusStr) {
                        console.log(`Updating order ${order.id} status to ${order.display_status}`);
                        statusContainer.innerHTML = `<span id="badge-${order.id}" class="status-badge status-${order.status}">${order.display_status}</span>`;
                        
                        // Update Action column: Handle Cancel and Return buttons
                        if (actionContainer) {
                            if (order.status === 'delivered') {
                                actionContainer.innerHTML = `
                                    <button type="button" class="btn-return" onclick="returnOrder(${order.id})">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                            <path d="M15 10l-5 5 5 5"></path>
                                            <path d="M4 4v7a4 4 0 0 0 4 4h12"></path>
                                        </svg>
                                        Return
                                    </button>
                                `;
                            } else if (order.status !== 'completed') {
                                actionContainer.innerHTML = '<span style="color: #94A3B8; font-size: 0.85rem;">No actions</span>';
                            }
                        }

                        // Update Secret Code container
                        const codeContainer = document.getElementById(`code-container-${order.id}`);
                        if (codeContainer) {
                            if (order.status === 'shipped' && order.secret_code) {
                                codeContainer.innerHTML = `
                                    <div style="background: #FFFBEB; color: #B45309; padding: 6px 12px; border-radius: 8px; font-weight: 800; font-family: monospace; border: 1px dashed #F59E0B; display: inline-block;">
                                        ${order.secret_code}
                                    </div>
                                `;
                            } else if (order.status === 'delivered') {
                                codeContainer.innerHTML = '<span style="color: #10B981; font-weight: 700;">VERIFIED</span>';
                            } else {
                                codeContainer.innerHTML = '<span style="color: #94A3B8; font-size: 0.85rem;">---</span>';
                            }
                        }
                    }
                }

                // Live update modal timeline if this order is currently open
                if (currentModalOrderId === order.id) {
                    renderOrderTimeline(order.status);
                    renderReturnTimeline(order.return_status);
                }
            });
        }
    });

    async function cancelOrder(orderId) {
        if (!confirm('Are you sure you want to cancel this order?')) return;

        try {
            const response = await fetch(`{{ url('dashboard/orders') }}/${orderId}/cancel`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            });
            const result = await response.json();
            if (result.success) {
                // Update UI immediately
                const statusContainer = document.getElementById(`status-container-${orderId}`);
                if (statusContainer) {
                    statusContainer.innerHTML = '<span class="status-badge status-cancelled">cancelled</span>';
                }
                const actionContainer = document.getElementById(`action-container-${orderId}`);
                if (actionContainer) {
                    actionContainer.innerHTML = '<span style="color: #94A3B8; font-size: 0.85rem;">No actions</span>';
                }
            } else {
                alert(result.message || 'Cancellation failed');
            }
        } catch (error) {
            console.error(error);
            alert('An error occurred.');
        }
    }

    async function returnOrder(orderId) {
        const reason = prompt('Why do you want to return this product?');
        if (!reason) return;

        try {
            const response = await fetch(`{{ url('dashboard/orders') }}/${orderId}/return`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ reason: reason })
            });
            const result = await response.json();
            if (result.success) {
                // Update UI immediately
                const statusContainer = document.getElementById(`status-container-${orderId}`);
                if (statusContainer) {
                    statusContainer.innerHTML = '<span class="status-badge status-returning">returning</span>';
                }
                const actionContainer = document.getElementById(`action-container-${orderId}`);
                if (actionContainer) {
                    actionContainer.innerHTML = '<span style="color: #94A3B8; font-size: 0.85rem;">No actions</span>';
                }
                alert(result.message);
            } else {
                alert(result.message || 'Return request failed');
            }
        } catch (error) {
            console.error(error);
            alert('An error occurred.');
        }
    }

    function showOrderDetails(order) {
        currentModalOrderId = order.id;
        currentModalOrderData = order;

        document.getElementById('modal-order-id').textContent = `Order #ORD-${order.id.toString().padStart(5, '0')}`;
        document.getElementById('modal-date').textContent = new Date(order.created_at).toLocaleDateString('en-US', { 
            month: 'long', day: 'numeric', year: 'numeric' 
        });
        document.getElementById('modal-status').textContent = order.status.toUpperCase();
        document.getElementById('modal-total-items').textContent = `${order.total_items} Items`;
        
        const currencySymbol = "{{ currency_symbol() }}";
        const formattedTotal = currencySymbol + parseFloat(order.total_price).toLocaleString(undefined, {minimumFractionDigits: 2});
        document.getElementById('modal-total-amount-info').textContent = formattedTotal;
        document.getElementById('modal-total-amount-footer').textContent = formattedTotal;

        const itemsList = document.getElementById('modal-items-list');
        itemsList.innerHTML = '';
        const items = Array.isArray(order.items_json) ? order.items_json : JSON.parse(order.items_json);
        items.forEach(item => {
            const imgPath = item.image ? (item.image.startsWith('http') ? item.image : `/${item.image}`) : '/placeholder-product.png';
            const row = document.createElement('div');
            row.className = 'modal-item-row';
            row.innerHTML = `
                <div class="item-main">
                    <img src="${imgPath}" class="item-img" onerror="this.src='https://via.placeholder.com/50x50?text=Product'">
                    <div class="item-details">
                        <h5 style="margin-bottom: 2px;">${item.name}</h5>
                        <div style="display: flex; flex-direction: column; gap: 2px;">
                            <span style="color: var(--color-primary); font-size: 0.75rem; font-weight: 700;">Store: ${item.store_name || 'E-Shop'}</span>
                            <span>${item.qty} x ${currencySymbol}${parseFloat(item.price).toFixed(2)}</span>
                        </div>
                    </div>
                </div>
                <div class="item-price-info">
                    <span class="item-total">${currencySymbol}${parseFloat(item.price * item.qty).toFixed(2)}</span>
                </div>
            `;
            itemsList.appendChild(row);
        });

        // Render tracking timelines
        renderOrderTimeline(order.status);
        
        // Get return status from the order's returns relation
        let returnStatus = null;
        if (order.returns && order.returns.length > 0) {
            const latestReturn = order.returns.sort((a, b) => new Date(b.created_at) - new Date(a.created_at))[0];
            returnStatus = latestReturn.status;
        }
        renderReturnTimeline(returnStatus);

        document.getElementById('order-details-modal').style.display = 'flex';
        document.body.classList.add('modal-open');
    }

    function closeOrderModal() {
        currentModalOrderId = null;
        currentModalOrderData = null;
        document.getElementById('order-details-modal').style.display = 'none';
        document.body.classList.remove('modal-open');
    }

    document.getElementById('order-details-modal').addEventListener('click', function(e) {
        if (e.target === this) closeOrderModal();
    });
</script>
@endsection
@endsection
