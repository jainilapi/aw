@extends('frontend.layouts.app')

@push('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        .dashboard-page {
            padding: 40px 0;
            background: #F8FAFC;
            min-height: 80vh;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 16px;
        }

        .page-header h1 {
            font-size: 28px;
            font-weight: 700;
            color: #203A72;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .page-header h1 i {
            font-size: 32px;
        }

        /* Date Range Filter */
        .date-filter {
            display: flex;
            align-items: center;
            gap: 12px;
            background: #fff;
            padding: 10px 16px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        }

        .date-filter label {
            font-weight: 500;
            color: #666;
            margin: 0;
            white-space: nowrap;
        }

        .date-filter input {
            padding: 8px 12px;
            border: 1px solid #E0E0E0;
            border-radius: 6px;
            font-size: 14px;
            width: 130px;
        }

        .date-filter input:focus {
            outline: none;
            border-color: #203A72;
        }

        .btn-filter {
            background: #203A72;
            color: #fff;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-filter:hover {
            background: #1a2d5a;
        }

        /* Stats Cards */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        @media (max-width: 992px) {
            .stats-row {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 576px) {
            .stats-row {
                grid-template-columns: 1fr;
            }
        }

        .stat-card {
            background: #fff;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
            position: relative;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
        }

        .stat-card.orders::before {
            background: linear-gradient(90deg, #203A72, #4A6FBA);
        }

        .stat-card.spending::before {
            background: linear-gradient(90deg, #11998e, #38ef7d);
        }

        .stat-card.pending::before {
            background: linear-gradient(90deg, #F2994A, #F2C94C);
        }

        .stat-card.credit::before {
            background: linear-gradient(90deg, #667eea, #764ba2);
        }

        .stat-icon {
            width: 56px;
            height: 56px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 16px;
        }

        .stat-card.orders .stat-icon {
            background: rgba(32, 58, 114, 0.1);
            color: #203A72;
        }

        .stat-card.spending .stat-icon {
            background: rgba(17, 153, 142, 0.1);
            color: #11998e;
        }

        .stat-card.pending .stat-icon {
            background: rgba(242, 153, 74, 0.1);
            color: #F2994A;
        }

        .stat-card.credit .stat-icon {
            background: rgba(102, 126, 234, 0.1);
            color: #667eea;
        }

        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 4px;
            line-height: 1.2;
        }

        .stat-label {
            font-size: 14px;
            color: #666;
            margin-bottom: 8px;
        }

        .stat-subtitle {
            font-size: 12px;
            color: #999;
        }

        /* Charts Section */
        .charts-row {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        @media (max-width: 992px) {
            .charts-row {
                grid-template-columns: 1fr;
            }
        }

        .chart-card {
            background: #fff;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
        }

        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .chart-title {
            font-size: 18px;
            font-weight: 600;
            color: #203A72;
            margin: 0;
        }

        .chart-container {
            position: relative;
            height: 280px;
        }

        /* Recent Orders */
        .recent-orders {
            background: #fff;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .section-title {
            font-size: 18px;
            font-weight: 600;
            color: #203A72;
            margin: 0;
        }

        .view-all-btn {
            color: #203A72;
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 4px;
            transition: all 0.3s;
        }

        .view-all-btn:hover {
            color: #1a2d5a;
        }

        .orders-table {
            width: 100%;
        }

        .orders-table th {
            font-size: 12px;
            font-weight: 600;
            color: #666;
            text-transform: uppercase;
            padding: 12px 8px;
            border-bottom: 2px solid #F0F0F0;
        }

        .orders-table td {
            padding: 16px 8px;
            font-size: 14px;
            color: #333;
            border-bottom: 1px solid #F0F0F0;
        }

        .orders-table tr:last-child td {
            border-bottom: none;
        }

        .orders-table .order-number {
            font-weight: 600;
            color: #203A72;
        }

        .badge {
            font-size: 11px;
            padding: 4px 10px;
            border-radius: 20px;
            font-weight: 500;
        }

        .btn-view {
            padding: 6px 12px;
            background: #F5FAFF;
            color: #203A72;
            border: 1px solid #E0E0E0;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s;
        }

        .btn-view:hover {
            background: #203A72;
            color: #fff;
            border-color: #203A72;
        }

        /* Quick Actions */
        .quick-actions {
            display: flex;
            gap: 16px;
            margin-bottom: 30px;
        }

        .action-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 12px 20px;
            background: #fff;
            border: 1px solid #E0E0E0;
            border-radius: 10px;
            color: #203A72;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
        }

        .action-btn:hover {
            background: #203A72;
            color: #fff;
            border-color: #203A72;
        }

        .action-btn i {
            font-size: 18px;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #666;
        }

        .empty-state i {
            font-size: 48px;
            color: #ccc;
            margin-bottom: 16px;
        }
    </style>
@endpush

@section('content')
    <section>
        <div class="bred-pro">
            <div class="container">
                <div class="breadcrumb-container">
                    <ol class="breadcrumb">
                        <li><a href="{{ route('home') }}">Home</a></li>
                        <li class="active">Dashboard</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <div class="dashboard-page">
        <div class="container">
            <div class="page-header">
                <h1><i class="bi bi-speedometer2"></i> Welcome, {{ $customer->name }}</h1>
                <div class="date-filter">
                    <label>From:</label>
                    <input type="text" id="startDate" value="{{ $startDate }}" placeholder="Start Date">
                    <label>To:</label>
                    <input type="text" id="endDate" value="{{ $endDate }}" placeholder="End Date">
                    <button class="btn-filter" onclick="applyDateFilter()">
                        <i class="bi bi-funnel"></i> Apply
                    </button>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions">
                <a href="{{ route('customer.orders') }}" class="action-btn">
                    <i class="bi bi-box-seam"></i> My Orders
                </a>
                <a href="{{ route('customer.wishlist') }}" class="action-btn">
                    <i class="bi bi-heart"></i> Wishlist
                </a>
                <a href="{{ route('customer.addresses') }}" class="action-btn">
                    <i class="bi bi-geo-alt"></i> Addresses
                </a>
                <a href="{{ route('customer.profile') }}" class="action-btn">
                    <i class="bi bi-person"></i> Profile
                </a>
            </div>

            <!-- Stats Cards -->
            <div class="stats-row">
                <div class="stat-card orders">
                    <div class="stat-icon"><i class="bi bi-box-seam"></i></div>
                    <div class="stat-value" id="totalOrders">{{ number_format($stats['total_orders']) }}</div>
                    <div class="stat-label">Total Orders</div>
                    <div class="stat-subtitle">All time: {{ number_format($stats['all_time_orders']) }}</div>
                </div>
                <div class="stat-card spending">
                    <div class="stat-icon"><i class="bi bi-currency-dollar"></i></div>
                    <div class="stat-value" id="totalSpent">{{ currency_format($stats['total_spent']) }}</div>
                    <div class="stat-label">Total Spent</div>
                    <div class="stat-subtitle">All time: {{ currency_format($stats['all_time_spent']) }}</div>
                </div>
                <div class="stat-card pending">
                    <div class="stat-icon"><i class="bi bi-hourglass-split"></i></div>
                    <div class="stat-value" id="pendingOrders">{{ number_format($stats['pending_orders']) }}</div>
                    <div class="stat-label">Pending Orders</div>
                    <div class="stat-subtitle">Delivered: {{ number_format($stats['delivered_orders']) }}</div>
                </div>
                <div class="stat-card credit">
                    <div class="stat-icon"><i class="bi bi-wallet2"></i></div>
                    <div class="stat-value">{{ currency_format($stats['credit_balance']) }}</div>
                    <div class="stat-label">Credit Balance</div>
                    <div class="stat-subtitle">Available to use</div>
                </div>
            </div>

            <!-- Charts -->
            <div class="charts-row">
                <div class="chart-card">
                    <div class="chart-header">
                        <h3 class="chart-title"><i class="bi bi-graph-up me-2"></i>Spending Trend</h3>
                    </div>
                    <div class="chart-container">
                        <canvas id="spendingChart"></canvas>
                    </div>
                </div>
                <div class="chart-card">
                    <div class="chart-header">
                        <h3 class="chart-title"><i class="bi bi-pie-chart me-2"></i>Order Status</h3>
                    </div>
                    <div class="chart-container">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Recent Orders -->
            <div class="recent-orders">
                <div class="section-header">
                    <h3 class="section-title"><i class="bi bi-clock-history me-2"></i>Recent Orders</h3>
                    <a href="{{ route('customer.orders') }}" class="view-all-btn">
                        View All <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
                @if($recentOrders->count() > 0)
                    <table class="orders-table">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Date</th>
                                <th>Items</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Payment</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentOrders as $order)
                                <tr>
                                    <td class="order-number">{{ $order->order_number }}</td>
                                    <td>{{ $order->created_at->format('M d, Y') }}</td>
                                    <td>{{ $order->items->count() }} items</td>
                                    <td><strong>{{ currency_format($order->grand_total) }}</strong></td>
                                    <td>{!! $order->status_badge !!}</td>
                                    <td>{!! $order->payment_status_badge !!}</td>
                                    <td>
                                        <a href="{{ route('customer.order.detail', $order->id) }}" class="btn-view">
                                            <i class="bi bi-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="empty-state">
                        <i class="bi bi-inbox"></i>
                        <p>No orders yet. Start shopping!</p>
                        <a href="{{ route('products') }}" class="btn btn-primary">Browse Products</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        // Initialize date pickers
        flatpickr('#startDate', {
            dateFormat: 'Y-m-d',
            maxDate: 'today'
        });

        flatpickr('#endDate', {
            dateFormat: 'Y-m-d',
            maxDate: 'today'
        });

        // Spending Trend Chart
        const spendingCtx = document.getElementById('spendingChart').getContext('2d');
        const spendingData = @json($spendingData);

        new Chart(spendingCtx, {
            type: 'line',
            data: {
                labels: spendingData.labels,
                datasets: [{
                    label: 'Monthly Spending',
                    data: spendingData.values,
                    fill: true,
                    backgroundColor: 'rgba(32, 58, 114, 0.1)',
                    borderColor: '#203A72',
                    borderWidth: 3,
                    tension: 0.4,
                    pointBackgroundColor: '#203A72',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: value => window.formatCurrency(value)
                        },
                        grid: { color: 'rgba(0,0,0,0.05)' }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });

        // Order Status Chart
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        const statusData = @json($statusData);

        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: statusData.labels,
                datasets: [{
                    data: statusData.values,
                    backgroundColor: statusData.colors,
                    borderWidth: 0,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    }
                }
            }
        });

        // Apply date filter
        function applyDateFilter() {
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;

            if (!startDate || !endDate) {
                alert('Please select both dates');
                return;
            }

            // AJAX to update stats
            fetch(`{{ route('customer.dashboard.stats') }}?start_date=${startDate}&end_date=${endDate}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('totalOrders').textContent = data.stats.total_orders.toLocaleString();
                        document.getElementById('totalSpent').textContent = window.formatCurrency(data.stats.total_spent);
                        document.getElementById('pendingOrders').textContent = data.stats.pending_orders.toLocaleString();
                    }
                })
                .catch(error => console.error('Error:', error));
        }
    </script>
@endpush