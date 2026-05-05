@extends('layouts.admin')

@section('title', 'Admin Settings')

@section('styles')
<style>
    .page-header { margin-bottom: 30px; }
    .page-title h1 { font-size: 24px; font-weight: 700; color: var(--admin-dark); }
    .page-title p { color: #64748B; font-size: 14px; }
    
    .settings-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 30px; }
    .settings-card { background: white; padding: 25px; border-radius: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); }
    .settings-card h3 { margin-bottom: 20px; font-size: 18px; border-bottom: 1px solid #F1F5F9; padding-bottom: 10px; }
    
    .form-group { margin-bottom: 20px; }
    .form-label { display: block; margin-bottom: 8px; font-size: 14px; font-weight: 500; color: #64748B; }
    .form-input { width: 100%; padding: 12px; border: 1.5px solid #E2E8F0; border-radius: 10px; font-family: inherit; }
    
    .btn-save { background: var(--admin-primary); color: white; border: none; padding: 12px 25px; border-radius: 10px; cursor: pointer; font-weight: 600; width: 100%; }
</style>
@endsection

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>Store Settings</h1>
        <p>Update your store information and preferences.</p>
    </div>
</div>

<div class="settings-grid">
    <div class="settings-card">
        <h3>General Information</h3>
        <form>
            <div class="form-group">
                <label class="form-label">Store Name</label>
                <input type="text" class="form-input" value="E-Shop Admin">
            </div>
            <div class="form-group">
                <label class="form-label">Contact Email</label>
                <input type="email" class="form-input" value="admin@eshop.com">
            </div>
            <button type="button" class="btn-save">Save Changes</button>
        </form>
    </div>

    <div class="settings-card">
        <h3>Security</h3>
        <form>
            <div class="form-group">
                <label class="form-label">Current Password</label>
                <input type="password" class="form-input" placeholder="••••••••">
            </div>
            <div class="form-group">
                <label class="form-label">New Password</label>
                <input type="password" class="form-input" placeholder="Enter new password">
            </div>
            <button type="button" class="btn-save">Update Security</button>
        </form>
    </div>
    <div class="settings-card">
        <h3>Marketing & Content</h3>
        <p style="color: #64748B; font-size: 14px; margin-bottom: 20px;">Manage promotional content and home page banners to attract customers.</p>
        <a href="{{ route('admin.banners') }}" style="display: inline-block; color: var(--admin-primary); text-decoration: none; font-weight: 600; font-size: 15px;">Manage Home Banners &rarr;</a>
    </div>
</div>
@endsection
