<?php

namespace App\Http\Controllers\Frontend;

use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AwWishlist;
use App\Models\Location;
use App\Models\Country;
use App\Models\User;
use App\Models\TaxSlab;

class CustomerController extends Controller
{
    /**
     * Display customer's wishlist
     */
    public function wishlist()
    {
        $customerId = auth('customer')->id();

        $wishlistItems = AwWishlist::where('user_id', $customerId)
            ->whereNull('deleted_at')
            ->with(['product.primaryImage', 'product.bundle', 'variant'])
            ->get()
            ->map(function ($item) {
                $product = $item->product;
                if (!$product) {
                    return null;
                }

                $imageUrl = asset('no-image-found.jpg');
                if ($product->primaryImage) {
                    $imageUrl = asset('storage/' . $product->primaryImage->image_path);
                }

                $price = $this->getWishlistItemPrice($product, $item->variant);

                // Determine tax slab for this wishlist entry (display only)
                $taxSlab = null;
                if ($item->variant && $item->variant->tax_slab_id) {
                    $taxSlab = TaxSlab::find($item->variant->tax_slab_id);
                } elseif ($product->product_type === 'simple' && $product->tax_slab_id) {
                    $taxSlab = TaxSlab::find($product->tax_slab_id);
                }

                return [
                    'id' => $item->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_slug' => $product->slug,
                    'product_type' => $product->product_type,
                    'variant_id' => $item->variant_id,
                    'variant_name' => $item->variant ? $item->variant->name : null,
                    'image_url' => $imageUrl,
                    'price' => $price,
                    'tax_slab' => $taxSlab ? [
                        'name' => $taxSlab->name,
                        'percentage' => $taxSlab->tax_percentage,
                    ] : null,
                ];
            })
            ->filter()
            ->values();

        return view('frontend.wishlist', compact('wishlistItems'));
    }

    /**
     * Get wishlist item price based on product type
     */
    private function getWishlistItemPrice($product, $variant = null)
    {
        if ($product->product_type == 'bundle' && $product->bundle) {
            return (float) $product->bundle->total;
        }

        $priceQuery = \App\Models\AwPrice::where('product_id', $product->id)
            ->whereNull('deleted_at');

        if ($variant) {
            $priceQuery->where('variant_id', $variant->id);
        } else {
            $priceQuery->whereNull('variant_id');
        }

        $priceRecord = $priceQuery->first();

        if ($priceRecord && $priceRecord->tiers && count($priceRecord->tiers) > 0) {
            $minTier = collect($priceRecord->tiers)->sortBy('min_qty')->first();
            return (float) ($minTier['price'] ?? 0);
        }

        return $priceRecord ? (float) $priceRecord->base_price : 0;
    }

    /**
     * Display customer's addresses
     */
    public function addresses()
    {
        $customerId = auth('customer')->id();

        $addresses = Location::where('customer_id', $customerId)
            ->with(['country', 'state', 'city'])
            ->orderBy('created_at', 'desc')
            ->get();

        $countries = Country::orderBy('name')->get();

        return view('frontend.addresses', compact('addresses', 'countries'));
    }

    /**
     * Store a new address
     */
    public function storeAddress(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'country_id' => 'required|exists:countries,id',
            'state_id' => 'required|exists:states,id',
            'city_id' => 'nullable|exists:cities,id',
            'zipcode' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'contact_number' => 'required|string|max:30',
            'fax' => 'nullable|string|max:30',
        ]);

        if ($request->ajax() && $validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        } elseif ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $customerId = auth('customer')->id();

        Location::create([
            'customer_id' => $customerId,
            'name' => $request->name,
            'address_line_1' => $request->address_line_1,
            'address_line_2' => $request->address_line_2,
            'country_id' => $request->country_id,
            'state_id' => $request->state_id,
            'city_id' => $request->city_id,
            'zipcode' => $request->zipcode,
            'email' => $request->email,
            'contact_number' => $request->contact_number,
            'fax' => $request->fax,
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Address added successfully.'
            ]);
        }

        return redirect()->route('customer.addresses')->with('success', 'Address added successfully.');
    }

    /**
     * Update an existing address
     */
    public function updateAddress(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'country_id' => 'required|exists:countries,id',
            'state_id' => 'required|exists:states,id',
            'city_id' => 'required|exists:cities,id',
            'zipcode' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'contact_number' => 'required|string|max:30',
            'fax' => 'nullable|string|max:30',
        ]);

        $customerId = auth('customer')->id();
        $address = Location::where('id', $id)->where('customer_id', $customerId)->firstOrFail();

        $address->update([
            'name' => $request->name,
            'address_line_1' => $request->address_line_1,
            'address_line_2' => $request->address_line_2,
            'country_id' => $request->country_id,
            'state_id' => $request->state_id,
            'city_id' => $request->city_id,
            'zipcode' => $request->zipcode,
            'email' => $request->email,
            'contact_number' => $request->contact_number,
            'fax' => $request->fax,
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Address updated successfully.'
            ]);
        }

        return redirect()->route('customer.addresses')->with('success', 'Address updated successfully.');
    }

    /**
     * Delete an address
     */
    public function deleteAddress(Request $request, $id)
    {
        $customerId = auth('customer')->id();
        $address = Location::where('id', $id)->where('customer_id', $customerId)->firstOrFail();

        $address->delete();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Address deleted successfully.'
            ]);
        }

        return redirect()->route('customer.addresses')->with('success', 'Address deleted successfully.');
    }

    /**
     * Display customer's profile
     */
    public function profile()
    {
        $customer = auth('customer')->user();

        return view('frontend.profile', compact('customer'));
    }

    /**
     * Update customer's profile
     */
    public function updateProfile(Request $request)
    {
        $customerId = auth('customer')->id();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $customerId,
            'phone' => 'nullable|string|max:30',
        ]);

        $customer = User::findOrFail($customerId);

        $emailChanged = $customer->email !== $request->email;

        $customer->update([
            'name' => $request->name,
            'phone' => $request->phone,
        ]);

        // If email changed, send verification email
        if ($emailChanged) {
            $token = \Illuminate\Support\Str::random(64);

            $customer->update([
                'email' => $request->email,
                'email_verified_at' => null,
                'verification_token' => $token,
                'verification_token_expires_at' => now()->addMinutes(30),
            ]);

            \App\Jobs\SendVerificationEmail::dispatch($customer, $token);

            return redirect()->route('customer.profile')
                ->with('success', 'Profile updated. Please verify your new email address.');
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully.'
            ]);
        }

        return redirect()->route('customer.profile')->with('success', 'Profile updated successfully.');
    }

    /**
     * Display customer dashboard with statistics
     */
    public function dashboard(Request $request)
    {
        $customerId = auth('customer')->id();
        $customer = auth('customer')->user();

        // Default date range (last 30 days)
        $startDate = $request->get('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        // Get statistics
        $stats = $this->getStats($customerId, $startDate, $endDate);

        // Get monthly spending for chart (last 12 months)
        $spendingData = $this->getMonthlySpending($customerId);

        // Get order status distribution
        $statusData = $this->getOrderStatusDistribution($customerId);

        // Get recent orders
        $recentOrders = \App\Models\AwOrder::forCustomer($customerId)
            ->with('items')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('frontend.dashboard', compact(
            'customer',
            'stats',
            'spendingData',
            'statusData',
            'recentOrders',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Get dashboard statistics
     */
    private function getStats(int $customerId, string $startDate, string $endDate): array
    {
        $orders = \App\Models\AwOrder::forCustomer($customerId)
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

        $totalOrders = (clone $orders)->count();
        $totalSpent = (clone $orders)->sum('grand_total');
        $pendingOrders = (clone $orders)->where('status', 'pending')->count();
        $deliveredOrders = (clone $orders)->where('status', 'delivered')->count();

        // All time stats
        $allTimeOrders = \App\Models\AwOrder::forCustomer($customerId)->count();
        $allTimeSpent = \App\Models\AwOrder::forCustomer($customerId)->sum('grand_total');

        // Credit balance
        $creditBalance = auth('customer')->user()->credit_balance ?? 0;

        return [
            'total_orders' => $totalOrders,
            'total_spent' => $totalSpent,
            'pending_orders' => $pendingOrders,
            'delivered_orders' => $deliveredOrders,
            'all_time_orders' => $allTimeOrders,
            'all_time_spent' => $allTimeSpent,
            'credit_balance' => $creditBalance,
        ];
    }

    /**
     * Get monthly spending data for chart
     */
    private function getMonthlySpending(int $customerId): array
    {
        $data = \App\Models\AwOrder::forCustomer($customerId)
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, SUM(grand_total) as total')
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->toArray();

        // Fill in missing months
        $labels = [];
        $values = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i)->format('Y-m');
            $labels[] = now()->subMonths($i)->format('M Y');
            $values[] = (float) ($data[$month] ?? 0);
        }

        return ['labels' => $labels, 'values' => $values];
    }

    /**
     * Get order status distribution for chart
     */
    private function getOrderStatusDistribution(int $customerId): array
    {
        $data = \App\Models\AwOrder::forCustomer($customerId)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $statuses = \App\Models\AwOrder::getStatuses();
        $colors = [
            'pending' => '#FFC107',
            'confirmed' => '#17A2B8',
            'processing' => '#007BFF',
            'packed' => '#6C757D',
            'shipped' => '#20C997',
            'delivered' => '#28A745',
            'cancelled' => '#DC3545',
            'rejected' => '#343A40',
            'returned' => '#FD7E14',
        ];

        $labels = [];
        $values = [];
        $backgroundColors = [];

        foreach ($data as $status => $count) {
            $labels[] = $statuses[$status] ?? ucfirst($status);
            $values[] = $count;
            $backgroundColors[] = $colors[$status] ?? '#6C757D';
        }

        return [
            'labels' => $labels,
            'values' => $values,
            'colors' => $backgroundColors,
        ];
    }

    /**
     * AJAX endpoint for dashboard stats
     */
    public function getDashboardStats(Request $request)
    {
        $customerId = auth('customer')->id();
        $startDate = $request->get('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        $stats = $this->getStats($customerId, $startDate, $endDate);

        return response()->json(['success' => true, 'stats' => $stats]);
    }

    /**
     * Display orders listing page
     */
    public function orders()
    {
        $statuses = \App\Models\AwOrder::getStatuses();
        $paymentStatuses = \App\Models\AwOrder::getPaymentStatuses();

        return view('frontend.orders', compact('statuses', 'paymentStatuses'));
    }

    /**
     * DataTable AJAX endpoint for orders
     */
    public function ordersData(Request $request)
    {
        $customerId = auth('customer')->id();

        $query = \App\Models\AwOrder::forCustomer($customerId)
            ->with('items');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ]);
        }

        // Search
        if ($request->filled('search.value')) {
            $search = $request->input('search.value');
            $query->where('order_number', 'like', "%{$search}%");
        }

        // Total records
        $totalRecords = \App\Models\AwOrder::forCustomer($customerId)->count();
        $filteredRecords = $query->count();

        // Ordering
        $orderColumn = $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'desc');
        $columns = ['order_number', 'created_at', 'items_count', 'grand_total', 'status', 'payment_status'];
        $orderBy = $columns[$orderColumn] ?? 'created_at';

        if ($orderBy === 'items_count') {
            $query->withCount('items')->orderBy('items_count', $orderDir);
        } else {
            $query->orderBy($orderBy, $orderDir);
        }

        // Pagination
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $orders = $query->skip($start)->take($length)->get();

        // Format data
        $data = $orders->map(function ($order) {
            return [
                'order_number' => $order->order_number,
                'date' => $order->created_at->format('M d, Y'),
                'items_count' => $order->items->count(),
                'grand_total' => '$' . number_format($order->grand_total, 2),
                'status' => $order->status_badge,
                'payment_status' => $order->payment_status_badge,
                'actions' => '<a href="' . route('customer.order.detail', $order->id) . '" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i> View</a>',
            ];
        });

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data,
        ]);
    }

    /**
     * Display order detail page
     */
    public function orderDetail($id)
    {
        $customerId = auth('customer')->id();

        $order = \App\Models\AwOrder::forCustomer($customerId)
            ->with([
                'items.product.primaryImage',
                'items.variant',
                'items.unit',
                'statusHistory.changedByUser',
                'shippingCountry',
                'shippingState',
                'shippingCity',
                'billingCountry',
                'billingState',
                'billingCity',
            ])
            ->findOrFail($id);

        // Format items with images
        $orderItems = $order->items->map(function ($item) {
            $imageUrl = asset('no-image-found.jpg');
            if ($item->product && $item->product->primaryImage) {
                $imageUrl = asset('storage/' . $item->product->primaryImage->image_path);
            }

            return [
                'id' => $item->id,
                'product_name' => $item->product_name,
                'sku' => $item->sku,
                'variant_name' => $item->variant?->name,
                'unit_name' => $item->unit?->name,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'discount_amount' => $item->discount_amount,
                'total' => $item->total,
                'image_url' => $imageUrl,
                'is_free_item' => $item->is_free_item ?? false,
                'is_bundle' => $item->is_bundle_parent ?? false,
            ];
        });

        return view('frontend.order-detail', compact('order', 'orderItems'));
    }
}
