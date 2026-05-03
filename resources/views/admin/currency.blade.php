@extends('layouts.admin')

@section('title', 'Currency Settings')

@section('styles')
<style>
    .page-header { margin-bottom: 30px; }
    .page-title h1 { font-size: 24px; font-weight: 700; color: var(--admin-dark); }
    .page-title p { color: #64748B; font-size: 14px; }
    
    .currency-card { background: white; padding: 25px; border-radius: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); }
</style>
@endsection

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>Currency Settings</h1>
        <p>Manage your store's currency and exchange rates.</p>
    </div>
</div>

<div class="currency-card">
    <h3>Currency Configuration</h3>
    <div class="currency-settings">
        <p>Currency management interface will appear here.</p>
    </div>
</div>
@endsection
