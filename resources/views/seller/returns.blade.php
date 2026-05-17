@extends('layouts.seller')

@section('title', 'Manage Returns')

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
    .status-pending { background: #FEF3C7; color: #92400E; }
    .status-approved { background: #DBEAFE; color: #1E40AF; }
    .status-completed { background: #DCFCE7; color: #10B981; }
    .status-rejected { background: #FEE2E2; color: #EF4444; }

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

    /* RETURN DETAILS MODAL */
    .return-modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.5);
        z-index: 9998;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    }
    .return-modal-overlay.active { opacity: 1; visibility: visible; }

    .return-modal {
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
    .return-modal.active { right: 0; }

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

    .detail-section { margin-bottom: 25px; }
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
    .status-toast.show { opacity: 1; transform: translateY(0); }
    .status-toast.success { background: linear-gradient(135deg, #10B981, #059669); }
    .status-toast.error { background: linear-gradient(135deg, #EF4444, #DC2626); }
</style>
@endsection

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>Return Requests</h1>
        <p>Manage product return requests for your store.</p>
    </div>
    <div class="header-actions">
        <form action="{{ route('seller.returns') }}" method="GET" id="filterForm">
            <select name="status" onchange="document.getElementById('filterForm').submit()" class="status-select" style="padding: 10px 15px;">
                <option value="">All Returns</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
            </select>
        </form>
    </div>
</div>

<div class="data-card">
    <table>
        <thead>
            <tr>
                <th>Return ID</th>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Reason</th>
                <th>Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($returns as $return)
            <tr>
                <td style="font-weight: 600;">#RET-{{ str_pad($return->id, 5, '0', STR_PAD_LEFT) }}</td>
                <td style="font-weight: 600; color: #64748B;">#ORD-{{ str_pad($return->order_id, 5, '0', STR_PAD_LEFT) }}</td>
                <td>
                    <div style="font-weight: 600;">{{ $return->user->name }}</div>
                    <div style="font-size: 12px; color: #64748B;">{{ $return->user->email }}</div>
                </td>
                <td style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $return->reason }}</td>
                <td>{{ $return->created_at->format('M d, Y') }}</td>
                <td>
                    <span class="status-badge status-{{ $return->status }}" style="text-transform: capitalize; width: 100%; text-align: center;">
                        {{ $return->status }}
                    </span>
                </td>
                <td>
                    <div style="display: flex; gap: 8px;">
                        <button class="status-select" onclick="viewReturnDetails({{ $return->id }})">Details</button>
                        @if($return->status == 'pending')
                            <button class="status-select" style="color: #EF4444; border-color: #FEE2E2;" onclick="rejectReturnRequest({{ $return->id }})">Reject</button>
                        @endif
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{-- Pagination --}}
<div style="margin-top: 20px;">
    {{ $returns->links() }}
</div>

<!-- TOAST NOTIFICATION -->
<div class="status-toast" id="status-toast"></div>

<!-- RETURN DETAILS MODAL -->
<div class="return-modal-overlay" id="return-overlay" onclick="closeReturnModal()"></div>
<div class="return-modal" id="return-modal">
    <div class="modal-header">
        <h2 id="modal-return-id">Return Details</h2>
        <button class="modal-close" onclick="closeReturnModal()">✕</button>
    </div>
    <div class="modal-body" id="modal-body">
        <!-- Filled by JS -->
    </div>
</div>

<script>
const returnsData = @json($returnsJson);
const deliveryBoys = @json($deliveryBoys);
const currencySymbol = @json(currency_symbol());

function showToast(message, type = 'success') {
    const toast = document.getElementById('status-toast');
    toast.textContent = message;
    toast.className = 'status-toast ' + type;
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 4000);
}

function viewReturnDetails(id) {
    const data = returnsData.find(r => r.id === id);
    if (!data) return;

    document.getElementById('modal-return-id').textContent = `Return #RET-${String(id).padStart(5, '0')}`;

    let itemsHtml = '';
    if (data.items) {
        data.items.forEach(item => {
            itemsHtml += `
                <div class="item-row">
                    <div>
                        <div class="item-name">${item.name}</div>
                        <div class="item-qty">${item.qty}x @ ${currencySymbol}${parseFloat(item.price).toFixed(2)}</div>
                    </div>
                    <div class="item-price">${currencySymbol}${(item.price * item.qty).toFixed(2)}</div>
                </div>
            `;
        });
    }

    document.getElementById('modal-body').innerHTML = `
        <div class="detail-section">
            <div class="detail-section-title">Return Reason</div>
            <div style="padding: 15px; background: #F8FAFC; border-radius: 12px; font-size: 14px; color: #1E293B; line-height: 1.6; border: 1px solid #F1F5F9;">
                ${data.reason}
            </div>
            ${data.status === 'rejected' ? `
                <div style="margin-top: 10px; padding: 12px; background: #FFF1F2; border: 1px solid #FECDD3; border-radius: 10px; color: #E11D48;">
                    <strong>Rejection Reason:</strong> ${data.rejection_reason || 'N/A'}
                </div>
            ` : ''}
        </div>

        <div class="detail-section">
            <div class="detail-section-title">Customer Details</div>
            <div class="detail-row">
                <span class="detail-label">Name</span>
                <span class="detail-value">${data.customer_name}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Email</span>
                <span class="detail-value">${data.customer_email}</span>
            </div>
            <div class="detail-row" style="flex-direction: column; align-items: flex-start;">
                <span class="detail-label" style="margin-bottom: 4px;">Address</span>
                <span class="detail-value" style="font-weight: 400; line-height: 1.4;">${data.buyer_address}</span>
            </div>
        </div>

        <div class="detail-section">
            <div class="detail-section-title">Return Evidence (Photos)</div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                <div>
                    <span class="detail-label" style="font-size: 11px; display: block; margin-bottom: 5px;">Pickup Photo</span>
                    ${data.pickup_image ? 
                        `<img src="${data.pickup_image}" style="width: 100%; height: 120px; object-fit: cover; border-radius: 10px; border: 1px solid #eee; cursor: pointer;" onclick="window.open('${data.pickup_image}')">` : 
                        `<div style="width: 100%; height: 120px; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #94a3b8; font-size: 11px;">Not picked up</div>`
                    }
                </div>
                <div>
                    <span class="detail-label" style="font-size: 11px; display: block; margin-bottom: 5px;">Store Photo</span>
                    ${data.store_image ? 
                        `<img src="${data.store_image}" style="width: 100%; height: 120px; object-fit: cover; border-radius: 10px; border: 1px solid #eee; cursor: pointer;" onclick="window.open('${data.store_image}')">` : 
                        `<div style="width: 100%; height: 120px; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #94a3b8; font-size: 11px;">Not returned</div>`
                    }
                </div>
            </div>
        </div>

        <div class="detail-section">
            <div class="detail-section-title">Order Info</div>
            <div class="detail-row">
                <span class="detail-label">Order ID</span>
                <span class="detail-value">#ORD-${String(data.order_id).padStart(5, '0')}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Requested On</span>
                <span class="detail-value">${data.date}</span>
            </div>
        </div>

        <div class="detail-section">
            <div class="detail-section-title">Delivery Partner</div>
            <div id="partner-assignment-section">
                <div class="detail-row">
                    <span class="detail-label">Assigned To</span>
                    <span class="detail-value" id="current-partner" style="color: ${data.delivery_boy_id ? '#6366F1' : '#64748B'}; font-weight: 700;">
                        ${data.delivery_boy_name || 'Not Assigned'}
                    </span>
                </div>
                ${!data.delivery_boy_id && data.status !== 'rejected' ? `
                    <div style="margin-top: 10px; display: flex; gap: 8px;">
                        <select class="status-select" id="partner-select" style="flex: 1;">
                            <option value="">Select Partner</option>
                            ${deliveryBoys.map(boy => `<option value="${boy.id}">${boy.name}</option>`).join('')}
                        </select>
                        <button class="status-select" style="background: var(--admin-primary); color: white; border: none;" onclick="assignReturnPartner(${data.id})">Assign</button>
                    </div>
                ` : `
                    <div style="margin-top: 5px; font-size: 11px; color: ${data.status === 'rejected' ? '#EF4444' : '#10B981'}; display: flex; align-items: center; gap: 5px;">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                            ${data.status === 'rejected' ? '<line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line>' : '<polyline points="20 6 9 17 4 12"></polyline>'}
                        </svg>
                        ${data.status === 'rejected' ? 'Cannot Assign (Rejected)' : 'Assignment Locked'}
                    </div>
                `}
            </div>
        </div>

        <div class="detail-section">
            <div class="detail-section-title">Items in Order</div>
            ${itemsHtml}
            <div class="detail-row" style="margin-top: 15px; border-top: 1px solid #F1F5F9; padding-top: 15px;">
                <span class="detail-label" style="font-weight: 700; color: #1E293B;">Order Total</span>
                <span class="detail-value" style="font-size: 18px; color: #10B981; font-weight: 800;">${currencySymbol}${parseFloat(data.total_price).toFixed(2)}</span>
            </div>
        </div>
    `;

    document.getElementById('return-overlay').classList.add('active');
    document.getElementById('return-modal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeReturnModal() {
    document.getElementById('return-overlay').classList.remove('active');
    document.getElementById('return-modal').classList.remove('active');
    document.body.style.overflow = '';
}

async function assignReturnPartner(id) {
    const boyId = document.getElementById('partner-select').value;
    if (!boyId) {
        showToast('Please select a delivery partner', 'error');
        return;
    }

    try {
        const response = await fetch(`{{ url('seller/returns') }}/${id}/assign`, {
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
            showToast(result.message);
            const boy = deliveryBoys.find(b => b.id == boyId);
            document.getElementById('current-partner').textContent = boy.name;
            
            // Update local data
            const dataIndex = returnsData.findIndex(r => r.id === id);
            if (dataIndex !== -1) {
                returnsData[dataIndex].delivery_boy_id = boyId;
                returnsData[dataIndex].delivery_boy_name = boy.name;
            }
        } else {
            showToast(result.message || 'Failed to assign partner', 'error');
        }
    } catch (error) {
        console.error(error);
        showToast('An error occurred', 'error');
    }
}

async function rejectReturnRequest(id) {
    const reason = prompt('Please enter the reason for rejection:');
    if (reason === null) return; // User cancelled
    if (reason.trim() === '') {
        alert('Rejection reason is required.');
        return;
    }

    try {
        const response = await fetch(`{{ url('seller/returns') }}/${id}/status`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ 
                status: 'rejected',
                rejection_reason: reason
            })
        });
        
        const result = await response.json();
        if (result.success) {
            showToast(result.message);
            location.reload(); 
        } else {
            showToast(result.message || 'Failed to update status', 'error');
        }
    } catch (error) {
        console.error(error);
        showToast('An error occurred', 'error');
    }
}

async function updateReturnStatus(id, el) {
    const status = el.value;
    el.disabled = true;

    try {
        const response = await fetch(`{{ url('seller/returns') }}/${id}/status`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ status: status })
        });
        
        const result = await response.json();
        if (result.success) {
            showToast(result.message);
            location.reload();
        } else {
            showToast(result.message || 'Failed to update status', 'error');
        }
    } catch (error) {
        console.error(error);
        showToast('An error occurred', 'error');
    } finally {
        el.disabled = false;
    }
}
</script>
@endsection
