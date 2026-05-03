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
</style>
@endsection

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>Recent Orders</h1>
        <p>Monitor and fulfill customer orders.</p>
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
                <td style="font-weight: 700;">${{ number_format($order->total_price, 2) }}</td>
                <td>
                    <select class="status-select status-{{ $order->status }}" onchange="updateOrderStatus({{ $order->id }}, this)">
                        <option value="processing" {{ $order->status == 'processing' ? 'selected' : '' }}>Processing</option>
                        <option value="completed" {{ $order->status == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="shipped" {{ $order->status == 'shipped' ? 'selected' : '' }}>Shipped</option>
                        <option value="delivered" {{ $order->status == 'delivered' ? 'selected' : '' }}>Delivered</option>
                        <option value="cancelled" {{ $order->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </td>
                <td>
                    <button class="status-select" style="background: white; border-color: #E2E8F0;" onclick="viewOrderDetails({{ $order->id }})">Details</button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

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
                        <div class="item-qty">${item.qty}x @ $${parseFloat(item.price).toFixed(2)}</div>
                    </div>
                    <div class="item-price">$${itemTotal}</div>
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
            <div class="detail-row">
                <span class="detail-label">Status</span>
                <span class="status-badge status-${order.status}" style="text-transform: capitalize;">${order.status}</span>
            </div>
        </div>

        <div class="detail-section">
            <div class="detail-section-title">Items Ordered</div>
            ${itemsHtml || '<div style="color: #94A3B8; font-size: 13px;">No item details available</div>'}
            <div class="detail-total">
                <span class="detail-label">Grand Total</span>
                <span class="detail-value">$${parseFloat(order.total_price).toFixed(2)}</span>
            </div>
        </div>
    `;

    document.getElementById('order-overlay').classList.add('active');
    document.getElementById('order-modal').classList.add('active');
}

function closeOrderModal() {
    document.getElementById('order-overlay').classList.remove('active');
    document.getElementById('order-modal').classList.remove('active');
}

async function updateOrderStatus(id, el) {
    const status = el.value;
    const originalClass = el.className;
    el.disabled = true;

    try {
        const response = await fetch(`{{ url('admin/orders') }}/${id}/status`, {
            method: 'POST',
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
</script>
@endsection
