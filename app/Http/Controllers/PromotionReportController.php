<?php

namespace App\Http\Controllers;

use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use App\Models\AwPromotion;
use App\Models\AwOrder;

class PromotionReportController extends Controller
{
    protected $title = 'Reports';
    protected $view = 'promotions.';

    public function index()
    {
        if (request()->ajax()) {
            return $this->ajax();
        }

        $title = $this->title;
        $subTitle = 'Promotion Usage Report';
        $statuses = ['pending', 'confirmed', 'processing', 'packed', 'shipped', 'delivered', 'cancelled', 'rejected', 'returned'];
        $promotions = AwPromotion::pluck('name', 'code');

        return view($this->view . 'usage', compact('title', 'subTitle', 'promotions', 'statuses'));
    }

    public function ajax()
    {
        $query = AwOrder::query()
            ->with(['customer', 'promotion'])
            ->whereNotNull('promotion_id');

        if (request()->filled('filter_promotion_code')) {
            $query->where('promotion_code', request()->filter_promotion_code);
        }

        if (request()->filled('filter_customer')) {
            $query->whereHas('customer', function ($q) {
                $q->where('name', 'like', '%' . request()->filter_customer . '%')
                    ->orWhere('email', 'like', '%' . request()->filter_customer . '%');
            });
        }

        if (request()->filled('filter_status')) {
            $query->where('status', request()->filter_status);
        }

        if (request()->filled('filter_date_from')) {
            $query->whereDate('created_at', '>=', request()->filter_date_from);
        }

        if (request()->filled('filter_date_to')) {
            $query->whereDate('created_at', '<=', request()->filter_date_to);
        }

        return datatables()
            ->eloquent($query)
            ->addColumn('order_number_link', function ($row) {
                return '<a href="' . route('orders.show', encrypt($row->id)) . '" target="_blank">' . $row->order_number . '</a>';
            })
            ->addColumn('customer_info', function ($row) {
                return $row->customer ? $row->customer->name . '<br><small class="text-muted">' . $row->customer->email . '</small>' : 'Guest';
            })
            ->addColumn('promotion_details', function ($row) {
                return '<span class="badge bg-info">' . $row->promotion_code . '</span><br><small>' . ($row->promotion ? $row->promotion->name : '') . '</small>';
            })
            ->addColumn('discount_amount_formatted', function ($row) {
                return '$' . number_format($row->promotion_discount, 2);
            })
            ->addColumn('total_amount_formatted', function ($row) {
                return '$' . number_format($row->grand_total, 2);
            })
            ->addColumn('order_date_formatted', function ($row) {
                return $row->created_at ? $row->created_at->format('Y-m-d H:i') : '—';
            })
            ->addColumn('status_badge', function ($row) {
                $badges = [
                    'pending' => 'secondary',
                    'confirmed' => 'info',
                    'processing' => 'primary',
                    'packed' => 'warning',
                    'shipped' => 'success',
                    'delivered' => 'success',
                    'cancelled' => 'danger',
                    'rejected' => 'danger',
                    'returned' => 'danger'
                ];
                $color = $badges[$row->status] ?? 'secondary';
                return '<span class="badge bg-' . $color . '">' . ucfirst($row->status) . '</span>';
            })
            ->rawColumns(['order_number_link', 'customer_info', 'promotion_details', 'status_badge'])
            ->addIndexColumn()
            ->toJson();
    }

    public function export(Request $request)
    {
        $query = AwOrder::query()
            ->with(['customer', 'promotion'])
            ->whereNotNull('promotion_id');

        if ($request->filled('filter_promotion_code')) {
            $query->where('promotion_code', $request->filter_promotion_code);
        }
        if ($request->filled('filter_customer')) {
            $query->whereHas('customer', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->filter_customer . '%')
                  ->orWhere('email', 'like', '%' . $request->filter_customer . '%');
            });
        }
        if ($request->filled('filter_status')) {
            $query->where('status', $request->filter_status);
        }
        if ($request->filled('filter_date_from')) {
            $query->whereDate('order_date', '>=', $request->filter_date_from);
        }
        if ($request->filled('filter_date_to')) {
            $query->whereDate('order_date', '<=', $request->filter_date_to);
        }

        $data = $query->get()->map(function($order) {
            return [
                'Order Number' => $order->order_number,
                'Order Date' => $order->order_date ? $order->order_date->format('Y-m-d H:i:s') : '',
                'Customer Name' => $order->customer ? $order->customer->name : 'Guest',
                'Customer Email' => $order->customer ? $order->customer->email : '',
                'Promotion Code' => $order->promotion_code,
                'Promotion Name' => $order->promotion ? $order->promotion->name : '',
                'Discount Amount' => $order->promotion_discount,
                'Total Amount' => $order->total_amount,
                'Status' => ucfirst($order->status),
            ];
        });

        return Excel::download(new class($data) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings, \Maatwebsite\Excel\Concerns\ShouldAutoSize {
            protected $data;
            public function __construct($data) { $this->data = $data; }
            public function collection() { return $this->data; }
            public function headings(): array {
                return array_keys($this->data->first() ?? []);
            }
        }, 'promotion_usage_report_' . date('Y-m-d_His') . '.xlsx');
    }
}
