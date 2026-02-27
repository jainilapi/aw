<?php

namespace App\Http\Controllers;

use App\Models\AwProductUnit;
use App\Models\AwPromotion;
use App\Models\AwCategory;
use App\Models\AwProduct;
use App\Models\AwProductVariant;
use App\Models\AwUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PromotionController extends Controller
{
    protected $title = 'Promotions';
    protected $view = 'promotions.';

    /**
     * Display listing of promotions
     */
    public function index()
    {
        if (request()->ajax()) {
            return $this->ajax();
        }

        $title = $this->title;
        $subTitle = 'Manage promotions and offers';
        return view($this->view . 'index', compact('title', 'subTitle'));
    }

    /**
     * Ajax DataTable handler
     */
    public function ajax()
    {
        $query = AwPromotion::query();

        return datatables()
            ->eloquent($query)
            ->addColumn('type_label', fn($row) => $row->type_label)
            ->addColumn('status_badge', fn($row) => $row->status_badge)
            ->addColumn('date_range', function ($row) {
                $start = $row->start_date ? $row->start_date->format('d M Y H:i') : '—';
                $end = $row->end_date ? $row->end_date->format('d M Y H:i') : '—';
                return "$start<br><small class='text-muted'>to</small><br>$end";
            })
            ->addColumn('discount_info', function ($row) {
                if ($row->type === 'buyxgetx') {
                    return 'Buy ' . ($row->x_quantity ?? 1) . ' Get ' . ($row->y_quantity ?? 1);
                }
                $symbol = $row->discount_type ? '$' : '%';
                return ($row->discount_amount ?? 0) . $symbol;
            })
            ->addColumn('action', function ($row) {
                $html = '';
                if (auth()?->user()?->isAdmin() || auth()->guard('web')->user()->can('promotions.edit')) {
                    $html .= '<a href="' . route('promotions.edit', encrypt($row->id)) . '" class="btn btn-sm btn-primary"><i class="fa fa-edit"></i></a>&nbsp;';
                }
                if (auth()?->user()?->isAdmin() || auth()->guard('web')->user()->can('promotions.destroy')) {
                    $html .= '<button type="button" class="btn btn-sm btn-danger" id="deleteRow" data-row-route="' . route('promotions.destroy', $row->id) . '"><i class="fa fa-trash"></i></button>';
                }
                return $html;
            })
            ->rawColumns(['action', 'status_badge', 'date_range'])
            ->addIndexColumn()
            ->toJson();
    }

    /**
     * Show create form
     */
    public function create()
    {
        $title = $this->title;
        $subTitle = 'Add New Promotion';
        return view($this->view . 'create', compact('title', 'subTitle'));
    }

    /**
     * Store new promotion
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:catdisc,prodisc,cardisc,buyxgetx',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:100|unique:aw_promotions,code',
            'poster' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
            'description' => 'required|string',
            'how_to_use' => 'required|string',
            'terms_and_condition' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'auto_applicable' => 'nullable|boolean',
            'application_limit' => 'required|integer|min:1',
            'status' => 'nullable|boolean',
            // Type-specific validation
            'category_id' => 'required_if:type,catdisc|nullable|array',
            'category_id.*' => 'exists:aw_categories,id',
            'product_id' => 'required_if:type,prodisc|nullable|array',
            'product_id.*' => 'exists:aw_products,id',
            'variant_id' => 'nullable|array',
            'unit_id' => 'nullable|array',
            'discount_type' => 'required_unless:type,buyxgetx|nullable|boolean',
            'discount_amount' => 'required_unless:type,buyxgetx|nullable|numeric|min:0',
            'cart_minimum_amount' => 'required_if:type,cardisc|nullable|numeric|min:0',
            // Buy X Get Y
            'x_product' => 'required_if:type,buyxgetx|nullable|exists:aw_products,id',
            'x_variant' => 'nullable|exists:aw_product_variants,id',
            'x_unit' => 'required_if:type,buyxgetx|nullable|integer',
            'x_quantity' => 'required_if:type,buyxgetx|nullable|integer|min:1',
            'y_item' => 'required_if:type,buyxgetx|nullable|exists:aw_products,id',
            'y_variant' => 'nullable|exists:aw_product_variants,id',
            'y_unit' => 'required_if:type,buyxgetx|nullable|integer',
            'y_quantity' => 'required_if:type,buyxgetx|nullable|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $data = [
                'type' => $validated['type'],
                'name' => $validated['name'],
                'code' => strtoupper($validated['code']),
                'description' => $validated['description'],
                'how_to_use' => $validated['how_to_use'],
                'terms_and_condition' => $validated['terms_and_condition'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'auto_applicable' => (bool) ($request->input('auto_applicable', false)),
                'application_limit' => $validated['application_limit'],
                'status' => (bool) ($request->input('status', false)),
            ];

            // Type-specific data
            switch ($validated['type']) {
                case 'catdisc':
                    $data['category_id'] = $validated['category_id'] ?? [];
                    $data['discount_type'] = (bool) ($validated['discount_type'] ?? false);
                    $data['discount_amount'] = $validated['discount_amount'] ?? 0;
                    break;

                case 'prodisc':
                    $data['product_id'] = $validated['product_id'] ?? [];
                    $data['variant_id'] = $validated['variant_id'] ?? [];
                    $data['unit_id'] = $validated['unit_id'] ?? [];
                    $data['discount_type'] = (bool) ($validated['discount_type'] ?? false);
                    $data['discount_amount'] = $validated['discount_amount'] ?? 0;
                    break;

                case 'cardisc':
                    $data['cart_minimum_amount'] = $validated['cart_minimum_amount'] ?? 0;
                    $data['discount_type'] = (bool) ($validated['discount_type'] ?? false);
                    $data['discount_amount'] = $validated['discount_amount'] ?? 0;
                    break;

                case 'buyxgetx':
                    $data['x_product'] = $validated['x_product'] ?? null;
                    $data['x_variant'] = $validated['x_variant'] ?? null;
                    $data['x_unit'] = $validated['x_unit'] ?? null;
                    $data['x_quantity'] = $validated['x_quantity'] ?? 1;
                    $data['y_item'] = $validated['y_item'] ?? null;
                    $data['y_variant'] = $validated['y_variant'] ?? null;
                    $data['y_unit'] = $validated['y_unit'] ?? null;
                    $data['y_quantity'] = $validated['y_quantity'] ?? 1;
                    break;
            }

            // Handle poster upload
            $destinationPath = storage_path('app/public/promotions');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            if ($request->hasFile('poster')) {
                $file = $request->file('poster');
                $filename = 'PROMO-' . date('YmdHis') . '-' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move($destinationPath, $filename);
                $data['posters'] = $filename;
            }

            AwPromotion::create($data);
            DB::commit();

            return redirect()->route('promotions.index')->with('success', 'Promotion created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Error creating promotion: ' . $e->getMessage());
        }
    }

    /**
     * Show edit form
     */
    public function edit(string $id)
    {
        $promotion = AwPromotion::findOrFail(decrypt($id));
        $title = $this->title;
        $subTitle = 'Edit Promotion';

        // Pre-load related data for Select2
        $selectedCategories = $promotion->category_id ? AwCategory::whereIn('id', $promotion->category_id)->get(['id', 'name']) : collect();
        $selectedProducts = $promotion->product_id ? AwProduct::whereIn('id', $promotion->product_id)->get(['id', 'name', 'product_type']) : collect();

        return view($this->view . 'edit', compact('title', 'subTitle', 'promotion', 'selectedCategories', 'selectedProducts'));
    }

    /**
     * Update promotion
     */
    public function update(Request $request, string $id)
    {
        $promotion = AwPromotion::findOrFail(decrypt($id));

        $validated = $request->validate([
            'type' => 'required|in:catdisc,prodisc,cardisc,buyxgetx',
            'name' => 'required|string|max:255',
            'code' => ['required', 'string', 'max:100', Rule::unique('aw_promotions', 'code')->ignore($promotion->id)],
            'poster' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'description' => 'required|string',
            'how_to_use' => 'required|string',
            'terms_and_condition' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'auto_applicable' => 'nullable|boolean',
            'application_limit' => 'required|integer|min:1',
            'status' => 'nullable|boolean',
            'category_id' => 'required_if:type,catdisc|nullable|array',
            'product_id' => 'required_if:type,prodisc|nullable|array',
            'variant_id' => 'nullable|array',
            'unit_id' => 'nullable|array',
            'discount_type' => 'required_unless:type,buyxgetx|nullable|boolean',
            'discount_amount' => 'required_unless:type,buyxgetx|nullable|numeric|min:0',
            'cart_minimum_amount' => 'required_if:type,cardisc|nullable|numeric|min:0',
            'x_product' => 'required_if:type,buyxgetx|nullable|exists:aw_products,id',
            'x_variant' => 'nullable|exists:aw_product_variants,id',
            'x_unit' => 'required_if:type,buyxgetx|nullable|integer',
            'x_quantity' => 'required_if:type,buyxgetx|nullable|integer|min:1',
            'y_item' => 'required_if:type,buyxgetx|nullable|exists:aw_products,id',
            'y_variant' => 'nullable|exists:aw_product_variants,id',
            'y_unit' => 'required_if:type,buyxgetx|nullable|integer',
            'y_quantity' => 'required_if:type,buyxgetx|nullable|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $data = [
                'type' => $validated['type'],
                'name' => $validated['name'],
                'code' => strtoupper($validated['code']),
                'description' => $validated['description'],
                'how_to_use' => $validated['how_to_use'],
                'terms_and_condition' => $validated['terms_and_condition'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'auto_applicable' => (bool) ($request->input('auto_applicable', false)),
                'application_limit' => $validated['application_limit'],
                'status' => (bool) ($request->input('status', false)),
                // Reset all type-specific fields
                'category_id' => null,
                'product_id' => null,
                'variant_id' => null,
                'unit_id' => null,
                'discount_type' => null,
                'discount_amount' => null,
                'cart_minimum_amount' => null,
                'x_product' => null,
                'x_variant' => null,
                'x_unit' => null,
                'x_quantity' => null,
                'y_item' => null,
                'y_variant' => null,
                'y_unit' => null,
                'y_quantity' => null,
            ];

            // Type-specific data
            switch ($validated['type']) {
                case 'catdisc':
                    $data['category_id'] = $validated['category_id'] ?? [];
                    $data['discount_type'] = (bool) ($validated['discount_type'] ?? false);
                    $data['discount_amount'] = $validated['discount_amount'] ?? 0;
                    break;

                case 'prodisc':
                    $data['product_id'] = $validated['product_id'] ?? [];
                    $data['variant_id'] = $validated['variant_id'] ?? [];
                    $data['unit_id'] = $validated['unit_id'] ?? [];
                    $data['discount_type'] = (bool) ($validated['discount_type'] ?? false);
                    $data['discount_amount'] = $validated['discount_amount'] ?? 0;
                    break;

                case 'cardisc':
                    $data['cart_minimum_amount'] = $validated['cart_minimum_amount'] ?? 0;
                    $data['discount_type'] = (bool) ($validated['discount_type'] ?? false);
                    $data['discount_amount'] = $validated['discount_amount'] ?? 0;
                    break;

                case 'buyxgetx':
                    $data['x_product'] = $validated['x_product'] ?? null;
                    $data['x_variant'] = $validated['x_variant'] ?? null;
                    $data['x_unit'] = $validated['x_unit'] ?? null;
                    $data['x_quantity'] = $validated['x_quantity'] ?? 1;
                    $data['y_item'] = $validated['y_item'] ?? null;
                    $data['y_variant'] = $validated['y_variant'] ?? null;
                    $data['y_unit'] = $validated['y_unit'] ?? null;
                    $data['y_quantity'] = $validated['y_quantity'] ?? 1;
                    break;
            }

            // Handle poster upload
            if ($request->hasFile('poster')) {
                $destinationPath = storage_path('app/public/promotions');
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }

                // Delete old poster if exists
                if ($promotion->posters) {
                    $oldFile = $destinationPath . '/' . $promotion->posters;
                    if (file_exists($oldFile)) {
                        unlink($oldFile);
                    }
                }

                $file = $request->file('poster');
                $filename = 'PROMO-' . date('YmdHis') . '-' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move($destinationPath, $filename);
                $data['posters'] = $filename;
            }

            $promotion->update($data);
            DB::commit();

            return redirect()->route('promotions.index')->with('success', 'Promotion updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Error updating promotion: ' . $e->getMessage());
        }
    }

    /**
     * Delete promotion
     */
    public function destroy(string $id)
    {
        $promotion = AwPromotion::findOrFail($id);
        $promotion->delete();
        return response()->json(['success' => 'Promotion deleted successfully.']);
    }

    /**
     * Get categories for Select2 with pagination
     */
    public function getCategories(Request $request)
    {
        $search = $request->input('searchQuery', '');
        $page = $request->input('page', 1);
        $perPage = 20;

        $query = AwCategory::where('status', 1);

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        $total = $query->count();
        $categories = $query->orderBy('name')
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get(['id', 'name']);

        return response()->json([
            'items' => $categories->map(fn($c) => ['id' => $c->id, 'text' => $c->name]),
            'pagination' => ['more' => ($page * $perPage) < $total]
        ]);
    }

    /**
     * Get products for Select2 with pagination
     */
    public function getProducts(Request $request)
    {
        $search = $request->input('searchQuery', '');
        $page = $request->input('page', 1);
        $perPage = 20;

        $query = AwProduct::where('status', 1);

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        $total = $query->count();
        $products = $query->orderBy('name')
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get(['id', 'name', 'product_type']);

        return response()->json([
            'items' => $products->map(fn($p) => [
                'id' => $p->id,
                'text' => $p->name,
                'type' => $p->product_type
            ]),
            'pagination' => ['more' => ($page * $perPage) < $total]
        ]);
    }

    /**
     * Get variants for a product
     */
    public function getVariants(int $productId)
    {
        $product = AwProduct::find($productId);

        if (!$product || $product->product_type !== 'variable') {
            return response()->json(['variants' => []]);
        }

        $variants = AwProductVariant::where('product_id', $productId)
            ->where('status', 1)
            ->get()
            ->map(function ($v) {
                return [
                    'id' => $v->id,
                    'text' => $v->name ?? "Variant #{$v->id}"
                ];
            });

        return response()->json(['variants' => $variants]);
    }

/**
     * Get units for a variant
     */
    public function getUnits(int $variantId)
    {
        $units = collect();
        
        // Base unit
        $baseUnit = AwProductUnit::with('unit')
            ->where('is_base', 1)
            ->where('variant_id', $variantId)
            ->first();
            
        if ($baseUnit) {
            $units->push([
                'id' => $baseUnit->id,
                'text' => $baseUnit->unit?->name ?? 'Base Unit',
                'type' => 'base',
                'price' => $baseUnit->price ?? 0
            ]);
        }

        $additionalUnits = AwProductUnit::with('unit')
            ->where('is_base', 0)
            ->where('variant_id', $variantId)
            ->get();
            
        foreach ($additionalUnits as $au) {
            $units->push([
                'id' => $au->id,
                'text' => $au->unit?->name ?? 'Additional Unit',
                'type' => 'additional',
                'price' => $au->price ?? 0
            ]);
        }

        return response()->json(['units' => $units]);
    }

    /**
     * Get units for a simple product
     */
    public function getSimpleProductUnits(int $productId)
    {
        $units = collect();
        
        // Base unit
        $baseUnit = AwProductUnit::with('unit')
            ->where('is_base', 1)
            ->where('product_id', $productId)
            ->whereNull('variant_id')
            ->first();
            
        if ($baseUnit) {
            $units->push([
                'id' => $baseUnit->id,
                'text' => $baseUnit->unit?->name ?? 'Base Unit',
                'type' => 'base',
                'price' => $baseUnit->price ?? 0
            ]);
        }

        // Additional units
        $additionalUnits = AwProductUnit::with('unit')
            ->where('product_id', $productId)
            ->whereNull('variant_id')
            ->where('is_base', 0)
            ->get();
            
        foreach ($additionalUnits as $au) {
            $units->push([
                'id' => $au->id,
                'text' => $au->unit?->name ?? 'Additional Unit',
                'type' => 'additional',
                'price' => $au->price ?? 0
            ]);
        }

        return response()->json(['units' => $units]);
    }
}
