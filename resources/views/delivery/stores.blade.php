@extends('layouts.delivery')

@section('title', 'Apply for Stores')

@section('styles')
<style>
    .card { 
        background: var(--partner-white); 
        padding: 25px; 
        border-radius: 12px; 
        box-shadow: var(--shadow); 
        margin-bottom: 20px; 
        display: flex; 
        justify-content: space-between; 
        align-items: center; 
        transition: var(--transition); 
        border: 1px solid #eee; 
    }
    .card:hover { box-shadow: 0 8px 25px rgba(242, 92, 59, 0.1); transform: translateY(-2px); border-color: #FDEEE4; }
    
    .info h3 { margin: 0 0 8px; font-size: 20px; color: var(--partner-dark); font-weight: 700; }
    .info p { margin: 0; font-size: 14px; color: #555; margin-bottom: 6px; display: flex; align-items: center; gap: 8px; }
    
    .btn-apply { 
        background: linear-gradient(135deg, var(--partner-primary), #E8553A); 
        color: var(--partner-white); 
        border: none; 
        padding: 12px 24px; 
        border-radius: 50px; 
        cursor: pointer; 
        font-weight: 600; 
        font-family: 'Poppins', sans-serif; 
        box-shadow: 0 4px 12px rgba(242, 92, 59, 0.2); 
        transition: var(--transition); 
    }
    .btn-apply:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(242, 92, 59, 0.3); }
    
    .status-badge { padding: 8px 16px; border-radius: 50px; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; }
    .status-pending { background: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
    .status-accepted { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    .status-rejected { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    
    .success-msg { background: #d4edda; color: #155724; padding: 15px 20px; border-radius: 8px; margin-bottom: 25px; border-left: 4px solid #28a745; font-weight: 500; font-size: 14px; }
    .error-msg { background: #f8d7da; color: #721c24; padding: 15px 20px; border-radius: 8px; margin-bottom: 25px; border-left: 4px solid #dc3545; font-weight: 500; font-size: 14px; }

    /* Modal Styling */
    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(4px);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 1000;
        padding: 20px;
    }
    .modal-overlay.active { display: flex; }
    .modal-content {
        background: white;
        padding: 40px;
        border-radius: 28px;
        width: 100%;
        max-width: 480px;
        box-shadow: 0 25px 60px rgba(0,0,0,0.15);
        position: relative;
        transform: translateY(20px);
        transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    .modal-overlay.active .modal-content { transform: translateY(0); }
    
    .modal-content h3 { margin: 0 0 10px; color: var(--partner-dark); font-size: 22px; font-weight: 800; }
    .modal-content p { color: #777; font-size: 14px; margin-bottom: 30px; line-height: 1.5; }
    
    .form-group { margin-bottom: 22px; text-align: left; }
    .form-group label { display: block; margin-bottom: 10px; font-weight: 700; color: #1E293B; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; }
    .form-group input, .form-group textarea {
        width: 100%;
        padding: 14px 18px;
        border: 2px solid #F1F5F9;
        background: #F8FAFC;
        border-radius: 14px;
        font-family: inherit;
        font-size: 14px;
        color: #1E293B;
        transition: all 0.2s ease;
    }
    .form-group input:focus, .form-group textarea:focus { 
        border-color: var(--partner-primary); 
        background: white;
        outline: none; 
        box-shadow: 0 0 0 4px rgba(242, 92, 59, 0.1);
    }
    .form-group input::placeholder, .form-group textarea::placeholder { color: #94A3B8; }
    
    .modal-btns { display: flex; gap: 15px; margin-top: 30px; }
    .btn-cancel { 
        background: #F1F5F9; 
        color: #64748B; 
        border: none; 
        padding: 14px 25px; 
        border-radius: 14px; 
        cursor: pointer; 
        font-weight: 700; 
        font-size: 14px;
        transition: 0.2s;
    }
    .btn-cancel:hover { background: #E2E8F0; color: #475569; }
    .btn-submit { 
        background: var(--partner-primary); 
        color: white; 
        border: none; 
        padding: 14px 25px; 
        border-radius: 14px; 
        cursor: pointer; 
        font-weight: 700; 
        font-size: 14px;
        flex: 1;
        box-shadow: 0 4px 12px rgba(242, 92, 59, 0.2);
        transition: 0.2s;
    }
    .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(242, 92, 59, 0.3); }

    @media (max-width: 768px) {
        .card { flex-direction: column; align-items: flex-start; padding: 20px; gap: 20px; }
        .action { width: 100%; }
        .btn-apply { width: 100%; }
        .info h3 { font-size: 18px; }
        .header-flex { flex-direction: column; align-items: flex-start !important; gap: 10px; }
    }
</style>
@endsection

@section('content')
    @if(session('success'))
        <div class="success-msg">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="error-msg">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="error-msg">
            <ul style="margin: 0; padding-left: 15px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="header-flex" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
        <div>
            <h2 style="color: var(--partner-dark); margin: 0; font-size: 24px;">Available Stores</h2>
            <p style="color: #666; margin: 5px 0 0; font-size: 14px;">Apply to stores or hubs to start receiving delivery requests.</p>
        </div>
    </div>

    @foreach($storeOwners as $store)
        <div class="card">
            <div class="info">
                <h3>{{ $store->name }} <span style="font-size: 11px; font-weight: 600; background: #eee; padding: 2px 8px; border-radius: 50px; color: #666; text-transform: uppercase; vertical-align: middle; margin-left: 5px;">{{ $store->role }}</span></h3>
                <p>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                    <strong>Phone:</strong> {{ $store->phno ?? 'N/A' }}
                </p>
                <p>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                    <strong>Address:</strong> {{ $store->address ?? 'N/A' }}
                </p>
            </div>
            <div class="action">
                @if(isset($myApplications[$store->id]))
                    <span class="status-badge status-{{ strtolower($myApplications[$store->id]) }}">
                        {{ $myApplications[$store->id] === 'pending' ? 'APPLIED (PENDING)' : strtoupper($myApplications[$store->id]) }}
                    </span>
                @else
                    <button type="button" class="btn-apply" onclick="openApplyModal({{ $store->id }}, '{{ $store->name }}')">APPLY NOW</button>
                @endif
            </div>
        </div>
    @endforeach

    <!-- Apply Modal -->
    <div class="modal-overlay" id="applyModal">
        <div class="modal-content">
            <div style="width: 50px; height: 50px; background: #FDEEE4; color: var(--partner-primary); border-radius: 14px; display: flex; align-items: center; justify-content: center; margin-bottom: 20px;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><polyline points="17 11 19 13 23 9"></polyline></svg>
            </div>
            <h3 id="modalStoreName">Apply to Store</h3>
            <p>We need your latest contact information to ensure smooth coordination with the store admin.</p>
            
            <form id="applyForm" method="POST" action="">
                @csrf
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="text" name="phno" placeholder="e.g. +1234567890" required value="{{ Auth::user()->info->phno ?? '' }}">
                </div>
                
                <div class="form-group">
                    <label>Full Address</label>
                    <textarea name="address" rows="3" placeholder="Enter your full home or work address" required>{{ Auth::user()->info->address ?? '' }}</textarea>
                </div>
                
                <div class="modal-btns">
                    <button type="button" class="btn-cancel" onclick="closeApplyModal()">Cancel</button>
                    <button type="submit" class="btn-submit">Submit Application</button>
                </div>
            </form>
        </div>
    </div>

    @if($storeOwners->isEmpty())
        <div style="text-align: center; padding: 60px 20px; background: var(--partner-white); border-radius: 12px; border: 1px dashed #ccc;">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#ccc" stroke-width="2" style="margin-bottom: 15px;"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
            <h3 style="color: #666; margin: 0;">No stores available yet</h3>
            <p style="color: #999; margin-top: 5px; font-size: 14px;">Check back later when active stores (with registered sellers) join the platform.</p>
        </div>
    @endif

    <script>
        function openApplyModal(storeId, storeName) {
            const modal = document.getElementById('applyModal');
            const form = document.getElementById('applyForm');
            const nameSpan = document.getElementById('modalStoreName');
            
            form.action = `{{ url('delivery/apply') }}/${storeId}`;
            nameSpan.textContent = `Apply to ${storeName}`;
            modal.classList.add('active');
        }

        function closeApplyModal() {
            document.getElementById('applyModal').classList.remove('active');
        }

        // Close on overlay click
        document.getElementById('applyModal').onclick = function(e) {
            if (e.target === this) closeApplyModal();
        };
    </script>
@endsection
