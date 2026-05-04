@extends('layouts.admin')

@section('title', 'Manage Delivery Partners')

@section('styles')
<style>
    .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
    .page-title h1 { font-size: 24px; font-weight: 700; color: var(--admin-dark); }
    .page-title p { color: #64748B; font-size: 14px; }
    
    .data-card { background: white; padding: 25px; border-radius: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); }
    table { width: 100%; border-collapse: collapse; }
    th { text-align: left; padding: 15px; color: #64748B; font-weight: 600; border-bottom: 1px solid #F1F5F9; }
    td { padding: 15px; border-bottom: 1px solid #F1F5F9; font-size: 14px; vertical-align: middle; }

    .status-badge { padding: 5px 12px; border-radius: 50px; font-size: 12px; font-weight: 600; text-transform: uppercase; }
    .status-pending { background: #FEF3C7; color: #92400E; border: 1px solid #FDE68A; }
    .status-approved { background: #DCFCE7; color: #166534; border: 1px solid #BBF7D0; }
    .status-rejected { background: #FEE2E2; color: #991B1B; border: 1px solid #FECACA; }

    .action-btns {
        display: flex;
        gap: 8px;
    }

    .btn-action {
        padding: 6px 14px;
        border-radius: 8px;
        border: none;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: 0.2s;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .btn-accept { background: #10B981; color: white; }
    .btn-accept:hover { background: #059669; }

    .btn-reject { background: #EF4444; color: white; }
    .btn-reject:hover { background: #DC2626; }

    .empty-state { text-align: center; padding: 40px; color: #64748B; font-size: 15px; }

    .success-msg { background: #DCFCE7; color: #166534; padding: 15px; border-radius: 12px; margin-bottom: 20px; font-weight: 500; }

    /* CUSTOM CONFIRMATION MODAL */
    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.4);
        backdrop-filter: blur(4px);
        z-index: 9998;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .modal-overlay.active { opacity: 1; visibility: visible; }

    .confirm-modal {
        background: white;
        width: 400px;
        border-radius: 24px;
        padding: 35px;
        text-align: center;
        box-shadow: 0 20px 50px rgba(0,0,0,0.1);
        transform: translateY(20px);
        transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    .modal-overlay.active .confirm-modal { transform: translateY(0); }

    .modal-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
    }
    .icon-hire { background: #DCFCE7; color: #10B981; }
    .icon-fire { background: #FEE2E2; color: #EF4444; }

    .modal-title { font-size: 20px; font-weight: 700; color: #1E293B; margin-bottom: 10px; }
    .modal-desc { font-size: 14px; color: #64748B; line-height: 1.6; margin-bottom: 20px; }

    .fire-reason-box {
        width: 100%;
        padding: 14px;
        border: 2px solid #F1F5F9;
        background: #F8FAFC;
        border-radius: 12px;
        font-family: inherit;
        font-size: 14px;
        resize: vertical;
        min-height: 80px;
        margin-bottom: 20px;
        box-sizing: border-box;
        transition: all 0.2s;
    }
    .fire-reason-box:focus {
        border-color: #EF4444;
        background: white;
        outline: none;
        box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.1);
    }
    .fire-reason-box::placeholder { color: #94A3B8; }
    .modal-field-label {
        display: block;
        text-align: left;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #64748B;
        margin-bottom: 8px;
    }
    .fire-reason-label { composes: modal-field-label; }
    .reason-group { display: none; }
    .salary-group { display: none; }
    .salary-input {
        width: 100%;
        padding: 14px;
        border: 2px solid #F1F5F9;
        background: #F8FAFC;
        border-radius: 12px;
        font-family: inherit;
        font-size: 16px;
        font-weight: 700;
        color: #10B981;
        box-sizing: border-box;
        transition: all 0.2s;
        margin-bottom: 20px;
    }
    .salary-input:focus {
        border-color: #10B981;
        background: white;
        outline: none;
        box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1);
    }
    .salary-input::placeholder { color: #94A3B8; font-weight: 400; font-size: 14px; }

    .modal-actions { display: flex; gap: 12px; }
    .modal-btn {
        flex: 1;
        padding: 12px;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        transition: 0.2s;
        border: none;
    }
    .btn-cancel { background: #F1F5F9; color: #64748B; }
    .btn-cancel:hover { background: #E2E8F0; }
    .btn-confirm-hire { background: #10B981; color: white; }
    .btn-confirm-hire:hover { background: #059669; }
    .btn-confirm-fire { background: #EF4444; color: white; }
    .btn-confirm-fire:hover { background: #DC2626; }
</style>
@endsection

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>Delivery Partners</h1>
        <p>Hire and manage delivery partners who applied to your store.</p>
    </div>
    <div style="display: flex; gap: 10px;">
        <a href="{{ route('admin.delivery.assign-work') }}" class="btn-action btn-accept" style="text-decoration: none; display: flex; align-items: center; gap: 8px; padding: 12px 20px;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><polyline points="17 11 19 13 23 9"></polyline></svg>
            ASSIGN WORK
        </a>
    </div>
</div>

@if(session('success'))
    <div class="success-msg">{{ session('success') }}</div>
@endif

<div class="data-card">
    @if($applications->count() > 0)
    <table>
        <thead>
            <tr>
                <th>Partner Name</th>
                <th>Contact</th>
                <th>Salary / Order</th>
                <th>Status</th>
                <th>History</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($applications as $app)
            <tr>
                <td>
                    <div style="font-weight: 600; color: #1E293B;">{{ $app->deliveryBoy->name }}</div>
                    <div style="font-size: 12px; color: #64748B;">{{ $app->deliveryBoy->email }}</div>
                </td>
                <td>
                    @if($app->deliveryBoy->info)
                        <div style="font-size: 12px; color: #1E293B;">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle;"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72"></path></svg>
                            {{ $app->deliveryBoy->info->phno }}
                        </div>
                        <div style="font-size: 11px; color: #94A3B8; margin-top: 4px;">
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle;"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                            {{ Str::limit($app->deliveryBoy->info->address, 30) }}
                        </div>
                    @else
                        <span style="color: #CBD5E1; font-size: 12px;">No info</span>
                    @endif
                </td>
                <td>
                    @if($app->status === 'approved')
                        <form action="{{ route('admin.delivery.update-fee', $app->id) }}" method="POST" style="display: flex; gap: 5px; align-items: center;">
                            @csrf
                            @method('PATCH')
                            <input type="number" name="delivery_fee" value="{{ $app->delivery_fee }}" step="0.01" min="0" 
                                style="width: 80px; padding: 5px; border: 1px solid #ddd; border-radius: 4px; font-size: 13px;">
                            <button type="submit" class="btn-action btn-accept" style="padding: 5px 8px; font-size: 11px;">Set</button>
                        </form>
                    @else
                        <span style="color: #94A3B8; font-size: 12px;">Hire first</span>
                    @endif
                </td>
                <td>
                    <span class="status-badge status-{{ $app->status }}">
                        {{ $app->status }}
                    </span>
                    @if($app->status === 'rejected' && $app->fire_reason)
                        <div style="font-size: 11px; color: #991B1B; margin-top: 6px; background: #FEF2F2; padding: 4px 8px; border-radius: 6px;">
                            <strong>Reason:</strong> {{ Str::limit($app->fire_reason, 40) }}
                        </div>
                    @endif
                </td>
                <td>
                    @if(isset($applicantHistories[$app->delivery_boy_id]) && $applicantHistories[$app->delivery_boy_id]->count() > 0)
                        @foreach($applicantHistories[$app->delivery_boy_id] as $history)
                            <div style="font-size: 11px; background: #FFF7ED; padding: 6px 10px; border-radius: 8px; margin-bottom: 4px; border-left: 3px solid #F59E0B;">
                                <div style="color: #92400E; font-weight: 600;">Fired from {{ $history->storeOwner->name ?? 'Unknown Store' }}</div>
                                <div style="color: #78716C; margin-top: 2px;">{{ Str::limit($history->fire_reason, 50) }}</div>
                            </div>
                        @endforeach
                    @else
                        <span style="color: #10B981; font-size: 12px;">✓ Clean record</span>
                    @endif
                </td>
                <td>
                    <div class="action-btns">
                        @if($app->status === 'pending' || $app->status === 'rejected')
                        <form action="{{ route('admin.delivery.status', $app->id) }}" method="POST" id="hire-form-{{ $app->id }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="approved">
                            <input type="hidden" name="delivery_fee" id="hire-fee-input-{{ $app->id }}" value="0">
                            <button type="button" class="btn-action btn-accept" onclick="showConfirmModal('hire', {{ $app->id }}, '{{ $app->deliveryBoy->name }}')">Hire</button>
                        </form>
                        @endif

                        @if($app->status === 'pending' || $app->status === 'approved')
                        <form action="{{ route('admin.delivery.status', $app->id) }}" method="POST" id="fire-form-{{ $app->id }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="rejected">
                            <input type="hidden" name="fire_reason" id="fire-reason-input-{{ $app->id }}" value="">
                            <button type="button" class="btn-action btn-reject" onclick="showConfirmModal('{{ $app->status === 'approved' ? 'fire' : 'reject' }}', {{ $app->id }}, '{{ $app->deliveryBoy->name }}')">
                                {{ $app->status === 'approved' ? 'Fire' : 'Reject' }}
                            </button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
        <div class="empty-state">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#CBD5E1" stroke-width="2" style="margin-bottom: 10px;">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                <circle cx="9" cy="7" r="4"></circle>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
            </svg>
            <p>No delivery partners have applied to your store yet.</p>
        </div>
    @endif
</div>
</div>

<!-- CONFIRMATION MODAL -->
<div class="modal-overlay" id="confirmOverlay">
    <div class="confirm-modal">
        <div id="modalIcon" class="modal-icon">
            <!-- SVG ICON INJECTED BY JS -->
        </div>
        <h2 class="modal-title" id="modalTitle">Confirm Action</h2>
        <p class="modal-desc" id="modalDesc">Are you sure you want to proceed with this action?</p>
        <div class="salary-group" id="salaryGroup">
            <label class="modal-field-label">Salary per delivery ($)</label>
            <input type="number" class="salary-input" id="salaryInput" step="0.01" min="0" placeholder="e.g. 5.00">
        </div>
        <div class="reason-group" id="reasonGroup">
            <label class="modal-field-label">Reason for dismissal</label>
            <textarea class="fire-reason-box" id="fireReasonText" placeholder="e.g. Consistently late deliveries, customer complaints, etc."></textarea>
        </div>
        <div class="modal-actions">
            <button class="modal-btn btn-cancel" onclick="closeConfirmModal()">Cancel</button>
            <button class="modal-btn" id="confirmBtn">Confirm</button>
        </div>
    </div>
</div>

@section('scripts')
<script>
    let currentFormId = null;
    let currentFireId = null;
    let currentHireId = null;

    function showConfirmModal(type, id, name) {
        const overlay = document.getElementById('confirmOverlay');
        const icon = document.getElementById('modalIcon');
        const title = document.getElementById('modalTitle');
        const desc = document.getElementById('modalDesc');
        const confirmBtn = document.getElementById('confirmBtn');
        const reasonGroup = document.getElementById('reasonGroup');
        const reasonText = document.getElementById('fireReasonText');
        const salaryGroup = document.getElementById('salaryGroup');
        const salaryInput = document.getElementById('salaryInput');

        // Reset
        reasonText.value = '';
        salaryInput.value = '';

        if (type === 'hire') {
            currentFormId = `hire-form-${id}`;
            currentFireId = null;
            currentHireId = id;
            icon.className = 'modal-icon icon-hire';
            icon.innerHTML = '<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><polyline points="17 11 19 13 23 9"></polyline></svg>';
            title.textContent = 'Hire Partner?';
            desc.textContent = `Set the delivery fee for ${name}. They will be notified of their earnings per order.`;
            confirmBtn.className = 'modal-btn btn-confirm-hire';
            confirmBtn.textContent = 'Hire & Set Salary';
            salaryGroup.style.display = 'block';
            reasonGroup.style.display = 'none';
        } else {
            currentFormId = `fire-form-${id}`;
            currentFireId = id;
            currentHireId = null;
            icon.className = 'modal-icon icon-fire';
            icon.innerHTML = '<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6L6 18M6 6l12 12"></path></svg>';
            title.textContent = type === 'fire' ? 'Fire Partner?' : 'Reject Application?';
            desc.textContent = `Are you sure you want to ${type} ${name}? Please provide a reason below.`;
            confirmBtn.className = 'modal-btn btn-confirm-fire';
            confirmBtn.textContent = type === 'fire' ? 'Yes, Fire' : 'Yes, Reject';
            salaryGroup.style.display = 'none';
            reasonGroup.style.display = 'block';
        }

        confirmBtn.onclick = () => {
            // If hiring, validate salary and copy to hidden input
            if (currentHireId) {
                const fee = parseFloat(salaryInput.value);
                if (!fee || fee <= 0) {
                    salaryInput.style.borderColor = '#EF4444';
                    salaryInput.focus();
                    return;
                }
                const feeInput = document.getElementById(`hire-fee-input-${currentHireId}`);
                if (feeInput) {
                    feeInput.value = fee;
                }
            }
            // If firing, copy reason into the hidden input
            if (currentFireId) {
                const reasonInput = document.getElementById(`fire-reason-input-${currentFireId}`);
                if (reasonInput) {
                    reasonInput.value = reasonText.value;
                }
            }
            document.getElementById(currentFormId).submit();
        };

        overlay.classList.add('active');
    }

    function closeConfirmModal() {
        document.getElementById('confirmOverlay').classList.remove('active');
    }

    // Close on overlay click
    document.getElementById('confirmOverlay').onclick = function(e) {
        if (e.target === this) closeConfirmModal();
    };
</script>
@endsection
@endsection
