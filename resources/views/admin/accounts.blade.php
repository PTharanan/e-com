@extends('layouts.admin')

@section('title', 'Admin Accounts')

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

        .btn-add {
            padding: 10px 20px;
            background: var(--admin-primary);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            transition: 0.2s;
            text-decoration: none;
        }

        .btn-add:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2);
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

        .admin-profile {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .admin-avatar {
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

        .status-blocked {
            background: #FEE2E2;
            color: #EF4444;
        }

        .actions-flex {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .action-btn {
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
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s;
            border: 1px solid #E2E8F0;
            background: white;
            color: #64748B;
        }

        .btn-block-action.blocked {
            color: #10B981;
            border-color: #10B981;
        }

        .btn-block-action.blocked:hover {
            background: #10B981;
            color: white;
        }

        .btn-block-action.unblocked {
            color: #EF4444;
            border-color: #EF4444;
        }

        .btn-block-action.unblocked:hover {
            background: #EF4444;
            color: white;
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

        .password-field-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .password-toggle {
            position: absolute;
            right: 12px;
            cursor: pointer;
            color: #64748B;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 5px;
            border-radius: 6px;
            transition: 0.2s;
        }

        .password-toggle:hover {
            background: #F1F5F9;
            color: var(--admin-primary);
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
    </style>
@endsection

@section('content')
    <div class="page-header">
        <div class="page-title">
            <h1>Admin Accounts</h1>
            <p>Manage all administrator accounts in the system.</p>
        </div>
        <button class="btn-add" onclick="openModal('addAdminModal')">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            Add New Admin
        </button>
    </div>

    <div class="data-card">
        <table>
            <thead>
                <tr>
                    <th>Admin</th>
                    <th>Email</th>
                    <th>Joined Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($admins as $admin)
                    <tr>
                        <td>
                            <div class="admin-profile">
                                <div class="admin-avatar">{{ substr($admin->name, 0, 1) }}</div>
                                <div class="admin-info">
                                    <div style="font-weight: 700; color: #1E293B;">{{ $admin->name }}</div>
                                    <div style="font-size: 11px; color: #64748B;">ID: #{{ $admin->id }}</div>
                                    @if($admin->last_edited_by)
                                        <div style="font-size: 10px; color: #F59E0B; margin-top: 3px; font-weight: 600;">
                                            ✏ Last edit: {{ $admin->lastEditor->name ?? 'Unknown' }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>{{ $admin->email }}</td>
                        <td>{{ $admin->created_at->format('M d, Y') }}</td>
                        <td>
                            @if($admin->is_blocked)
                                <span class="status-badge status-blocked" id="status-{{ $admin->id }}">Blocked</span>
                            @else
                                <span class="status-badge status-active" id="status-{{ $admin->id }}">Active</span>
                            @endif
                        </td>
                        <td>
                            <div class="actions-flex">
                                @if(auth()->id() != $admin->id)
                                    <button class="action-btn btn-edit" title="Edit Admin"
                                        onclick="openEditModal({{ json_encode($admin) }})">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                        </svg>
                                    </button>

                                    <button class="btn-block-action {{ $admin->is_blocked ? 'blocked' : 'unblocked' }}"
                                        id="block-btn-{{ $admin->id }}" onclick="toggleBlock({{ $admin->id }})">
                                        {{ $admin->is_blocked ? 'Unblock' : 'Block' }}
                                    </button>

                                    <button class="action-btn btn-delete" title="Delete Admin"
                                        onclick="openDeleteModal({{ $admin->id }})">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2">
                                            <polyline points="3 6 5 6 21 6"></polyline>
                                            <path
                                                d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2">
                                            </path>
                                        </svg>
                                    </button>
                                @else
                                    <div class="admin-profile" style="opacity: 0.6;">
                                        <button class="action-btn btn-edit" title="Edit Profile"
                                            onclick="openEditModal({{ json_encode($admin) }})">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2">
                                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                            </svg>
                                        </button>
                                        <span style="color: #64748B; font-size: 11px; font-weight: 600;">(You)</span>
                                    </div>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 50px; color: #64748B;">
                            No admin accounts found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Add Admin Modal -->
    <div class="modal-overlay" id="addAdminModal">
        <div class="modal-container">
            <div class="modal-header">
                <h2>Add New Administrator</h2>
            </div>
            <form id="addAdminForm">
                @csrf
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" class="form-control" placeholder="Enter name" required>
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" class="form-control" placeholder="Enter email" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <div class="password-field-wrapper">
                        <input type="password" name="password" id="add_password" class="form-control"
                            placeholder="Minimum 8 characters" required>
                        <span class="password-toggle" onclick="togglePassVisibility('add_password', this)">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" id="eye-icon">
                                <path
                                    d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24">
                                </path>
                                <line x1="1" y1="1" x2="23" y2="23"></line>
                            </svg>
                        </span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="closeModal('addAdminModal')">Cancel</button>
                    <button type="submit" class="btn-save">Create Account</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Admin Modal -->
    <div class="modal-overlay" id="editAdminModal">
        <div class="modal-container">
            <div class="modal-header">
                <h2>Edit Administrator</h2>
                <p style="font-size: 12px; color: #64748B; margin-top: 5px;">Verify your identity to save changes.</p>
            </div>
            <form id="editAdminForm">
                @csrf
                @method('PATCH')
                <input type="hidden" name="id" id="edit_admin_id">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" id="edit_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" id="edit_email" class="form-control" readonly
                        style="background: #F8FAFC; cursor: not-allowed;">
                    <p style="font-size: 11px; color: #94A3B8; margin-top: 5px;">Email address cannot be changed for
                        security reasons.</p>
                </div>
                <div class="form-group">
                    <label>New Password (Leave blank to keep current)</label>
                    <div class="password-field-wrapper">
                        <input type="password" name="password" id="edit_new_password" class="form-control"
                            placeholder="Optional">
                        <span class="password-toggle" onclick="togglePassVisibility('edit_new_password', this)">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <path
                                    d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24">
                                </path>
                                <line x1="1" y1="1" x2="23" y2="23"></line>
                            </svg>
                        </span>
                    </div>
                </div>
                <hr style="border: 0; border-top: 1px solid #F1F5F9; margin: 20px 0;">
                <div class="form-group">
                    <label style="color: #EF4444;">Your Current Password (Required)</label>
                    <div class="password-field-wrapper">
                        <input type="password" name="current_password" id="verify_password" class="form-control"
                            placeholder="Confirm your password" required>
                        <span class="password-toggle" onclick="togglePassVisibility('verify_password', this)">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <path
                                    d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24">
                                </path>
                                <line x1="1" y1="1" x2="23" y2="23"></line>
                            </svg>
                        </span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="closeModal('editAdminModal')">Cancel</button>
                    <button type="submit" class="btn-save">Verify & Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Admin Confirmation Modal -->
    <div class="modal-overlay" id="deleteAdminModal">
        <div class="modal-container">
            <div class="modal-header">
                <h2 style="color: #EF4444;">Delete Administrator</h2>
                <p style="font-size: 12px; color: #64748B; margin-top: 5px;">This action is permanent and cannot be undone. The admin's account and address will be completely removed.</p>
            </div>
            <input type="hidden" id="delete_admin_id">
            <div style="background: #FEF2F2; border: 1px solid #FECACA; border-radius: 12px; padding: 15px; margin-bottom: 20px;">
                <p style="font-size: 14px; color: #991B1B; font-weight: 600; margin: 0;">⚠ Are you sure you want to delete this admin?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeModal('deleteAdminModal')">No, Cancel</button>
                <button type="button" class="btn-save" id="confirmDeleteBtn" style="background: #EF4444;" onclick="confirmDeleteAdmin()">Yes, Delete</button>
            </div>
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

        function togglePassVisibility(inputId, toggleBtn) {
            const input = document.getElementById(inputId);
            const icon = toggleBtn.querySelector('svg');

            if (input.type === 'password') {
                input.type = 'text';
                icon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line>';
            } else {
                input.type = 'password';
                icon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>';
            }
        }

        function openEditModal(admin) {
            document.getElementById('edit_admin_id').value = admin.id;
            document.getElementById('edit_name').value = admin.name;
            document.getElementById('edit_email').value = admin.email;
            openModal('editAdminModal');
        }

        // Add Admin Handler
        document.getElementById('addAdminForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const btn = e.target.querySelector('.btn-save');
            btn.disabled = true;
            btn.innerText = 'Creating...';

            try {
                const response = await fetch("{{ route('admin.accounts.store') }}", {
                    method: 'POST',
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
                btn.innerText = 'Create Account';
            }
        });

        // Edit Admin Handler
        document.getElementById('editAdminForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const id = formData.get('id');
            const btn = e.target.querySelector('.btn-save');
            btn.disabled = true;
            btn.innerText = 'Saving...';

            try {
                const response = await fetch(`{{ url('admin/settings/admin-accounts') }}/${id}`, {
                    method: 'POST', // Using POST with _method=PATCH
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
                btn.innerText = 'Save Changes';
            }
        });

        function openDeleteModal(id) {
            document.getElementById('delete_admin_id').value = id;
            openModal('deleteAdminModal');
        }

        async function confirmDeleteAdmin() {
            const id = document.getElementById('delete_admin_id').value;
            const btn = document.getElementById('confirmDeleteBtn');
            btn.disabled = true;
            btn.innerText = 'Deleting...';

            try {
                const formData = new FormData();
                formData.append('_method', 'DELETE');
                formData.append('_token', '{{ csrf_token() }}');

                const response = await fetch(`{{ url('admin/settings/admin-accounts') }}/${id}`, {
                    method: 'POST',
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
                btn.innerText = 'Yes, Delete';
            }
        }

        async function toggleBlock(adminId) {
            const btn = document.getElementById(`block-btn-${adminId}`);
            const statusBadge = document.getElementById(`status-${adminId}`);

            btn.disabled = true;
            try {
                const formData = new FormData();
                formData.append('_method', 'PATCH');
                formData.append('_token', '{{ csrf_token() }}');

                const response = await fetch(`{{ url('admin/settings/admin-accounts') }}/${adminId}/toggle-block`, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                });
                const result = await response.json();

                if (result.success) {
                    if (result.is_blocked) {
                        btn.innerText = 'Unblock';
                        btn.className = 'btn-block-action blocked';
                        statusBadge.innerText = 'Blocked';
                        statusBadge.className = 'status-badge status-blocked';
                    } else {
                        btn.innerText = 'Block';
                        btn.className = 'btn-block-action unblocked';
                        statusBadge.innerText = 'Active';
                        statusBadge.className = 'status-badge status-active';
                    }
                } else {
                    alert(result.message);
                }
            } catch (error) {
                console.error(error);
            } finally {
                btn.disabled = false;
            }
        }
    </script>
@endsection