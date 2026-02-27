
    <!-- KPI Cards -->
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-icon bg-blue"><i class="fas fa-money-bill"></i></div>
            <div>
                <h4>Total Sales</h4>
                <p class="kpi-value">{{ currency_format($totalSales) }}</p>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon bg-green"><i class="fas fa-shopping-cart"></i></div>
            <div>
                <h4>Orders</h4>
                <p class="kpi-value">{{ number_format($totalOrders) }}</p>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon bg-purple"><i class="fas fa-users"></i></div>
            <div>
                <h4>New Customers</h4>
                <p class="kpi-value">{{ number_format($newCustomers) }}</p>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon bg-orange"><i class="fas fa-box"></i></div>
            <div>
                <h4>Low Stock Products</h4>
                <p class="kpi-value">{{ $lowStockProducts->count() }}</p>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="">

        <!-- Sales Chart -->
        <div class="card">
            <div class="card-header">
                <h3>Sales Overview</h3>
            </div>
            <div style="position: relative; height: 300px; width: 100%;">
                <canvas id="salesChart"></canvas>
            </div>
        </div>

        <!-- Order Status -->
        {{-- <div class="card">
            <div class="card-header">
                <h3>Order Status</h3>
            </div>
            <ul class="status-list">
                @foreach($orderStatusCounts as $status => $count)
                    <li>
                        <span class="dot {{ match($status) { 'completed' => 'green', 'pending' => 'orange', 'cancelled' => 'red', default => 'blue' } }}"></span> 
                        {{ ucfirst($status) }} 
                        <strong>{{ $count }}</strong>
                    </li>
                @endforeach
            </ul>
        </div> --}}

    </div>

    <!-- Bottom Row -->
    <div class="dashboard-grid">

        <!-- Recent Orders -->
        <div class="card">
            <div class="card-header">
                <h3>Recent Orders</h3>
            </div>
            <table class="table">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Status</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentOrders as $order)
                    <tr>
                        <td>{{ $order->order_number }}</td>
                        <td>{{ $order->customer->name ?? 'N/A' }}</td>
                        <td>{!! $order->status_badge !!}</td>
                        <td>{{ currency_format($order->grand_total) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center">No recent orders found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Low Stock -->
        <div class="card">
            <div class="card-header">
                <h3>Low Stock Products</h3>
            </div>
            <ul class="low-stock">
                @forelse($lowStockProducts as $item)
                    <li>
                        {{ $item->product->name ?? 'Unknown Product' }} 
                        @if($item->variant) ({{ $item->variant->name }}) @endif
                        <span>{{ $item->quantity }} left</span>
                    </li>
                @empty
                    <li>No low stock products.</li>
                @endforelse
            </ul>
        </div>

    </div>
