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

    /* Charts Layout - Light Theme */
    .charts-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 20px;
        margin-top: 30px;
    }

    .chart-container {
        background: white;
        padding: 25px;
        border-radius: 20px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        border: 1px solid #f0f0f0;
    }

    .chart-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .chart-header h2 {
        font-size: 18px;
        color: #1a1a1a;
        font-weight: 600;
    }

    /* Time Range Selector */
    .time-selector {
        display: flex;
        gap: 5px;
        background: #f8f9fa;
        padding: 4px;
        border-radius: 10px;
    }

    .time-btn {
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
        color: #666;
        cursor: pointer;
        transition: all 0.2s;
        border: none;
        background: transparent;
    }

    .time-btn.active {
        background: white;
        color: #2196F3;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }

    @media (max-width: 1024px) {
        .charts-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection

@section('content')


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

<div class="charts-grid">
    <div class="chart-container">
        <div class="chart-header">
            <h2>Revenue Analysis</h2>
            <div class="time-selector">
                <button class="time-btn">1D</button>
                <button class="time-btn active">5D</button>
                <button class="time-btn">1M</button>
                <button class="time-btn">1Y</button>
            </div>
        </div>
        <div style="position: relative; height: 350px; width: 100%;">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>

    <div class="chart-container">
        <div class="chart-header">
            <h2>Top Product Sales</h2>
        </div>
        <div style="position: relative; height: 350px; width: 100%;">
            <canvas id="productSalesChart"></canvas>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- Revenue Line Chart (Light Theme) ---
        window.myCharts = window.myCharts || [];
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        let days = {!! json_encode($days) !!};
        let revenueData = {!! json_encode($revenueData) !!};
        let currencySymbol = "{{ currency_symbol() }}";
        let revenueChart;

        const gradient = revenueCtx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(33, 150, 243, 0.2)');
        gradient.addColorStop(1, 'rgba(33, 150, 243, 0)');

        function initRevenueChart(labels, data) {
            if (revenueChart) revenueChart.destroy();
            
            revenueChart = new Chart(revenueCtx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Revenue',
                        data: data,
                        borderColor: '#2196F3',
                        backgroundColor: gradient,
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: '#2196F3',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }]
                },
                plugins: [{
                    id: 'hoverLine',
                    afterDraw: chart => {
                        if (chart.tooltip?._active?.length) {
                            const x = chart.tooltip._active[0].element.x;
                            const yAxis = chart.scales.y;
                            const ctx = chart.ctx;
                            ctx.save();
                            ctx.beginPath();
                            ctx.setLineDash([5, 5]);
                            ctx.moveTo(x, yAxis.top);
                            ctx.lineTo(x, yAxis.bottom);
                            ctx.lineWidth = 1;
                            ctx.strokeStyle = '#ddd';
                            ctx.stroke();
                            ctx.restore();
                        }
                    }
                }],
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        intersect: false,
                        mode: 'index',
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#fff',
                            titleColor: '#1a1a1a',
                            bodyColor: '#2196F3',
                            bodyFont: { size: 14, weight: 'bold' },
                            borderColor: '#eee',
                            borderWidth: 1,
                            padding: 12,
                            displayColors: false,
                            callbacks: {
                                label: function(context) {
                                    return currencySymbol + context.parsed.y.toLocaleString('en-US', { minimumFractionDigits: 2 });
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: '#f0f0f0', drawBorder: false },
                            ticks: { 
                                color: '#888', 
                                font: { size: 11 },
                                callback: function(value) {
                                    if (value >= 1000) return currencySymbol + (value / 1000) + 'k';
                                    return currencySymbol + value;
                                }
                            }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { color: '#888', font: { size: 11 } }
                        }
                    }
                }
            });
        }

        // Initialize with default data
        initRevenueChart(days, revenueData);
        window.myCharts.push(revenueChart);

        // Handle Time Range Clicks
        document.querySelectorAll('.time-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                // Update active state
                document.querySelectorAll('.time-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');

                const range = this.textContent.trim();
                fetch(`{{ route('seller.dashboard.analytics') }}?range=${range}`)
                    .then(res => res.json())
                    .then(result => {
                        initRevenueChart(result.labels, result.data);
                    })
                    .catch(err => console.error('Error fetching analytics:', err));
            });
        });

        // --- Product Sales Doughnut (Light Theme) ---
        const productCtx = document.getElementById('productSalesChart').getContext('2d');
        const productLabels = {!! json_encode($productLabels) !!};
        const productCounts = {!! json_encode($productCounts) !!};

        const productChart = new Chart(productCtx, {
            type: 'doughnut',
            data: {
                labels: productLabels,
                datasets: [{
                    label: 'Units Sold',
                    data: productCounts,
                    backgroundColor: [
                        '#2196F3',
                        '#4CAF50',
                        '#FF9800',
                        '#9C27B0',
                        '#F44336'
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 20,
                            font: { size: 12 },
                            color: '#666'
                        }
                    }
                },
                cutout: '70%'
            }
        });
        window.myCharts.push(productChart);

        // --- NEW: ResizeObserver for foolproof responsiveness ---
        const resizeObserver = new ResizeObserver(() => {
            if (window.myCharts) {
                window.myCharts.forEach(chart => {
                    chart.resize();
                    chart.update('none'); // Force immediate update without animation
                });
            }
        });

        // Observe both chart containers
        document.querySelectorAll('.chart-container').forEach(container => {
            resizeObserver.observe(container);
        });
    });
</script>
@endsection