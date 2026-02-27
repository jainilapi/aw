<?php

namespace App\Http\Controllers;

use App\Models\{User, AwProduct, AwWarehouse, AwBrand, AwCategory, AwTag, AwProductTag, AwProductUnit, AwSupplierWarehouseProduct, AwInventoryMovement, AwProductImage, AwUnit, AwPrice, AwPriceTier, AwProductCategory, TaxSlab};
use Illuminate\Support\Facades\{Log, DB, Storage};
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SimpleProductController extends Controller
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

        $taxSlabs = TaxSlab::active()->get();

        return view("products/{$type}/step-{$step}", compact('product', 'step', 'type', 'brands', 'productTagIds', 'allTags', 'mainImage', 'gallery', 'units', 'baseProductUnit', 'additionalUnits', 'allUnits', 'warehouses', 'suppliers', 'existingInventory', 'categories', 'additionalCategories', 'selectedPrimaryCategory', 'selectedAdditionalCategories', 'taxSlabs'));
    }

    public static function store(Request $request, $step, $id, $type = 'simple')
    {
        $product = AwProduct::findOrFail($id);

        switch ($step) {
            case 1: //basic & media
                return self::basic($request, $step, $id, $product, $type = 'simple');
            case 2: //units & unit conversation mapping
                return self::units($request, $step, $id, $product, $type = 'simple');
            case 3: // pricing units wise (tier or non-tier pricing)
                return self::pricing($request, $step, $id, $product, $type = 'simple');
            case 4: // supplier, inventory & stock management
                return self::supplier($request, $step, $id, $product, $type = 'simple');
            case 5: // categories & seo content
                return self::categories($request, $step, $id, $product, $type = 'simple');
            case 6: // substitutes
                return self::substitutes($request, $step, $id, $product, $type = 'simple');
            case 7: // final overview
                return self::review($request, $step, $id, $product, $type = 'simple');
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
            'tax_slab_id' => 'nullable|exists:tax_slabs,id',
            
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
                'status' => $request->has('status') && $request->status ? 'active' : 'inactive',
                'tax_slab_id' => $request->tax_slab_id,
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

    private static function units(Request $request, $step, $id, $product, $type = 'simple')
    {
        $request->validate([
            'base_unit_id' => 'required|exists:aw_units,id',
            'units' => 'nullable|array',
            'units.*.unit_id' => 'required|exists:aw_units,id',
            'units.*.quantity' => 'required|numeric|min:0.0001',
            'default_selling_unit' => 'required'
        ]);

        DB::beginTransaction();
        try {
            $baseUnit = AwProductUnit::updateOrCreate(
                ['product_id' => $id, 'is_base' => 1],
                [
                    'unit_id' => $request->base_unit_id,
                    'parent_unit_id' => null,
                    'conversion_factor' => 1.0000,
                    'quantity' => 1.0000,
                    'is_default_selling' => ($request->default_selling_unit == 'base') ? 1 : 0
                ]
            );

            $keptUnitIds = [$baseUnit->id];
            $prevParentId = $request->base_unit_id;
            $runningFactor = 1.0000;

            if ($request->has('units')) {
                foreach ($request->units as $index => $u) {
                    $runningFactor = $runningFactor * $u['quantity'];
                    
                    $unitRecord = AwProductUnit::updateOrCreate(
                        ['product_id' => $id, 'unit_id' => $u['unit_id']],
                        [
                            'parent_unit_id' => $prevParentId,
                            'conversion_factor' => $runningFactor,
                            'quantity' => $u['quantity'],
                            'is_base' => 0,
                            'is_default_selling' => ($request->default_selling_unit == $index) ? 1 : 0
                        ]
                    );

                    $keptUnitIds[] = $unitRecord->id;
                    $prevParentId = $u['unit_id'];
                }
            }

            AwProductUnit::where('product_id', $id)
                ->whereNotIn('id', $keptUnitIds)
                ->delete();

            DB::commit();
            return redirect()->route('product-management', ['type' => encrypt($type), 'step' => encrypt(3), 'id' => encrypt($id)]);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors('Sync error: ' . $e->getMessage());
        }
    }

    private static function pricing(Request $request, $step, $id, $product, $type = 'simple')
    {
        DB::beginTransaction();
        try {
            foreach ($request->pricing as $unitId => $data) {

                $priceRecord = AwPrice::updateOrCreate(
                    ['product_id' => $id, 'original_unit_id' => $data['unit_id']],
                    [
                        'unit_id' => $unitId,
                        'pricing_type' => $data['pricing_type'],
                        'base_price' => $data['base_price']
                    ]
                );

                if ($data['pricing_type'] === 'tiered' && isset($data['tiers'])) {
                    $activeTierIds = [];
                    foreach ($data['tiers'] as $tier) {
                        $tierEntry = AwPriceTier::updateOrCreate(
                            ['price_id' => $priceRecord->id, 'min_qty' => $tier['min_qty']],
                            [
                                'max_qty' => $tier['max_qty'],
                                'price' => $tier['price']
                            ]
                        );
                        $activeTierIds[] = $tierEntry->id;
                    }

                    AwPriceTier::where('price_id', $priceRecord->id)->whereNotIn('id', $activeTierIds)->delete();
                } else {

                    AwPriceTier::where('price_id', $priceRecord->id)->delete();
                }
            }
            DB::commit();
            return redirect()->route('product-management', ['type' => encrypt($type), 'step' => encrypt(4), 'id' => encrypt($id)]);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors('Pricing Sync Failed: ' . $e->getMessage());
        }
    }

    private static function supplier(Request $request, $step, $id, $product, $type = 'simple')
    {
        $request->validate([
            'inventory' => 'required|array',
            'inventory.*.supplier_id' => 'required|exists:users,id',
            'inventory.*.warehouse_id' => 'required|exists:aw_warehouses,id',
            'inventory.*.unit_id' => 'required|exists:aw_units,id',
            'inventory.*.quantity' => 'required|integer|min:0',
            'inventory.*.cost_price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $keptMappingIds = [];

            foreach ($request->inventory as $item) {
                $existingRecord = AwSupplierWarehouseProduct::where([
                    'product_id' => $id,
                    'supplier_id' => $item['supplier_id'],
                    'warehouse_id' => $item['warehouse_id'],
                    'unit_id' => $item['unit_id']
                ])->first();

                $newQty = (int)$item['quantity'];
                $oldQty = $existingRecord ? (int)$existingRecord->quantity : 0;
                $difference = $newQty - $oldQty;

                $mapping = AwSupplierWarehouseProduct::updateOrCreate(
                    [
                        'product_id' => $id,
                        'supplier_id' => $item['supplier_id'],
                        'warehouse_id' => $item['warehouse_id'],
                        'unit_id' => $item['unit_id']
                    ],
                    [
                        'quantity' => $newQty,
                        'cost_price' => $item['cost_price'],
                        'reorder_level' => $item['reorder_level'],
                        'max_stock' => $item['max_stock'],
                        'notes' => $item['notes']
                    ]
                );

                if ($difference !== 0) {
                    AwInventoryMovement::create([
                        'product_id' => $id,
                        'unit_id' => $item['unit_id'],
                        'warehouse_id' => $item['warehouse_id'],
                        'quantity_change' => $difference,
                        'reason' => $existingRecord ? 'adjustment' : 'purchase',
                        'reference' => $existingRecord ? 'Manual Quantity Update' : 'Initial Stock Entry',
                    ]);
                }
                $keptMappingIds[] = $mapping->id;
            }

            AwSupplierWarehouseProduct::where('product_id', $id)
                ->whereNotIn('id', $keptMappingIds)
                ->delete();

            DB::commit();
            return redirect()->route('product-management', ['type' => encrypt($type), 'step' => encrypt(5), 'id' => encrypt($id)]);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors('Inventory Sync Failed: ' . $e->getMessage());
        }
    }

    private static function categories(Request $request, $step, $id, $product, $type = 'simple')
    {
        $request->validate([
            'primary_category' => 'required|exists:aw_categories,id',
            'additional_categories' => 'array',
            'additional_categories.*' => 'exists:aw_categories,id',
            'seo_title' => 'nullable|string|max:60',
            'seo_description' => 'nullable|string|max:160',
        ]);

        DB::beginTransaction();
        try {

            $product->update([
                'meta_title' => $request->input('seo_title'),
                'meta_description' => $request->input('seo_description')
            ]);

            AwProductCategory::updateOrCreate([
                'product_id' => $product->id,
                'is_primary' => 1,
            ], [
                'category_id' => $request->input('primary_category') ?? 1,
            ]);

            $toBeKept = [];

            if ($request->has('additional_categories')) {
                $additionalCategories = array_diff(
                    $request->input('additional_categories'),
                    [$request->input('primary_category')]
                );

                foreach ($additionalCategories as $categoryId) {
                    $toBeKept[] = AwProductCategory::updateOrCreate([
                        'product_id' => $product->id,
                        'category_id' => $categoryId,
                        'is_primary' => 0,
                    ])->id;
                }
            }

            if (!empty($toBeKept)) {
                AwProductCategory::where('product_id', $product->id)
                    ->where('is_primary', 0)
                    ->whereNotIn('id', $toBeKept)
                    ->delete();
            } else {
                AwProductCategory::where('product_id', $product->id)
                    ->where('is_primary', 0)
                    ->delete();
            }

            DB::commit();

            return redirect()->route('product-management', ['type' => encrypt($type), 'step' => encrypt(6), 'id' => encrypt($product->id)])
                ->with('success', 'Data saved successfully');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors('Category Management Failed: ' . $e->getMessage());
        }
    }

    private static function substitutes(Request $request, $step, $id, $product, $type = 'simple')
    {
        $request->validate([
            'substitutes' => 'nullable|array',
            'substitutes.*' => 'exists:aw_products,id'
        ]);

        try {
            $product = AwProduct::findOrFail($id);
            $product->substitutes()->sync($request->substitutes ?? []);

            return redirect()->route('product-management', [
                'type' => encrypt($type), 
                'step' => encrypt(7),
                'id' => encrypt($id)
            ]);
        } catch (\Exception $e) {
            return back()->withErrors('Error linking substitutes: ' . $e->getMessage());
        }
    }

    private static function review(Request $request, $step, $id, $product, $type = 'simple')
    {
        try {
            $product = AwProduct::findOrFail($id);
            
            if (!$product->categories()->where('is_primary', 1)->exists()) {
                return back()->withErrors('Error: You must select a Primary Category in Step 5 before publishing.');
            }

            $product->update([
                'status' => $request->has('status') && $request->status ? 'active' : 'inactive'
            ]);

            return redirect()->route('products.index')->with('success', 'Product "' . $product->name . '" has been published successfully!');
        } catch (\Exception $e) {
            return back()->withErrors('Publishing failed: ' . $e->getMessage());
        }        
    }
}
