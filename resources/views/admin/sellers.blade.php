@extends('layouts.admin')

@section('title', 'Manage Sellers')

@section('styles')
    <style>
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .page-title h1 {
            font-size: 24px;
            font-weight: 700;
            color: var(--admin-dark);
        }

        .page-title p {
            color: #64748B;
            font-size: 14px;
        }

        .data-card {
            background: white;
            padding: 25px;
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            text-align: left;
            padding: 15px;
            color: #64748B;
            font-weight: 600;
            border-bottom: 1px solid #F1F5F9;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid #F1F5F9;
            font-size: 14px;
        }

        .seller-profile {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .seller-avatar {
            width: 35px;
            height: 35px;
            background: #F1F5F9;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: var(--admin-primary);
        }

        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-active {
            background: #E1F7E3;
            color: #0E731F;
        }

        .status-pending {
            background: #FFF1EE;
            color: #F25C3B;
        }

        .status-blocked {
            background: #FEE2E2;
            color: #EF4444;
        }

        .status-deleted {
            background: #F3F4F6;
            color: #6B7280;
            opacity: 0.8;
        }

        .actions-flex {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .action-icon-btn {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: none;
            cursor: pointer;
            transition: 0.2s;
            color: #64748B;
            background: #F8FAFC;
        }

        .btn-edit:hover {
            background: #DBEAFE;
            color: #2563EB;
        }

        .btn-delete:hover {
            background: #FEE2E2;
            color: #EF4444;
        }

        .btn-block-action {
            padding: 5px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            cursor: pointer;
            transition: 0.2s;
            border: 1px solid #E2E8F0;
            background: white;
            color: #64748B;
        }

        .btn-block-action:hover {
            background: #F8FAFC;
        }

        /* Modal Styles */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            backdrop-filter: blur(4px);
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal-container {
            background: white;
            width: 100%;
            max-width: 450px;
            padding: 30px;
            border-radius: 24px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.1);
            animation: modalSlideUp 0.3s ease;
        }

        @keyframes modalSlideUp {
            from {
                transform: translateY(20px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .modal-header {
            margin-bottom: 25px;
        }

        .modal-header h2 {
            font-size: 20px;
            font-weight: 700;
            color: #1E293B;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 13px;
            font-weight: 600;
            color: #64748B;
        }

        .form-control {
            width: 100%;
            padding: 10px 15px;
            border-radius: 10px;
            border: 1px solid #E2E8F0;
            font-size: 14px;
            outline: none;
            transition: 0.2s;
        }

        .form-control:focus {
            border-color: var(--admin-primary);
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
        }

        .modal-footer {
            margin-top: 30px;
            display: flex;
            gap: 12px;
            justify-content: flex-end;
        }

        .btn-cancel {
            padding: 10px 20px;
            background: #F1F5F9;
            color: #64748B;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-save {
            padding: 10px 20px;
            background: var(--admin-primary);
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-destructive {
            background: #EF4444;
        }

        .audit-info {
            font-size: 10px;
            color: #94A3B8;
            margin-top: 4px;
        }
    </style>
@endsection

@section('content')
    <div class="page-header">
        <div class="page-title">
            <h1>All Sellers</h1>
            <p>View and manage all registered sellers and their status.</p>
        </div>
    </div>

    <div class="data-card">
        <table>
            <thead>
                <tr>
                    <th>Seller</th>
                    <th>Email</th>
                    <th>Approved By</th>
                    <th>Joined Date</th>
                    <th>Products</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sellers as $seller)
                    <tr>
                        <td>
                            <div class="seller-profile">
                                <div class="seller-avatar">{{ substr($seller->name, 0, 1) }}</div>
                                <div class="seller-info">
                                    <div style="font-weight: 700; color: #1E293B;">{{ $seller->name }}</div>
                                    <div style="font-size: 11px; color: #64748B;">ID: #{{ $seller->id }}</div>
                                    @if($seller->lastEditor)
                                        <div class="audit-info">
                                            Last edited by: {{ $seller->lastEditor->name }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>{{ $seller->email }}</td>
                        <td>
                            @if($seller->approver)
                                <div style="font-weight: 600; color: var(--admin-primary);">{{ $seller->approver->name }}</div>
                                <div style="font-size: 11px; color: #64748B;">Admin ID: #{{ $seller->approver->id }}</div>
                            @else
                                <span style="color: #A0AEC0; font-style: italic;">Direct Registry</span>
                            @endif
                        </td>
                        <td>{{ $seller->created_at->format('M d, Y') }}</td>
                        <td>
                            <span style="font-weight: 600;">{{ $seller->products_count }}</span> Products
                        </td>
                        <td>
                            @if($seller->trashed())
                                <span class="status-badge status-deleted" title="Reason: {{ $seller->deletion_reason }}">Account
                                    Deleted</span>
                                @if($seller->deletion_reason)
                                    <div
                                        style="font-size: 10px; color: #EF4444; max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        Reason: {{ $seller->deletion_reason }}
                                    </div>
                                @endif
                            @elseif($seller->is_blocked)
                                <span class="status-badge status-blocked" id="status-{{ $seller->id }}">Blocked</span>
                            @else
                                <span class="status-badge status-active" id="status-{{ $seller->id }}">Active</span>
                            @endif
                        </td>
                        <td>
                            <div class="actions-flex">
                                @if(!$seller->trashed())

                                    <button class="btn-block-action" id="block-btn-{{ $seller->id }}"
                                        onclick="toggleBlock({{ $seller->id }})">
                                        {{ $seller->is_blocked ? 'Unblock' : 'Block' }}
                                    </button>

                                    <button class="action-icon-btn btn-delete" title="Delete Seller"
                                        onclick="openDeleteModal({{ $seller->id }})">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2">
                                            <polyline points="3 6 5 6 21 6"></polyline>
                                            <path
                                                d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2">
                                            </path>
                                        </svg>
                                    </button>
                                @else
                                    <span style="font-size: 11px; color: #94A3B8; font-style: italic;">No Actions</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 50px; color: #64748B;">
                            No sellers found in the system.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>



    <!-- Delete Reason Modal -->
    <div class="modal-overlay" id="deleteReasonModal">
        <div class="modal-container">
            <div class="modal-header">
                <h2 style="color: #EF4444;">Delete Seller Account</h2>
                <p style="font-size: 13px; color: #64748B; margin-top: 5px;">This will permanently delete his products and
                    images.</p>
            </div>
            <form id="deleteSellerForm">
                @csrf
                @method('DELETE')
                <input type="hidden" name="id" id="delete_seller_id">
                <div class="form-group">
                    <label>Reason for Deletion</label>
                    <textarea name="reason" class="form-control"
                        placeholder="Please provide a reason for deleting this seller..."
                        style="height: 100px; resize: none;" required></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="closeModal('deleteReasonModal')">Cancel</button>
                    <button type="submit" class="btn-save btn-destructive">Confirm and Delete All Data</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function openModal(id) {
            document.getElementById(id).classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeModal(id) {
            document.getElementById(id).classList.remove('active');
            document.body.style.overflow = '';
        }

        function openDeleteModal(id) {
            document.getElementById('delete_seller_id').value = id;
            openModal('deleteReasonModal');
        }

        // Delete Seller Handler
        document.getElementById('deleteSellerForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const id = formData.get('id');
            const btn = e.target.querySelector('.btn-save');

            if (!confirm('DANGER: This will delete ALL products and media files for this seller. Continue?')) return;

            btn.disabled = true;
            btn.innerText = 'Deleting & Cleaning Files...';

            try {
                const response = await fetch(`{{ url('admin/settings/sellers') }}/${id}`, {
                    method: 'POST', // Using POST with _method=DELETE
                    body: formData,
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                });
                const result = await response.json();
                if (result.success) {
                    location.reload();
                } else {
                    alert(result.message);
                }
            } catch (error) {
                console.error(error);
                alert('An error occurred.');
            } finally {
                btn.disabled = false;
                btn.innerText = 'Confirm and Delete All Data';
            }
        });

        async function toggleBlock(sellerId) {
            const btn = document.getElementById(`block-btn-${sellerId}`);
            const statusBadge = document.getElementById(`status-${sellerId}`);

            btn.disabled = true;
            try {
                const formData = new FormData();
                formData.append('_method', 'PATCH');
                formData.append('_token', '{{ csrf_token() }}');

                const response = await fetch(`{{ url('admin/settings/sellers') }}/${sellerId}/toggle-block`, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                });
                const result = await response.json();

                if (result.success) {
                    if (result.is_blocked) {
                        btn.innerText = 'Unblock';
                        statusBadge.innerText = 'Blocked';
                        statusBadge.className = 'status-badge status-blocked';
                    } else {
                        btn.innerText = 'Block';
                        statusBadge.innerText = 'Active';
                        statusBadge.className = 'status-badge status-active';
                    }
                }
            } catch (error) {
                console.error(error);
            } finally {
                btn.disabled = false;
            }
        }
    </script>
@endsection