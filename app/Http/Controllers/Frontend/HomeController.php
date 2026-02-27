<?php

namespace App\Http\Controllers\Frontend;

use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use \App\Models\HomePageSetting;
use App\Models\AwCategory;
use App\Models\AwProduct;
use App\Models\AwBrand;
use App\Models\AwProductCategory;
use App\Models\AwProductVariant;
use App\Models\AwProductUnit;
use App\Models\AwPrice;
use App\Models\AwPriceTier;
use App\Models\AwAttribute;
use App\Models\AwAttributeValue;
use App\Models\AwCart;
use App\Models\AwCartItem;
use App\Models\AwWishlist;
use App\Models\AwBundle;
use App\Models\AwOrder;
use App\Models\AwOrderItem;
use App\Helpers\Helper;
use App\Services\PromotionService;
use App\Services\CurrencyService;
use App\Models\AwPromotion;
use App\Models\TaxSlab;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $sections = HomePageSetting::oldest('ordering')->get();

        foreach ($sections as $section) {
            if ($section->key == 'top_categories_grid' || $section->key == 'top_categories_linear') {
                 if (isset($section->value->categories)) {
                    $categories = $section->value->categories;
                    foreach ($categories as $key => $cat) {
                        if (!empty($cat->items)) {
                            // Fetch products for this category configuration
                            $products = AwProduct::whereIn('id', $cat->items)
                                ->where('status', 'active')
                                ->with(['primaryImage', 'prices', 'units'])
                                ->get();
                            
                            // Attach products to the category object temporarily
                            $categories[$key]->products = $products;
                        }
                    }
                    // Re-assign modified categories back to section value
                    $value = $section->value;
                    $value->categories = $categories;
                    $section->value = $value;
                 }
            } elseif ($section->key == 'top_selling_products') {
                if (!empty($section->value->products)) {
                    $products = AwProduct::whereIn('id', $section->value->products)
                        ->where('status', 'active')
                        ->with(['primaryImage', 'prices', 'units'])
                        ->get();
                    
                    $value = $section->value;
                    $value->loaded_products = $products;
                    $section->value = $value;
                }
            } elseif ($section->key == 'recently_viewed') {
                 // Get recently viewed IDs from cookie/session
                 $recentIds = json_decode($request->cookie('recently_viewed', '[]'), true);
                 
                 if (!empty($recentIds)) {
                     $products = AwProduct::whereIn('id', $recentIds)
                        ->where('status', 'active')
                        ->with(['primaryImage', 'prices', 'units'])
                        ->get();
                     
                     // Sort by recently viewed order (reverse of IDs array if it was pushed in order)
                     // or just trust the DB order. For now DB order, can optimize later.
                     $value = $section->value;
                     $value->loaded_products = $products;
                     $section->value = $value;
                 }
            }
        }

        return view('frontend.home', compact('sections'));
    }

    public function categories(Request $request)
    {
        $search = $request->get('search');
        $perPage = $request->get('per_page', 15);

        $query = AwCategory::where('status', true)
            ->with(['parent', 'children'])
            ->withCount([
                'productCategories as products_count' => function ($q) {
                    $q->whereNull('aw_product_categories.deleted_at');
                }
            ]);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        $query->orderBy('name', 'asc');

        $categories = $query->paginate($perPage)->withQueryString();

        $parentCategory = null;

        return view('frontend.categories', compact('categories', 'parentCategory', 'search'));
    }

    public function products(Request $request)
    {
        // Get filter parameters
        $categoryId = $request->get('category');
        $brandIds = $request->get('brands', []);
        $attributeValueIds = $request->get('attributes', []);
        $minPrice = $request->get('min_price');
        $maxPrice = $request->get('max_price');
        $priceRange = $request->get('price_range');
        $inStock = $request->get('in_stock');
        $search = $request->get('search');
        $sort = $request->get('sort', 'name_asc');
        $perPage = $request->get('per_page', 12);

        // Build base query for active products
        $query = AwProduct::where('status', 'active')
            ->whereNull('deleted_at')
            ->with(['primaryImage', 'brand', 'categories']);

        // Filter by category
        if ($categoryId) {
            $query->whereHas('categories', function ($q) use ($categoryId) {
                $q->where('aw_categories.id', $categoryId)
                    ->whereNull('aw_product_categories.deleted_at');
            });
        }

        // Filter by brands
        if (!empty($brandIds) && is_array($brandIds)) {
            $query->whereIn('brand_id', $brandIds);
        }

        // Filter by price range
        if ($priceRange) {
            switch ($priceRange) {
                case 'under_50':
                    $query->whereHas('prices', function ($q) {
                        $q->where('base_price', '<', 50);
                    });
                    break;
                case '50_100':
                    $query->whereHas('prices', function ($q) {
                        $q->whereBetween('base_price', [50, 100]);
                    });
                    break;
                case '100_200':
                    $query->whereHas('prices', function ($q) {
                        $q->whereBetween('base_price', [100, 200]);
                    });
                    break;
                case '200_500':
                    $query->whereHas('prices', function ($q) {
                        $q->whereBetween('base_price', [200, 500]);
                    });
                    break;
                case 'above_500':
                    $query->whereHas('prices', function ($q) {
                        $q->where('base_price', '>', 500);
                    });
                    break;
            }
        } elseif ($minPrice || $maxPrice) {
            $query->whereHas('prices', function ($q) use ($minPrice, $maxPrice) {
                if ($minPrice) {
                    $q->where('base_price', '>=', $minPrice);
                }
                if ($maxPrice) {
                    $q->where('base_price', '<=', $maxPrice);
                }
            });
        }

        // Filter by stock status
        if ($inStock !== null) {
            if ($inStock == '1') {
                $query->whereHas('supplierWarehouseProducts', function ($q) {
                    $q->where('quantity', '>', 0)
                        ->whereNull('aw_supplier_warehouse_products.deleted_at');
                });
            } else {
                $query->whereDoesntHave('supplierWarehouseProducts', function ($q) {
                    $q->where('quantity', '>', 0)
                        ->whereNull('aw_supplier_warehouse_products.deleted_at');
                });
            }
        }

        // Filter by attributes (for variable products)
        if (!empty($attributeValueIds) && is_array($attributeValueIds)) {
            $query->whereHas('variants', function ($q) use ($attributeValueIds) {
                $q->whereHas('attributes', function ($attrQuery) use ($attributeValueIds) {
                    $attrQuery->whereIn('aw_attribute_values.id', $attributeValueIds);
                });
            });
        }

        // Search functionality
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('sku', 'like', '%' . $search . '%')
                    ->orWhere('short_description', 'like', '%' . $search . '%');
            });
        }

        // Sorting
        switch ($sort) {
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            case 'price_asc':
                $query->select('aw_products.*')
                    ->leftJoin(DB::raw('(SELECT product_id, MIN(base_price) as min_price FROM aw_prices WHERE deleted_at IS NULL GROUP BY product_id) as price_min'), 'aw_products.id', '=', 'price_min.product_id')
                    ->orderBy('price_min.min_price', 'asc');
                break;
            case 'price_desc':
                $query->select('aw_products.*')
                    ->leftJoin(DB::raw('(SELECT product_id, MIN(base_price) as min_price FROM aw_prices WHERE deleted_at IS NULL GROUP BY product_id) as price_min'), 'aw_products.id', '=', 'price_min.product_id')
                    ->orderBy('price_min.min_price', 'desc');
                break;
            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            default:
                $query->orderBy('name', 'asc');
        }

        // Paginate results
        $products = $query->paginate($perPage)->withQueryString();

        // Preload all active tax slabs for displaying tax info on listing cards
        $taxSlabs = TaxSlab::active()->get();

        // Get filter data for sidebar
        $categories = AwCategory::where('status', true)
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get();

        $brands = AwBrand::where('status', true)
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get();

        // Get selected category info
        $selectedCategory = null;
        if ($categoryId) {
            $selectedCategory = AwCategory::where('id', $categoryId)
                ->where('status', true)
                ->whereNull('deleted_at')
                ->first();
        }

        // Get price range for products (for custom range)
        $priceStats = DB::table('aw_prices')
            ->whereNull('deleted_at')
            ->whereNull('variant_id')
            ->selectRaw('MIN(base_price) as min_price, MAX(base_price) as max_price')
            ->first();

        // Get attributes for filtering (only for variable products in selected category)
        $attributes = collect();
        if ($categoryId) {
            $variableProducts = AwProduct::where('product_type', 'variable')
                ->where('status', 'active')
                ->whereNull('deleted_at')
                ->whereHas('categories', function ($q) use ($categoryId) {
                    $q->where('aw_categories.id', $categoryId)
                        ->whereNull('aw_product_categories.deleted_at');
                })
                ->pluck('id');

            if ($variableProducts->count() > 0) {
                $attributeIds = DB::table('aw_variant_attribute_values')
                    ->join('aw_product_variants', 'aw_variant_attribute_values.variant_id', '=', 'aw_product_variants.id')
                    ->join('aw_attribute_values', 'aw_variant_attribute_values.attribute_value_id', '=', 'aw_attribute_values.id')
                    ->whereIn('aw_product_variants.product_id', $variableProducts)
                    ->whereNull('aw_variant_attribute_values.deleted_at')
                    ->whereNull('aw_attribute_values.deleted_at')
                    ->distinct()
                    ->pluck('aw_attribute_values.attribute_id')
                    ->toArray();

                $attributes = AwAttribute::whereIn('id', $attributeIds)
                    ->whereNull('deleted_at')
                    ->with([
                        'values' => function ($q) use ($variableProducts) {
                            $q->whereNull('deleted_at')
                                ->whereHas('variants', function ($query) use ($variableProducts) {
                                    $query->whereIn('aw_product_variants.product_id', $variableProducts);
                                });
                        }
                    ])
                    ->get();
            }
        }

        return view('frontend.products', compact(
            'products',
            'categories',
            'brands',
            'attributes',
            'selectedCategory',
            'categoryId',
            'brandIds',
            'attributeValueIds',
            'priceRange',
            'minPrice',
            'maxPrice',
            'inStock',
            'search',
            'sort',
            'priceStats',
            'taxSlabs'
        ));
    }

    public function details(Request $request, $id = null, $slug = null, $variant = null)
    {
        $product = AwProduct::findOrFail($id);

        $selectedVariant = null;
        $attributes = collect();
        $variants = collect();
        $units = collect();
        $taxSlab = null;

        // Add to recently viewed
        $recentIds = json_decode($request->cookie('recently_viewed', '[]'), true);
        // Remove id if exists to push to top
        $recentIds = array_diff($recentIds, [$product->id]);
        // Add to front
        array_unshift($recentIds, $product->id);
        // Limit to 20
        $recentIds = array_slice($recentIds, 0, 20);
        
        \Illuminate\Support\Facades\Cookie::queue('recently_viewed', json_encode($recentIds), 60 * 24 * 30);

        // For variable products, get all variants and attributes
        if ($product->product_type == 'variable') {
            $variants = AwProductVariant::where('product_id', $product->id)
                ->whereNull('deleted_at')
                ->with([
                    'attributes.attribute',
                    'images' => function ($q) {
                        $q->whereNull('deleted_at')->orderBy('position');
                    }
                ])
                ->get();

            // Get selected variant
            if (!empty($variant)) {
                $selectedVariant = $variants->where('id', $variant)->first();
            }
            if (!$selectedVariant && $variants->count() > 0) {
                $selectedVariant = $variants->first();
            }

            // For variable products, tax slab is defined on variants
            if ($selectedVariant && $selectedVariant->tax_slab_id) {
                $taxSlab = TaxSlab::find($selectedVariant->tax_slab_id);
            }

            // Get all attributes used by variants
            $attributeIds = DB::table('aw_variant_attribute_values')
                ->join('aw_attribute_values', 'aw_variant_attribute_values.attribute_value_id', '=', 'aw_attribute_values.id')
                ->whereIn('aw_variant_attribute_values.variant_id', $variants->pluck('id'))
                ->whereNull('aw_variant_attribute_values.deleted_at')
                ->whereNull('aw_attribute_values.deleted_at')
                ->distinct()
                ->pluck('aw_attribute_values.attribute_id')
                ->toArray();

            $attributes = AwAttribute::whereIn('id', $attributeIds)
                ->whereNull('deleted_at')
                ->with([
                    'values' => function ($q) use ($variants) {
                        $q->whereNull('deleted_at')
                            ->whereHas('variants', function ($query) use ($variants) {
                                $query->whereIn('aw_product_variants.id', $variants->pluck('id'));
                            });
                    }
                ])
                ->get();

            // Get units for selected variant
            if ($selectedVariant) {
                $variantUnits = $selectedVariant->units()->orderBy('is_base', 'desc')->orderBy('conversion_factor')->get();
                $baseUnit = $variantUnits->where('is_base', true)->first();
                $additionalUnits = $variantUnits->where('is_base', false);

                // Only show units if additional units exist
                if ($additionalUnits->count() > 0) {
                    $units = $variantUnits->map(function ($element) {
                        return [
                            'id' => $element->id,
                            'unit_type' => $element->is_base,
                            'unit_id' => $element->unit_id,
                            'title' => $element->unit->name ?? 'Unit',
                            'quantity' => (float) $element->conversion_factor,
                            'is_default' => (bool) $element->is_default_selling,
                        ];
                    });
                } else {
                    // Only base unit, don't show selector
                    $units = collect();
                }
            }
        } elseif ($product->product_type == 'bundle') {
            // Bundle product - get bundle items
            $bundle = $product->bundle;
            $bundleItems = collect();

            if ($bundle) {
                $bundleItems = $bundle->items()
                    ->whereNull('deleted_at')
                    ->with(['product.primaryImage', 'variant', 'unit.unit'])
                    ->get();
            }

            // Bundle products typically don't have units selector
            $units = collect();
        } else {
            // Simple product
            $productUnits = $product->units()->orderBy('is_base', 'desc')->orderBy('conversion_factor')->get();
            $baseUnit = $productUnits->where('is_base', true)->first();
            $additionalUnits = $productUnits->where('is_base', false);

            // Only show units if additional units exist
            if ($additionalUnits->count() > 0) {
                $units = $productUnits->map(function ($element) {
                    return [
                        'id' => $element->id,
                        'unit_type' => $element->is_base,
                        'unit_id' => $element->unit_id,
                        'title' => $element->unit->name ?? 'Unit',
                        'quantity' => (float) $element->conversion_factor,
                        'is_default' => (bool) $element->is_default_selling,
                    ];
                });
            } else {
                $units = collect();
            }

            // Simple products can have a direct tax slab
            if ($product->tax_slab_id) {
                $taxSlab = TaxSlab::find($product->tax_slab_id);
            }
        }

        // Get primary category
        $primaryCategory = $product->primaryCategory;
        $categoryHierarchy = [];
        if ($primaryCategory && $primaryCategory->category) {
            Helper::getProductHierarchy($primaryCategory->category->id, $categoryHierarchy);
        }

        $categoryHierarchy = collect($categoryHierarchy);

        if ($categoryHierarchy->count() > 3) {
            $firstTwo = collect([
                [
                    'display' => true,
                    'name' => '...'
                ],
                $categoryHierarchy->take(-1)->first()
            ])
                ->values()->all();

            $categoryHierarchy = $categoryHierarchy->take(2)->merge($firstTwo)->reverse()->values()->all();
        }

        // Use selectedVariant as variant for view compatibility
        $variant = $selectedVariant;

        // Get bundle data if bundle product
        $bundle = null;
        $bundleItems = collect();
        if ($product->product_type == 'bundle') {
            $bundle = $product->bundle;
            if ($bundle) {
                $bundleItems = $bundle->items()
                    ->whereNull('deleted_at')
                    ->with(['product.primaryImage', 'variant', 'unit.unit'])
                    ->get();
            }
        }

        // Check if product is in cart
        $cartItem = null;
        $userId = auth('customer')->id();
        $guestId = $request->cookie('guest_id');

        if ($userId || $guestId) {
            $cart = $this->getOrCreateCart($userId, $guestId);
            $selectedUnitId = $units->where('is_default', true)->first()['id'] ?? ($units->first()['id'] ?? null);

            if ($selectedUnitId || $product->product_type == 'bundle') {
                $cartItem = $cart->items()
                    ->where('product_id', $product->id)
                    ->where('variant_id', $variant ? $variant->id : null)
                    ->when($selectedUnitId, function ($q) use ($selectedUnitId) {
                        $q->where('unit_id', $selectedUnitId);
                    })
                    ->whereNull('deleted_at')
                    ->first();
            }
        }

        $unitsJs = $units->map(function ($u) {
            return [
                'id' => $u['id'],
                'unit_id' => $u['unit_id'],
                'title' => $u['title'],
                'is_default' => $u['is_default']
            ];
        });

        $variantsJs = $variants->map(function ($v) {
            return [
                'id' => $v->id,
                'name' => $v->name,
                'attributes' => $v->attributes ? $v->attributes->pluck('id')->toArray() : [],
                'images' => $v->images ? $v->images->map(function ($img) {
                    return asset('storage/' . $img->image_path);
                })->toArray() : []
            ];
        });

        return view('frontend.detail', compact(
            'product',
            'variant',
            'selectedVariant',
            'attributes',
            'variants',
            'units',
            'categoryHierarchy',
            'cartItem',
            'unitsJs',
            'variantsJs',
            'bundle',
            'bundleItems',
            'taxSlab'
        ));
    }

    /**
     * Get product pricing based on variant, unit, and quantity
     */
    public function productPricing(Request $request)
    {
        $productId = $request->get('product_id');
        $variantId = $request->get('variant_id');
        $unitId = $request->get('unit_id');
        $quantity = $request->get('quantity', 1);

        if (!$productId) {
            return response()->json(['error' => 'Missing required parameters'], 400);
        }

        // Get the product to check its type
        $product = AwProduct::find($productId);
        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        // Handle bundle products differently
        if ($product->product_type == 'bundle') {
            return $this->getBundlePricing($product, $quantity);
        }

        if (empty($unitId) || $unitId == 'null') {
            if ($variantId) {
                $variantUnit = AwProductUnit::where('variant_id', $variantId)
                    ->where('is_default_selling', true)
                    ->first();
                if ($variantUnit) {
                    $unitId = $variantUnit->id;
                }
            } else {
                $productUnit = AwProductUnit::where('product_id', $productId)
                    ->where('is_default_selling', true)
                    ->first();
                if ($productUnit) {
                    $unitId = $productUnit->id;
                }
            }
        }

        // Get product unit to verify it exists
        $productUnit = AwProductUnit::where('id', $unitId)->first();
        if (!$productUnit) {
            return response()->json([
                'price' => 0,
                'originalPrice' => 0,
                'tiers' => []
            ]);
        }

        // Find price record - price.unit_id refers to product_unit.id
        $priceQuery = AwPrice::where('product_id', $productId)
            ->where('unit_id', $unitId);

        if ($variantId) {
            $priceQuery->where('variant_id', $variantId);
        } else {
            $priceQuery->whereNull('variant_id');
        }

        $price = $priceQuery->first();

        if (!$price) {
            return response()->json([
                'price' => 0,
                'originalPrice' => 0,
                'tiers' => []
            ]);
        }

        $basePrice = (float) $price->base_price;
        $originalPrice = $basePrice;
        $finalPrice = $basePrice;
        $tiers = [];

        // Handle tiered pricing
        if ($price->pricing_type == 'tiered') {
            $priceTiers = AwPriceTier::where('price_id', $price->id)
                ->orderBy('min_qty')
                ->get();

            foreach ($priceTiers as $tier) {
                $tierPrice = (float) $tier->price;
                $tierMRP = $tierPrice;

                $tiers[] = [
                    'min_qty' => $tier->min_qty,
                    'max_qty' => $tier->max_qty,
                    'price' => $tierPrice,
                    'mrp' => $originalPrice
                ];

                // Check if current quantity falls in this tier
                if ($quantity >= $tier->min_qty && (!$tier->max_qty || $quantity <= $tier->max_qty)) {
                    $finalPrice = $tierPrice;
                    $originalPrice = $tierMRP;
                }
            }
        } else {
            // Fixed pricing - create a single tier
            $tiers[] = [
                'min_qty' => 1,
                'max_qty' => null,
                'price' => $basePrice,
                'mrp' => $originalPrice
            ];
        }

        return response()->json([
            'price' => $finalPrice,
            'originalPrice' => $originalPrice,
            'tiers' => $tiers
        ]);
    }

    /**
     * Get bundle product pricing
     */
    private function getBundlePricing(AwProduct $product, $quantity = 1)
    {
        $bundle = $product->bundle;
        if (!$bundle) {
            return response()->json([
                'price' => 0,
                'originalPrice' => 0,
                'tiers' => [],
                'is_bundle' => true
            ]);
        }

        $bundleItems = $bundle->items()
            ->whereNull('deleted_at')
            ->with(['product', 'variant', 'unit'])
            ->get();

        $originalTotal = 0;
        $finalPrice = 0;

        if ($bundle->pricing_mode == 'fixed') {
            // Fixed bundle price - get from aw_prices for the bundle product
            $priceRecord = AwPrice::where('product_id', $product->id)
                ->whereNull('variant_id')
                ->whereNull('deleted_at')
                ->first();

            if ($priceRecord) {
                $finalPrice = (float) $priceRecord->base_price;
            }

            // Calculate original total from items for comparison
            foreach ($bundleItems as $item) {
                $itemPrice = $this->getItemPrice($item);
                $originalTotal += $itemPrice * $item->quantity;
            }
        } else {
            // sum_discount mode - sum of items minus discount
            foreach ($bundleItems as $item) {
                $itemPrice = $this->getItemPrice($item);
                $originalTotal += $itemPrice * $item->quantity;
            }

            $finalPrice = $originalTotal;

            // Apply discount
            if ($bundle->discount_value > 0) {
                if ($bundle->discount_type == 'percentage') {
                    $discount = $originalTotal * ($bundle->discount_value / 100);
                    $finalPrice = $originalTotal - $discount;
                } else {
                    $finalPrice = $originalTotal - $bundle->discount_value;
                }
            }
        }

        // Ensure price is not negative
        $finalPrice = max(0, $bundle->total);

        $originalTotal = (float) $originalTotal;
        $discountedPrice = (float) $finalPrice;

        $discountPercentage = 0;

        if ($originalTotal > 0 && $discountedPrice >= 0 && $discountedPrice <= $originalTotal) {
            $discountPercentage = round(
                (($originalTotal - $discountedPrice) / $originalTotal) * 100,
                2
            );
        }


        $tiers = [
            [
                'min_qty' => 1,
                'max_qty' => null,
                'price' => $finalPrice,
                'mrp' => $originalTotal
            ]
        ];

        return response()->json([
            'price' => $finalPrice,
            'originalPrice' => $originalTotal,
            'tiers' => $tiers,
            'is_bundle' => true,
            'discount_type' => $bundle->discount_type,
            'discount_value' => $bundle->discount_value,
            'pricing_mode' => $bundle->pricing_mode
        ]);
    }

    /**
     * Get price for a bundle item
     */
    private function getItemPrice($bundleItem)
    {
        $priceQuery = AwPrice::where('product_id', $bundleItem->product_id);

        if ($bundleItem->variant_id) {
            $priceQuery->where('variant_id', $bundleItem->variant_id);
        } else {
            $priceQuery->whereNull('variant_id');
        }

        if ($bundleItem->unit_id) {
            $priceQuery->where('unit_id', $bundleItem->unit_id);
        }

        $priceRecord = $priceQuery->whereNull('deleted_at')->first();

        return $priceRecord ? (float) $priceRecord->base_price : 0;
    }

    /**
     * Get price for a cart item (handles bundle products)
     */
    private function getCartItemPrice($item)
    {
        if (!$item->product) {
            return 0;
        }

        // Check if this is a bundle product
        if ($item->product->product_type == 'bundle') {
            return $this->calculateBundlePrice($item->product);
        }

        // Regular product pricing
        $priceRecord = AwPrice::where('product_id', $item->product_id)
            ->where('variant_id', $item->variant_id)
            ->where('unit_id', $item->unit_id)
            ->whereNull('deleted_at')
            ->first();

        return $priceRecord ? (float) $priceRecord->base_price : 0;
    }

    /**
     * Calculate bundle price for cart items
     */
    private function calculateBundlePrice(AwProduct $product)
    {
        $bundle = $product->bundle;
        if (!$bundle) {
            return 0;
        }

        $bundleItems = $bundle->items()
            ->whereNull('deleted_at')
            ->get();


        return max(0, (float) $bundle->total);
    }

    /**
     * Get bundle items for cart display
     */
    private function getBundleItemsForCart($product)
    {
        if (!$product || $product->product_type != 'bundle') {
            return [];
        }

        $bundle = $product->bundle;
        if (!$bundle) {
            return [];
        }

        $bundleItems = $bundle->items()
            ->whereNull('deleted_at')
            ->with(['product.primaryImage', 'variant', 'unit.unit'])
            ->get();

        $items = [];
        foreach ($bundleItems as $bundleItem) {
            $imageUrl = asset('assets/images/default-product.png');
            if ($bundleItem->product && $bundleItem->product->primaryImage) {
                $imageUrl = asset('storage/' . $bundleItem->product->primaryImage->image_path);
            }

            $items[] = [
                'product_name' => $bundleItem->product ? $bundleItem->product->name : 'Product',
                'variant_name' => $bundleItem->variant ? $bundleItem->variant->name : null,
                'quantity' => $bundleItem->quantity,
                'unit_name' => $bundleItem->unit && $bundleItem->unit->unit ? $bundleItem->unit->unit->name : null,
                'image_url' => $imageUrl,
            ];
        }

        return $items;
    }

    /**
     * Get cart items for current user/guest (for page load state)
     */
    public function getCartItems(Request $request)
    {
        try {
            $userId = auth('customer')->id();
            $guestId = $request->cookie('guest_id');

            if (!$userId && !$guestId) {
                return response()->json([
                    'success' => true,
                    'items' => []
                ]);
            }

            $cart = $this->getOrCreateCart($userId, $guestId);
            $items = $cart->items()
                ->whereNull('deleted_at')
                ->get();

            $cartItems = [];
            foreach ($items as $item) {
                $cartItems[] = [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'variant_id' => $item->variant_id,
                    'unit_id' => $item->unit_id,
                    'quantity' => $item->quantity
                ];
            }

            return response()->json([
                'success' => true,
                'items' => $cartItems
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load cart items: ' . $e->getMessage(),
                'items' => []
            ], 500);
        }
    }

    /**
     * Get or create guest ID for cart/wishlist
     */
    private function getOrCreateGuestId(Request $request)
    {
        $guestId = $request->cookie('guest_id');
        if (!$guestId) {
            $guestId = Str::uuid()->toString();
        }
        return $guestId;
    }

    /**
     * Get or create cart for user/guest
     */
    private function getOrCreateCart($userId = null, $guestId = null)
    {
        if ($userId) {
            return AwCart::firstOrCreate(['user_id' => $userId]);
        } else {
            return AwCart::firstOrCreate(['guest_id' => $guestId]);
        }
    }

    /**
     * Add item to cart
     */
    public function addToCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:aw_products,id',
            'variant_id' => 'nullable|exists:aw_product_variants,id',
            'unit_id' => 'nullable|exists:aw_product_units,id',
            'quantity' => 'required|integer|min:1',
        ]);

        try {
            $userId = auth('customer')->id();
            $guestId = $this->getOrCreateGuestId($request);
            $unitId = $request->unit_id;

            if (empty($unitId) || $unitId == 'null') {
                if ($request->variant_id) {
                    $variantUnit = AwProductUnit::where('variant_id', $request->variant_id)
                        ->where('is_default_selling', true)
                        ->first();
                    if ($variantUnit) {
                        $unitId = $variantUnit->id;
                    }
                } else {
                    $productUnit = AwProductUnit::where('product_id', $request->product_id)
                        ->where('is_default_selling', true)
                        ->first();
                    if ($productUnit) {
                        $unitId = $productUnit->id;
                    }
                }
            }

            $cart = $this->getOrCreateCart($userId, $guestId);

            // Check if item already exists
            $existingItem = $cart->items()
                ->where('product_id', $request->product_id)
                ->where('variant_id', $request->variant_id)
                ->where('unit_id', $unitId)
                ->first();

            if ($existingItem) {
                $existingItem->update([
                    'quantity' => $existingItem->quantity + $request->quantity
                ]);
                $cartItemId = $existingItem->id;
            } else {
                $cartItem = AwCartItem::create([
                    'cart_id' => $cart->id,
                    'product_id' => $request->product_id,
                    'variant_id' => $request->variant_id,
                    'unit_id' => $unitId,
                    'quantity' => $request->quantity,
                ]);
                $cartItemId = $cartItem->id;
            }

            $cart->update(['last_activity_at' => now()]);

            // Set cookie if guest
            if (!$userId) {
                $response = response()->json([
                    'success' => true,
                    'message' => 'Item added to cart',
                    'cart_count' => $cart->items()->count(),
                    'cart_item_id' => $cartItemId
                ]);
                return $response->cookie('guest_id', $guestId, 60 * 24 * 30); // 30 days
            }

            return response()->json([
                'success' => true,
                'message' => 'Item added to cart',
                'cart_count' => $cart->items()->count(),
                'cart_item_id' => $cartItemId
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add item to cart: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update cart item quantity
     */
    public function updateCartItem(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:aw_cart_items,id',
            'quantity' => 'required|integer|min:1',
        ]);

        try {
            $userId = auth('customer')->id();
            $guestId = $this->getOrCreateGuestId($request);

            $cart = $this->getOrCreateCart($userId, $guestId);

            $item = $cart->items()->where('id', $request->item_id)->first();

            if (!$item) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cart item not found'
                ], 404);
            }

            $item->update(['quantity' => $request->quantity]);
            $cart->update(['last_activity_at' => now()]);

            return response()->json([
                'success' => true,
                'message' => 'Cart updated',
                'cart_count' => $cart->items()->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update cart: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove item from cart
     */
    public function removeFromCart(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:aw_cart_items,id',
        ]);

        try {
            $userId = auth('customer')->id();
            $guestId = $this->getOrCreateGuestId($request);

            $cart = $this->getOrCreateCart($userId, $guestId);

            $item = $cart->items()->where('id', $request->item_id)->first();

            if (!$item) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cart item not found'
                ], 404);
            }

            $item->delete();
            $cart->update(['last_activity_at' => now()]);

            return response()->json([
                'success' => true,
                'message' => 'Item removed from cart',
                'cart_count' => $cart->items()->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove item: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add to wishlist
     */
    public function addToWishlist(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:aw_products,id',
            'variant_id' => 'nullable|exists:aw_product_variants,id',
        ]);

        try {
            $userId = auth('customer')->id();
            $guestId = $this->getOrCreateGuestId($request);

            // Check if already in wishlist
            $exists = AwWishlist::where(function ($q) use ($userId, $guestId) {
                if ($userId) {
                    $q->where('user_id', $userId);
                } else {
                    $q->where('guest_id', $guestId);
                }
            })
                ->where('product_id', $request->product_id)
                ->where('variant_id', $request->variant_id)
                ->whereNull('deleted_at')
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Item already in wishlist'
                ], 400);
            }

            AwWishlist::create([
                'user_id' => $userId,
                'guest_id' => $userId ? null : $guestId,
                'product_id' => $request->product_id,
                'variant_id' => $request->variant_id,
            ]);

            $response = response()->json([
                'success' => true,
                'message' => 'Added to wishlist',
                'in_wishlist' => true
            ]);

            if (!$userId) {
                return $response->cookie('guest_id', $guestId, 60 * 24 * 30);
            }

            return $response;
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add to wishlist: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove from wishlist
     */
    public function removeFromWishlist(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:aw_products,id',
            'variant_id' => 'nullable|exists:aw_product_variants,id',
        ]);

        try {
            $userId = auth('customer')->id();
            $guestId = $this->getOrCreateGuestId($request);

            $wishlistItem = AwWishlist::where(function ($q) use ($userId, $guestId) {
                if ($userId) {
                    $q->where('user_id', $userId);
                } else {
                    $q->where('guest_id', $guestId);
                }
            })
                ->where('product_id', $request->product_id)
                ->where('variant_id', $request->variant_id)
                ->whereNull('deleted_at')
                ->first();

            if ($wishlistItem) {
                $wishlistItem->delete();
            }

            return response()->json([
                'success' => true,
                'message' => 'Removed from wishlist',
                'in_wishlist' => false
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove from wishlist: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if item is in wishlist
     */
    public function checkWishlist(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:aw_products,id',
            'variant_id' => 'nullable|exists:aw_product_variants,id',
        ]);

        $userId = auth('customer')->id();
        $guestId = $this->getOrCreateGuestId($request);

        $inWishlist = AwWishlist::where(function ($q) use ($userId, $guestId) {
            if ($userId) {
                $q->where('user_id', $userId);
            } else {
                $q->where('guest_id', $guestId);
            }
        })
            ->where('product_id', $request->product_id)
            ->where('variant_id', $request->variant_id)
            ->whereNull('deleted_at')
            ->exists();

        return response()->json([
            'in_wishlist' => $inWishlist
        ]);
    }

    /**
     * Cart page
     */
    public function cart(Request $request)
    {
        $userId = auth('customer')->id();
        $guestId = $this->getOrCreateGuestId($request);

        $cart = $this->getOrCreateCart($userId, $guestId);
        $items = $cart->items()
            ->whereNull('deleted_at')
            ->with(['product.primaryImage', 'variant', 'unit.productUnits', 'product.brand'])
            ->get();

        $subtotal = 0;
        $taxTotal = 0;
        $cartItems = [];

        // Preload tax slabs used by products/variants in cart (for display and calculation)
        $productTaxSlabIds = $items->pluck('product.tax_slab_id')->filter()->unique();
        $variantTaxSlabIds = $items->pluck('variant.tax_slab_id')->filter()->unique();
        $allTaxSlabIds = $productTaxSlabIds->merge($variantTaxSlabIds)->unique();
        $loadedTaxSlabs = $allTaxSlabIds->isNotEmpty()
            ? TaxSlab::whereIn('id', $allTaxSlabIds)->get()->keyBy('id')
            : collect();

        foreach ($items as $item) {
            // Calculate base price for item (handles bundles)
            $price = $this->getCartItemPrice($item);

            $lineSubtotal = $price * $item->quantity;
            $subtotal += $lineSubtotal;

            $imageUrl = asset('assets/images/default-product.png');
            if ($item->product && $item->product->primaryImage) {
                $imageUrl = asset('storage/' . $item->product->primaryImage->image_path);
            }

            // Determine applicable tax slab and tax amount for this line
            $taxSlabData = null;
            $lineTaxAmount = 0;
            if ($item->variant && $item->variant->tax_slab_id) {
                $slab = $loadedTaxSlabs->get($item->variant->tax_slab_id);
            } elseif ($item->product && $item->product->tax_slab_id) {
                $slab = $loadedTaxSlabs->get($item->product->tax_slab_id);
            } else {
                $slab = null;
            }

            if ($slab) {
                $percentage = (float) $slab->tax_percentage;
                $lineTaxAmount = round(($lineSubtotal * $percentage) / 100, 2);
                $taxSlabData = [
                    'name' => $slab->name,
                    'percentage' => $percentage,
                ];
            }

            $taxTotal += $lineTaxAmount;

            $cartItems[] = [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product' => $item->product,
                'variant' => $item->variant,
                'unit' => $item->unit,
                'quantity' => $item->quantity,
                'price' => $price,
                'total' => $lineSubtotal,
                'tax_amount' => $lineTaxAmount,
                'image_url' => $imageUrl,
                'is_bundle' => $item->product && $item->product->product_type == 'bundle',
                'bundle_items' => $this->getBundleItemsForCart($item->product),
                'tax_slab' => $taxSlabData,
            ];
        }

        // Tax is not persisted on cart; this is an on-the-fly estimate for customer summary
        $grandTotal = $subtotal + $taxTotal;

        // Get customer addresses for checkout
        $addresses = collect();
        if ($userId) {
            $addresses = \App\Models\Location::where('customer_id', $userId)
                ->with(['country', 'state', 'city'])
                ->get();
        }

        // Get applied coupon and available coupons
        $appliedCoupon = null;
        $discountAmount = 0;
        $freeItem = null;
        if ($cart->applied_coupon_id) {
            $appliedCoupon = $cart->appliedCoupon;
            $discountAmount = $cart->discount_amount;

            // Get free item details for buyxgetx promotion
            if ($appliedCoupon && $appliedCoupon->type === 'buyxgetx') {
                $promotionService = new PromotionService();
                $freeItem = $promotionService->getFreeItemDetails($appliedCoupon);
            }
        }

        // Get available promotions
        $promotionService = $promotionService ?? new PromotionService();
        $availableCoupons = $promotionService->getAvailablePromotions(collect($items), $userId);

        return view('frontend.cart', compact(
            'cartItems',
            'subtotal',
            'taxTotal',
            'grandTotal',
            'addresses',
            'appliedCoupon',
            'discountAmount',
            'availableCoupons',
            'freeItem'
        ));
    }

    /**
     * Get cart data for sidebar
     */
    public function getCartData(Request $request)
    {
        try {
            $userId = auth('customer')->id();
            $guestId = $this->getOrCreateGuestId($request);

            $cart = $this->getOrCreateCart($userId, $guestId);
            $items = $cart->items()
                ->whereNull('deleted_at')
                ->with(['product.primaryImage', 'variant', 'unit.productUnits'])
                ->get();

            $subtotal = 0;
            $cartItems = [];

            foreach ($items as $item) {
                // Calculate price for item (handles bundles)
                $price = $this->getCartItemPrice($item);

                $itemTotal = $price * $item->quantity;
                $subtotal += $itemTotal;

                $imageUrl = asset('assets/images/default-product.png');
                if ($item->product && $item->product->primaryImage) {
                    $imageUrl = asset('storage/' . $item->product->primaryImage->image_path);
                }

                $cartItems[] = [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product ? $item->product->name : 'Product',
                    'product_type' => $item->product ? $item->product->product_type : 'simple',
                    'variant_name' => $item->variant ? $item->variant->name : null,
                    'unit_name' => $item->unit && $item->unit->unit ? $item->unit->unit->name : null,
                    'quantity' => $item->quantity,
                    'price' => $price,
                    'total' => $itemTotal,
                    'image_url' => $imageUrl,
                    'is_bundle' => $item->product && $item->product->product_type == 'bundle',
                    'bundle_items' => $this->getBundleItemsForCart($item->product),
                ];
            }

            return response()->json([
                'success' => true,
                'items' => $cartItems,
                'subtotal' => $subtotal,
                'item_count' => count($cartItems)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load cart: ' . $e->getMessage(),
                'items' => [],
                'subtotal' => 0,
                'item_count' => 0
            ], 500);
        }
    }

    /**
     * Sync cart and wishlist on login
     */
    public function syncCartAndWishlist(Request $request)
    {
        $userId = auth('customer')->id();
        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }

        try {
            $guestId = $this->getOrCreateGuestId($request);

            // Sync cart
            AwCart::mergeGuestCartToUser($guestId, $userId);

            // Sync wishlist
            AwWishlist::mergeGuestWishlistToUser($guestId, $userId);

            return response()->json([
                'success' => true,
                'message' => 'Cart and wishlist synced'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to sync: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available coupons for cart
     */
    public function getAvailableCoupons(Request $request)
    {
        try {
            $userId = auth('customer')->id();
            $guestId = $this->getOrCreateGuestId($request);

            $cart = $this->getOrCreateCart($userId, $guestId);
            $items = $cart->items()->whereNull('deleted_at')->with(['product'])->get();

            $promotionService = new PromotionService();
            $coupons = $promotionService->getAvailablePromotions($items, $userId);

            return response()->json([
                'success' => true,
                'coupons' => $coupons,
                'applied_coupon' => $cart->applied_coupon_id ? [
                    'id' => $cart->applied_coupon_id,
                    'code' => $cart->coupon_code,
                    'discount' => $cart->discount_amount,
                ] : null,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load coupons: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Apply coupon to cart
     */
    public function applyCoupon(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:50',
        ]);

        try {
            $userId = auth('customer')->id();
            $guestId = $this->getOrCreateGuestId($request);

            $cart = $this->getOrCreateCart($userId, $guestId);
            $items = $cart->items()->whereNull('deleted_at')->with(['product'])->get();

            if ($items->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your cart is empty.'
                ], 422);
            }

            $promotionService = new PromotionService();
            $result = $promotionService->validateCouponCode($request->code, $items, $userId);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 422);
            }

            // Apply coupon to cart
            $promotionService->applyCouponToCart($cart, $result['promotion'], $result['discount']);

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'discount' => $result['discount'],
                'coupon' => [
                    'id' => $result['promotion']->id,
                    'code' => $result['promotion']->code,
                    'name' => $result['promotion']->name,
                    'type' => $result['promotion']->type,
                    'discount_amount' => $result['discount'],
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to apply coupon: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove coupon from cart
     */
    public function removeCoupon(Request $request)
    {
        try {
            $userId = auth('customer')->id();
            $guestId = $this->getOrCreateGuestId($request);

            $cart = $this->getOrCreateCart($userId, $guestId);

            if (!$cart->applied_coupon_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No coupon applied.'
                ], 422);
            }

            $promotionService = new PromotionService();
            $promotionService->removeCouponFromCart($cart);

            return response()->json([
                'success' => true,
                'message' => 'Coupon removed successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove coupon: ' . $e->getMessage()
            ], 500);
        }
    }

    public function search(Request $request)
    {
        $term = trim($request->input('q', ''));

        if ($request->ajax() || $request->wantsJson() || $request->boolean('ajax')) {
            if ($term === '') {
                return response()->json([
                    'products' => [],
                    'categories' => [],
                ]);
            }

            $products = AwProduct::query()
                ->select('id', 'name', 'slug', 'sku')
                ->active()
                ->where(function ($q) use ($term) {
                    $q->where('name', 'like', '%' . $term . '%')
                        ->orWhere('sku', 'like', '%' . $term . '%');
                })
                ->orderBy('name')
                ->limit(3)
                ->get()
                ->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'sku' => $product->sku,
                        'url' => route('product.detail', [
                            'id' => $product->id,
                            'slug' => $product->slug
                        ]),
                    ];
                });

            $variants = AwProductVariant::with(['product'])
                ->select('id', 'name', 'sku', 'product_id')
                ->active()
                ->where(function ($q) use ($term) {
                    $q->where('name', 'like', '%' . $term . '%')
                        ->orWhere('sku', 'like', '%' . $term . '%');
                })
                ->orderBy('name')
                ->limit(3)
                ->get()
                ->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'sku' => $product->sku,
                        'url' => route('product.detail', [
                            'id' => $product->product->id,
                            'slug' => $product->product->slug,
                            'variant' => $product->id
                        ]),
                    ];
                });

            $products = $products
                ->toBase()
                ->merge($variants)
                ->take(6);


            $categories = AwCategory::query()
                ->select('id', 'name', 'slug')
                ->where('status', 1)
                ->where('name', 'like', '%' . $term . '%')
                ->orderBy('name')
                ->limit(6)
                ->get()
                ->map(function ($category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                        'url' => route('products', [
                            'category' => $category->id,
                        ]),
                    ];
                });

            return response()->json([
                'products' => $products,
                'categories' => $categories,
            ]);
        }

        return redirect()->route('home');
    }

    /**
     * Place order from checkout
     */
    public function placeOrder(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
        ]);

        $userId = auth('customer')->id();
        $guestId = $this->getOrCreateGuestId($request);

        $cart = $this->getOrCreateCart($userId, $guestId);
        $items = $cart->items()
            ->whereNull('deleted_at')
            ->with(['product', 'variant', 'unit'])
            ->get();

        if ($items->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Your cart is empty.'], 422);
        }

        // Get shipping address details
        $shippingData = [];

        if ($request->filled('address_id') && $userId) {
            // Use saved address
            $address = \App\Models\Location::where('id', $request->address_id)
                ->where('customer_id', $userId)
                ->with(['country', 'state', 'city'])
                ->first();

            if (!$address) {
                return response()->json(['success' => false, 'message' => 'Selected address not found.'], 422);
            }

            $shippingData = [
                'shipping_address_line_1' => $address->address_line_1,
                'shipping_address_line_2' => $address->address_line_2,
                'shipping_country_id' => $address->country_id,
                'shipping_state_id' => $address->state_id,
                'shipping_city_id' => $address->city_id ?? 0,
                'shipping_zipcode' => $address->zipcode,
                'recipient_name' => $request->name,
                'recipient_contact_number' => $request->phone,
                'recipient_email' => $request->email,
            ];
        } else {
            // Use manual address fields (guest or no saved addresses)
            $request->validate([
                'address_line_1' => 'required|string|max:255',
                'country_id' => 'required|exists:countries,id',
                'state_id' => 'required|exists:states,id',
                'zipcode' => 'required|string|max:20',
            ]);

            $shippingData = [
                'shipping_address_line_1' => $request->address_line_1,
                'shipping_address_line_2' => $request->address_line_2,
                'shipping_country_id' => $request->country_id,
                'shipping_state_id' => $request->state_id,
                'shipping_city_id' => $request->city_id ?? 0,
                'shipping_zipcode' => $request->zipcode,
                'recipient_name' => $request->name,
                'recipient_contact_number' => $request->phone,
                'recipient_email' => $request->email,
            ];
        }

        DB::beginTransaction();
        try {
            // Calculate subtotal and tax based on product/variant tax slab
            $subtotal = 0;
            $taxTotal = 0;
            $computedItems = [];

            foreach ($items as $item) {
                $price = $this->getCartItemPrice($item);
                $lineSubtotal = $price * $item->quantity;

                // Determine applicable tax slab (variant first, then product) for non-bundle items
                $taxSlabId = null;
                $taxPercentage = 0.0;
                if ($item->product && $item->product->product_type === 'bundle') {
                    // Bundles currently have no specific tax slab configured
                    $taxSlabId = null;
                    $taxPercentage = 0.0;
                } elseif ($item->variant && $item->variant->tax_slab_id) {
                    $taxSlabId = $item->variant->tax_slab_id;
                    $slab = TaxSlab::find($taxSlabId);
                    $taxPercentage = $slab?->tax_percentage ?? 0.0;
                } elseif ($item->product && $item->product->tax_slab_id) {
                    $taxSlabId = $item->product->tax_slab_id;
                    $slab = TaxSlab::find($taxSlabId);
                    $taxPercentage = $slab?->tax_percentage ?? 0.0;
                }

                $lineTaxAmount = 0.0;
                if ($taxPercentage > 0) {
                    $lineTaxAmount = round(($lineSubtotal * $taxPercentage) / 100, 2);
                }

                $subtotal += $lineSubtotal;
                $taxTotal += $lineTaxAmount;

                $computedItems[$item->id] = [
                    'unit_price' => $price,
                    'line_subtotal' => $lineSubtotal,
                    'tax_slab_id' => $taxSlabId,
                    'tax_amount' => $lineTaxAmount,
                ];
            }

            // Handle credit utilization
            $creditUsed = 0;
            $paymentMethod = $request->payment_method ?? 'cash_on_delivery';

            if ($userId && $request->has('use_credit') && $request->use_credit == '1') {
                $customer = auth('customer')->user();
                $availableCredit = $customer->credit_balance ?? 0;

                if ($availableCredit > 0) {
                    // Calculate credit to apply (min of available credit or order total)
                    $creditUsed = min($availableCredit, $subtotal);

                    // Deduct credit from customer balance
                    $customer->credit_balance = $availableCredit - $creditUsed;
                    $customer->save();

                    // Log credit usage
                    \App\Models\CreditLog::create([
                        'customer_id' => $userId,
                        'amount' => -$creditUsed,
                        'transaction_type' => 'debit',
                        'description' => 'Credit used for order payment',
                        'balance_after' => $customer->credit_balance,
                    ]);
                }
            }

            // Handle coupon discount
            $couponDiscount = 0;
            $appliedCouponId = null;
            $couponCode = null;
            $promotionType = null;
            if ($cart->applied_coupon_id) {
                $couponDiscount = (float) $cart->discount_amount;
                $appliedCouponId = $cart->applied_coupon_id;
                $couponCode = $cart->coupon_code;
                // Get promotion type for order storage
                $promotion = AwPromotion::find($appliedCouponId);
                $promotionType = $promotion ? $promotion->type : null;
            }

            // Calculate final amounts (subtotal + tax - discounts)
            $totalDiscount = $creditUsed + $couponDiscount;
            $grandTotal = $subtotal + $taxTotal;
            $amountDue = $grandTotal - $totalDiscount;
            $amountDue = max(0, $amountDue); // Ensure non-negative
            $paymentStatus = $amountDue <= 0 ? 'paid' : 'unpaid';

            // Generate order number
            $orderNumber = 'ORD-' . strtoupper(Str::random(8)) . '-' . date('ymd');

            // Create order
            $order = AwOrder::create(array_merge($shippingData, [
                'order_number' => $orderNumber,
                'customer_id' => $userId ?? 0,
                'status' => 'pending',
                'payment_method' => $paymentMethod,
                'payment_status' => $paymentStatus,
                'is_b2b' => $userId ? true : false,
                'billing_address_line_1' => $shippingData['shipping_address_line_1'],
                'billing_address_line_2' => $shippingData['shipping_address_line_2'],
                'billing_country_id' => $shippingData['shipping_country_id'],
                'billing_state_id' => $shippingData['shipping_state_id'],
                'billing_city_id' => $shippingData['shipping_city_id'],
                'billing_zipcode' => $shippingData['shipping_zipcode'],
                'billing_name' => $request->name,
                'billing_contact_number' => $request->phone,
                'billing_email' => $request->email,
                'sub_total' => $subtotal,
                'tax_total' => $taxTotal,
                'shipping_total' => 0,
                'discount_total' => $totalDiscount, // Credit + Coupon discount
                'promotion_id' => $appliedCouponId,
                'promotion_code' => $couponCode,
                'promotion_type' => $promotionType,
                'promotion_discount' => $couponDiscount,
                'grand_total' => $grandTotal,
                'amount_due' => $amountDue,
                'credit_utilization' => $creditUsed,
                'notes' => $request->notes,
            ]));

            // Create order items (using pre-computed pricing and tax for consistency)
            foreach ($items as $item) {
                $computed = $computedItems[$item->id] ?? null;
                if (!$computed) {
                    continue;
                }

                $price = $computed['unit_price'];
                $itemTotal = $computed['line_subtotal'];

                AwOrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'variant_id' => $item->variant_id,
                    'unit_id' => $item->unit_id ?? 1,
                    'product_name' => $item->product->name ?? 'Product',
                    'sku' => $item->product->sku ?? $item->variant?->sku ?? 'N/A',
                    'quantity' => $item->quantity,
                    'unit_price' => $price,
                    'tax_amount' => $computed['tax_amount'],
                    'discount_amount' => 0,
                    'total' => $itemTotal,
                    'is_bundle_parent' => $item->product && $item->product->product_type == 'bundle',
                    'tax_slab_id' => $computed['tax_slab_id'],
                ]);
            }

            // Add free item for buyxgetx promotion
            if ($appliedCouponId && $promotionType === 'buyxgetx') {
                $promotion = AwPromotion::find($appliedCouponId);
                if ($promotion) {
                    $promotionService = new PromotionService();
                    $freeItemDetails = $promotionService->getFreeItemDetails($promotion);
                    if ($freeItemDetails) {
                        AwOrderItem::create([
                            'order_id' => $order->id,
                            'product_id' => $freeItemDetails['product_id'],
                            'variant_id' => $freeItemDetails['variant']?->id,
                            'unit_id' => $freeItemDetails['unit']?->id ?? 1,
                            'product_name' => $freeItemDetails['product_name'],
                            'sku' => $freeItemDetails['product']->sku ?? 'N/A',
                            'quantity' => $freeItemDetails['quantity'],
                            'unit_price' => $freeItemDetails['price'],
                            'tax_amount' => 0,
                            'discount_amount' => $freeItemDetails['price'] * $freeItemDetails['quantity'], // Full price as discount
                            'total' => 0, // Free item
                            'is_bundle_parent' => $freeItemDetails['is_bundle'],
                            'is_free_item' => true,
                            'free_from_promotion_id' => $appliedCouponId,
                        ]);
                    }
                }
            }

            // Record coupon usage
            if ($appliedCouponId && $userId) {
                $promotionService = $promotionService ?? new PromotionService();
                $promotionService->recordUsage($appliedCouponId, $userId, $order->id, $couponDiscount);
            }

            // Clear cart items and coupon
            $cart->items()->delete();
            $cart->update([
                'applied_coupon_id' => null,
                'coupon_code' => null,
                'discount_amount' => 0,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order placed successfully! Order #' . $orderNumber,
                'order_number' => $orderNumber,
                'redirect' => route('order.success', $order->id),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order placement failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to place order. Please try again.'], 500);
        }
    }

    /**
     * Show order success page
     */
    public function orderSuccess($id)
    {
        $order = AwOrder::with([
            'items.product.primaryImage',
            'items.variant',
            'shippingCountry',
            'shippingState',
            'shippingCity'
        ])->findOrFail($id);

        return view('frontend.order-success', compact('order'));
    }

    /**
     * Download order invoice as PDF
     */
    public function downloadInvoice($id)
    {
        $order = AwOrder::with([
            'items.product',
            'items.variant',
            'items.unit',
            'customer',
            'shippingCountry',
            'shippingState',
            'shippingCity',
            'billingCountry',
            'billingState',
            'billingCity'
        ])->findOrFail($id);

        $pdf = \PDF::loadView('frontend.invoice-pdf', compact('order'));

        return $pdf->download('invoice-' . $order->order_number . '.pdf');
    }

    /**
     * Set the user's preferred currency
     */
    public function setCurrency(Request $request)
    {
        $request->validate([
            'currency_id' => 'required|exists:aw_currencies,id',
        ]);

        $currencyService = app(CurrencyService::class);
        $currencyService->setSelectedCurrency($request->currency_id);

        // Update cart currency if user has a cart
        $userId = auth('customer')->id();
        $guestId = $this->getOrCreateGuestId($request);

        $cart = AwCart::when($userId, fn($q) => $q->where('user_id', $userId))
            ->when(!$userId && $guestId, fn($q) => $q->where('guest_id', $guestId))
            ->first();

        if ($cart) {
            $cart->update(['currency_id' => $request->currency_id]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Currency updated successfully.',
            'currency' => $currencyService->getJsConfig(),
        ]);
    }
}
