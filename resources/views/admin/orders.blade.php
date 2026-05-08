@extends('layouts.admin')

@section('title', 'Manage Orders')

@section('styles')
<style>
    .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
    .page-title h1 { font-size: 24px; font-weight: 700; color: var(--admin-dark); }
    .page-title p { color: #64748B; font-size: 14px; }
    
    .data-card { background: white; padding: 25px; border-radius: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); }
    table { width: 100%; border-collapse: collapse; }
    th { text-align: left; padding: 15px; color: #64748B; font-weight: 600; border-bottom: 1px solid #F1F5F9; }
    td { padding: 15px; border-bottom: 1px solid #F1F5F9; font-size: 14px; }
    .status-badge { padding: 4px 12px; border-radius: 50px; font-size: 12px; font-weight: 600; display: inline-block; }
    .status-completed { background: #DCFCE7; color: #10B981; }
    .status-shipped { background: #DBEAFE; color: #1E40AF; }
    .status-delivered { background: #E0E7FF; color: #4338CA; }
    .status-processing { background: #FEF3C7; color: #92400E; }
    .status-cancelled { background: #FEE2E2; color: #EF4444; }
    .status-refunded { background: #F1F5F9; color: #475569; border: 1px solid #CBD5E1; }

    .status-select {
        padding: 6px 12px;
        border-radius: 8px;
        border: 1px solid #E2E8F0;
        font-size: 13px;
        font-weight: 600;
        outline: none;
        cursor: pointer;
        transition: 0.2s;
    }
    .status-select:focus { border-color: var(--admin-primary); }

    /* ORDER DETAILS MODAL */
    .order-modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.5);
        z-index: 9998;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    }
    .order-modal-overlay.active { opacity: 1; visibility: visible; }

    .order-modal {
        position: fixed;
        top: 0;
        right: -500px;
        width: 460px;
        max-width: 90vw;
        height: 100vh;
        background: white;
        z-index: 9999;
        box-shadow: -10px 0 40px rgba(0,0,0,0.15);
        transition: right 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        flex-direction: column;
    }
    .order-modal.active { right: 0; }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 25px 30px;
        border-bottom: 1px solid #F1F5F9;
    }
    .modal-header h2 { font-size: 18px; font-weight: 700; color: #1E293B; }
    .modal-close {
        width: 36px; height: 36px;
        border: none; background: #F1F5F9;
        border-radius: 10px; cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        transition: 0.2s;
        font-size: 18px; color: #64748B;
    }
    .modal-close:hover { background: #FEE2E2; color: #EF4444; }

    .modal-body {
        flex: 1;
        overflow-y: auto;
        padding: 25px 30px;
        -webkit-overflow-scrolling: touch; /* Smooth scroll for iOS */
    }

    .order-modal {
        overscroll-behavior: contain;
    }

    .detail-section {
        margin-bottom: 25px;
    }
    .detail-section-title {
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #94A3B8;
        margin-bottom: 12px;
    }

    .detail-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 0;
    }
    .detail-label { color: #64748B; font-size: 13px; }
    .detail-value { font-weight: 600; font-size: 13px; color: #1E293B; }

    .item-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 14px;
        background: #F8FAFC;
        border-radius: 10px;
        margin-bottom: 8px;
    }
    .item-name { font-weight: 600; font-size: 13px; color: #1E293B; }
    .item-qty { font-size: 12px; color: #64748B; margin-top: 2px; }
    .item-price { font-weight: 700; font-size: 14px; color: var(--admin-primary); }

    .detail-total {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 18px 0;
        border-top: 2px solid #F1F5F9;
        margin-top: 10px;
    }
    .detail-total .detail-label { font-weight: 700; font-size: 15px; color: #1E293B; }
    .detail-total .detail-value { font-weight: 800; font-size: 18px; color: #10B981; }
    /* TOAST NOTIFICATION */
    .status-toast {
        position: fixed;
        top: 30px;
        right: 30px;
        z-index: 99999;
        padding: 16px 28px;
        border-radius: 14px;
        font-size: 14px;
        font-weight: 600;
        color: white;
        box-shadow: 0 10px 40px rgba(0,0,0,0.15);
        opacity: 0;
        transform: translateY(-20px);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        pointer-events: none;
    }
    .status-toast.show {
        opacity: 1;
        transform: translateY(0);
    }
    .status-toast.success { background: linear-gradient(135deg, #10B981, #059669); }
    .status-toast.warning { background: linear-gradient(135deg, #F59E0B, #D97706); }
    .status-toast.error { background: linear-gradient(135deg, #EF4444, #DC2626); }

    /* PAGINATION STYLES */
    .pagination-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 25px;
        padding: 0 10px;
    }
    .pagination-info {
        font-size: 14px;
        color: #64748B;
        font-weight: 500;
    }
    .pagination-nav {
        display: flex;
        gap: 10px;
        align-items: center;
    }
    .pagination-link {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: 12px;
        background: white;
        border: 1px solid #E2E8F0;
        color: #1E293B;
        text-decoration: none;
        transition: all 0.2s ease;
        cursor: pointer;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
    }
    .pagination-link:hover:not(.disabled) {
        border-color: var(--admin-primary);
        color: var(--admin-primary);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
    .pagination-link.disabled {
        opacity: 0.5;
        cursor: not-allowed;
        background: #F8FAFC;
    }
    .pagination-link svg {
        width: 20px;
        height: 20px;
    }
</style>
@endsection

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>Recent Orders</h1>
        <p>Monitor and fulfill customer orders.</p>
    </div>
    <div class="header-actions">
        <form action="{{ route('admin.orders') }}" method="GET" id="filterForm">
            <select name="status" onchange="document.getElementById('filterForm').submit()" style="padding: 10px 15px; border-radius: 10px; border: 1px solid #E2E8F0; background: white; font-family: inherit; font-size: 14px; font-weight: 500; color: #1E293B; cursor: pointer; box-shadow: 0 2px 10px rgba(0,0,0,0.02);">
                <option value="">All Orders</option>
                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Payment Complet</option>
                <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Processing</option>
                <option value="shipped" {{ request('status') == 'shipped' ? 'selected' : '' }}>Shipped</option>
                <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Delivered</option>
                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                <option value="refunded" {{ request('status') == 'refunded' ? 'selected' : '' }}>Refund</option>
            </select>
        </form>
    </div>
</div>

<div class="data-card">
    <table>
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Items</th>
                <th>Date</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Delivery</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orders as $order)
            <tr>
                <td style="font-weight: 600;">#ORD-{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</td>
                <td>
                    <div style="font-weight: 600;">{{ $order->user->name }}</div>
                    <div style="font-size: 12px; color: #64748B;">{{ $order->user->email }}</div>
                </td>
                <td>
                    @if(is_array($order->items_json))
                        @foreach($order->items_json as $item)
                            <div style="font-size: 12px; margin-bottom: 2px;">
                                <span style="font-weight: 700; color: var(--admin-primary);">{{ $item['qty'] }}x</span> {{ $item['name'] }}
                            </div>
                        @endforeach
                    @else
                        <span style="color: #94A3B8;">No items info</span>
                    @endif
                </td>
                <td>{{ $order->created_at->format('M d, Y') }}</td>
                <td style="font-weight: 700;">{{ currency_symbol() }}{{ number_format($order->total_price, 2) }}</td>
                <td>
                    <span id="order-status-{{ $order->id }}" class="status-badge status-{{ $order->status }}" style="text-transform: capitalize; width: 100%; text-align: center;">
                        {{ $order->status === 'completed' ? 'payment complet' : ($order->status === 'refunded' ? 'Refund' : $order->status) }}
                    </span>
                </td>
                <td id="delivery-td-{{ $order->id }}">
                    @if($order->deliveryBoy)
                        @php
                            $deliveryColor = ($order->assignment_type === 'self') ? '#10B981' : '#1E40AF';
                        @endphp
                        <div style="font-weight: 600; font-size: 13px; color: {{ $deliveryColor }};">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 2px;"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                            ID #{{ $order->delivery_boy_id }} - {{ $order->deliveryBoy->name }}
                        </div>
                    @else
                        <span style="color: #94A3B8; font-size: 12px;">Unassigned</span>
                    @endif
                </td>
                <td>
                    <button class="status-select" style="background: white; border-color: #E2E8F0;" onclick="viewOrderDetails({{ $order->id }})">Details</button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@if($orders->hasPages())
<div class="pagination-container">
    <div class="pagination-info">
        Showing <b>{{ $orders->firstItem() }}</b> to <b>{{ $orders->lastItem() }}</b> of <b>{{ $orders->total() }}</b> orders
    </div>
    <div class="pagination-nav">
        @if($orders->onFirstPage())
            <span class="pagination-link disabled">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
            </span>
        @else
            <a href="{{ $orders->appends(request()->query())->previousPageUrl() }}" class="pagination-link" title="Previous Page">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
            </a>
        @endif

        <div style="font-weight: 700; color: #1E293B; margin: 0 10px;">
            Page {{ $orders->currentPage() }}
        </div>

        @if($orders->hasMorePages())
            <a href="{{ $orders->appends(request()->query())->nextPageUrl() }}" class="pagination-link" title="Next Page">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
            </a>
        @else
            <span class="pagination-link disabled">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
            </span>
        @endif
    </div>
</div>
@endif

<!-- TOAST NOTIFICATION -->
<div class="status-toast" id="status-toast"></div>

<!-- ORDER DETAILS MODAL -->
<div class="order-modal-overlay" id="order-overlay" onclick="closeOrderModal()"></div>
<div class="order-modal" id="order-modal">
    <div class="modal-header">
        <h2 id="modal-order-id">Order Details</h2>
        <button class="modal-close" onclick="closeOrderModal()">✕</button>
    </div>
    <div class="modal-body" id="modal-body">
        <!-- Filled by JS -->
    </div>
</div>

<script>
const currencySymbol = @json(currency_symbol());
// Embed all orders as JSON for instant detail access
const ordersData = @json($ordersJson);

// Toast notification helper
function showToast(message, type = 'success') {
    const toast = document.getElementById('status-toast');
    toast.textContent = message;
    toast.className = 'status-toast ' + type;
    // Force reflow
    void toast.offsetWidth;
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 4000);
}

function viewOrderDetails(id) {
    const order = ordersData.find(o => o.id === id);
    if (!order) return;

    const statusColors = {
        processing: '#FEF3C7',
        completed: '#DCFCE7',
        shipped: '#DBEAFE',
        delivered: '#E0E7FF',
        cancelled: '#FEE2E2'
    };

    document.getElementById('modal-order-id').textContent = `Order #ORD-${String(id).padStart(5, '0')}`;

    let itemsHtml = '';
    if (order.items && order.items.length > 0) {
        order.items.forEach(item => {
            const itemTotal = (item.price * item.qty).toFixed(2);
            itemsHtml += `
                <div class="item-row">
                    <div>
                        <div class="item-name">${item.name}</div>
                        <div class="item-qty">${item.qty}x @ ${currencySymbol}${parseFloat(item.price).toFixed(2)}</div>
                    </div>
                    <div class="item-price">${currencySymbol}${itemTotal}</div>
                </div>
            `;
        });
    }

    document.getElementById('modal-body').innerHTML = `
        <div class="detail-section">
            <div class="detail-section-title">Customer Information</div>
            <div class="detail-row">
                <span class="detail-label">Name</span>
                <span class="detail-value">${order.customer_name}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Email</span>
                <span class="detail-value">${order.customer_email}</span>
            </div>
        </div>

        <div class="detail-section">
            <div class="detail-section-title">Order Information</div>
            <div class="detail-row">
                <span class="detail-label">Date</span>
                <span class="detail-value">${order.date}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Total Items</span>
                <span class="detail-value">${order.total_items}</span>
            </div>
            <div class="detail-row" style="display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <span class="detail-label">Status</span>
                    <span class="status-badge status-${order.status}" style="text-transform: capitalize;">${order.status === 'completed' ? 'payment complet' : (order.status === 'refunded' ? 'Refund' : order.status)}</span>
                </div>
                ${order.status === 'cancelled' ? `
                    <button class="status-btn" onclick="refundOrder(${order.id})" style="background: #10B981; color: white; border: none; padding: 6px 15px; border-radius: 8px; cursor: pointer; font-weight: 700;">
                        REFUND ${currencySymbol}${order.total_price}
                    </button>
                ` : ''}
            </div>
        </div>

        <div class="detail-section">
            <div class="detail-section-title">Delivery Proof</div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div>
                    <span class="detail-label" style="display: block; margin-bottom: 5px;">Pickup Photo</span>
                    ${order.pickup_image ? 
                        `<a href="${order.pickup_image}" target="_blank"><img src="${order.pickup_image}" style="width: 100%; height: 100px; object-fit: cover; border-radius: 10px; border: 1px solid #eee;"></a>` : 
                        '<div style="background: #f8fafc; height: 100px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 11px; color: #94A3B8; border: 1px dashed #E2E8F0;">Not available</div>'
                    }
                </div>
                <div>
                    <span class="detail-label" style="display: block; margin-bottom: 5px;">Delivery Photo</span>
                    ${order.delivery_image ? 
                        `<a href="${order.delivery_image}" target="_blank"><img src="${order.delivery_image}" style="width: 100%; height: 100px; object-fit: cover; border-radius: 10px; border: 1px solid #eee;"></a>` : 
                        '<div style="background: #f8fafc; height: 100px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 11px; color: #94A3B8; border: 1px dashed #E2E8F0;">Not available</div>'
                    }
                </div>
            </div>
        </div>

        <div class="detail-section">
            <div class="detail-section-title">Delivery Assignment</div>
            <div class="detail-row">
                <span class="detail-label">Current Partner</span>
                <span class="detail-value" id="current-partner-name">${order.delivery_boy_name || 'Not Assigned'}</span>
            </div>
            ${(order.status === 'completed' || order.status === 'processing') ? `
                <div style="margin-top: 10px; display: flex; gap: 8px;">
                    <select id="assign-boy-select" class="status-select" style="flex: 1;">
                        <option value="">Select Partner...</option>
                        ${Array.isArray(deliveryBoysData) ? deliveryBoysData.map(boy => `
                            <option value="${boy.id}" ${order.delivery_boy_id == boy.id ? 'selected' : ''}>${boy.name}</option>
                        `).join('') : ''}
                    </select>
                    <button class="status-select" style="background: var(--admin-primary); color: white; border: none;" onclick="assignPartner(${order.id})">Assign</button>
                </div>
            ` : ''}
        </div>

        <div class="detail-section">
            <div class="detail-section-title">Items Ordered</div>
            ${itemsHtml || '<div style="color: #94A3B8; font-size: 13px;">No item details available</div>'}
            <div class="detail-total">
                <span class="detail-label">Grand Total</span>
                <span class="detail-value">${currencySymbol}${parseFloat(order.total_price).toFixed(2)}</span>
            </div>
        </div>
    `;

    document.getElementById('order-overlay').classList.add('active');
    document.getElementById('order-modal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

const deliveryBoysData = @json($deliveryBoys);

async function refundOrder(orderId) {
    if (!confirm('Are you sure you want to refund this order? The money will be added back to the customer\'s balance.')) return;
    
    try {
        const response = await fetch(`{{ url('admin/orders') }}/${orderId}/refund`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        });
        const result = await response.json();
        if (result.success) {
            showToast(result.message);
            // Manually update status to Refund immediately
            const el = document.getElementById(`order-status-${orderId}`);
            if (el) {
                el.innerText = 'Refund';
                el.className = 'status-badge status-refunded';
            }
        } else {
            showToast(result.message, 'error');
        }
    } catch (error) {
        console.error(error);
        showToast('An error occurred during refund.', 'error');
    }
}

async function assignPartner(orderId) {
    const boyId = document.getElementById('assign-boy-select').value;
    if (!boyId) {
        alert('Please select a delivery partner first.');
        return;
    }

    try {
        const response = await fetch(`{{ url('admin/orders') }}/${orderId}/assign`, {
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
            showToast('✅ ' + result.message);
            // Manually update status to Processing immediately
            const el = document.getElementById(`order-status-${orderId}`);
            if (el) {
                el.innerText = 'Processing';
                el.className = 'status-badge status-processing';
            }
        } else {
            showToast('❌ ' + (result.message || 'Assignment failed'), 'error');
        }
    } catch (error) {
        console.error(error);
        showToast('❌ An error occurred during assignment', 'error');
    }
}

function closeOrderModal() {
    document.getElementById('order-overlay').classList.remove('active');
    document.getElementById('order-modal').classList.remove('active');
    document.body.style.overflow = '';
}

async function updateOrderStatus(id, el) {
    const status = el.value;
    const originalClass = el.className;
    el.disabled = true;

    try {
        const response = await fetch(`{{ url('admin/orders') }}/${id}/status`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ _method: 'PATCH', status: status })
        });
        
        const result = await response.json();
        if (result.success) {
            el.className = 'status-select status-' + status;
            // Show appropriate toast based on message
            if (result.message && result.message.includes('failed')) {
                showToast('⚠️ ' + result.message, 'warning');
            } else {
                showToast('✅ ' + result.message, 'success');
            }
        } else {
            showToast('❌ ' + (result.message || 'Failed to update status'), 'error');
            el.className = originalClass;
        }
    } catch (error) {
        console.error(error);
        showToast('❌ An error occurred while updating status', 'error');
        el.className = originalClass;
    } finally {
        el.disabled = false;
    }
}
    // Real-time Updates via SSE
    const eventSource = new EventSource("{{ route('sse.stream') }}");
    console.log("Admin SSE: Connecting...");

    eventSource.onopen = () => console.log("Admin SSE: Connected ✅");
    eventSource.onerror = (e) => console.error("Admin SSE: Connection error ❌", e);

    eventSource.addEventListener('update', (e) => {
        const data = JSON.parse(e.data);
        console.log("Admin SSE: Received update", data);
        
        if (data.orders && data.orders.all_statuses) {
            const serverIds = data.orders.all_statuses.map(o => o.id);
            
            // 1. Update existing statuses and delivery info
            data.orders.all_statuses.forEach(o => {
                // Update memory data for modal
                const existingOrder = ordersData.find(order => order.id === o.id);
                if (existingOrder) {
                    existingOrder.status = o.status;
                    existingOrder.delivery_boy_id = o.delivery_boy_id;
                    existingOrder.delivery_boy_name = o.delivery_boy_name || 'Not Assigned';
                    existingOrder.assignment_type = o.assignment_type;
                    existingOrder.pickup_image = o.pickup_image;
                    existingOrder.delivery_image = o.delivery_image;
                }

                const el = document.getElementById(`order-status-${o.id}`);
                if (el) {
                    const newStatus = o.status;
                    const displayText = newStatus === 'completed' ? 'payment complet' : (newStatus === 'refunded' ? 'Refund' : newStatus);
                    if (el.innerText.toLowerCase() !== displayText.toLowerCase()) {
                        el.innerText = displayText;
                        el.className = `status-badge status-${newStatus}`;
                    }
                }

                const delTd = document.getElementById(`delivery-td-${o.id}`);
                if (delTd) {
                    if (o.delivery_boy_id) {
                        const deliveryColor = (o.assignment_type === 'self') ? '#10B981' : '#1E40AF';
                        delTd.innerHTML = `
                        <div style="font-weight: 600; font-size: 13px; color: ${deliveryColor};">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 2px;"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                            ID #${o.delivery_boy_id} - ${o.delivery_boy_name}
                        </div>`;
                    } else {
                        delTd.innerHTML = `<span style="color: #94A3B8; font-size: 12px;">Unassigned</span>`;
                    }
                }
            });

            // 2. Remove rows that were recently in the list but are now gone (Auto-Deleted)
            // Note: We only check against the IDs that the server sent (top 20)
            const tableRows = document.querySelectorAll('tbody tr');
            tableRows.forEach(row => {
                const orderIdCell = row.querySelector('td:first-child');
                if (orderIdCell) {
                    const idMatch = orderIdCell.innerText.match(/#ORD-(\d+)/);
                    if (idMatch) {
                        const orderId = parseInt(idMatch[1]);
                        // If the order was in the list but is now missing from the server's update
                        // And we are within the reasonable range of recent orders
                        if (!serverIds.includes(orderId) && orderId > 0) {
                            // Verify if it was one of the "recent" ones by checking if it's NOT an old paginated record
                            // For simplicity, we fade it out if it disappears from the current view's status update
                            row.style.transition = 'opacity 0.8s ease, transform 0.8s ease';
                            row.style.opacity = '0';
                            row.style.transform = 'translateX(20px)';
                            setTimeout(() => row.remove(), 800);
                        }
                    }
                }
            });
        }
    });
</script>
@endsection
