@extends('layouts.seller')

@section('title', 'Seller Dashboard')

@section('styles')
<style>
    .welcome-card {
        background: white;
        padding: 30px;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 30px;
    }

    .welcome-text h1 {
        font-size: 24px;
        color: #1a1a1a;
        margin-bottom: 5px;
    }

    .welcome-text p {
        color: #666;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 20px;
        margin-bottom: 40px;
    }

    .stat-card {
        background: white;
        padding: 25px;
        border-radius: 20px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.02);
        display: flex;
        align-items: center;
        gap: 20px;
        transition: transform 0.3s ease;
        overflow: hidden;
    }

    .stat-card:hover {
        transform: translateY(-5px);
    }

    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .icon-orders { background: #E3F2FD; color: #2196F3; }
    .icon-users { background: #E8F5E9; color: #4CAF50; }
    .icon-revenue { background: #FFF3E0; color: #FF9800; }
    .icon-products { background: #F3E5F5; color: #9C27B0; }
    .icon-refunds { background: #F1F5F9; color: #475569; }

    .stat-info {
        min-width: 0;
        flex: 1;
    }

    .stat-info h3 {
        font-size: clamp(18px, 2.5vw, 28px);
        margin-bottom: 2px;
        word-wrap: break-word;
        overflow-wrap: break-word;
        line-height: 1.2;
    }

    .stat-info p {
        font-size: 14px;
        color: #888;
        font-weight: 500;
    }
</style>
@endsection

@section('content')
<div class="welcome-card">
    <div class="welcome-text">
        <h1>Welcome back, {{ Auth::user()->name }}! 👋</h1>
        <p>Here's what's happening with your store today.</p>
    </div>
    <div class="Seller-badge" style="background: var(--admin-primary); color: white; padding: 8px 20px; border-radius: 50px; font-weight: 600; font-size: 14px;">
        Seller
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon icon-orders">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg>
        </div>
        <div class="stat-info">
            <h3>{{ number_format($totalOrders) }}</h3>
            <p>Total Orders</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon icon-users">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
        </div>
        <div class="stat-info">
            <h3>{{ number_format($totalUsers) }}</h3>
            <p>Total Users</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon icon-revenue">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
        </div>
        <div class="stat-info">
            <h3>{{ currency_symbol() }}{{ number_format($totalRevenue, 2) }}</h3>
            <p>Total Revenue</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon icon-products">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
        </div>
        <div class="stat-info">
            <h3>{{ number_format($totalProducts) }}</h3>
            <p>Products</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon icon-refunds">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 4v6h6"></path><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path></svg>
        </div>
        <div class="stat-info">
            <h3>{{ number_format($totalRefunds) }}</h3>
            <p>Refunded Orders</p>
        </div>
    </div>
</div>
@endsection
