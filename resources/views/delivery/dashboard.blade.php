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

    .chart-container {
        background: var(--partner-white);
        border-radius: 20px;
        padding: 30px;
        box-shadow: var(--shadow);
        margin-bottom: 40px;
    }
    .chart-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
    }
    .chart-title {
        font-size: 20px;
        color: var(--partner-dark);
        margin: 0;
    }
    .chart-filters {
        display: flex;
        gap: 10px;
        background: var(--partner-bg);
        padding: 4px;
        border-radius: 8px;
    }
    .filter-btn {
        background: transparent;
        border: none;
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 600;
        color: #666;
        cursor: pointer;
        transition: var(--transition);
        font-family: inherit;
    }
    .filter-btn.active, .filter-btn:hover {
        background: var(--partner-white);
        color: var(--partner-primary);
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    @media (max-width: 768px) {
        .chart-header { flex-direction: column; align-items: flex-start; gap: 15px; }
        .chart-container { padding: 20px; }
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
                <h3>{{ currency_symbol() }}{{ number_format($stats['total_income'], 2) }}</h3>
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

    <!-- Income Tracking Chart -->
    <div class="chart-container">
        <div class="chart-header">
            <h2 class="chart-title">Income Analytics</h2>
            <div class="chart-filters">
                <button class="filter-btn {{ $timeRange == '1D' ? 'active' : '' }}" onclick="updateChart('1D')">1D</button>
                <button class="filter-btn {{ $timeRange == '5D' ? 'active' : '' }}" onclick="updateChart('5D')">5D</button>
                <button class="filter-btn {{ $timeRange == '1M' ? 'active' : '' }}" onclick="updateChart('1M')">1M</button>
                <button class="filter-btn {{ $timeRange == '1Y' ? 'active' : '' }}" onclick="updateChart('1Y')">1Y</button>
            </div>
        </div>
        <div style="position: relative; height: 350px; width: 100%;">
            <canvas id="incomeChart"></canvas>
        </div>
    </div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let incomeChart;

    const chartConfig = {
        type: 'line',
        data: {
            labels: @json($chartData['labels']),
            datasets: [{
                label: 'Income ({{ currency_symbol() }})',
                data: @json($chartData['income']),
                borderColor: '#4CAF50',
                backgroundColor: 'rgba(76, 175, 80, 0.1)',
                borderWidth: 3,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#fff',
                pointBorderColor: '#4CAF50',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6,
                yAxisID: 'y'
            }, {
                label: 'Deliveries Count',
                data: @json($chartData['count']),
                borderColor: '#2196F3',
                backgroundColor: 'rgba(33, 150, 243, 0.1)',
                borderWidth: 2,
                tension: 0.4,
                borderDash: [5, 5],
                pointBackgroundColor: '#fff',
                pointBorderColor: '#2196F3',
                pointBorderWidth: 2,
                pointRadius: 3,
                pointHoverRadius: 5,
                yAxisID: 'y1'
            }, {
                label: 'Returns Count',
                data: @json($chartData['returns']),
                borderColor: '#F44336',
                backgroundColor: 'rgba(244, 67, 54, 0.1)',
                borderWidth: 2,
                tension: 0.4,
                borderDash: [3, 3],
                pointBackgroundColor: '#fff',
                pointBorderColor: '#F44336',
                pointBorderWidth: 2,
                pointRadius: 3,
                pointHoverRadius: 5,
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        padding: 20,
                        font: { family: "'Poppins', sans-serif", size: 12 }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(26, 26, 26, 0.9)',
                    titleFont: { family: "'Poppins', sans-serif", size: 13 },
                    bodyFont: { family: "'Poppins', sans-serif", size: 12 },
                    padding: 12,
                    cornerRadius: 8,
                    displayColors: true
                }
            },
            scales: {
                x: {
                    grid: { display: false, drawBorder: false },
                    ticks: {
                        font: { family: "'Poppins', sans-serif", size: 11 },
                        color: '#888'
                    }
                },
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,0.04)', drawBorder: false },
                    ticks: {
                        font: { family: "'Poppins', sans-serif", size: 11 },
                        color: '#888',
                        callback: function(value) { return '{{ currency_symbol() }}' + value; }
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    beginAtZero: true,
                    grid: { drawOnChartArea: false },
                    ticks: {
                        font: { family: "'Poppins', sans-serif", size: 11 },
                        color: '#888',
                        stepSize: 1
                    }
                }
            }
        }
    };

    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('incomeChart').getContext('2d');
        incomeChart = new Chart(ctx, chartConfig);
    });

    function updateChart(range) {
        // Update active button
        document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
        event.target.classList.add('active');

        // Fetch new data
        fetch(`{{ route('delivery.dashboard') }}?range=${range}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            incomeChart.data.labels = data.labels;
            incomeChart.data.datasets[0].data = data.income;
            incomeChart.data.datasets[1].data = data.count;
            incomeChart.data.datasets[2].data = data.returns;
            incomeChart.update();
        })
        .catch(error => console.error('Error updating chart:', error));
    }
</script>
@endsection
