<?php

namespace App\Http\Controllers;

use App\Models\{User, AwProduct, AwAttribute, AwAttributeValue, AwVariantAttributeValue, AwProductVariant, AwWarehouse, AwBrand, AwCategory, AwTag, AwProductTag, AwProductUnit, AwSupplierWarehouseProduct, AwInventoryMovement, AwProductImage, AwUnit, AwPrice, AwPriceTier, AwProductCategory};
use App\Models\{AwBundle, AwBundleItem};
use Illuminate\Support\Facades\{Storage, Log, DB};
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BundledProductController extends Controller
{
    public static function view($product, $step, $type)
    {
        $product = AwProduct::findOrFail($product->id);
        $brands = AwBrand::active()->get();
        $units = AwUnit::get();
        $allTags = AwTag::get();
        $productTagIds = $product->tags->pluck('id')->toArray();
        $mainImage = $product->images->where('position', 0)->first();
        $gallery = $product->images->where('position', '>', 0)->sortBy('position');
        $baseProductUnit = $product->units->where('is_base', 1)->first();
        $additionalUnits = $product->units->where('is_base', 0)->sortBy('conversion_factor')->values();
        $allUnits = $product->units->sortByDesc('is_base');
        $warehouses = AwWarehouse::all();
        $suppliers = User::whereHas('roles', function ($q) {
            $q->where('slug', 'supplier');
        })->get();
        $existingInventory = $product->supplierWarehouseProducts;

        $categories = AwCategory::buildCategoryTree();
        $additionalCategories = AwCategory::where('status', 1)->orderBy('name')->get();
        $selectedPrimaryCategory = null;
        $selectedAdditionalCategories = [];

        $primaryCategory = AwProductCategory::where('product_id', $product->id)
            ->where('is_primary', 1)
            ->first();

        $selectedPrimaryCategory = $primaryCategory ? $primaryCategory->category_id : null;
        $selectedAdditionalCategories = AwProductCategory::where('product_id', $product->id)
            ->where('is_primary', 0)
            ->pluck('category_id')
            ->toArray();

        $bundle = $product->bundle()->with([
            'items' => function ($q) {
                $q->with(['product', 'variant.attributes', 'unit.unit']);
            }
        ])->first();

        return view("products/{$type}/step-{$step}", compact(
            'product',
            'step',
            'type',
            'brands',
            'productTagIds',
            'allTags',
            'mainImage',
            'gallery',
            'units',
            'baseProductUnit',
            'additionalUnits',
            'allUnits',
            'warehouses',
            'suppliers',
            'existingInventory',
            'categories',
            'additionalCategories',
            'selectedPrimaryCategory',
            'selectedAdditionalCategories',
            'bundle'
        ));
    }

    public static function store($request, $step, $id, $type = 'bundle')
    {

        $product = AwProduct::findOrFail($id);

        switch ($step) {
            case 1: //basic & media
                return self::basic($request, $step, $id, $product, $type = 'bundle');
            case 2: // products selection with their variant base unit
                return self::selection($request, $step, $id, $product, $type = 'bundle');
            case 3: // step not decided yet
                return self::review($request, $step, $id, $product, $type = 'bundle');
            default:
                abort(404);
                break;
        }
    }

    private static function basic(Request $request, $step, $id, $product, $type = 'simple')
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:aw_products,name,' . $id,
            'brand_id' => 'required|exists:aw_brands,id',
            'short_description' => 'required|string|min:100',
            'type_switch' => 'required|in:simple,variable,bundle',
            'long_description' => 'required|string|min:200',
            
            'main_image' => $request->hasFile('main_image')
                ? 'image|mimes:jpeg,png,webp|max:3072'
                : 'nullable',

            'secondary_media.*' => 'nullable',
        ]);

        if ($request->hasFile('secondary_media')) {
            $request->validate([
                'secondary_media.*' => 'mimes:jpeg,png,webp,mp4,wav|max:5120'
            ]);

            $existingGalleryIds = json_decode($request->existing_gallery_ids ?? '[]', true);
            $newFilesCount = count($request->file('secondary_media'));
            $totalCount = count($existingGalleryIds) + $newFilesCount;

            if ($totalCount > 5) {
                return response()->json([
                    'errors' => ['secondary_media' => ['Maximum 5 gallery items allowed.']]
                ], 422);
            }
        }

        $product = AwProduct::findOrFail($id);
        $type = decrypt($request->route('type'));

        DB::beginTransaction();
        try {

            $product->update([
                'name' => $request->name,
                'slug' => str($request->name)->slug(),
                'product_type' => $request->type_switch,
                'brand_id' => $request->brand_id,
                'short_description' => $request->short_description,
                'long_description' => $request->long_description,
                'status' => $request->has('status') && $request->status ? 'active' : 'inactive'
            ]);

            if ($request->has('tags') && is_array($request->tags)) {
                $tagIds = [];
                foreach ($request->tags as $tagName) {
                    $tag = AwTag::firstOrCreate(
                        ['name' => $tagName],
                        ['slug' => Str::slug($tagName)]
                    );
                    $tagIds[] = $tag->id;
                }
                
                AwProductTag::where('product_id', $id)
                    ->whereNotIn('tag_id', $tagIds)
                    ->delete();
                
                foreach ($tagIds as $tagId) {
                    AwProductTag::firstOrCreate(
                        ['product_id' => $id, 'variant_id' => null, 'tag_id' => $tagId]
                    );
                }
            } else {
                AwProductTag::where('product_id', $id)->delete();
            }

            if ($request->hasFile('main_image')) {
                $oldMainImage = AwProductImage::where('product_id', $id)
                    ->where('position', 0)
                    ->first();
                
                if ($oldMainImage && Storage::disk('public')->exists($oldMainImage->image_path)) {
                    Storage::disk('public')->delete($oldMainImage->image_path);
                }

                $path = $request->file('main_image')->store('products/main', 'public');
                
                AwProductImage::updateOrCreate(
                    ['product_id' => $id, 'position' => 0],
                    ['image_path' => $path]
                );
            }

            $existingIds = json_decode($request->existing_gallery_ids ?? '[]', true);
            
            $imagesToDelete = AwProductImage::where('product_id', $id)
                ->where('position', '>', 0)
                ->whereNotIn('id', $existingIds)
                ->get();

            foreach ($imagesToDelete as $image) {
                if (Storage::disk('public')->exists($image->image_path)) {
                    Storage::disk('public')->delete($image->image_path);
                }
                $image->delete();
            }

            foreach ($existingIds as $index => $existingId) {
                AwProductImage::where('id', $existingId)
                    ->update(['position' => $index + 1]);
            }

            $lastPos = count($existingIds);
            if ($request->hasFile('secondary_media')) {
                foreach ($request->file('secondary_media') as $media) {
                    $lastPos++;
                    $path = $media->store('products/gallery', 'public');
                    
                    AwProductImage::create([
                        'product_id' => $id,
                        'image_path' => $path,
                        'position' => $lastPos
                    ]);
                }
            }

            DB::commit();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Step 1 completed successfully!',
                    'redirect' => route('product-management', [
                        'type' => encrypt($request->type_switch),
                        'step' => encrypt(2),
                        'id' => encrypt($id)
                    ])
                ]);
            }

            return redirect()->route('product-management', [
                'type' => encrypt($request->type_switch),
                'step' => encrypt(2),
                'id' => encrypt($id)
            ])->with('success', 'Step 1 completed successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error saving product: ' . $e->getMessage()
                ], 500);
            }

            return back()->withErrors('Error saving product: ' . $e->getMessage());
        }
    }

    protected static function selection(Request $request, $step, $id, $product, $type)
    {
        $request->validate([
            'pricing_mode' => 'required|in:sum_discount,fixed',
            'fixed_bundle_price' => 'nullable|numeric|min:0',
            'discount_type' => 'nullable|in:percentage,fixed',
            'discount_value' => 'nullable|numeric|min:0',
            'bundle_items' => 'required|array|min:1',
            'bundle_items.*.product_id' => 'required|exists:aw_products,id',
            'bundle_items.*.variant_id' => 'nullable|exists:aw_product_variants,id',
            'bundle_items.*.unit_id' => 'required|exists:aw_product_units,id',
            'bundle_items.*.quantity' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $bundle = AwBundle::updateOrCreate(
                ['product_id' => $id],
                [
                    'pricing_mode' => $request->pricing_mode,
                    'discount_type' => $request->pricing_mode === 'sum_discount' ? $request->discount_type : 'fixed',
                    'discount_value' => $request->pricing_mode === 'sum_discount' ? $request->discount_value : 0,
                    'total' => $request->total_amount
                ]
            );

            AwBundleItem::where('bundle_id', $bundle->id)->delete();

            foreach ($request->bundle_items as $item) {
                AwBundleItem::create([
                    'bundle_id' => $bundle->id,
                    'product_id' => $item['product_id'],
                    'variant_id' => $item['variant_id'] ?? null,
                    'unit_id' => $item['unit_id'],
                    'quantity' => $item['quantity'],
                ]);
            }

            DB::commit();
            return redirect()->route('product-management', [
                'type' => encrypt($type),
                'step' => encrypt(3),
                'id' => encrypt($id)
            ])->with('success', 'Bundle items saved successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bundle selection error: ' . $e->getMessage());
            return back()->withErrors('Error saving bundle items: ' . $e->getMessage());
        }
    }

    protected static function review(Request $request, $step, $id, $product, $type)
    {
        try {
            $product = AwProduct::findOrFail($id);

            $product->update([
                'status' => $request->has('status') && $request->status ? 'active' : 'inactive'
            ]);

            return redirect()->route('products.index')->with('success', 'Product "' . $product->name . '" has been published successfully!');
        } catch (\Exception $e) {
            return back()->withErrors('Publishing failed: ' . $e->getMessage());
        }
    }
    public function searchProducts(Request $request)
    {
        $query = $request->get('q', '');
        $type = $request->get('type', 'simple');

        $products = AwProduct::where('product_type', $type)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('sku', 'like', "%{$query}%");
            })
            ->select('id', 'name', 'sku', 'product_type')
            ->limit(20)
            ->get();

        return response()->json($products);
    }

    public function variants(AwProduct $product)
    {
        $variants = $product->variants()
            ->with('attributes')
            ->get()
            ->map(function ($variant) use ($product) {
                $attrNames = $variant->attributes->pluck('value')->implode(' / ');
                return [
                    'id' => $variant->id,
                    'sku' => $variant->sku,
                    'name' => $product->name . ' - ' . $attrNames,
                ];
            });

        return response()->json($variants);
    }

    public function units(Request $request, AwProduct $product)
    {
        $variantId = $request->get('variant_id');

        $unitsQuery = AwProductUnit::with('unit')
            ->where('product_id', $product->id);

        if ($variantId) {
            $unitsQuery->where('variant_id', $variantId);
        } else {
            $unitsQuery->whereNull('variant_id');
        }

        $productUnits = $unitsQuery->orderByDesc('is_base')->orderBy('conversion_factor')->get();

        $result = [];
        $baseUnit = $productUnits->where('is_base', 1)->first();

        foreach ($productUnits as $pu) {
            $priceQuery = AwPrice::where('product_id', $product->id)
                ->where('unit_id', $pu->id);

            if ($variantId) {
                $priceQuery->where('variant_id', $variantId);
            } else {
                $priceQuery->whereNull('variant_id');
            }

            $price = $priceQuery->first();

            $parentDisplay = '';
            if (!$pu->is_base && $pu->parent_unit_id) {
                $parentUnit = $productUnits->where('id', $pu->parent_unit_id)->first();
                if ($parentUnit) {
                    $qty = $pu->conversion_factor / $parentUnit->conversion_factor;
                    $parentDisplay = "({$qty} " . ($parentUnit->unit->name ?? 'Unit') . ")";
                }
            } elseif (!$pu->is_base && $baseUnit) {
                $parentDisplay = "({$pu->conversion_factor} " . ($baseUnit->unit->name ?? 'Base') . ")";
            }

            $result[] = [
                'id' => $pu->id,
                'unit_name' => $pu->unit->name ?? 'Unknown',
                'price' => $price ? (float) $price->base_price : 0,
                'is_base' => (bool) $pu->is_base,
                'parent_display' => $parentDisplay,
                'conversion_factor' => (float) $pu->conversion_factor,
            ];
        }

        return response()->json($result);
    }
    public function itemPrice(Request $request)
    {
        $unitId = $request->get('unit_id');
        $quantity = (int) $request->get('quantity', 1);
        $variantId = $request->get('variant_id');

        $productUnit = AwProductUnit::find($unitId);
        if (!$productUnit) {
            return response()->json(['price' => 0]);
        }

        $priceQuery = AwPrice::where('product_id', $productUnit->product_id)
            ->where('unit_id', $unitId);

        if ($variantId) {
            $priceQuery->where('variant_id', $variantId);
        } else {
            $priceQuery->whereNull('variant_id');
        }

        $price = $priceQuery->first();
        $unitPrice = $price ? (float) $price->base_price : 0;
        $totalPrice = $unitPrice * $quantity;

        return response()->json([
            'unit_price' => $unitPrice,
            'quantity' => $quantity,
            'total_price' => $totalPrice,
        ]);
    }
}