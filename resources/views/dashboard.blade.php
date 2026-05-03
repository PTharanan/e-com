@extends('layouts.master')

@section('title', 'My Account Dashboard')

@section('styles')
<style>
    .dashboard-container {
        padding: 60px 5%;
        max-width: 1200px;
        margin: 0 auto;
    }

    .dashboard-header {
        margin-bottom: 40px;
    }

    .welcome-text {
        font-size: 2.5rem;
        font-weight: 800;
        color: var(--color-text-dark);
        margin-bottom: 10px;
    }

    .welcome-text span {
        color: var(--color-primary);
    }

    .dashboard-subtitle {
        color: var(--color-text-medium);
        font-size: 1.1rem;
    }

    /* STATS GRID */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 30px;
        margin-bottom: 50px;
    }

    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }
    }

    .stat-card {
        background: var(--color-white);
        padding: 35px;
        border-radius: var(--radius-lg);
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        border: 1px solid var(--color-border);
        display: flex;
        align-items: center;
        gap: 25px;
        transition: transform 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        border-color: var(--color-primary);
    }

    .stat-icon {
        width: 70px;
        height: 70px;
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .icon-products { background: #EEF2FF; color: #4F46E5; }
    .icon-cash { background: #ECFDF5; color: #10B981; }
    .icon-items { background: #FFF7ED; color: #F59E0B; }

    .stat-info {
        display: flex;
        flex-direction: column;
    }

    .stat-value {
        font-size: 1.8rem;
        font-weight: 800;
        color: var(--color-text-dark);
        line-height: 1.2;
    }

    .stat-label {
        font-size: 0.95rem;
        color: var(--color-text-medium);
        font-weight: 500;
        margin-top: 5px;
    }

    /* RECENT ORDERS TABLE */
    .orders-section {
        background: var(--color-white);
        border-radius: var(--radius-lg);
        padding: 40px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        border: 1px solid var(--color-border);
    }

    .section-title {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 30px;
        color: var(--color-text-dark);
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .order-table-wrapper {
        overflow-x: auto;
    }

    .order-table {
        width: 100%;
        border-collapse: collapse;
    }

    .order-table th {
        text-align: left;
        padding: 15px 20px;
        background: #F8F9FA;
        color: var(--color-text-medium);
        font-weight: 600;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .order-table td {
        padding: 20px;
        border-bottom: 1px solid #F1F5F9;
        color: var(--color-text-dark);
        font-weight: 500;
    }

    .order-table tr:last-child td {
        border-bottom: none;
    }

    .status-badge {
        padding: 6px 14px;
        border-radius: 10px;
        font-size: 0.85rem;
        font-weight: 700;
        text-transform: uppercase;
    }

    .status-completed { background: #DCFCE7; color: #10B981; }
    .status-shipped { background: #DBEAFE; color: #1E40AF; }
    .status-delivered { background: #E0E7FF; color: #4338CA; }
    .status-processing { background: #FEF3C7; color: #92400E; }
    .status-cancelled { background: #FEE2E2; color: #EF4444; }

    .empty-state {
        text-align: center;
        padding: 40px 0;
        color: var(--color-text-medium);
    }
</style>
@endsection

@section('content')
<div class="dashboard-container">
    <div class="dashboard-header">
        <h1 class="welcome-text">Hello, <span>{{ Auth::user()->name }}!</span></h1>
        <p class="dashboard-subtitle">Welcome back! Here's an overview of your shopping journey.</p>
    </div>

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
                <div class="stat-value">${{ number_format($totalCashSpent, 2) }}</div>
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
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders->sortByDesc('created_at') as $order)
                        <tr>
                            <td>#ORD-{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</td>
                            <td>{{ $order->created_at->format('M d, Y') }}</td>
                            <td>{{ $order->total_items }} Items</td>
                            <td style="font-weight: 700;">${{ number_format($order->total_price, 2) }}</td>
                            <td><span class="status-badge status-{{ $order->status }}">{{ $order->status }}</span></td>
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
@endsection
