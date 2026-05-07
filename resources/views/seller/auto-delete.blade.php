@extends('layouts.seller')

@section('title', 'Auto Delete Settings')

@section('styles')
<style>
    .page-header { margin-bottom: 24px; }
    .page-title h1 { font-size: 24px; font-weight: 700; color: #1E293B; margin-bottom: 4px; }
    .page-title p { color: #64748B; font-size: 14px; }

    .content-card { 
        background: #FFFFFF; 
        border: 1px solid #E2E8F0; 
        border-radius: 12px; 
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        overflow: hidden;
        margin-bottom: 24px;
    }

    .card-header {
        padding: 16px 24px;
        border-bottom: 1px solid #E2E8F0;
        background: #F8FAFC;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .card-header h2 { font-size: 16px; font-weight: 600; color: #334155; margin: 0; }

    .settings-table { width: 100%; border-collapse: collapse; }
    .settings-table th { 
        text-align: left; 
        padding: 12px 24px; 
        background: #F8FAFC; 
        font-size: 12px; 
        font-weight: 600; 
        color: #64748B; 
        text-transform: uppercase; 
        letter-spacing: 0.05em;
        border-bottom: 1px solid #E2E8F0;
    }
    .settings-table td { 
        padding: 20px 24px; 
        border-bottom: 1px solid #F1F5F9; 
        vertical-align: middle;
    }

    .status-badge {
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        background: #F1F5F9;
        color: #475569;
        display: inline-block;
    }

    .input-group { display: flex; align-items: center; gap: 8px; }
    .input-val {
        width: 80px; padding: 8px 12px; border: 1px solid #CBD5E1; border-radius: 6px;
        font-size: 14px; font-weight: 500; color: #1E293B;
    }
    .unit-select {
        padding: 8px 12px; border: 1px solid #CBD5E1; border-radius: 6px;
        background: #FFFFFF; font-size: 14px; color: #475569; cursor: pointer;
    }

    .desc-text { font-size: 13px; color: #64748B; margin-top: 4px; line-height: 1.4; }

    .card-footer { padding: 16px 24px; background: #FFFFFF; display: flex; justify-content: flex-end; gap: 12px; }

    .btn-save {
        background: #F25C3B; color: #FFFFFF; border: none; padding: 10px 20px;
        border-radius: 6px; font-weight: 600; font-size: 14px; cursor: pointer;
        transition: background 0.2s;
    }
    .btn-save:hover { background: #E04B2A; }

    .btn-run {
        background: #FFFFFF; color: #475569; border: 1px solid #CBD5E1; padding: 10px 20px;
        border-radius: 6px; font-weight: 600; font-size: 14px; cursor: pointer;
        display: flex; align-items: center; gap: 8px;
    }
    .btn-run:hover { background: #F8FAFC; border-color: #94A3B8; }

    .alert-banner {
        background: #ECFDF5; border: 1px solid #10B981; color: #065F46;
        padding: 12px 20px; border-radius: 8px; margin-bottom: 24px; font-size: 14px;
        display: flex; align-items: center; gap: 10px;
    }

    #cleanup-result { font-size: 13px; margin-top: 10px; font-weight: 500; }
</style>
@endsection

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>Auto-Delete Settings</h1>
        <p>Configure automated data cleanup rules for your workspace.</p>
    </div>
</div>

@if(session('success'))
    <div class="alert-banner">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
        {{ session('success') }}
    </div>
@endif

<form action="{{ route('seller.settings.auto-delete.update') }}" method="POST">
    @csrf
    <div class="content-card">
        <div class="card-header">
            <h2>Order Cleanup Rules</h2>
        </div>
        <table class="settings-table">
            <thead>
                <tr>
                    <th style="width: 200px;">Order Status</th>
                    <th style="width: 300px;">Retention Duration</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                {{-- Delivered --}}
                <tr>
                    <td><span class="status-badge">Delivered</span></td>
                    <td>
                        <div class="input-group">
                            <input type="number" name="auto_delete_delivered_value" class="input-val" value="{{ $settings['auto_delete_delivered_value'] ?? 12 }}">
                            <select name="auto_delete_delivered_unit" class="unit-select">
                                <option value="minutes" {{ ($settings['auto_delete_delivered_unit'] ?? 'months') == 'minutes' ? 'selected' : '' }}>Minutes</option>
                                <option value="hours" {{ ($settings['auto_delete_delivered_unit'] ?? 'months') == 'hours' ? 'selected' : '' }}>Hours</option>
                                <option value="days" {{ ($settings['auto_delete_delivered_unit'] ?? 'months') == 'days' ? 'selected' : '' }}>Days</option>
                                <option value="months" {{ ($settings['auto_delete_delivered_unit'] ?? 'months') == 'months' ? 'selected' : '' }}>Months</option>
                                <option value="years" {{ ($settings['auto_delete_delivered_unit'] ?? 'months') == 'years' ? 'selected' : '' }}>Years</option>
                            </select>
                        </div>
                    </td>
                    <td>
                        <div class="desc-text">Orders marked as delivered and their associated media files (pickup/delivery images) will be permanently deleted after this time.</div>
                    </td>
                </tr>
                {{-- Cancelled --}}
                <tr>
                    <td><span class="status-badge">Cancelled</span></td>
                    <td>
                        <div class="input-group">
                            <input type="number" name="auto_delete_cancelled_value" class="input-val" value="{{ $settings['auto_delete_cancelled_value'] ?? 30 }}">
                            <select name="auto_delete_cancelled_unit" class="unit-select">
                                <option value="minutes" {{ ($settings['auto_delete_cancelled_unit'] ?? 'days') == 'minutes' ? 'selected' : '' }}>Minutes</option>
                                <option value="hours" {{ ($settings['auto_delete_cancelled_unit'] ?? 'days') == 'hours' ? 'selected' : '' }}>Hours</option>
                                <option value="days" {{ ($settings['auto_delete_cancelled_unit'] ?? 'days') == 'days' ? 'selected' : '' }}>Days</option>
                                <option value="months" {{ ($settings['auto_delete_cancelled_unit'] ?? 'days') == 'months' ? 'selected' : '' }}>Months</option>
                                <option value="years" {{ ($settings['auto_delete_cancelled_unit'] ?? 'days') == 'years' ? 'selected' : '' }}>Years</option>
                            </select>
                        </div>
                    </td>
                    <td>
                        <div class="desc-text">Orders that were cancelled by the customer or system.</div>
                    </td>
                </tr>
                {{-- Refunded --}}
                <tr>
                    <td><span class="status-badge">Refunded</span></td>
                    <td>
                        <div class="input-group">
                            <input type="number" name="auto_delete_refunded_value" class="input-val" value="{{ $settings['auto_delete_refunded_value'] ?? 12 }}">
                            <select name="auto_delete_refunded_unit" class="unit-select">
                                <option value="minutes" {{ ($settings['auto_delete_refunded_unit'] ?? 'months') == 'minutes' ? 'selected' : '' }}>Minutes</option>
                                <option value="hours" {{ ($settings['auto_delete_refunded_unit'] ?? 'months') == 'hours' ? 'selected' : '' }}>Hours</option>
                                <option value="days" {{ ($settings['auto_delete_refunded_unit'] ?? 'months') == 'days' ? 'selected' : '' }}>Days</option>
                                <option value="months" {{ ($settings['auto_delete_refunded_unit'] ?? 'months') == 'months' ? 'selected' : '' }}>Months</option>
                                <option value="years" {{ ($settings['auto_delete_refunded_unit'] ?? 'months') == 'years' ? 'selected' : '' }}>Years</option>
                            </select>
                        </div>
                    </td>
                    <td>
                        <div class="desc-text">Orders where a refund was issued.</div>
                    </td>
                </tr>
                {{-- Notifications --}}
                <tr>
                    <td><span class="status-badge">Notifications</span></td>
                    <td>
                        <div class="input-group">
                            <input type="number" name="auto_delete_notifications_days" class="input-val" value="{{ $settings['auto_delete_notifications_days'] ?? 7 }}">
                            <span style="font-size: 14px; font-weight: 600; color: #64748B;">Days</span>
                        </div>
                    </td>
                    <td>
                        <div class="desc-text">In-app notifications that have already been read.</div>
                    </td>
                </tr>
            </tbody>
        </table>
        <div class="card-footer">
            <button type="submit" class="btn-save">Save Settings</button>
        </div>
    </div>
</form>

<div class="content-card" style="border-left: 4px solid #F25C3B;">
    <div class="card-header">
        <h2>Manual Data Cleanup</h2>
    </div>
    <div style="padding: 24px;">
        <p style="color: #64748B; font-size: 14px; margin-bottom: 20px;">
            Trigger the cleanup process immediately based on your active rules. This will permanently remove all eligible data and images from the server.
        </p>
        <button type="button" class="btn-run" onclick="runCleanup(this)">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-9-9c2.52 0 4.93 1 6.74 2.74L21 8"></path><polyline points="21 3 21 8 16 8"></polyline></svg>
            <span>Run Cleanup Now</span>
        </button>
        <div id="cleanup-result" style="display: none;"></div>
    </div>
</div>

<script>
async function runCleanup(btn) {
    if (!confirm('Are you sure? This will permanently delete old orders and their images based on your rules.')) return;

    const span = btn.querySelector('span');
    const originalText = span.textContent;
    const resultDiv = document.getElementById('cleanup-result');

    btn.disabled = true;
    span.textContent = 'Processing...';
    resultDiv.style.display = 'none';

    try {
        const response = await fetch('{{ route('seller.settings.auto-delete.run') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        });

        const data = await response.json();
        
        resultDiv.textContent = data.message;
        resultDiv.style.color = '#059669';
        resultDiv.style.display = 'block';
    } catch (error) {
        resultDiv.textContent = 'An error occurred during cleanup.';
        resultDiv.style.color = '#DC2626';
        resultDiv.style.display = 'block';
    } finally {
        btn.disabled = false;
        span.textContent = originalText;
    }
}
</script>
@endsection
