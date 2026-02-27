@extends('layouts.app')

@push('css')
<style>
.content {
    margin-top: 0;
}
.dashboard {
    background: #f4f6f9;
}

.dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
}

.dashboard-header h1 {
    font-size: 24px;
    font-weight: 600;
}

.dashboard-actions {
    display: flex;
    gap: 10px;
}

.btn-primary {
    background: #1979c3;
    border: none;
    color: #fff;
    padding: 6px 14px;
    border-radius: 4px;
    cursor: pointer;
}

.kpi-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 24px;
}

.kpi-card {
    background: #fff;
    border-radius: 6px;
    padding: 16px;
    display: flex;
    gap: 14px;
    align-items: center;
    box-shadow: 0 1px 3px rgba(0,0,0,.08);
}

.kpi-icon {
    width: 42px;
    height: 42px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
}

.bg-blue { background:#1979c3 }
.bg-green { background:#3ab54a }
.bg-purple { background:#7b61ff }
.bg-orange { background:#f2994a }

.kpi-value {
    font-size: 20px;
    font-weight: 600;
}

.kpi-meta {
    font-size: 12px;
    color: #6b7280;
}

.dashboard-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 16px;
    margin-bottom: 24px;
}

.card {
    background: #fff;
    border-radius: 6px;
    padding: 16px;
    box-shadow: 0 1px 3px rgba(0,0,0,.08);
}

.card-header {
    margin-bottom: 12px;
}

.status-list {
    list-style: none;
    padding: 0;
}

.status-list li {
    display: flex;
    justify-content: space-between;
    margin-bottom: 8px;
}

.dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    display: inline-block;
    margin-right: 6px;
}

.dot.blue { background:#1979c3 }
.dot.green { background:#3ab54a }
.dot.orange { background:#f2994a }
.dot.red { background:#eb5757 }

.table {
    width: 100%;
    border-collapse: collapse;
}

.table th, .table td {
    padding: 8px;
    border-bottom: 1px solid #e5e7eb;
}

.badge {
    padding: 3px 8px;
    border-radius: 12px;
    font-size: 12px;
}

.badge.success { background:#e6f4ea; color:#1e7f3f }
.badge.warning { background:#fff4e5; color:#b45309 }
.badge.info { background:#e8f1fd; color:#1d4ed8 }

.low-stock li {
    display: flex;
    justify-content: space-between;
    padding: 6px 0;
}
    
</style>
@endpush

@section('content')
<div class="dashboard">

    <!-- Page Header -->
    <div class="dashboard-header">
        <h1>Dashboard</h1>
        <div class="dashboard-actions">
            <input type="text" name="daterange" class="form-control" style="background: #fff; cursor: pointer; padding: 6px 12px; border: 1px solid #ccc; border-radius: 4px; min-width: 240px; text-align: center;" readonly />
        </div>
    </div>

    <!-- Dynamic Content -->
    <div id="dashboard-content">
        @include('dashboard_partials.stats')
    </div>

</div>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(function() {
    // Check if moment is available, otherwise try to load it or warn
    if (typeof moment === 'undefined') {
        console.warn('Moment.js is not loaded. DateRangePicker might fail.');
         // Fallback: minimal moment-like object for daterangepicker if absolutely needed, 
         // but better to rely on app.js or add CDN if it fails.
    }

    // Initialize Date Range Picker
    $('input[name="daterange"]').daterangepicker({
        opens: 'left',
        startDate: moment().startOf('day'),
        endDate: moment().endOf('day'),
        ranges: {
           'Today': [moment(), moment()],
           'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
           'Last 7 Days': [moment().subtract(6, 'days'), moment()],
           'Last 30 Days': [moment().subtract(29, 'days'), moment()],
           'This Month': [moment().startOf('month'), moment().endOf('month')],
           'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        },
        locale: {
            format: 'YYYY-MM-DD'
        }
    }, function(start, end, label) {
        fetchDashboardData(start.format('YYYY-MM-DD'), end.format('YYYY-MM-DD'));
    });

    // Helper to fetch data
    function fetchDashboardData(startDate, endDate) {
        // Show loading state (optional)
        $('#dashboard-content').css('opacity', '0.5');

        $.ajax({
            url: "{{ route('dashboard') }}",
            type: "GET",
            data: {
                start_date: startDate,
                end_date: endDate
            },
            success: function(response) {
                $('#dashboard-content').html(response.html);
                $('#dashboard-content').css('opacity', '1');
                
                // Re-initialize chart
                if(response.chartData) {
                    initChart(response.chartData.labels, response.chartData.values);
                }
            },
            error: function() {
                alert('Error fetching dashboard data');
                $('#dashboard-content').css('opacity', '1');
            }
        });
    }

    // Chart Handling
    let salesChart = null;

    function initChart(labels, values) {
        const ctx = document.getElementById('salesChart');
        if (!ctx) return;

        if (salesChart) {
            salesChart.destroy();
        }

        salesChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Sales',
                    data: values,
                    borderColor: '#1979c3',
                    backgroundColor: 'rgba(25,121,195,0.1)',
                    fill: true,
                    tension: 0.4
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
                            callback: function(value) {
                                return "{{ currency_symbol() }}" + value;
                            }
                        }
                    }
                }
            }
        });
    }

    // Initial Chart Load
    const initialLabels = @json($salesChartLabels);
    const initialValues = @json($salesChartValues);
    initChart(initialLabels, initialValues);
});
</script>
@endpush