@extends('layouts.admin')

@section('title', 'Manage Categories')

@section('styles')
<style>
    .page-header { 
        display: flex; 
        justify-content: space-between; 
        align-items: center; 
        margin-bottom: 30px; 
    }
    .page-title h1 { font-size: 24px; font-weight: 700; color: var(--admin-dark); margin: 0; }
    .page-title p { color: #64748B; font-size: 14px; margin-top: 5px; }
    
    .btn-add { 
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
    .btn-add:hover { background: var(--admin-primary-hover); transform: translateY(-2px); box-shadow: 0 4px 12px rgba(242, 92, 59, 0.2); }

    .category-card { background: white; padding: 0; border-radius: 24px; box-shadow: 0 10px 30px rgba(0,0,0,0.03); overflow: hidden; }
    
    .table-container { width: 100%; overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; text-align: left; }
    th { padding: 20px 25px; background: #F8FAFC; color: #64748B; font-weight: 600; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid #F1F5F9; }
    td { padding: 20px 25px; color: #1E293B; font-size: 15px; border-bottom: 1px solid #F1F5F9; }
    tr:last-child td { border-bottom: none; }
    tr:hover td { background: #F8FAFC; }

    .category-info { display: flex; align-items: center; gap: 12px; }
    .category-icon { width: 40px; height: 40px; background: #FFF1EE; color: var(--admin-primary); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-weight: 600; overflow: hidden; }
    .category-icon img { width: 100%; height: 100%; object-fit: cover; }

    .actions { display: flex; gap: 10px; }
    .action-btn { 
        width: 38px; 
        height: 38px; 
        border-radius: 10px; 
        display: flex; 
        align-items: center; 
        justify-content: center; 
        transition: var(--transition);
        border: none;
        cursor: pointer;
        background: #F8FAFC;
    }
    
    .btn-edit { color: #3B82F6; }
    .btn-edit:hover { background: #DBEAFE; color: #1D4ED8; }
    
    .btn-delete { color: #EF4444; }
    .btn-delete:hover { background: #FEE2E2; color: #DC2626; }

    .badge { padding: 6px 12px; border-radius: 8px; font-size: 12px; font-weight: 600; background: #F1F5F9; color: #64748B; }

    /* Modal Styles */
    .modal-overlay {
        position: fixed;
        top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0, 0, 0, 0.6);
        backdrop-filter: blur(8px);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 2000;
        padding: 20px;
    }
    .modal-overlay.active { display: flex; }
    .modal-content {
        background: white;
        width: 100%;
        max-width: 450px;
        border-radius: 24px;
        padding: 35px;
        position: relative;
        box-shadow: 0 20px 50px rgba(0,0,0,0.15);
        animation: modalSlideUp 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }
    @keyframes modalSlideUp {
        from { transform: translateY(30px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
    .modal-header { margin-bottom: 25px; text-align: center; }
    .modal-header h2 { font-size: 22px; font-weight: 700; color: #1a1a1a; margin-bottom: 5px; }
    .modal-header p { color: #666; font-size: 14px; }

    .step { display: none; }
    .step.active { display: block; }

    .form-group { margin-bottom: 20px; }
    .form-label { display: block; margin-bottom: 8px; font-weight: 600; color: #374151; font-size: 14px; }
    .form-input { 
        width: 100%; padding: 14px 18px; border: 2px solid #F3F4F6; border-radius: 12px; 
        font-family: inherit; font-size: 15px; transition: 0.2s;
    }
    .form-input:focus { border-color: var(--admin-primary); outline: none; background: #FFF9F8; }

    .image-upload-wrapper {
        border: 2px dashed #E5E7EB;
        border-radius: 16px;
        padding: 30px 20px;
        text-align: center;
        cursor: pointer;
        transition: 0.2s;
        position: relative;
    }
    .image-upload-wrapper:hover { border-color: var(--admin-primary); background: #FFF9F8; }
    .image-upload-wrapper input { position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; }
    .upload-placeholder svg { width: 40px; height: 40px; color: #9CA3AF; margin-bottom: 12px; }
    .upload-placeholder p { font-size: 13px; color: #6B7280; }
    .image-preview { 
        width: 100px; height: 100px; border-radius: 12px; object-fit: cover; 
        margin: 0 auto 15px; display: none; 
    }

    .modal-footer { display: flex; gap: 12px; margin-top: 30px; }
    .modal-btn { 
        flex: 1; padding: 14px; border-radius: 12px; font-weight: 600; 
        cursor: pointer; border: none; transition: 0.2s; font-family: inherit;
    }
    .btn-cancel { background: #F3F4F6; color: #374151; }
    .btn-next, .btn-save { background: var(--admin-primary); color: white; }
    .btn-next:hover, .btn-save:hover { background: var(--admin-primary-hover); transform: translateY(-2px); }
</style>
@endsection

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>Categories</h1>
        <p>Organize your products into meaningful groups.</p>
    </div>
    <button class="btn-add" id="openModalBtn">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
        Add Category
    </button>
</div>

<div class="category-card">
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Category Name</th>
                    <th>Product Count</th>
                    <th>Created Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="categoryTableBody">
                @forelse($categories as $category)
                <tr id="category-row-{{ $category->id }}">
                    <td>
                        <div class="category-info">
                            <div class="category-icon">
                                @if($category->dp_img_url)
                                    <img src="{{ asset($category->dp_img_url) }}" alt="{{ $category->name }}">
                                @else
                                    {{ substr($category->name, 0, 1) }}
                                @endif
                            </div>
                            <div class="category-name">
                                <strong>{{ $category->name }}</strong>
                            </div>
                        </div>
                    </td>
                    <td><span class="badge">{{ $category->products_count }} {{ $category->products_count == 1 ? 'Product' : 'Products' }}</span></td>
                    <td>{{ $category->created_at->format('Y-m-d') }}</td>
                    <td>
                        <div class="actions">
                            <button class="action-btn btn-edit" title="Edit">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                            </button>
                            <button class="action-btn btn-delete" title="Delete">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" style="text-align: center; padding: 40px; color: #64748B;">No categories found. Click "Add Category" to create one.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Add Category Modal -->
<div class="modal-overlay" id="categoryModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Add New Category</h2>
            <p id="stepDescription">Step 1: Enter Category Details</p>
        </div>

        <form id="addCategoryForm" enctype="multipart/form-data">
            @csrf
            <!-- Step 1 -->
            <div class="step active" id="step1">
                <div class="form-group">
                    <label class="form-label">Category Name</label>
                    <input type="text" name="name" id="cat_name" class="form-input" placeholder="e.g. Electronics" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="modal-btn btn-cancel" onclick="closeModal()">Cancel</button>
                    <button type="button" class="modal-btn btn-next" onclick="nextStep()">Next Step</button>
                </div>
            </div>

            <!-- Step 2 -->
            <div class="step" id="step2">
                <div class="form-group">
                    <label class="form-label">Category Image (DP)</label>
                    <div class="image-upload-wrapper">
                        <img id="previewImage" class="image-preview">
                        <div class="upload-placeholder" id="uploadPlaceholder">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                            <p>Click to upload image or drag & drop</p>
                            <span style="font-size: 11px; color: #9CA3AF;">JPG, PNG or GIF (Max 2MB)</span>
                        </div>
                        <input type="file" name="image" id="cat_image" accept="image/*" required onchange="previewFile()">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="modal-btn btn-cancel" onclick="prevStep()">Back</button>
                    <button type="submit" class="modal-btn btn-save" id="saveBtn">Save Category</button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
    const modal = document.getElementById('categoryModal');
    const openModalBtn = document.getElementById('openModalBtn');
    const step1 = document.getElementById('step1');
    const step2 = document.getElementById('step2');
    const stepDesc = document.getElementById('stepDescription');
    const form = document.getElementById('addCategoryForm');

    openModalBtn.onclick = () => {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    };

    const closeModal = () => {
        modal.classList.remove('active');
        document.body.style.overflow = 'auto';
        resetForm();
    };

    const resetForm = () => {
        form.reset();
        step1.classList.add('active');
        step2.classList.remove('active');
        stepDesc.innerText = 'Step 1: Enter Category Details';
        document.getElementById('previewImage').style.display = 'none';
        document.getElementById('uploadPlaceholder').style.display = 'block';
    };

    const nextStep = () => {
        const name = document.getElementById('cat_name').value;
        if (!name) {
            alert('Please enter a category name.');
            return;
        }
        step1.classList.remove('active');
        step2.classList.add('active');
        stepDesc.innerText = 'Step 2: Upload Category Image';
    };

    const prevStep = () => {
        step2.classList.remove('active');
        step1.classList.add('active');
        stepDesc.innerText = 'Step 1: Enter Category Details';
    };

    const previewFile = () => {
        const preview = document.getElementById('previewImage');
        const placeholder = document.getElementById('uploadPlaceholder');
        const file = document.getElementById('cat_image').files[0];
        const reader = new FileReader();

        reader.onloadend = () => {
            preview.src = reader.result;
            preview.style.display = 'block';
            placeholder.style.display = 'none';
        }

        if (file) {
            reader.readAsDataURL(file);
        } else {
            preview.src = "";
        }
    };

    form.onsubmit = async (e) => {
        e.preventDefault();
        const saveBtn = document.getElementById('saveBtn');
        const originalText = saveBtn.innerText;
        
        saveBtn.disabled = true;
        saveBtn.innerText = 'Saving...';

        const formData = new FormData(form);

        try {
            const response = await fetch('{{ route("admin.categories.store") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });

            const result = await response.json();

            if (result.success) {
                location.reload(); // Simple reload to show new category
            } else {
                alert(result.message || 'Something went wrong');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred while saving.');
        } finally {
            saveBtn.disabled = false;
            saveBtn.innerText = originalText;
        }
    };

    // Close modal when clicking outside
    modal.onclick = (e) => {
        if (e.target === modal) closeModal();
    };
</script>
@endsection
