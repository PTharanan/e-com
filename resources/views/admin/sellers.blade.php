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

        .actions-flex {
            display: flex;
            gap: 8px;
        }

        .btn-block {
            padding: 6px 14px;
            border-radius: 8px;
            border: 1px solid #EF4444;
            background: white;
            font-size: 13px;
            font-weight: 600;
            color: #EF4444;
            cursor: pointer;
            transition: 0.2s;
        }

        .btn-block:hover {
            background: #EF4444;
            color: white;
        }

        .btn-unblock {
            padding: 6px 14px;
            border-radius: 8px;
            border: 1px solid #10B981;
            background: white;
            font-size: 13px;
            font-weight: 600;
            color: #10B981;
            cursor: pointer;
            transition: 0.2s;
        }

        .btn-unblock:hover {
            background: #10B981;
            color: white;
        }

        .btn-view {
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

        .btn-view:hover {
            background: var(--admin-primary);
            color: white;
        }
    </style>
@endsection

@section('content')
    <div class="page-header">
        <div class="page-title">
            <h1>Seller Management</h1>
            <p>View and manage sellers assigned to your store.</p>
        </div>
    </div>

    <div class="data-card">
        <table>
            <thead>
                <tr>
                    <th>Seller</th>
                    <th>Email</th>
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
                                </div>
                            </div>
                        </td>
                        <td>{{ $seller->email }}</td>
                        <td>{{ $seller->created_at->format('M d, Y') }}</td>
                        <td>
                            @php
                                $productCount = \App\Models\Product::where('seller_id', $seller->id)->count();
                            @endphp
                            <span style="font-weight: 600;">{{ $productCount }}</span> Products
                        </td>
                        <td>
                            @if($seller->is_blocked)
                                <span class="status-badge status-blocked" id="status-{{ $seller->id }}">Blocked</span>
                            @else
                                <span class="status-badge status-active" id="status-{{ $seller->id }}">Active</span>
                            @endif
                        </td>
                        <td>
                            <div class="actions-flex">
                                <button class="btn-view"
                                    onclick="window.location.href='/admin/products?seller_id={{ $seller->id }}'">
                                    View Products
                                </button>
                                <button class="{{ $seller->is_blocked ? 'btn-unblock' : 'btn-block' }}"
                                    id="block-btn-{{ $seller->id }}" onclick="toggleBlock({{ $seller->id }})">
                                    {{ $seller->is_blocked ? 'Unblock' : 'Block' }}
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 50px; color: #64748B;">
                            No sellers assigned to your store yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection

@section('scripts')
    <script>
        async function toggleBlock(sellerId) {
            if (!confirm('Are you sure you want to change this seller\'s status?')) return;

            const btn = document.getElementById(`block-btn-${sellerId}`);
            const statusBadge = document.getElementById(`status-${sellerId}`);

            btn.disabled = true;
            const originalText = btn.innerText;
            btn.innerText = 'Processing...';

            try {
                const response = await fetch(`/admin/settings/sellers/${sellerId}/toggle-block`, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                const result = await response.json();

                if (result.success) {
                    if (result.is_blocked) {
                        btn.innerText = 'Unblock';
                        btn.className = 'btn-unblock';
                        statusBadge.innerText = 'Blocked';
                        statusBadge.className = 'status-badge status-blocked';
                    } else {
                        btn.innerText = 'Block';
                        btn.className = 'btn-block';
                        statusBadge.innerText = 'Active';
                        statusBadge.className = 'status-badge status-active';
                    }
                } else {
                    alert('Operation failed.');
                    btn.innerText = originalText;
                }
            } catch (error) {
                console.error(error);
                alert('An error occurred.');
                btn.innerText = originalText;
            } finally {
                btn.disabled = false;
            }
        }
    </script>
@endsection