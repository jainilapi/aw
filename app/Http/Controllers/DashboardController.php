<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\AwOrder;
use App\Models\User;
use App\Models\AwSupplierWarehouseProduct;

class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return $this->getDashboardData($request);
        }

        $data = $this->getDashboardData($request);

        return view('dashboard', $data + ['datepicker' => true]);
    }

    private function getDashboardData(Request $request)
    {
        $startDate = $request->start_date ? Carbon::parse($request->start_date)->startOfDay() : Carbon::today()->startOfDay();
        $endDate = $request->end_date ? Carbon::parse($request->end_date)->endOfDay() : Carbon::today()->endOfDay();

        // Key Metrics
        $totalSales = AwOrder::whereBetween('created_at', [$startDate, $endDate])
            ->whereNotIn('status', ['cancelled', 'rejected', 'returned'])
            ->sum('grand_total');
        
        $totalOrders = AwOrder::whereBetween('created_at', [$startDate, $endDate])->count();
        
        $newCustomers = User::whereHas('roles', function($q) {
                $q->where('name', 'customer');
            })
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        $lowStockProducts = AwSupplierWarehouseProduct::with(['product', 'variant'])
            ->where('quantity', '<', 10)
            ->take(5)
            ->get();

        // Chart Data (Sales per day)
        $salesChartData = AwOrder::selectRaw('DATE(created_at) as date, SUM(grand_total) as total')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNotIn('status', ['cancelled', 'rejected', 'returned'])
            ->groupBy('date')
            ->orderBy('date')
            ->get();
            
        // Order Status Distribution
        $orderStatusCounts = AwOrder::selectRaw('status, count(*) as count')
             ->whereBetween('created_at', [$startDate, $endDate])
             ->groupBy('status')
             ->pluck('count', 'status')
             ->toArray();

        // Recent Orders
        $recentOrders = AwOrder::with('customer')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->latest()
            ->take(5)
            ->get();

        $currencySymbol = currency_symbol();

        $response = [
            'totalSales' => $totalSales,
            'totalOrders' => $totalOrders,
            'newCustomers' => $newCustomers,
            'lowStockProducts' => $lowStockProducts,
            'salesChartLabels' => $salesChartData->pluck('date'),
            'salesChartValues' => $salesChartData->map(function ($el) use ($currencySymbol) {
                $el->total = str_replace($currencySymbol, '', currency_format($el->total));
                return $el;
            })->pluck('total')->toArray(),
            'orderStatusCounts' => $orderStatusCounts,
            'recentOrders' => $recentOrders
        ];

        if ($request->ajax()) {
             return response()->json([
                'html' => view('dashboard_partials.stats', $response)->render(),
                'chartData' => [
                    'labels' => $response['salesChartLabels'],
                    'values' => $response['salesChartValues']
                ]
             ]);
        }

        return $response;
    }
}
