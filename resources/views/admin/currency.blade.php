@extends('layouts.admin')

@section('title', 'Currency Settings')

@section('styles')
<style>
    .currency-wrapper {
        max-width: 650px;
        margin: 0 auto;
        background: white;
        border-radius: 20px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.02);
        padding: 40px;
    }

    .page-title {
        margin-bottom: 30px;
        text-align: center;
    }

    .page-title h1 { 
        font-size: 24px; 
        font-weight: 700; 
        color: #1a1a1a; 
        margin-bottom: 8px;
    }
    
    .page-title p { 
        color: #666; 
        font-size: 14px; 
        line-height: 1.5;
    }

    .current-currency-box {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 20px;
        margin-bottom: 30px;
        padding: 20px;
        background: #F8FAFC;
        border-radius: 16px;
        border: 1px dashed #E2E8F0;
    }

    .currency-icon-large {
        width: 50px;
        height: 50px;
        background: white;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--admin-primary);
        font-size: 20px;
        font-weight: 800;
        box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    }

    .current-info {
        display: flex;
        flex-direction: column;
    }

    .current-info .label {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #64748B;
        font-weight: 600;
        margin-bottom: 4px;
    }

    .current-info .value {
        font-size: 22px;
        font-weight: 700;
        color: #1E293B;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .active-tag {
        font-size: 11px;
        background: #10B981;
        color: white;
        padding: 4px 12px;
        border-radius: 50px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .form-group { 
        position: relative;
        margin-bottom: 25px; 
    }

    .form-group label {
        display: block;
        font-size: 14px;
        font-weight: 600;
        color: #334155;
        margin-bottom: 10px;
    }

    .input-wrapper {
        position: relative;
    }

    .input-wrapper svg {
        position: absolute;
        left: 16px;
        top: 50%;
        transform: translateY(-50%);
        color: #94A3B8;
        transition: color 0.3s ease;
    }

    .form-group input {
        width: 100%;
        padding: 14px 16px 14px 45px;
        border: 1px solid #E2E8F0;
        background: #F8FAFC;
        border-radius: 12px;
        font-size: 15px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #1E293B;
        transition: all 0.2s ease;
        outline: none;
        box-sizing: border-box;
    }

    .form-group input:focus { 
        border-color: var(--admin-primary);
        background: white;
        box-shadow: 0 0 0 4px rgba(242, 92, 59, 0.1);
    }

    .form-group input:focus + svg {
        color: var(--admin-primary);
    }

    .form-group .hint {
        font-size: 13px;
        color: #64748B;
        margin-top: 10px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .btn-apply {
        width: 100%;
        padding: 14px 20px;
        background: var(--admin-primary);
        color: white;
        border: none;
        border-radius: 12px;
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }

    .btn-apply:hover {
        background: #e04b2b;
        transform: translateY(-1px);
        box-shadow: 0 6px 15px rgba(242, 92, 59, 0.2);
    }

    .btn-apply:active {
        transform: translateY(0);
    }

    .btn-apply:disabled {
        opacity: 0.7;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }

    .info-card {
        margin-top: 30px;
        background: #F8FAFC;
        border-radius: 12px;
        padding: 20px;
        display: flex;
        gap: 15px;
    }

    .info-card-icon {
        width: 24px;
        height: 24px;
        color: #F59E0B;
        flex-shrink: 0;
    }

    .info-card-content {
        flex: 1;
    }

    .info-card-title {
        font-size: 14px;
        font-weight: 700;
        color: #1E293B;
        margin-bottom: 6px;
    }

    .info-card-body p {
        font-size: 13px;
        color: #64748B;
        line-height: 1.6;
        margin-bottom: 8px;
    }
    
    .info-card-body p:last-child {
        margin-bottom: 0;
    }

    .alert {
        padding: 14px 20px;
        border-radius: 12px;
        margin-bottom: 25px;
        font-size: 14px;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 12px;
        animation: slideDown 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .alert-success { background: #DCFCE7; color: #166534; }
    .alert-error { background: #FEE2E2; color: #991B1B; }
    .alert-info { background: #E0F2FE; color: #075985; }

    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    
    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endsection

@section('content')
<div class="currency-wrapper">
    <div class="page-title">
        <h1>Global Currency</h1>
        <p>Manage the primary currency of your store. Updating this will automatically fetch live exchange rates and convert all product prices.</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-error">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
            {{ session('error') }}
        </div>
    @endif
    @if(session('info'))
        <div class="alert alert-info">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
            {{ session('info') }}
        </div>
    @endif

    <div class="current-currency-box">
        <div class="currency-icon-large">
            {{ $currentSymbol }}
        </div>
        <div class="current-info">
            <span class="label">Store Base Currency</span>
            <span class="value">
                {{ $currentCurrency }}
                <span class="active-tag">Active</span>
            </span>
        </div>
    </div>

    <form action="{{ route('admin.currency.update') }}" method="POST" id="currencyForm">
        @csrf
        
        <div class="form-group">
            <label for="currency">Set New Currency Code</label>
            <div class="input-wrapper">
                <input type="text" id="currency" name="currency" placeholder="e.g. LKR" maxlength="3" required autocomplete="off">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"></path><path d="M12 18V6"></path></svg>
            </div>
            <div class="hint">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                Use standard 3-letter ISO codes like USD, EUR, GBP, LKR, INR
            </div>
        </div>

        <button type="submit" class="btn-apply" id="submitBtn">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 2v6h-6"></path><path d="M3 12a9 9 0 0 1 15-6.7L21 8"></path><path d="M3 22v-6h6"></path><path d="M21 12a9 9 0 0 1-15 6.7L3 16"></path></svg>
            Sync Rates & Convert Prices
        </button>
    </form>

    <div class="info-card">
        <div class="info-card-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
        </div>
        <div class="info-card-content">
            <div class="info-card-title">Important Notice</div>
            <div class="info-card-body">
                <p>Changing the currency will trigger an automated update across your entire database. The system connects to a live financial API to retrieve the current exchange rate.</p>
                <p><strong>Every product price, order history, and balance</strong> will be permanently converted and updated to reflect the new currency value.</p>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('currencyForm').addEventListener('submit', function(e) {
        const input = document.getElementById('currency').value.trim().toUpperCase();
        const current = '{{ $currentCurrency }}';
        const btn = document.getElementById('submitBtn');

        if (input.length !== 3) {
            e.preventDefault();
            alert('Please enter a valid 3-letter ISO currency code.');
            return;
        }

        if (input === current) {
            e.preventDefault();
            alert('Your store is already using ' + current + ' as the base currency.');
            return;
        }

        if (!confirm('Are you sure you want to change the currency to ' + input + '?\n\nThis will permanently convert all product prices based on real-time exchange rates. Please do not close the window while the conversion is processing.')) {
            e.preventDefault();
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="animation: spin 1s linear infinite;"><path d="M21 12a9 9 0 1 1-6.219-8.56"></path></svg> Processing Conversion...';
    });
</script>
@endsection
