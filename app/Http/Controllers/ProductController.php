<?php

namespace App\Http\Controllers;

use App\Models\{AwSupplierWarehouseProduct, AwInventoryMovement, AwProduct};
use App\Http\Controllers\VariableProductController;
use App\Http\Controllers\BundledProductController;
use App\Http\Controllers\SimpleProductController;
use Illuminate\Support\Facades\{Log, DB};
use \App\Models\ProductImport;
use Illuminate\Http\Request;
use App\Helpers\Helper;

class ProductController extends Controller
{
    protected $title = 'Products';
    protected $view = 'products.';

    public function __construct()
    {
        $this->middleware('permission:products.index')->only(['index']);
        $this->middleware('permission:product-management')->only(['steps']);
    }

    public function index()
    {
        if (request()->ajax()) {
            return $this->ajax();
        }

        $title = $this->title;
        $subTitle = 'Manage products here';
        return view($this->view . 'index', compact('title', 'subTitle'));
    }

    public function ajax()
    {
        $query = AwProduct::query();

        return datatables()
            ->eloquent($query)
            ->addColumn('action', function ($row) {
                $html = '';
                if (auth()?->user()?->isAdmin() || auth()->guard('web')->user()->can('products.edit')) {
                    $html .= '<a href="' . route('product-management', ['type' => encrypt($row->product_type), 'step' => encrypt(1), 'id' => encrypt($row->id)]) . '" class="btn btn-sm btn-primary"> <i class="fa fa-edit"> </i> </a>&nbsp;';
                }

                return $html;
            })
            ->addColumn('category_name', function ($row) {
                $categories = $row->categories->pluck('name')->toArray();
                return implode(', ', $categories);
            })
            ->addColumn('status_badge', function ($row) {
                return $row->status ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>';
            })
            ->addColumn('stock_badge', function ($row) {
                return $row->in_stock ? '<span class="badge bg-info">In stock</span>' : '<span class="badge bg-warning">Out of stock</span>';
            })
            ->editColumn('product_type', function ($row) {
                if ($row->product_type == 'simple') {
                    return "Simple";
                } else if ($row->product_type == 'variable') {
                    return "Variable";
                } else if ($row->product_type == 'bundle') {
                    return "Bundle";
                } else {
                    return "Unknown";
                }
            })
            ->rawColumns(['action', 'product_type', 'status_badge', 'stock_badge'])
            ->addIndexColumn()
            ->toJson();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function steps(Request $request, $type = null, $step = null, $id = null)
    {
        $notFoundMessage = 'You are lost';

        if (empty($type) || empty($step) || !Helper::isValidEncryption($type) || !Helper::isValidEncryption($step)) {
            abort(404, $notFoundMessage);
        }

        $type = decrypt($type);

        if (!in_array($type, ['simple', 'variable', 'bundle'])) {
            abort(404, $notFoundMessage);
        }

        $step = decrypt($step);

        if ($type == 'simple' && !($step >= 1 && $step <= 7)) {
            abort(404, $notFoundMessage);
        }

        if ($type == 'variable' && !($step >= 1 && $step <= 8)) {
            abort(404, $notFoundMessage);
        }

        if ($type == 'bundle' && !($step >= 1 && $step <= 3)) {
            abort(404, $notFoundMessage);
        }

        if (empty($id)) {
            $product = AwProduct::create([
                'name' => 'Untitled Product',
                'slug' => uniqid() . '-' . uniqid(),
                'product_type' => $type
            ]);

            return redirect()->route('product-management', ['type' => encrypt($type), 'step' => encrypt($step), 'id' => encrypt($product->id)]);
        }

        $id = decrypt($id);
        $product = AwProduct::find($id);
        $product->product_type = $type;
        $product->save();

        if ($request->method() == 'GET') {
            if ($type == 'simple') {
                return SimpleProductController::view($product, $step, $type);
            }

            if ($type == 'variable') {
                return VariableProductController::view($product, $step, $type);
            }

            if ($type == 'bundle') {
                return BundledProductController::view($product, $step, $type);
            }
        } else {
            if ($type == 'simple') {
                return SimpleProductController::store($request, $step, $id, $type);
            }

            if ($type == 'variable') {
                return VariableProductController::store($request, $step, $id);
            }

            if ($type == 'bundle') {
                return BundledProductController::store($request, $step, $id);
            }
        }
    }

    public function getHistory($productId, $warehouseId)
    {
        $history = AwInventoryMovement::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->when(request()->has('variant_id'), function ($builder) {
                $builder->where('variant_id', request('variant_id'));
            })
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return response()->json($history);
    }

    public function adjust(Request $request)
    {
        $request->validate([
            'mapping_id' => 'required|exists:aw_supplier_warehouse_products,id',
            'adjustment_qty' => 'required|numeric',
            'reason' => 'required|string|max:255'
        ]);

        return DB::transaction(function () use ($request) {
            $mapping = AwSupplierWarehouseProduct::lockForUpdate()->find($request->mapping_id);

            $oldQty = $mapping->quantity;
            $adjustment = $request->adjustment_qty;
            $newQty = $oldQty + $adjustment;

            $mapping->update(['quantity' => $newQty]);

            AwInventoryMovement::create([
                'product_id'   => $mapping->product_id,
                'variant_id'   => $mapping->variant_id,
                'unit_id'      => $mapping->unit_id,
                'warehouse_id' => $mapping->warehouse_id,
                'quantity_change' => $adjustment,
                'reason'       => 'adjustment',
                'reference'    => $request->reason,
                'reference_id' => $mapping->id
            ]);

            return response()->json([
                'success' => true,
                'new_qty' => $newQty,
                'message' => 'Stock adjusted successfully.'
            ]);
        });
    }

    public function searchSubstitutes(Request $request)
    {
        $search = $request->get('q');
        $excludeId = $request->get('exclude');

        $products = AwProduct::query()
            ->with('brand')
            ->where('id', '!=', $excludeId)
            ->where(function ($query) use ($search) {
                $query->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('slug', 'LIKE', "%{$search}%");

                $query->orWhereHas('variants', function ($q) use ($search) {
                    $q->where('sku', 'LIKE', "%{$search}%")
                        ->orWhere('barcode', 'LIKE', "%{$search}%");
                });
            })
            ->limit(10)
            ->get();

        return response()->json($products->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'brand_name' => $product->brand?->name ?? 'No Brand',
                'image_path' => $product->images->where('position', 0)->first()?->image_path
                    ? asset('storage/' . $product->images->where('position', 0)->first()->image_path)
                    : asset('assets/images/default-product.png')
            ];
        }));
    }

    public function import(Request $request) 
    {
        ini_set('max_execution_time', 1000);
        ini_set('memory_limit', '-1');

        $request->validate([
            'file' => 'required|mimes:xlsx,zip'
        ]);

        DB::beginTransaction();
        try {

            $destinationPath = storage_path('app/public/product-imports');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            $destinationPath2 = storage_path('app/public/product-images');
            if (!file_exists($destinationPath2)) {
                mkdir($destinationPath2, 0755, true);
            }

            $fileName = date('YmdHis') . uniqid() . $request->file('file')->getClientOriginalName();
            $request->file('file')->move($request->type == 1 ? $destinationPath2 : $destinationPath, $fileName);

            ProductImport::create([
                'override' => $request->filled('override') && $request->override ? 1 : 0,
                'type' => $request->type,
                'file' => $fileName,
                'imported_by' => auth()->guard('web')->user()->id
            ]);

            DB::commit();
            return back()->with('success', 'Data will be imported shortly.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error importing products: ' . $e->getMessage());
        }
    }

    public function importInventory(Request $request)
    {
        ini_set('max_execution_time', 1000);
        ini_set('memory_limit', '-1');

        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        DB::beginTransaction();
        try {
            $destinationPath = storage_path('app/public/inventory-imports');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            $fileName = date('YmdHis') . uniqid() . $request->file('file')->getClientOriginalName();
            $request->file('file')->move($destinationPath, $fileName);

            ProductImport::create([
                'override' => $request->filled('override') && $request->override ? 1 : 0,
                'type' => $request->type,
                'file' => $fileName,
                'imported_by' => auth()->guard('web')->user()->id
            ]);

            DB::commit();
            return back()->with('success', 'Data will be imported shortly.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error importing inventory: ' . $e->getMessage());
        }
    }

    public function getImportHistory(Request $request) {
        $query = ProductImport::latest();

        return datatables()
        ->eloquent($query)
        ->editColumn('file', function ($row) {
            return $row->file;
        })
        ->editColumn('type', function ($row) {
            if ($row->type == 3) {
                return '<span class="badge bg-primary">Supplier Import</span>';
            }
            return $row->type == 2 ? '<span class="badge bg-info">Product Inventory Import</span>' : (
                $row->type == 1 ? '<span class="badge bg-success">Product Image Import</span>' : '<span class="badge bg-secondary">Product Excel Import</span>'
            );
        })
        ->editColumn('status', function ($row) {
            return 
                $row->status == 'pending' ? '<span class="badge bg-warning">Pending</span>' : (
                    ($row->status == 'in-queue' ? '<span class="badge bg-info">In-Queue</span>' : (
                        $row->status == 'imported' ? '<span class="badge bg-success">Imported</span>' : '<span class="badge bg-danger">Failed</span>'
                    ))
                );
        })
        ->editColumn('imported_by', function ($row) {
            return $row->user->name ?? 'N/A';
        })
        ->editColumn('uploaded_at', function ($row) {
            return date('d F Y, H:i', strtotime($row->created_at));
        })
        ->rawColumns(['action', 'status', 'stock_badge', 'type'])
        ->addIndexColumn()
        ->toJson();
    }
}
