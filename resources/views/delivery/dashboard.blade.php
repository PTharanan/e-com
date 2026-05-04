@extends('layouts.delivery')

@section('title', 'Partner Dashboard')

@section('styles')
<style>
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 40px; }
    .stat-card { background: var(--partner-white); padding: 25px; border-radius: 20px; box-shadow: var(--shadow); display: flex; align-items: center; gap: 20px; transition: transform 0.3s ease; }
    .stat-card:hover { transform: translateY(-5px); }
    .stat-icon { width: 60px; height: 60px; border-radius: 15px; display: flex; align-items: center; justify-content: center; }
    .icon-income { background: #E8F5E9; color: #4CAF50; }
    .icon-delivered { background: #E3F2FD; color: #2196F3; }
    .icon-active { background: #FFF3E0; color: #FF9800; }
    .icon-returned { background: #FFEBEE; color: #F44336; }
    
    .stat-info h3 { font-size: 28px; margin: 0; color: var(--partner-dark); }
    .stat-info p { font-size: 14px; color: #888; font-weight: 500; margin: 2px 0 0; }

    .welcome-card { background: var(--partner-white); padding: 30px; border-radius: 20px; box-shadow: var(--shadow); display: flex; align-items: center; justify-content: space-between; margin-bottom: 30px; gap: 20px; }
    .welcome-text h1 { font-size: 24px; color: var(--partner-dark); margin: 0 0 5px; }
    .welcome-text p { color: #666; margin: 0; }

    @media (max-width: 768px) {
        .welcome-card { flex-direction: column; align-items: flex-start; padding: 20px; }
        .welcome-text h1 { font-size: 20px; }
        .stats-grid { grid-template-columns: 1fr; }
        .stat-card { padding: 15px; }
    }
</style>
@endsection

@section('content')
    <div class="welcome-card">
        <div class="welcome-text">
            <h1>Welcome back, {{ Auth::user()->name }}! 👋</h1>
            <p>Here's your performance overview for this month.</p>
        </div>
        <div style="background: var(--partner-primary); color: white; padding: 8px 20px; border-radius: 50px; font-weight: 600; font-size: 14px;">
            Partner Status: Active
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon icon-income">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
            </div>
            <div class="stat-info">
                <h3>${{ number_format($stats['total_income'], 2) }}</h3>
                <p>Total Income</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon icon-delivered">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
            </div>
            <div class="stat-info">
                <h3>{{ $stats['delivered_count'] }}</h3>
                <p>Delivered</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon icon-active">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg>
            </div>
            <div class="stat-info">
                <h3>{{ $stats['active_deliveries'] }}</h3>
                <p>On Duty</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon icon-returned">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 4v6h6"></path><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path></svg>
            </div>
            <div class="stat-info">
                <h3>{{ $stats['returned_count'] }}</h3>
                <p>Returns</p>
            </div>
        </div>
    </div>
@endsection
