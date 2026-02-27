<?php

namespace App\Http\Controllers;

use App\Models\AwOrder;
use App\Models\AwOrderItem;
use App\Models\User;
use App\Models\AwProduct;
use App\Models\Country;
use App\Models\TaxSlab;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Yajra\DataTables\Facades\DataTables;

class OrderController extends Controller
{
    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Display order listing
     */
    public function index(Request $request): View|JsonResponse
    {
        $title = 'Orders';
        $subTitle = 'Manage customer orders';

        if ($request->ajax()) {
            return $this->getDataTable($request);
        }

        $statuses = AwOrder::getStatuses();
        $paymentStatuses = AwOrder::getPaymentStatuses();

        return view('orders.index', compact('title', 'subTitle', 'statuses', 'paymentStatuses'));
    }

    /**
     * Get DataTable data
     */
    protected function getDataTable(Request $request): JsonResponse
    {
        $query = AwOrder::with(['customer', 'items'])
            ->select('aw_orders.*');

        // Apply filters
        if ($request->filled('status')) {
            $statuses = is_array($request->status) ? $request->status : [$request->status];
            $query->whereIn('status', $statuses);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->filled('order_number')) {
            $query->where('order_number', 'like', "%{$request->order_number}%");
        }

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->dateRange($request->date_from, $request->date_to);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('customer_name', function ($order) {
                return $order->customer?->name ?? 'N/A';
            })
            ->addColumn('items_count', function ($order) {
                return $order->items->count();
            })
            ->addColumn('grand_total_formatted', function ($order) {
                return number_format($order->grand_total, 2) . ' ' . $order->currency;
            })
            ->addColumn('status_badge', function ($order) {
                return $order->status_badge;
            })
            ->addColumn('payment_status_badge', function ($order) {
                return $order->payment_status_badge;
            })
            ->addColumn('source_badge', function ($order) {
                return $order->source_badge;
            })
            ->addColumn('created_date', function ($order) {
                return $order->created_at->format('M d, Y H:i');
            })
            ->addColumn('action', function ($order) {
                $actions = '<div class="btn-group btn-group-sm">';

                if (auth()?->user()?->isAdmin() || auth()->guard('web')->user()->can('orders.show')) {
                    $actions .= '<a href="' . route('orders.show', $order) . '" class="btn btn-info btn-sm" title="View">
                        <i class="fa fa-eye"></i>
                    </a>';
                }

                if ((auth()?->user()?->isAdmin() || auth()->guard('web')->user()->can('orders.edit')) && $order->isEditable()) {
                    $actions .= '<a href="' . route('orders.edit', $order) . '" class="btn btn-primary btn-sm" title="Edit">
                        <i class="fa fa-edit"></i>
                    </a>';
                }

                if (auth()?->user()?->isAdmin() || auth()->guard('web')->user()->can('orders.delete')) {
                    $actions .= '<button type="button" class="btn btn-danger btn-sm" id="deleteRow" data-row-route="' . route('orders.destroy', $order) . '" title="Delete">
                        <i class="fa fa-trash"></i>
                    </button>';
                }

                $actions .= '</div>';
                return $actions;
            })
            ->rawColumns(['status_badge', 'payment_status_badge', 'source_badge', 'action'])
            ->make(true);
    }

    /**
     * Show order creation form
     */
    public function create(): View
    {
        $title = 'Orders';
        $subTitle = 'Create new order';

        $countries = Country::orderBy('name')->get();
        $paymentMethods = [
            'cash_on_delivery' => 'Cash on Delivery',
            'bank_transfer' => 'Bank Transfer',
            'credit_card' => 'Credit Card',
            'credit' => 'Store Credit',
        ];

        $taxSlabs = TaxSlab::active()->get();

        return view('orders.create', compact('title', 'subTitle', 'countries', 'paymentMethods', 'taxSlabs'));
    }

    /**
     * Store a new order
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:users,id',
            'shipping_address_line_1' => 'required|string|max:255',
            'shipping_address_line_2' => 'nullable|string|max:255',
            'shipping_country_id' => 'required|exists:countries,id',
            'shipping_state_id' => 'required|exists:states,id',
            'shipping_city_id' => 'required|exists:cities,id',
            'shipping_zipcode' => 'required|string|max:20',
            'recipient_name' => 'required|string|max:255',
            'recipient_contact_number' => 'required|string|max:20',
            'recipient_email' => 'nullable|email|max:255',
            'billing_address_line_1' => 'required|string|max:255',
            'billing_address_line_2' => 'nullable|string|max:255',
            'billing_country_id' => 'required|exists:countries,id',
            'billing_state_id' => 'required|exists:states,id',
            'billing_city_id' => 'required|exists:cities,id',
            'billing_zipcode' => 'required|string|max:20',
            'billing_name' => 'required|string|max:255',
            'billing_contact_number' => 'required|string|max:20',
            'billing_email' => 'nullable|email|max:255',
            'payment_method' => 'required|string|max:50',
            'shipping_total' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'internal_notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:aw_products,id',
            'items.*.variant_id' => 'nullable|exists:aw_product_variants,id',
            'items.*.unit_id' => 'required|exists:aw_units,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_amount' => 'nullable|numeric|min:0',
            'items.*.tax_slab_id' => 'required|exists:tax_slabs,id',
            'items.*.tax_amount' => 'nullable|numeric|min:0',
        ]);

        try {
            $orderData = collect($validated)->except('items')->toArray();
            $orderData['shipping_total'] = $orderData['shipping_total'] ?? 0;
            $orderData['is_b2b'] = true;

            $order = $this->orderService->createOrder(
                $orderData,
                $validated['items'],
                auth()->id()
            );

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Order created successfully',
                    'redirect' => route('orders.show', $order),
                ]);
            }

            return redirect()->route('orders.show', $order)
                ->with('success', 'Order #' . $order->order_number . ' created successfully');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create order: ' . $e->getMessage(),
                ], 422);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create order: ' . $e->getMessage());
        }
    }

    /**
     * Display order details
     */
    public function show(AwOrder $order): View
    {
        $title = 'Orders';
        $subTitle = 'Order #' . $order->order_number;

        $order->load([
            'customer',
            'createdBy',
            'items.product',
            'items.variant',
            'items.unit',
            'statusHistory.user',
            'shippingCountry',
            'shippingState',
            'shippingCity',
            'billingCountry',
            'billingState',
            'billingCity',
        ]);

        $allowedStatuses = $order->getAllowedNextStatuses();
        $taxSlabs = TaxSlab::active()->get();

        return view('orders.show', compact('title', 'subTitle', 'order', 'allowedStatuses', 'taxSlabs'));
    }

    /**
     * Show order edit form
     */
    public function edit(AwOrder $order): View|RedirectResponse
    {
        if (!$order->isEditable()) {
            return redirect()->route('orders.show', $order)
                ->with('error', 'This order cannot be edited in its current status.');
        }

        $title = 'Orders';
        $subTitle = 'Edit Order #' . $order->order_number;

        $order->load([
            'customer',
            'items.product',
            'items.variant',
            'items.unit',
            'shippingCountry',
            'shippingState',
            'shippingCity',
            'billingCountry',
            'billingState',
            'billingCity',
        ]);

        $countries = Country::orderBy('name')->get();
        $paymentMethods = [
            'cash_on_delivery' => 'Cash on Delivery',
            'bank_transfer' => 'Bank Transfer',
            'credit_card' => 'Credit Card',
            'credit' => 'Store Credit',
        ];

        return view('orders.edit', compact('title', 'subTitle', 'order', 'countries', 'paymentMethods'));
    }

    /**
     * Update order
     */
    public function update(Request $request, AwOrder $order): RedirectResponse|JsonResponse
    {
        if (!$order->isEditable()) {
            $message = 'This order cannot be edited in its current status.';
            return $request->ajax()
                ? response()->json(['success' => false, 'message' => $message], 403)
                : redirect()->route('orders.show', $order)->with('error', $message);
        }

        $validated = $request->validate([
            'shipping_address_line_1' => 'required|string|max:255',
            'shipping_address_line_2' => 'nullable|string|max:255',
            'shipping_country_id' => 'required|exists:countries,id',
            'shipping_state_id' => 'required|exists:states,id',
            'shipping_city_id' => 'required|exists:cities,id',
            'shipping_zipcode' => 'required|string|max:20',
            'recipient_name' => 'required|string|max:255',
            'recipient_contact_number' => 'required|string|max:20',
            'recipient_email' => 'nullable|email|max:255',
            'billing_address_line_1' => 'required|string|max:255',
            'billing_address_line_2' => 'nullable|string|max:255',
            'billing_country_id' => 'required|exists:countries,id',
            'billing_state_id' => 'required|exists:states,id',
            'billing_city_id' => 'required|exists:cities,id',
            'billing_zipcode' => 'required|string|max:20',
            'billing_name' => 'required|string|max:255',
            'billing_contact_number' => 'required|string|max:20',
            'billing_email' => 'nullable|email|max:255',
            'payment_method' => 'required|string|max:50',
            'shipping_total' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'internal_notes' => 'nullable|string',
        ]);

        try {
            $this->orderService->updateOrder($order, $validated);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Order updated successfully',
                ]);
            }

            return redirect()->route('orders.show', $order)
                ->with('success', 'Order updated successfully');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update order: ' . $e->getMessage(),
                ], 422);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update order: ' . $e->getMessage());
        }
    }

    /**
     * Delete order
     */
    public function destroy(AwOrder $order): JsonResponse
    {
        try {
            $order->delete();
            return response()->json(['success' => 'Order deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete order'], 500);
        }
    }

    /**
     * Update order status
     */
    public function updateStatus(Request $request, AwOrder $order): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|string|in:' . implode(',', array_keys(AwOrder::getStatuses())),
            'comment' => 'nullable|string|max:500',
        ]);

        $allowedStatuses = $order->getAllowedNextStatuses();
        if (!array_key_exists($validated['status'], $allowedStatuses)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid status transition',
            ], 422);
        }

        try {
            $order = $this->orderService->updateStatus(
                $order,
                $validated['status'],
                $validated['comment'] ?? null,
                auth()->id()
            );

            return response()->json([
                'success' => true,
                'message' => 'Order status updated successfully',
                'new_status' => $order->status,
                'status_badge' => $order->status_badge,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Bulk update order status
     */
    public function bulkUpdateStatus(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_ids' => 'required|array|min:1',
            'order_ids.*' => 'exists:aw_orders,id',
            'status' => 'required|string|in:' . implode(',', array_keys(AwOrder::getStatuses())),
            'comment' => 'nullable|string|max:500',
        ]);

        $updated = $this->orderService->bulkUpdateStatus(
            $validated['order_ids'],
            $validated['status'],
            $validated['comment'] ?? null,
            auth()->id()
        );

        return response()->json([
            'success' => true,
            'message' => "{$updated} order(s) updated successfully",
            'updated_count' => $updated,
        ]);
    }

    /**
     * Add item to order
     */
    public function addItem(Request $request, AwOrder $order): JsonResponse
    {
        if (!$order->isEditable()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot add items to this order',
            ], 403);
        }

        $validated = $request->validate([
            'product_id' => 'required|exists:aw_products,id',
            'variant_id' => 'nullable|exists:aw_product_variants,id',
            'unit_id' => 'required|exists:aw_units,id',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
        ]);

        try {
            $item = $this->orderService->addOrderItem($order, $validated);
            $order->recalculateTotals();

            return response()->json([
                'success' => true,
                'message' => 'Item added successfully',
                'item' => $item->load(['product', 'variant', 'unit']),
                'totals' => [
                    'sub_total' => $order->sub_total,
                    'tax_total' => $order->tax_total,
                    'discount_total' => $order->discount_total,
                    'grand_total' => $order->grand_total,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add item: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update order item
     */
    public function updateItem(Request $request, AwOrder $order, AwOrderItem $item): JsonResponse
    {
        if (!$order->isEditable()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot update items in this order',
            ], 403);
        }

        if ($item->order_id !== $order->id) {
            return response()->json([
                'success' => false,
                'message' => 'Item does not belong to this order',
            ], 404);
        }

        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
        ]);

        try {
            $item = $this->orderService->updateOrderItem($item, $validated);
            $order->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Item updated successfully',
                'item' => $item,
                'totals' => [
                    'sub_total' => $order->sub_total,
                    'tax_total' => $order->tax_total,
                    'discount_total' => $order->discount_total,
                    'grand_total' => $order->grand_total,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update item: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove item from order
     */
    public function removeItem(AwOrder $order, AwOrderItem $item): JsonResponse
    {
        if (!$order->isEditable()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot remove items from this order',
            ], 403);
        }

        if ($item->order_id !== $order->id) {
            return response()->json([
                'success' => false,
                'message' => 'Item does not belong to this order',
            ], 404);
        }

        try {
            $this->orderService->removeOrderItem($item);
            $order->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Item removed successfully',
                'totals' => [
                    'sub_total' => $order->sub_total,
                    'tax_total' => $order->tax_total,
                    'discount_total' => $order->discount_total,
                    'grand_total' => $order->grand_total,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove item: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get customers for Select2
     */
    public function getCustomers(Request $request): JsonResponse
    {
        $search = $request->get('search', '');

        $customers = User::query()
            ->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%");
            })
            ->select('id', 'name', 'email', 'phone_number')
            ->limit(20)
            ->get();

        return response()->json([
            'results' => $customers->map(fn($c) => [
                'id' => $c->id,
                'text' => "{$c->name} ({$c->email})",
            ]),
        ]);
    }

    /**
     * Get products for Select2
     */
    public function getProducts(Request $request): JsonResponse
    {
        $search = $request->get('search', '');

        $products = AwProduct::query()
            ->where('status', 'active')
            ->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            })
            ->select('id', 'name', 'sku', 'product_type')
            ->limit(20)
            ->get();

        return response()->json([
            'results' => $products->map(fn($p) => [
                'id' => $p->id,
                'text' => "{$p->name} (SKU: {$p->sku})",
                'product_type' => $p->product_type
            ]),
        ]);
    }

    /**
     * Get customer locations
     */
    public function getCustomerLocations(int $customerId): JsonResponse
    {
        $customer = User::with('locations')->find($customerId);

        if (!$customer) {
            return response()->json(['locations' => []]);
        }

        return response()->json([
            'locations' => $customer->locations->map(fn($loc) => [
                'id' => $loc->id,
                'name' => $loc->name ?? $loc->address_line_1,
                'address_line_1' => $loc->address_line_1,
                'address_line_2' => $loc->address_line_2,
                'country_id' => $loc->country_id,
                'state_id' => $loc->state_id,
                'city_id' => $loc->city_id,
                'zipcode' => $loc->zipcode,
                'contact_name' => $loc->contact_name,
                'contact_number' => $loc->contact_number,
                'email' => $loc->email,
            ]),
        ]);
    }

    /**
     * Get product units for Simple products
     */
    public function getProductUnits(int $productId): JsonResponse
    {
        $product = AwProduct::with([
            'prices' => function ($q) {
                $q->whereNull('variant_id')->with(['belongs.unit', 'tiers']);
            }
        ])->find($productId);

        if (!$product) {
            return response()->json(['units' => []]);
        }

        $units = $product->prices->map(function ($price) use ($product) {
            return [
                'id' => $price->unit_id,
                'name' => $price->belongs?->unit?->name ?? 'Unknown',
                'pricing_type' => $price->pricing_type,
                'base_price' => $price->base_price,
                'tax_slab_id' => $product->tax_slab_id,
                'tax_percentage' => $product->tax_slab_id
                    ? (TaxSlab::find($product->tax_slab_id)?->tax_percentage ?? 0)
                    : 0,
                'tiers' => $price->pricing_type === 'tiered'
                    ? $price->tiers->map(fn($tier) => [
                        'min_qty' => $tier->min_qty,
                        'max_qty' => $tier->max_qty,
                        'price' => $tier->price,
                    ])
                    : [],
            ];
        });

        return response()->json(['units' => $units]);
    }

    /**
     * Get product variants for Variable products
     */
    public function getProductVariants(int $productId): JsonResponse
    {
        $product = AwProduct::with('variants')->find($productId);

        if (!$product) {
            return response()->json(['variants' => []]);
        }

        return response()->json([
            'variants' => $product->variants->map(fn($v) => [
                'id' => $v->id,
                'name' => $v->sku ?? $v->name ?? "Variant #{$v->id}",
                'tax_slab_id' => $v->tax_slab_id,
                'tax_percentage' => $v->tax_slab_id
                    ? (TaxSlab::find($v->tax_slab_id)?->tax_percentage ?? 0)
                    : 0,
            ]),
        ]);
    }

    /**
     * Get variant units for Variable products
     */
    public function getVariantUnits(int $variantId): JsonResponse
    {
        $prices = \App\Models\AwPrice::where('variant_id', $variantId)
            ->with(['belongs.unit', 'tiers'])
            ->get();

        $variantRecord = \App\Models\AwProductVariant::find($variantId);

        $units = $prices->map(function ($price) use ($variantRecord) {
            return [
                'id' => $price->unit_id,
                'name' => $price->belongs?->unit?->name ?? 'Unknown',
                'pricing_type' => $price->pricing_type,
                'base_price' => $price->base_price,
                'tax_slab_id' => $variantRecord?->tax_slab_id,
                'tax_percentage' => $variantRecord?->tax_slab_id
                    ? (TaxSlab::find($variantRecord->tax_slab_id)?->tax_percentage ?? 0)
                    : 0,
                'tiers' => $price->pricing_type === 'tiered'
                    ? $price->tiers->map(fn($tier) => [
                        'min_qty' => $tier->min_qty,
                        'max_qty' => $tier->max_qty,
                        'price' => $tier->price,
                    ])
                    : [],
            ];
        });

        return response()->json(['units' => $units]);
    }

    /**
     * Get bundle price for Bundle products
     */
    public function getBundlePrice(int $productId): JsonResponse
    {
        $product = AwProduct::with('bundle')->find($productId);

        if (!$product || !$product->bundle) {
            return response()->json(['price' => 0]);
        }

        return response()->json([
            'price' => $product->bundle->total ?? 0,
            'pricing_mode' => $product->bundle->pricing_mode,
        ]);
    }

    /**
     * Get warehouses for Select2
     */
    public function getWarehouses(Request $request): JsonResponse
    {
        $search = $request->get('search', '');

        $warehouses = \App\Models\AwWarehouse::W()
            ->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            })
            ->select('id', 'name', 'code')
            ->limit(20)
            ->get();

        return response()->json([
            'results' => $warehouses->map(fn($w) => [
                'id' => $w->id,
                'text' => "{$w->name} ({$w->code})",
            ]),
        ]);
    }

    /**
     * Get available stock for a product/variant/unit in a warehouse
     */
    public function getAvailableStock(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'warehouse_id' => 'required|exists:aw_warehouses,id',
            'product_id' => 'required|exists:aw_products,id',
            'variant_id' => 'nullable|exists:aw_product_variants,id',
            'unit_id' => 'nullable|exists:aw_product_units,id',
        ]);

        $query = \App\Models\AwSupplierWarehouseProduct::where('warehouse_id', $validated['warehouse_id'])
            ->where('product_id', $validated['product_id']);

        if (!empty($validated['variant_id'])) {
            $query->where('variant_id', $validated['variant_id']);
        } else {
            $query->whereNull('variant_id');
        }

        if (!empty($validated['unit_id'])) {
            $query->where('unit_id', $validated['unit_id']);
        }

        $stock = $query->first();

        return response()->json([
            'available_stock' => $stock ? $stock->quantity : 0,
        ]);
    }

    /**
     * Export orders
     */
    public function export(Request $request)
    {
        // Export implementation would go here
        // Using Laravel Excel or similar
        return response()->json(['message' => 'Export feature coming soon']);
    }
}

