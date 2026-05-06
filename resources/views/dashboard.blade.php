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

    .btn-cancel {
        background: #FEE2E2;
        color: #EF4444;
        border: none;
        padding: 8px 15px;
        border-radius: 8px;
        font-size: 0.85rem;
        font-weight: 700;
        cursor: pointer;
        transition: 0.2s;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .btn-cancel:hover {
        background: #EF4444;
        color: white;
    }

    .alert {
        padding: 15px 20px;
        border-radius: 12px;
        margin-bottom: 30px;
        font-weight: 600;
    }
    .alert-success { background: #DCFCE7; color: #10B981; border: 1px solid #10B981; }
    .alert-error { background: #FEE2E2; color: #EF4444; border: 1px solid #EF4444; }

    /* MOBILE RESPONSIVE OVERRIDES */
    @media (max-width: 768px) {
        .dashboard-container {
            padding: 30px 15px;
        }

        .welcome-text {
            font-size: 1.8rem;
        }

        .dashboard-subtitle {
            font-size: 1rem;
        }

        .stat-card {
            padding: 20px;
            gap: 15px;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
        }

        .stat-icon svg {
            width: 24px;
            height: 24px;
        }

        .stat-value {
            font-size: 1.4rem;
        }

        .orders-section {
            padding: 20px;
        }

        /* Responsive Table to Cards */
        .order-table thead {
            display: none;
        }

        .order-table tr {
            display: block;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 2px solid #F1F5F9;
        }

        .order-table td {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border: none;
            text-align: right;
        }

        .order-table td::before {
            content: attr(data-label);
            font-weight: 700;
            color: var(--color-text-medium);
            font-size: 0.85rem;
            text-transform: uppercase;
            text-align: left;
        }

        .order-table td:last-child {
            justify-content: center;
            padding-top: 15px;
        }
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
                            <td data-label="Total Amount" style="font-weight: 700;">${{ number_format($order->total_price, 2) }}</td>
                            <td data-label="Status" id="status-container-{{ $order->id }}">
                                <span id="badge-{{ $order->id }}" class="status-badge status-{{ $order->status }}">
                                     {{ $order->status == 'completed' ? 'payment complet' : ($order->status == 'refunded' ? 'Refund' : $order->status) }}
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
                                @if($order->delivery?->delivery_image)
                                    <a href="{{ asset($order->delivery->delivery_image) }}" target="_blank">
                                        <img src="{{ asset($order->delivery->delivery_image) }}" style="width: 40px; height: 40px; border-radius: 8px; object-fit: cover; border: 1px solid #eee;">
                                    </a>
                                @else
                                    <span style="color: #94A3B8; font-size: 0.85rem;">---</span>
                                @endif
                            </td>
                            <td data-label="Action" id="action-container-{{ $order->id }}">
                                @if($order->status == 'completed')
                                    <button type="button" class="btn-cancel" onclick="cancelOrder({{ $order->id }})">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                            <line x1="18" y1="6" x2="6" y2="18"></line>
                                            <line x1="6" y1="6" x2="18" y2="18"></line>
                                        </svg>
                                        Cancel
                                    </button>
                                @else
                                    <span style="color: #94A3B8; font-size: 0.85rem;">No actions</span>
                                @endif
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
@section('scripts')
<script>
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
                        
                        // Update Action column: Hide cancel button if no longer 'completed'
                        if (actionContainer) {
                            if (order.status !== 'completed') {
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
</script>
@endsection
@endsection
