@extends('layouts.admin')

@section('title', 'Manage Products')

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
            margin: 0;
        }

        .page-title p {
            color: #64748B;
            font-size: 14px;
            margin-top: 5px;
        }

        .btn-primary {
            background: var(--admin-primary);
            color: white;
            padding: 12px 24px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: var(--transition);
            border: none;
            cursor: pointer;
            font-family: inherit;
        }

        .btn-primary:hover {
            background: var(--admin-primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(242, 92, 59, 0.2);
        }

        .data-card {
            background: white;
            padding: 0;
            border-radius: 24px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03);
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        th {
            padding: 20px 25px;
            background: #F8FAFC;
            color: #64748B;
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #F1F5F9;
        }

        td {
            padding: 20px 25px;
            color: #1E293B;
            font-size: 15px;
            border-bottom: 1px solid #F1F5F9;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover td {
            background: #F8FAFC;
        }

        .product-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .product-img {
            width: 45px;
            height: 45px;
            background: #F1F5F9;
            border-radius: 10px;
            object-fit: cover;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-available {
            background: #DCFCE7;
            color: #166534;
        }

        .status-not {
            background: #FEE2E2;
            color: #991B1B;
        }

        .action-btn {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: 0.2s;
            border: none;
            cursor: pointer;
            background: #F8FAFC;
        }

        .btn-edit {
            color: #3B82F6;
        }

        .btn-edit:hover {
            background: #DBEAFE;
        }

        .btn-delete {
            color: #EF4444;
        }

        .btn-delete:hover {
            background: #FEE2E2;
        }

        /* Discount Popover Styles */
        .discount-edit-wrapper {
            position: relative;
            display: inline-block;
        }

        .discount-badge {
            cursor: pointer;
            background: #DCFCE7;
            color: #10B981;
            padding: 6px 12px;
            border-radius: 10px;
            font-weight: 700;
            transition: 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            border: 1px solid #BBF7D0;
        }

        .discount-badge:hover {
            background: #10B981;
            color: white;
            border-color: #10B981;
        }

        .discount-popover {
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            background: white;
            border: 1px solid #E2E8F0;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            padding: 10px;
            border-radius: 14px;
            z-index: 100;
            display: none;
            align-items: center;
            gap: 8px;
            margin-top: 8px;
            animation: popIn 0.2s ease-out;
        }

        @keyframes popIn {
            from {
                transform: translateX(-50%) translateY(-5px);
                opacity: 0;
            }

            to {
                transform: translateX(-50%) translateY(0);
                opacity: 1;
            }
        }

        .discount-popover.active {
            display: flex;
        }

        .tick-btn {
            background: #10B981;
            color: white;
            border: none;
            border-radius: 8px;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: 0.2s;
        }

        .tick-btn:hover {
            background: #059669;
            transform: scale(1.05);
        }

        /* Modal Styles */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(8px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 2000;
            padding: 20px;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal-content {
            background: white;
            width: 100%;
            max-width: 600px;
            border-radius: 24px;
            padding: 35px;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
            animation: modalSlideUp 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        @keyframes modalSlideUp {
            from {
                transform: translateY(30px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .step {
            display: none;
        }

        .step.active {
            display: block;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group.full {
            grid-column: 1 / -1;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #374151;
            font-size: 14px;
        }

        .form-input,
        .form-select,
        .form-textarea {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #F3F4F6;
            border-radius: 12px;
            font-family: inherit;
            font-size: 14px;
            transition: 0.2s;
        }

        .form-input:focus,
        .form-select:focus,
        .form-textarea:focus {
            border-color: var(--admin-primary);
            outline: none;
            background: #FFF9F8;
        }

        .form-textarea {
            resize: vertical;
            min-height: 100px;
        }

        /* Image Upload Grid */
        .image-upload-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 10px;
            margin-top: 10px;
        }

        .image-slot {
            aspect-ratio: 1;
            border: 2px dashed #E5E7EB;
            border-radius: 12px;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            overflow: hidden;
            transition: 0.2s;
        }

        .image-slot:hover {
            border-color: var(--admin-primary);
            background: #FFF9F8;
        }

        .image-slot input {
            position: absolute;
            inset: 0;
            opacity: 0;
            cursor: pointer;
            z-index: 2;
        }

        .image-slot img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            position: absolute;
            inset: 0;
            z-index: 1;
            display: none;
        }

        .image-slot .plus-icon {
            color: #9CA3AF;
            font-size: 24px;
            z-index: 0;
        }

        .remove-img {
            position: absolute;
            top: 5px;
            right: 5px;
            width: 22px;
            height: 22px;
            background: rgba(239, 68, 68, 0.9);
            color: white;
            border-radius: 50%;
            display: none;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            cursor: pointer;
            z-index: 10;
            border: none;
            transition: 0.2s;
        }

        .remove-img:hover {
            background: #DC2626;
            transform: scale(1.1);
        }

        .main-image-selection {
            margin-top: 15px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .main-image-option {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            background: #F8FAFC;
            border-radius: 10px;
            cursor: pointer;
            border: 2px solid transparent;
            font-size: 12px;
            font-weight: 600;
        }

        .main-image-option input {
            display: none;
        }

        .main-image-option.selected {
            border-color: var(--admin-primary);
            background: #FFF1EE;
            color: var(--admin-primary);
        }

        .modal-footer {
            display: flex;
            gap: 12px;
            margin-top: 30px;
        }

        .modal-btn {
            flex: 1;
            padding: 14px;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: 0.2s;
            font-family: inherit;
        }

        .btn-cancel {
            background: #F3F4F6;
            color: #374151;
        }

        .btn-save {
            background: var(--admin-primary);
            color: white;
        }

        /* Delete Modal Styles */
        .delete-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 3000;
            padding: 20px;
        }

        .delete-modal-overlay.active {
            display: flex;
        }

        .delete-modal {
            background: white;
            padding: 30px;
            border-radius: 24px;
            width: 100%;
            max-width: 400px;
            text-align: center;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15);
            animation: modalSlideUp 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .delete-modal-icon {
            width: 60px;
            height: 60px;
            background: #FEF2F2;
            color: #EF4444;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }

        .delete-modal-icon svg {
            width: 30px;
            height: 30px;
        }

        .delete-modal h3 {
            font-size: 20px;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 10px;
        }

        .delete-modal p {
            color: #6B7280;
            font-size: 14px;
            line-height: 1.5;
            margin-bottom: 25px;
        }

        .delete-modal-actions {
            display: flex;
            gap: 12px;
        }

        .delete-modal-actions button {
            flex: 1;
            padding: 12px;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: 0.2s;
            font-family: inherit;
        }

        .delete-btn-cancel {
            background: #F3F4F6;
            color: #374151;
        }

        .delete-btn-confirm {
            background: #EF4444;
            color: white;
        }

        .delete-btn-confirm:hover {
            background: #DC2626;
        }

        /* SEARCH BAR STYLES */
        .search-container {
            display: flex;
            align-items: center;
            background: white;
            border-radius: 12px;
            padding: 4px 15px;
            border: 1px solid #E2E8F0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.02);
            width: 300px;
            transition: all 0.3s ease;
        }

        .search-container:focus-within {
            border-color: var(--admin-primary);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            width: 350px;
        }

        .search-input {
            border: none;
            padding: 8px 10px;
            font-size: 14px;
            font-family: inherit;
            outline: none;
            width: 100%;
            color: #1E293B;
        }

        .search-icon {
            color: #94A3B8;
            flex-shrink: 0;
        }

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
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
        }

        .pagination-link:hover:not(.disabled) {
            border-color: var(--admin-primary);
            color: var(--admin-primary);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
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
            <h1>Products Catalog</h1>
            <p>Manage your inventory and product listings.</p>
        </div>
        <div style="display: flex; gap: 15px; align-items: center;">
            <form action="{{ route('admin.products') }}" method="GET" id="searchForm">
                <div class="search-container">
                    <svg class="search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2.5">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <input type="text" name="search" id="adminSearchInput" class="search-input"
                        placeholder="Search name or category..." value="{{ request('search') }}" oninput="debounceSearch()">
                </div>
            </form>
            <button class="btn-primary" id="openProductModal">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                Add Product
            </button>
        </div>
    </div>

    <div class="data-card">
        <table>
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Discount %</th>
                    <th>Stock</th>
                    <th>Status</th>
                    <th>Flags</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                    <tr>
                        <td>
                            <div class="product-info">
                                <img src="{{ asset($product->main_image_url) }}" class="product-img" alt="{{ $product->name }}">
                                <strong>{{ $product->name }}</strong>
                            </div>
                        </td>
                        <td>{{ $product->category->name }}</td>
                        <td>{{ currency_symbol() }}{{ number_format($product->price, 2) }}</td>
                        <td>
                            <div class="discount-edit-wrapper">
                                <div class="discount-badge" onclick="toggleDiscountPopover({{ $product->id }})">
                                    {{ $product->discount_percentage ?: 0 }}%
                                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="3">
                                        <path d="M6 9l6 6 6-6" />
                                    </svg>
                                </div>
                                <div class="discount-popover" id="popover-{{ $product->id }}">
                                    <input type="number" id="input-{{ $product->id }}"
                                        value="{{ $product->discount_percentage }}" class="form-input"
                                        style="width: 70px; padding: 6px 10px;" min="0" max="100">
                                    <button class="tick-btn" onclick="saveQuickDiscount({{ $product->id }})">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="3">
                                            <polyline points="20 6 9 17 4 12"></polyline>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </td>
                        <td>{{ $product->stock_quantity }}</td>
                        <td>
                            <span class="status-badge status-{{ $product->stock_status }}">
                                {{ ucfirst($product->stock_status) }}
                            </span>
                        </td>
                        <td>
                            <label style="cursor: pointer; display: flex; align-items: center; gap: 8px;">
                                <input type="checkbox" {{ $product->is_new ? 'checked' : '' }}
                                    style="width: 18px; height: 18px; accent-color: var(--admin-primary);"
                                    onchange="quickUpdateProduct({{ $product->id }}, 'is_new', this.checked ? 1 : 0)">
                                @if($product->is_new)
                                    <span
                                        style="background: #E0E7FF; color: #4338CA; padding: 4px 8px; border-radius: 6px; font-size: 11px; font-weight: 700;">NEW</span>
                                @else
                                    <span style="color: #9CA3AF; font-size: 11px;">Mark New</span>
                                @endif
                            </label>
                        </td>
                        <td>
                            <div style="display: flex; gap: 8px;">
                                <button class="action-btn btn-edit" title="Edit"
                                    onclick="editProduct({{ json_encode($product) }})">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                    </svg>
                                </button>
                                <button class="action-btn btn-delete" title="Delete"
                                    onclick="deleteProduct({{ $product->id }})">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="3 6 5 6 21 6"></polyline>
                                        <path
                                            d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2">
                                        </path>
                                        <line x1="10" y1="11" x2="10" y2="17"></line>
                                        <line x1="14" y1="11" x2="14" y2="17"></line>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 40px; color: #64748B;">No products found. Add your
                            first product!</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($products->hasPages())
        <div class="pagination-container">
            <div class="pagination-info">
                Showing <b>{{ $products->firstItem() }}</b> to <b>{{ $products->lastItem() }}</b> of
                <b>{{ $products->total() }}</b> products
            </div>
            <div class="pagination-nav">
                @if($products->onFirstPage())
                    <span class="pagination-link disabled">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round">
                            <polyline points="15 18 9 12 15 6"></polyline>
                        </svg>
                    </span>
                @else
                    <a href="{{ $products->appends(request()->query())->previousPageUrl() }}" class="pagination-link"
                        title="Previous Page">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round">
                            <polyline points="15 18 9 12 15 6"></polyline>
                        </svg>
                    </a>
                @endif

                <div style="font-weight: 700; color: #1E293B; margin: 0 10px;">
                    Page {{ $products->currentPage() }}
                </div>

                @if($products->hasMorePages())
                    <a href="{{ $products->appends(request()->query())->nextPageUrl() }}" class="pagination-link" title="Next Page">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </a>
                @else
                    <span class="pagination-link disabled">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </span>
                @endif
            </div>
        </div>
    @endif

    <!-- Add Product Modal -->
    <div class="modal-overlay" id="productModal">
        <div class="modal-content">
            <div class="modal-header" style="text-align: center; margin-bottom: 25px;">
                <h2 id="modalTitle" style="font-size: 22px; font-weight: 700; margin-bottom: 5px;">Add New Product</h2>
                <p id="productStepDesc" style="color: #666; font-size: 14px;">Step 1: Basic Product Details</p>
            </div>

            <form id="addProductForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="product_id" id="product_id">
                <input type="hidden" name="_method" id="form_method" value="POST">

                <!-- Step 1: Basic Details -->
                <div class="step active" id="p-step1">
                    <div class="form-grid">
                        <div class="form-group full">
                            <label class="form-label">Product Name</label>
                            <input type="text" name="name" id="p_name" class="form-input" placeholder="Enter product name"
                                required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Category</label>
                            <select name="category_id" class="form-select" required>
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Regular Price ({{ currency_symbol() }})</label>
                            <input type="number" name="price" step="0.01" class="form-input" placeholder="0.00" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Discount Percentage (%) <small
                                    style="color: #9CA3AF;">(Optional)</small></label>
                            <input type="number" name="discount_percentage" class="form-input" placeholder="0-100" min="0"
                                max="100">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="modal-btn btn-cancel" onclick="closeProductModal()">Cancel</button>
                        <button type="button" class="modal-btn btn-save" onclick="nextProductStep(2)">Next:
                            Description</button>
                    </div>
                </div>

                <!-- Step 2: Description & Stock -->
                <div class="step" id="p-step2">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group">
                            <label class="form-label">Stock Status</label>
                            <select name="stock_status" id="p_status" class="form-select" required>
                                <option value="available">Available</option>
                                <option value="not">Not Available</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Stock Quantity</label>
                            <input type="number" name="stock_quantity" id="p_quantity" class="form-input" placeholder="0"
                                min="0" required oninput="updateStatusFromQty(this.value)">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                            <input type="checkbox" name="is_new" value="1"
                                style="width: 18px; height: 18px; accent-color: var(--admin-primary);">
                            <span>Mark as "NEW" Product</span>
                        </label>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Product Description</label>
                        <textarea name="description" class="form-textarea"
                            placeholder="Tell us about the product..."></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="modal-btn btn-cancel" onclick="nextProductStep(1)">Back</button>
                        <button type="button" class="modal-btn btn-save" onclick="nextProductStep(3)">Next: Images</button>
                    </div>
                </div>

                <!-- Step 3: Images -->
                <div class="step" id="p-step3">
                    <div class="form-group">
                        <label class="form-label">Product Images (Max 5)</label>
                        <div class="image-upload-grid">
                            @for($i = 0; $i < 5; $i++)
                                <div class="image-slot" id="slot-{{ $i }}">
                                    <span class="plus-icon" id="plus-{{ $i }}">+</span>
                                    <img id="preview-{{ $i }}">
                                    <button type="button" class="remove-img" id="remove-{{ $i }}"
                                        onclick="event.stopPropagation(); removeImage({{ $i }})">&times;</button>
                                    <input type="file" name="images[]" id="img-input-{{ $i }}" accept=".jpg,.jpeg,.png,.webp"
                                        onchange="previewImage(this, {{ $i }})">
                                </div>
                            @endfor
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Select Main Display Image</label>
                        <div class="main-image-selection" id="mainImageOptions">
                            <p style="font-size: 13px; color: #9CA3AF;">Upload images first to select main image.</p>
                        </div>
                        <input type="hidden" name="main_image_index" id="main_image_index" value="0">
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="modal-btn btn-cancel" onclick="nextProductStep(2)">Back</button>
                        <button type="submit" class="modal-btn btn-save" id="saveProductBtn">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="delete-modal-overlay" id="deleteModal">
        <div class="delete-modal">
            <div class="delete-modal-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 6h18m-2 0v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                    <line x1="10" y1="11" x2="10" y2="17"></line>
                    <line x1="14" y1="11" x2="14" y2="17"></line>
                </svg>
            </div>
            <h3>Delete Product?</h3>
            <p>Are you sure you want to delete this product? This action cannot be undone and will remove all product images
                from the server.</p>
            <div class="delete-modal-actions">
                <button class="delete-btn-cancel" onclick="closeDeleteModal()">Cancel</button>
                <button class="delete-btn-confirm" id="confirmDeleteBtn">Yes, Delete</button>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        // Live AJAX Search Logic
        let searchTimeout = null;
        async function performAjaxSearch(targetUrl = null) {
            let url;
            if (targetUrl) {
                url = targetUrl;
            } else {
                const form = document.getElementById('searchForm');
                const formData = new FormData(form);
                const params = new URLSearchParams(formData);
                url = `${window.location.pathname}?${params.toString()}`;
            }

            // Update URL in browser
            window.history.pushState({}, '', url);

            // Show loading state
            const table = document.querySelector('table');
            if (table) table.style.opacity = '0.5';

            try {
                const response = await fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const html = await response.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');

                // Replace table body
                const newTable = doc.querySelector('table tbody');
                const currentTable = document.querySelector('table tbody');
                if (newTable && currentTable) {
                    currentTable.innerHTML = newTable.innerHTML;
                }

                // Replace pagination
                const newPagination = doc.querySelector('.pagination-container');
                const currentPagination = document.querySelector('.pagination-container');
                const dataCard = document.querySelector('.data-card');

                if (newPagination) {
                    if (currentPagination) {
                        currentPagination.innerHTML = newPagination.innerHTML;
                    } else if (dataCard) {
                        dataCard.insertAdjacentHTML('afterend', newPagination.outerHTML);
                    }
                } else if (currentPagination) {
                    currentPagination.remove();
                }

                if (table) table.style.opacity = '1';

                if (targetUrl) {
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            } catch (error) {
                console.error('Search failed:', error);
                if (!targetUrl) document.getElementById('searchForm').submit();
                else window.location.href = targetUrl;
            }
        }

        function debounceSearch() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => performAjaxSearch(), 500);
        }

        // Intercept pagination clicks
        document.addEventListener('click', (e) => {
            const link = e.target.closest('.pagination-link');
            if (link && link.href && !link.classList.contains('disabled')) {
                e.preventDefault();
                performAjaxSearch(link.href);
            }
        });

        // Restore focus on reload
        document.addEventListener('DOMContentLoaded', () => {
            if (sessionStorage.getItem('admin_search_focused') === 'true') {
                sessionStorage.removeItem('admin_search_focused');
                const input = document.getElementById('adminSearchInput');
                if (input) {
                    input.focus();
                    // Move cursor to end
                    const val = input.value;
                    input.value = '';
                    input.value = val;
                }
            }
        });

        const modal = document.getElementById('productModal');
        const openBtn = document.getElementById('openProductModal');
        const form = document.getElementById('addProductForm');
        const mainImgOptionsContainer = document.getElementById('mainImageOptions');
        const productStepDesc = document.getElementById('productStepDesc');
        const assetBase = "{{ asset('') }}";

        openBtn.onclick = () => {
            document.getElementById('modalTitle').innerText = 'Add New Product';
            document.getElementById('saveProductBtn').innerText = 'Save';
            document.getElementById('form_method').value = 'POST';
            document.getElementById('product_id').value = '';
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        };

        function editProduct(product) {
            document.getElementById('modalTitle').innerText = 'Edit Product';
            document.getElementById('saveProductBtn').innerText = 'Update';
            document.getElementById('form_method').value = 'PUT';
            document.getElementById('product_id').value = product.id;

            // Fill form fields
            form.name.value = product.name;
            form.category_id.value = product.category_id;
            form.price.value = product.price;
            form.discount_percentage.value = product.discount_percentage || '';
            form.is_new.checked = product.is_new == 1;
            form.stock_status.value = product.stock_status;
            form.stock_quantity.value = product.stock_quantity;
            form.description.value = product.description || '';

            // Handle images
            resetProductForm(false); // Reset steps and previews, but keep ID

            if (product.image_urls && product.image_urls.length > 0) {
                product.image_urls.forEach((url, index) => {
                    if (index < 5) {
                        const preview = document.getElementById(`preview-${index}`);
                        const plus = document.getElementById(`plus-${index}`);
                        const removeBtn = document.getElementById(`remove-${index}`);

                        preview.src = assetBase + url;
                        preview.style.display = 'block';
                        plus.style.display = 'none';
                        removeBtn.style.display = 'flex';

                        // Note: We don't put the file object in imageFiles[index] because it's a URL
                        // But we need to know it's "filled" for the main image selection
                        imageFiles[index] = { isExisting: true, url: url };
                    }
                });

                // Find main image index
                const mainIndex = product.image_urls.indexOf(product.main_image_url);
                if (mainIndex !== -1) {
                    document.getElementById('main_image_index').value = mainIndex;
                }
                updateMainImageOptions();
            }

            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        let productToDelete = null;

        function deleteProduct(id) {
            productToDelete = id;
            document.getElementById('deleteModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.remove('active');
            document.body.style.overflow = 'auto';
            productToDelete = null;
        }

        document.getElementById('confirmDeleteBtn').onclick = async () => {
            if (!productToDelete) return;

            const btn = document.getElementById('confirmDeleteBtn');
            btn.disabled = true;
            btn.innerText = 'Deleting...';

            try {
                const response = await fetch(`{{ url('admin/products') }}/${productToDelete}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                const result = await response.json();
                if (result.success) {
                    location.reload();
                } else {
                    alert('Delete failed.');
                }
            } catch (error) {
                console.error(error);
                alert('An error occurred during deletion.');
            } finally {
                btn.disabled = false;
                btn.innerText = 'Yes, Delete';
                closeDeleteModal();
            }
        };

        function closeProductModal() {
            modal.classList.remove('active');
            document.body.style.overflow = 'auto';
            resetProductForm();
        }

        function updateStatusFromQty(qty) {
            const statusSelect = document.getElementById('p_status');
            if (qty > 0) {
                statusSelect.value = 'available';
            } else {
                statusSelect.value = 'not';
            }
        }

        async function quickUpdateProduct(id, field, value) {
            const formData = new FormData();
            formData.append(field, value);

            try {
                const response = await fetch(`{{ url('admin/products') }}/${id}/quick`, {
                    method: 'POST', // Use POST with X-HTTP-Method-Override or just use PATCH if supported
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-HTTP-Method-Override': 'PATCH',
                        'Accept': 'application/json'
                    }
                });
                const result = await response.json();
                if (result.success) {
                    location.reload();
                    return true;
                } else {
                    alert('Failed to update product: ' + (result.message || 'Error'));
                    return false;
                }
            } catch (error) {
                console.error(error);
                alert('An error occurred during quick update.');
                return false;
            }
        }

        function toggleDiscountPopover(id) {
            // Close all other popovers first
            document.querySelectorAll('.discount-popover').forEach(pop => {
                if (pop.id !== `popover-${id}`) pop.classList.remove('active');
            });

            const popover = document.getElementById(`popover-${id}`);
            popover.classList.toggle('active');
            if (popover.classList.contains('active')) {
                document.getElementById(`input-${id}`).focus();
            }
        }

        async function saveQuickDiscount(id) {
            const input = document.getElementById(`input-${id}`);
            const newValue = input.value;
            const success = await quickUpdateProduct(id, 'discount_percentage', newValue);

            if (success) {
                // Update the badge text
                const badge = input.closest('.discount-edit-wrapper').querySelector('.discount-badge');
                badge.innerHTML = `${newValue || 0}% <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M6 9l6 6 6-6"/></svg>`;
                document.getElementById(`popover-${id}`).classList.remove('active');
            }
        }

        // Close popovers when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.discount-edit-wrapper')) {
                document.querySelectorAll('.discount-popover').forEach(pop => pop.classList.remove('active'));
            }
        });

        function resetProductForm(full = true) {
            if (full) {
                form.reset();
                document.getElementById('product_id').value = '';
                document.getElementById('form_method').value = 'POST';
                document.getElementById('saveProductBtn').innerText = 'Save';
            }
            nextProductStep(1);
            for (let i = 0; i < 5; i++) {
                document.getElementById(`preview-${i}`).style.display = 'none';
                document.getElementById(`plus-${i}`).style.display = 'block';
                document.getElementById(`remove-${i}`).style.display = 'none';
            }
            imageFiles.fill(null);
            updateMainImageOptions();
        }

        function nextProductStep(step) {
            // Validation for step 1
            if (step === 2 && !document.getElementById('p_name').value) {
                alert('Please enter a product name.');
                return;
            }

            document.querySelectorAll('.step').forEach(s => s.classList.remove('active'));
            document.getElementById(`p-step${step}`).classList.add('active');

            const descriptions = [
                'Step 1: Basic Product Details',
                'Step 2: Description & Stock',
                'Step 3: Upload Product Images'
            ];
            productStepDesc.innerText = descriptions[step - 1];
        }

        const imageFiles = new Array(5).fill(null);

        function previewImage(input, index) {
            if (input.files && input.files[0]) {
                const file = input.files[0];
                const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
                if (!allowedTypes.includes(file.type)) {
                    alert('Invalid file type. Please upload JPG, JPEG, PNG, or WebP.');
                    input.value = '';
                    return;
                }

                // 10MB Limit check
                if (file.size > 10 * 1024 * 1024) {
                    alert('File is too large. Maximum size is 10MB.');
                    input.value = '';
                    return;
                }

                // Show loading state or similar if needed
                const slot = document.getElementById(`slot-${index}`);
                slot.style.opacity = '0.5';

                compressImageClientSide(file).then(compressedBlob => {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        const preview = document.getElementById(`preview-${index}`);
                        const plus = document.getElementById(`plus-${index}`);
                        const removeBtn = document.getElementById(`remove-${index}`);

                        preview.src = e.target.result;
                        preview.style.display = 'block';
                        plus.style.display = 'none';
                        removeBtn.style.display = 'flex';
                        slot.style.opacity = '1';

                        // Update tracking array with compressed file
                        const compressedFile = new File([compressedBlob], file.name.split('.')[0] + '.webp', {
                            type: 'image/webp'
                        });
                        imageFiles[index] = compressedFile;
                        updateMainImageOptions();
                    }
                    reader.readAsDataURL(compressedBlob);
                }).catch(err => {
                    console.error('Compression error:', err);
                    slot.style.opacity = '1';
                    // Fallback to original file
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        const preview = document.getElementById(`preview-${index}`);
                        const plus = document.getElementById(`plus-${index}`);
                        const removeBtn = document.getElementById(`remove-${index}`);
                        preview.src = e.target.result;
                        preview.style.display = 'block';
                        plus.style.display = 'none';
                        removeBtn.style.display = 'flex';
                        imageFiles[index] = file;
                        updateMainImageOptions();
                    }
                    reader.readAsDataURL(file);
                });
            }
        }

        async function compressImageClientSide(file) {
            return new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.readAsDataURL(file);
                reader.onload = event => {
                    const img = new Image();
                    img.src = event.target.result;
                    img.onload = () => {
                        const canvas = document.createElement('canvas');
                        let width = img.width;
                        let height = img.height;
                        const maxWidth = 1200;
                        const maxHeight = 1200;

                        if (width > height) {
                            if (width > maxWidth) {
                                height *= maxWidth / width;
                                width = maxWidth;
                            }
                        } else {
                            if (height > maxHeight) {
                                width *= maxHeight / height;
                                height = maxHeight;
                            }
                        }

                        canvas.width = width;
                        canvas.height = height;
                        const ctx = canvas.getContext('2d');
                        ctx.drawImage(img, 0, 0, width, height);

                        canvas.toBlob(blob => {
                            if (blob) {
                                resolve(blob);
                            } else {
                                reject(new Error('Canvas toBlob failed'));
                            }
                        }, 'image/webp', 0.8);
                    };
                    img.onerror = reject;
                };
                reader.onerror = reject;
            });
        }

        function removeImage(index) {
            const input = document.getElementById(`img-input-${index}`);
            const preview = document.getElementById(`preview-${index}`);
            const plus = document.getElementById(`plus-${index}`);
            const removeBtn = document.getElementById(`remove-${index}`);

            input.value = '';
            preview.src = '';
            preview.style.display = 'none';
            plus.style.display = 'block';
            removeBtn.style.display = 'none';

            // IMPORTANT: Completely clear the slot in our tracking array
            imageFiles[index] = null;

            // Reset main image index if we just removed the current main image
            const mainIdxInput = document.getElementById('main_image_index');
            if (mainIdxInput.value == index) {
                mainIdxInput.value = 0;
            }

            updateMainImageOptions();
        }

        function updateMainImageOptions() {
            mainImgOptionsContainer.innerHTML = '';
            let hasImages = false;

            imageFiles.forEach((file, index) => {
                if (file) {
                    hasImages = true;
                    const option = document.createElement('div');
                    option.className = `main-image-option ${document.getElementById('main_image_index').value == index ? 'selected' : ''}`;
                    option.innerHTML = `Image ${index + 1}`;
                    option.onclick = () => selectMainImage(index);
                    mainImgOptionsContainer.appendChild(option);
                }
            });

            if (!hasImages) {
                mainImgOptionsContainer.innerHTML = '<p style="font-size: 13px; color: #9CA3AF;">Upload images first to select main image.</p>';
            }
        }

        form.onsubmit = async (e) => {
            e.preventDefault();
            const productId = document.getElementById('product_id').value;
            const saveBtn = document.getElementById('saveProductBtn');
            saveBtn.disabled = true;
            saveBtn.innerText = productId ? 'Updating...' : 'Saving...';

            const formData = new FormData();
            formData.append('name', form.name.value);
            formData.append('category_id', form.category_id.value);
            formData.append('price', form.price.value);
            formData.append('discount_percentage', form.discount_percentage.value || 0);
            formData.append('is_new', form.is_new.checked ? 1 : 0);
            formData.append('stock_status', form.stock_status.value);
            formData.append('stock_quantity', form.stock_quantity.value);
            formData.append('description', form.description.value);
            formData.append('_method', document.getElementById('form_method').value);
            formData.append('_token', '{{ csrf_token() }}');

            // Collect images
            let combinedImages = [];
            let actualMainIndex = 0;
            let targetIndex = parseInt(document.getElementById('main_image_index').value);

            for (let i = 0; i < 5; i++) {
                if (imageFiles[i]) {
                    if (imageFiles[i].isExisting) {
                        formData.append('existing_images[]', imageFiles[i].url);
                        if (i === targetIndex) {
                            actualMainIndex = combinedImages.length;
                        }
                        combinedImages.push({ type: 'existing', data: imageFiles[i].url });
                    } else {
                        // For new images, use the compressed file from imageFiles array
                        if (imageFiles[i]) {
                            formData.append('images[]', imageFiles[i]);
                            if (i === targetIndex) {
                                actualMainIndex = combinedImages.length;
                            }
                            combinedImages.push({ type: 'new', data: imageFiles[i] });
                        }
                    }
                }
            }

            formData.set('main_image_index', actualMainIndex);

            const url = productId ? `{{ url('admin/products') }}/${productId}` : '{{ route("admin.products.store") }}';

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                const result = await response.json();
                if (result.success) {
                    location.reload();
                } else {
                    alert('Failed to save product. Please check inputs.');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred.');
            } finally {
                saveBtn.disabled = false;
                saveBtn.innerText = productId ? 'Update' : 'Save';
            }
        };

        function selectMainImage(index) {
            document.getElementById('main_image_index').value = index;
            const options = mainImgOptionsContainer.querySelectorAll('.main-image-option');
            options.forEach((opt, i) => {
                // Find the option by its text content since they are dynamic
                if (opt.innerText.includes(`${index + 1}`)) {
                    opt.classList.add('selected');
                } else {
                    opt.classList.remove('selected');
                }
            });
        }

    </script>
@endsection