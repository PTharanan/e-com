@extends('layouts.admin')

@section('title', 'Manage Banners')

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

    .banner-card { background: white; padding: 0; border-radius: 24px; box-shadow: 0 10px 30px rgba(0,0,0,0.03); overflow: hidden; }
    
    .table-container { width: 100%; overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; text-align: left; }
    th { padding: 20px 25px; background: #F8FAFC; color: #64748B; font-weight: 600; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid #F1F5F9; }
    td { padding: 20px 25px; color: #1E293B; font-size: 15px; border-bottom: 1px solid #F1F5F9; }
    tr:last-child td { border-bottom: none; }
    tr:hover td { background: #F8FAFC; }

    .banner-preview-img { width: 120px; height: 60px; object-fit: cover; border-radius: 8px; }

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

    .badge { padding: 6px 12px; border-radius: 8px; font-size: 12px; font-weight: 600; }
    .badge-active { background: #DCFCE7; color: #166534; }
    .badge-inactive { background: #FEE2E2; color: #991B1B; }

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
        max-width: 600px;
        border-radius: 24px;
        padding: 35px;
        position: relative;
        box-shadow: 0 20px 50px rgba(0,0,0,0.15);
        animation: modalSlideUp 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        max-height: 90vh;
        overflow-y: auto;
    }
    @keyframes modalSlideUp {
        from { transform: translateY(30px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
    .modal-header { margin-bottom: 25px; text-align: center; }
    .modal-header h2 { font-size: 22px; font-weight: 700; color: #1a1a1a; margin-bottom: 5px; }

    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .form-group { margin-bottom: 20px; }
    .form-group.full-width { grid-column: 1 / -1; }
    .form-label { display: block; margin-bottom: 8px; font-weight: 600; color: #374151; font-size: 14px; }
    .form-input { 
        width: 100%; padding: 12px 16px; border: 2px solid #F3F4F6; border-radius: 12px; 
        font-family: inherit; font-size: 15px; transition: 0.2s;
    }
    .form-input:focus { border-color: var(--admin-primary); outline: none; background: #FFF9F8; }

    .image-upload-wrapper {
        border: 2px dashed #E5E7EB;
        border-radius: 16px;
        padding: 20px;
        text-align: center;
        cursor: pointer;
        transition: 0.2s;
        position: relative;
        margin-bottom: 10px;
    }
    .image-upload-wrapper:hover { border-color: var(--admin-primary); background: #FFF9F8; }
    .image-upload-wrapper input { position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; }
    .upload-placeholder svg { width: 32px; height: 32px; color: #9CA3AF; margin-bottom: 8px; }
    .upload-placeholder p { font-size: 12px; color: #6B7280; }
    .image-preview { 
        width: 100%; height: 150px; border-radius: 12px; object-fit: cover; 
        margin: 0 auto 10px; display: none; 
    }

    .modal-footer { display: flex; gap: 12px; margin-top: 20px; }
    .modal-btn { 
        flex: 1; padding: 12px; border-radius: 12px; font-weight: 600; 
        cursor: pointer; border: none; transition: 0.2s; font-family: inherit;
    }
    .btn-cancel { background: #F3F4F6; color: #374151; }
    .btn-save { background: var(--admin-primary); color: white; }
</style>
@endsection

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>Home Banners</h1>
        <p>Manage the promotional banners shown on your home page.</p>
    </div>
    <button class="btn-add" id="openModalBtn">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
        Add Banner
    </button>
</div>

<div class="banner-card">
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Preview</th>
                    <th>Title</th>
                    <th>Badge</th>
                    <th>Order</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="bannerTableBody">
                @forelse($banners as $banner)
                <tr id="banner-row-{{ $banner->id }}">
                    <td>
                        <img src="{{ asset($banner->image_url) }}" alt="{{ $banner->title }}" class="banner-preview-img">
                    </td>
                    <td>
                        <strong>{{ $banner->title }}</strong>
                    </td>
                    <td><span class="badge">{{ $banner->badge_text ?? 'None' }}</span></td>
                    <td>{{ $banner->order }}</td>
                    <td>
                        @if($banner->is_active)
                            <span class="badge badge-active">Active</span>
                        @else
                            <span class="badge badge-inactive">Inactive</span>
                        @endif
                    </td>
                    <td>
                        <div class="actions">
                            <button class="action-btn btn-edit" title="Edit" onclick="editBanner({{ json_encode($banner) }})">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                            </button>
                            <button class="action-btn btn-delete" title="Delete" onclick="deleteBanner({{ $banner->id }})">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 40px; color: #64748B;">No banners found. Click "Add Banner" to create one.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Banner Modal -->
<div class="modal-overlay" id="bannerModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">Add New Banner</h2>
        </div>

        <form id="bannerForm" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="_method" id="formMethod" value="POST">
            <input type="hidden" name="id" id="bannerId">
            
            <div class="form-group full-width">
                <label class="form-label">Banner Image</label>
                <div class="image-upload-wrapper">
                    <img id="previewImage" class="image-preview">
                    <div class="upload-placeholder" id="uploadPlaceholder">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                        <p>Click to upload image</p>
                        <span style="font-size: 11px; color: #9CA3AF;">Recommended: 1200x500px</span>
                    </div>
                    <input type="file" name="image" id="banner_image" accept="image/*" onchange="previewFile()">
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Banner Title</label>
                    <input type="text" name="title" id="banner_title" class="form-input" placeholder="e.g. Summer Sale" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Badge Text</label>
                    <input type="text" name="badge_text" id="banner_badge" class="form-input" placeholder="e.g. Special Offer">
                </div>
            </div>

            <div class="form-group full-width">
                <label class="form-label">Subtitle / Description</label>
                <textarea name="subtitle" id="banner_subtitle" class="form-input" rows="3" placeholder="Enter a short description..."></textarea>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Button Text</label>
                    <input type="text" name="button_text" id="banner_btn_text" class="form-input" placeholder="Shop Now">
                </div>
                <div class="form-group">
                    <label class="form-label">Button Link</label>
                    <input type="text" name="button_link" id="banner_btn_link" class="form-input" placeholder="/products or #">
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Display Order</label>
                    <input type="number" name="order" id="banner_order" class="form-input" value="0">
                </div>
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="is_active" id="banner_active" class="form-input">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="modal-btn btn-cancel" onclick="closeModal()">Cancel</button>
                <button type="submit" class="modal-btn btn-save" id="saveBtn">Save Banner</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const modal = document.getElementById('bannerModal');
    const openModalBtn = document.getElementById('openModalBtn');
    const form = document.getElementById('bannerForm');
    const modalTitle = document.getElementById('modalTitle');
    const preview = document.getElementById('previewImage');
    const placeholder = document.getElementById('uploadPlaceholder');

    openModalBtn.onclick = () => {
        modalTitle.innerText = 'Add New Banner';
        form.reset();
        document.getElementById('formMethod').value = 'POST';
        document.getElementById('bannerId').value = '';
        preview.style.display = 'none';
        placeholder.style.display = 'block';
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    };

    const closeModal = () => {
        modal.classList.remove('active');
        document.body.style.overflow = 'auto';
    };

    const previewFile = () => {
        const file = document.getElementById('banner_image').files[0];
        const reader = new FileReader();

        reader.onloadend = () => {
            preview.src = reader.result;
            preview.style.display = 'block';
            placeholder.style.display = 'none';
        }

        if (file) {
            reader.readAsDataURL(file);
        }
    };

    const editBanner = (banner) => {
        modalTitle.innerText = 'Edit Banner';
        document.getElementById('formMethod').value = 'PUT';
        document.getElementById('bannerId').value = banner.id;
        document.getElementById('banner_title').value = banner.title;
        document.getElementById('banner_badge').value = banner.badge_text || '';
        document.getElementById('banner_subtitle').value = banner.subtitle || '';
        document.getElementById('banner_btn_text').value = banner.button_text || 'Shop Now';
        document.getElementById('banner_btn_link').value = banner.button_link || '#';
        document.getElementById('banner_order').value = banner.order;
        document.getElementById('banner_active').value = banner.is_active;

        if (banner.image_url) {
            preview.src = `/${banner.image_url}`;
            preview.style.display = 'block';
            placeholder.style.display = 'none';
        }

        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    };

    form.onsubmit = async (e) => {
        e.preventDefault();
        const saveBtn = document.getElementById('saveBtn');
        const originalText = saveBtn.innerText;
        const bannerId = document.getElementById('bannerId').value;
        const method = document.getElementById('formMethod').value;
        
        saveBtn.disabled = true;
        saveBtn.innerText = 'Saving...';

        const formData = new FormData(form);
        const url = method === 'POST' ? '{{ route("admin.banners.store") }}' : `/admin/settings/banners/${bannerId}`;

        if (method === 'PUT') {
            formData.append('_method', 'PUT');
        }

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

    const deleteBanner = async (id) => {
        if (!confirm('Are you sure you want to delete this banner?')) return;

        try {
            const response = await fetch(`/admin/settings/banners/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });

            const result = await response.json();
            if (result.success) {
                document.getElementById(`banner-row-${id}`).remove();
            } else {
                alert(result.message || 'Delete failed');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred.');
        }
    };

    modal.onclick = (e) => {
        if (e.target === modal) closeModal();
    };
</script>
@endsection
