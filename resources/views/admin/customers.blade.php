@extends('layouts.admin')

@section('title', 'Manage Customers')

@section('styles')
<style>
    .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
    .page-title h1 { font-size: 24px; font-weight: 700; color: var(--admin-dark); }
    .page-title p { color: #64748B; font-size: 14px; }
    
    .data-card { background: white; padding: 25px; border-radius: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); }
    table { width: 100%; border-collapse: collapse; }
    th { text-align: left; padding: 15px; color: #64748B; font-weight: 600; border-bottom: 1px solid #F1F5F9; }
    td { padding: 15px; border-bottom: 1px solid #F1F5F9; font-size: 14px; }

    .btn-history {
        padding: 6px 14px;
        border-radius: 8px;
        border: 1px solid #E2E8F0;
        background: white;
        font-size: 13px;
        font-weight: 600;
        color: var(--admin-primary);
        cursor: pointer;
        transition: 0.2s;
    }
    .btn-history:hover { background: var(--admin-primary); color: white; }

    /* HISTORY MODAL */
    .history-overlay {
        position: fixed; inset: 0;
        background: rgba(0,0,0,0.5);
        z-index: 9998;
        opacity: 0; visibility: hidden;
        transition: all 0.3s ease;
    }
    .history-overlay.active { opacity: 1; visibility: visible; }

    .history-modal {
        position: fixed; top: 0; right: -520px;
        width: 500px; max-width: 92vw; height: 100vh;
        background: white; z-index: 9999;
        box-shadow: -10px 0 40px rgba(0,0,0,0.15);
        transition: right 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex; flex-direction: column;
    }
    .history-modal.active { right: 0; }

    .hm-header {
        display: flex; justify-content: space-between; align-items: center;
        padding: 25px 30px;
        border-bottom: 1px solid #F1F5F9;
    }
    .hm-header h2 { font-size: 18px; font-weight: 700; color: #1E293B; }
    .hm-close {
        width: 36px; height: 36px;
        border: none; background: #F1F5F9;
        border-radius: 10px; cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        font-size: 18px; color: #64748B; transition: 0.2s;
    }
    .hm-close:hover { background: #FEE2E2; color: #EF4444; }

    .hm-body { flex: 1; overflow-y: auto; padding: 25px 30px; }

    .hm-stats {
        display: grid; grid-template-columns: 1fr 1fr;
        gap: 12px; margin-bottom: 25px;
    }
    .hm-stat {
        background: #F8FAFC; border-radius: 12px; padding: 16px;
        text-align: center;
    }
    .hm-stat-value { font-size: 22px; font-weight: 800; color: #1E293B; }
    .hm-stat-label { font-size: 11px; color: #94A3B8; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 4px; }

    .hm-section-title {
        font-size: 12px; font-weight: 700;
        text-transform: uppercase; letter-spacing: 0.5px;
        color: #94A3B8; margin-bottom: 12px;
    }

    .hm-order-card {
        background: #F8FAFC; border-radius: 12px;
        padding: 16px; margin-bottom: 12px;
        border-left: 4px solid var(--admin-primary);
    }
    .hm-order-top {
        display: flex; justify-content: space-between; align-items: center;
        margin-bottom: 10px;
    }
    .hm-order-id { font-weight: 700; font-size: 13px; color: #1E293B; }
    .hm-order-date { font-size: 12px; color: #94A3B8; }

    .hm-order-item {
        display: flex; justify-content: space-between; align-items: center;
        padding: 5px 0; font-size: 12px;
    }
    .hm-order-item-name { color: #64748B; }
    .hm-order-item-price { font-weight: 700; color: #1E293B; }

    .hm-order-total {
        display: flex; justify-content: space-between;
        border-top: 1px solid #E2E8F0;
        padding-top: 8px; margin-top: 8px;
        font-weight: 700; font-size: 13px;
    }
    .hm-order-total-val { color: #10B981; }

    .status-badge { padding: 3px 10px; border-radius: 50px; font-size: 11px; font-weight: 600; text-transform: capitalize; }
    .status-completed { background: #DCFCE7; color: #10B981; }
    .status-shipped { background: #DBEAFE; color: #1E40AF; }
    .status-delivered { background: #E0E7FF; color: #4338CA; }
    .status-processing { background: #FEF3C7; color: #92400E; }
    .status-cancelled { background: #FEE2E2; color: #EF4444; }

    .hm-empty { text-align: center; padding: 30px 0; color: #94A3B8; font-size: 14px; }
</style>
@endsection

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>Customers List</h1>
        <p>View and manage your registered customers.</p>
    </div>
</div>

<div class="data-card">
    <table>
        <thead>
            <tr>
                <th>Customer Name</th>
                <th>Email</th>
                <th>Joined Date</th>
                <th>Total Orders</th>
                <th>Amount</th>
                <th>History</th>
            </tr>
        </thead>
        <tbody>
            @foreach($customers as $customer)
            <tr>
                <td style="font-weight: 600;">{{ $customer->name }}</td>
                <td>{{ $customer->email }}</td>
                <td>{{ $customer->created_at->format('M d, Y') }}</td>
                <td>
                    <span style="font-weight: 700; color: var(--admin-primary);">{{ $customer->orders_count }}</span> Orders
                </td>
                <td style="font-weight: 700; color: #10B981;">
                    ${{ number_format($customer->total_spent, 2) }}
                </td>
                <td>
                    <button class="btn-history" onclick="showHistory({{ $customer->id }})">View History</button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- HISTORY MODAL -->
<div class="history-overlay" id="history-overlay" onclick="closeHistory()"></div>
<div class="history-modal" id="history-modal">
    <div class="hm-header">
        <h2 id="hm-title">Customer History</h2>
        <button class="hm-close" onclick="closeHistory()">✕</button>
    </div>
    <div class="hm-body" id="hm-body"></div>
</div>

<script>
const customersData = @json($customersJson);

function showHistory(id) {
    const customer = customersData.find(c => c.id === id);
    if (!customer) return;

    document.getElementById('hm-title').textContent = customer.name + "'s History";

    let html = `
        <div class="hm-stats">
            <div class="hm-stat">
                <div class="hm-stat-value">${customer.orders_count}</div>
                <div class="hm-stat-label">Total Orders</div>
            </div>
            <div class="hm-stat">
                <div class="hm-stat-value" style="color: #10B981;">$${parseFloat(customer.total_spent).toFixed(2)}</div>
                <div class="hm-stat-label">Total Spent</div>
            </div>
        </div>
        <div class="hm-section-title">Order History</div>
    `;

    if (customer.orders.length === 0) {
        html += `<div class="hm-empty">No orders yet from this customer.</div>`;
    } else {
        customer.orders.forEach(order => {
            let itemsHtml = '';
            if (order.items && order.items.length > 0) {
                order.items.forEach(item => {
                    itemsHtml += `
                        <div class="hm-order-item">
                            <span class="hm-order-item-name">${item.qty}x ${item.name}</span>
                            <span class="hm-order-item-price">$${(item.price * item.qty).toFixed(2)}</span>
                        </div>
                    `;
                });
            }

            html += `
                <div class="hm-order-card">
                    <div class="hm-order-top">
                        <div>
                            <span class="hm-order-id">#ORD-${String(order.id).padStart(5, '0')}</span>
                            <span class="status-badge status-${order.status}" style="margin-left: 8px;">${order.status}</span>
                        </div>
                        <span class="hm-order-date">${order.date}</span>
                    </div>
                    ${itemsHtml}
                    <div class="hm-order-total">
                        <span>Total</span>
                        <span class="hm-order-total-val">$${parseFloat(order.total_price).toFixed(2)}</span>
                    </div>
                </div>
            `;
        });
    }

    document.getElementById('hm-body').innerHTML = html;
    document.getElementById('history-overlay').classList.add('active');
    document.getElementById('history-modal').classList.add('active');
}

function closeHistory() {
    document.getElementById('history-overlay').classList.remove('active');
    document.getElementById('history-modal').classList.remove('active');
}
</script>
@endsection
