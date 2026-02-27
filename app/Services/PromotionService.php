<?php

namespace App\Services;

use App\Models\AwPromotion;
use App\Models\AwPromotionUsage;
use App\Models\AwCart;
use App\Models\AwCartItem;
use App\Models\AwProduct;
use App\Models\AwProductVariant;
use Illuminate\Support\Collection;

class PromotionService
{
    /**
     * Get all available promotions for the cart
     */
    public function getAvailablePromotions(Collection $cartItems, ?int $customerId = null): array
    {
        $promotions = AwPromotion::active()->valid()->get();
        $available = [];
        $cartTotal = $this->calculateCartTotal($cartItems);

        foreach ($promotions as $promotion) {
            $eligibility = $this->checkEligibility($promotion, $cartItems, $customerId, $cartTotal);

            $available[] = [
                'id' => $promotion->id,
                'code' => $promotion->code,
                'name' => $promotion->name,
                'type' => $promotion->type,
                'type_label' => $promotion->type_label,
                'description' => $promotion->description,
                'discount_type' => $promotion->discount_type, // 0 = Percentage, 1 = Fixed
                'discount_amount' => $promotion->discount_amount,
                'cart_minimum_amount' => $promotion->cart_minimum_amount,
                'poster_url' => $promotion->poster_url,
                'auto_applicable' => $promotion->auto_applicable,
                'is_eligible' => $eligibility['eligible'],
                'eligibility_message' => $eligibility['message'],
                'potential_discount' => $eligibility['eligible'] ? $this->calculateDiscount($promotion, $cartItems, $cartTotal) : 0,
                'progress' => $eligibility['progress'] ?? 0,
            ];
        }

        // Sort: eligible first, then by discount amount
        usort($available, function ($a, $b) {
            if ($a['is_eligible'] !== $b['is_eligible']) {
                return $b['is_eligible'] <=> $a['is_eligible'];
            }
            return $b['potential_discount'] <=> $a['potential_discount'];
        });

        return $available;
    }

    /**
     * Check if cart is eligible for a promotion
     */
    public function checkEligibility(AwPromotion $promotion, Collection $cartItems, ?int $customerId, float $cartTotal = 0): array
    {
        // Check usage limit
        if ($customerId && $promotion->application_limit > 0) {
            $usageCount = $this->getUsageCount($promotion->id, $customerId);
            if ($usageCount >= $promotion->application_limit) {
                return [
                    'eligible' => false,
                    'message' => 'You have already used this coupon the maximum number of times.',
                    'progress' => 100,
                ];
            }
        }

        // Check by promotion type
        switch ($promotion->type) {
            case 'cardisc':
                return $this->checkCartDiscountEligibility($promotion, $cartTotal);

            case 'catdisc':
                return $this->checkCategoryDiscountEligibility($promotion, $cartItems, $cartTotal);

            case 'prodisc':
                return $this->checkProductDiscountEligibility($promotion, $cartItems);

            case 'buyxgetx':
                return $this->checkBuyXGetYEligibility($promotion, $cartItems);

            default:
                return ['eligible' => false, 'message' => 'Invalid promotion type.', 'progress' => 0];
        }
    }

    /**
     * Check eligibility for cart discount (cardisc)
     */
    private function checkCartDiscountEligibility(AwPromotion $promotion, float $cartTotal): array
    {
        $minAmount = $promotion->cart_minimum_amount ?? 0;

        if ($cartTotal < $minAmount) {
            $remaining = $minAmount - $cartTotal;
            $progress = ($cartTotal / $minAmount) * 100;
            return [
                'eligible' => false,
                'message' => "Add \${$remaining} more to unlock this offer!",
                'progress' => round($progress, 0),
            ];
        }

        return ['eligible' => true, 'message' => 'Eligible!', 'progress' => 100];
    }

    /**
     * Check eligibility for category discount (catdisc)
     */
    private function checkCategoryDiscountEligibility(AwPromotion $promotion, Collection $cartItems, float $cartTotal): array
    {
        $categoryIds = $promotion->category_id ?? [];
        if (empty($categoryIds)) {
            return ['eligible' => false, 'message' => 'No categories configured.', 'progress' => 0];
        }

        $eligibleTotal = 0;
        foreach ($cartItems as $item) {
            $product = $item->product ?? AwProduct::find($item->product_id);
            if ($product && in_array($product->category_id, $categoryIds)) {
                $eligibleTotal += ($item->quantity * $this->getItemPrice($item));
            }
        }

        if ($eligibleTotal <= 0) {
            return [
                'eligible' => false,
                'message' => 'Add products from eligible categories to use this coupon.',
                'progress' => 0,
            ];
        }

        $minAmount = $promotion->cart_minimum_amount ?? 0;
        if ($minAmount > 0 && $eligibleTotal < $minAmount) {
            $remaining = $minAmount - $eligibleTotal;
            $progress = ($eligibleTotal / $minAmount) * 100;
            return [
                'eligible' => false,
                'message' => "Add \${$remaining} more from eligible categories to unlock!",
                'progress' => round($progress, 0),
            ];
        }

        return ['eligible' => true, 'message' => 'Eligible!', 'progress' => 100];
    }

    /**
     * Check eligibility for product discount (prodisc)
     */
    private function checkProductDiscountEligibility(AwPromotion $promotion, Collection $cartItems): array
    {
        $productIds = $promotion->product_id ?? [];
        $variantIds = $promotion->variant_id ?? [];
        $unitIds = $promotion->unit_id ?? [];

        if (empty($productIds) && empty($variantIds) && empty($unitIds)) {
            return ['eligible' => false, 'message' => 'No products configured.', 'progress' => 0];
        }

        foreach ($cartItems as $item) {
            // Check product match
            if (!empty($productIds) && in_array($item->product_id, $productIds)) {
                // Check variant if specified
                if (!empty($variantIds) && $item->variant_id && !in_array($item->variant_id, $variantIds)) {
                    continue;
                }
                // Check unit if specified
                if (!empty($unitIds) && $item->unit_id && !in_array($item->unit_id, $unitIds)) {
                    continue;
                }
                return ['eligible' => true, 'message' => 'Eligible!', 'progress' => 100];
            }
        }

        return [
            'eligible' => false,
            'message' => 'Add eligible products to use this coupon.',
            'progress' => 0,
        ];
    }

    /**
     * Check eligibility for Buy X Get Y (buyxgetx)
     */
    private function checkBuyXGetYEligibility(AwPromotion $promotion, Collection $cartItems): array
    {
        $xProductId = $promotion->x_product;
        $xVariantId = $promotion->x_variant;
        $xUnitId = $promotion->x_unit;
        $xQuantity = $promotion->x_quantity ?? 1;

        if (!$xProductId) {
            return ['eligible' => false, 'message' => 'Promotion not configured properly.', 'progress' => 0];
        }

        $foundQuantity = 0;
        foreach ($cartItems as $item) {
            if ($item->product_id == $xProductId) {
                // Check variant if specified
                if ($xVariantId && $item->variant_id != $xVariantId) {
                    continue;
                }
                // Check unit if specified
                if ($xUnitId && $item->unit_id != $xUnitId) {
                    continue;
                }
                $foundQuantity += $item->quantity;
            }
        }

        if ($foundQuantity < $xQuantity) {
            $progress = ($foundQuantity / $xQuantity) * 100;
            $remaining = $xQuantity - $foundQuantity;
            $productName = AwProduct::find($xProductId)?->name ?? 'required product';
            return [
                'eligible' => false,
                'message' => "Add {$remaining} more {$productName} to unlock!",
                'progress' => round($progress, 0),
            ];
        }

        return ['eligible' => true, 'message' => 'Eligible!', 'progress' => 100];
    }

    /**
     * Calculate discount amount for a promotion
     */
    public function calculateDiscount(AwPromotion $promotion, Collection $cartItems, float $cartTotal = 0): float
    {
        if ($cartTotal === 0.0) {
            $cartTotal = $this->calculateCartTotal($cartItems);
        }

        switch ($promotion->type) {
            case 'cardisc':
                return $this->calculateCartDiscount($promotion, $cartTotal);

            case 'catdisc':
                return $this->calculateCategoryDiscount($promotion, $cartItems);

            case 'prodisc':
                return $this->calculateProductDiscount($promotion, $cartItems);

            case 'buyxgetx':
                return $this->calculateBuyXGetYDiscount($promotion, $cartItems);

            default:
                return 0;
        }
    }

    /**
     * Calculate cart-wide discount (cardisc)
     */
    private function calculateCartDiscount(AwPromotion $promotion, float $cartTotal): float
    {
        if ($promotion->discount_type) {
            // Fixed discount
            return min($promotion->discount_amount, $cartTotal);
        } else {
            // Percentage discount
            return round(($cartTotal * $promotion->discount_amount) / 100, 2);
        }
    }

    /**
     * Calculate category discount (catdisc)
     */
    private function calculateCategoryDiscount(AwPromotion $promotion, Collection $cartItems): float
    {
        $categoryIds = $promotion->category_id ?? [];
        $eligibleTotal = 0;

        foreach ($cartItems as $item) {
            $product = $item->product ?? AwProduct::find($item->product_id);
            if ($product && in_array($product->category_id, $categoryIds)) {
                $eligibleTotal += ($item->quantity * $this->getItemPrice($item));
            }
        }

        if ($promotion->discount_type) {
            return min($promotion->discount_amount, $eligibleTotal);
        } else {
            return round(($eligibleTotal * $promotion->discount_amount) / 100, 2);
        }
    }

    /**
     * Calculate product discount (prodisc)
     */
    private function calculateProductDiscount(AwPromotion $promotion, Collection $cartItems): float
    {
        $productIds = $promotion->product_id ?? [];
        $variantIds = $promotion->variant_id ?? [];
        $unitIds = $promotion->unit_id ?? [];
        $eligibleTotal = 0;

        foreach ($cartItems as $item) {
            if (!empty($productIds) && in_array($item->product_id, $productIds)) {
                if (!empty($variantIds) && $item->variant_id && !in_array($item->variant_id, $variantIds)) {
                    continue;
                }
                if (!empty($unitIds) && $item->unit_id && !in_array($item->unit_id, $unitIds)) {
                    continue;
                }
                $eligibleTotal += ($item->quantity * $this->getItemPrice($item));
            }
        }

        if ($promotion->discount_type) {
            return min($promotion->discount_amount, $eligibleTotal);
        } else {
            return round(($eligibleTotal * $promotion->discount_amount) / 100, 2);
        }
    }

    /**
     * Calculate Buy X Get Y discount (buyxgetx)
     */
    private function calculateBuyXGetYDiscount(AwPromotion $promotion, Collection $cartItems): float
    {
        $yProductId = $promotion->y_item;
        $yVariantId = $promotion->y_variant;
        $yUnitId = $promotion->y_unit;
        $yQuantity = $promotion->y_quantity ?? 1;

        if (!$yProductId) {
            return 0;
        }

        // Get the price of the free item
        $product = AwProduct::with(['prices', 'bundle'])->find($yProductId);
        if (!$product) {
            return 0;
        }

        // Get price based on variant/unit or default price
        $price = $this->getProductPrice($product, $yVariantId, $yUnitId);

        return round($price * $yQuantity, 2);
    }

    /**
     * Get free item details for buyxgetx promotion
     */
    public function getFreeItemDetails(AwPromotion $promotion): ?array
    {
        if ($promotion->type !== 'buyxgetx') {
            return null;
        }

        $yProductId = $promotion->y_item;
        $yVariantId = $promotion->y_variant;
        $yUnitId = $promotion->y_unit;
        $yQuantity = $promotion->y_quantity ?? 1;

        if (!$yProductId) {
            return null;
        }

        // Get the free product with images
        $product = AwProduct::with(['primaryImage', 'brand'])->find($yProductId);
        if (!$product) {
            return null;
        }

        // Get variant if specified
        $variant = null;
        if ($yVariantId) {
            $variant = AwProductVariant::find($yVariantId);
        }

        // Get unit if specified
        $unit = null;
        if ($yUnitId) {
            $unit = \App\Models\AwProductUnit::with('unit')->find($yUnitId);
        }

        // Get the price (for display purposes - will be shown as FREE)
        $price = $this->getProductPrice($product, $yVariantId, $yUnitId);

        // Get image URL
        $imageUrl = asset('assets/images/default-product.png');
        if ($product->primaryImage) {
            $imageUrl = asset('storage/' . $product->primaryImage->image_path);
        }

        return [
            'is_free_item' => true,
            'promotion_id' => $promotion->id,
            'promotion_code' => $promotion->code,
            'product_id' => $product->id,
            'product' => $product,
            'product_name' => $product->name,
            'variant' => $variant,
            'variant_name' => $variant?->name,
            'unit' => $unit,
            'unit_name' => $unit?->unit?->name,
            'quantity' => $yQuantity,
            'price' => $price,
            'total' => 0, // Free item
            'image_url' => $imageUrl,
            'is_bundle' => $product->product_type === 'bundle',
        ];
    }

    /**
     * Validate and apply a coupon code
     */
    public function validateCouponCode(string $code, Collection $cartItems, ?int $customerId = null): array
    {
        $promotion = AwPromotion::active()->valid()->where('code', strtoupper($code))->first();

        if (!$promotion) {
            return [
                'success' => false,
                'message' => 'Invalid coupon code. Please check and try again.',
            ];
        }

        $cartTotal = $this->calculateCartTotal($cartItems);
        $eligibility = $this->checkEligibility($promotion, $cartItems, $customerId, $cartTotal);

        if (!$eligibility['eligible']) {
            return [
                'success' => false,
                'message' => $eligibility['message'],
            ];
        }

        $discount = $this->calculateDiscount($promotion, $cartItems, $cartTotal);

        return [
            'success' => true,
            'message' => 'Coupon applied successfully!',
            'promotion' => $promotion,
            'discount' => $discount,
        ];
    }

    /**
     * Apply coupon to cart
     */
    public function applyCouponToCart(AwCart $cart, AwPromotion $promotion, float $discount): void
    {
        $cart->update([
            'applied_coupon_id' => $promotion->id,
            'coupon_code' => $promotion->code,
            'discount_amount' => $discount,
        ]);
    }

    /**
     * Remove coupon from cart
     */
    public function removeCouponFromCart(AwCart $cart): void
    {
        $cart->update([
            'applied_coupon_id' => null,
            'coupon_code' => null,
            'discount_amount' => 0,
        ]);
    }

    /**
     * Record promotion usage
     */
    public function recordUsage(int $promotionId, int $customerId, int $orderId, float $discountAmount): void
    {
        AwPromotionUsage::create([
            'promotion_id' => $promotionId,
            'customer_id' => $customerId,
            'order_id' => $orderId,
            'discount_amount' => $discountAmount,
            'used_at' => now(),
        ]);
    }

    /**
     * Get usage count for a promotion by customer
     */
    public function getUsageCount(int $promotionId, int $customerId): int
    {
        return AwPromotionUsage::where('promotion_id', $promotionId)
            ->where('customer_id', $customerId)
            ->count();
    }

    /**
     * Helper: Calculate total cart value
     */
    private function calculateCartTotal(Collection $cartItems): float
    {
        $total = 0;
        foreach ($cartItems as $item) {
            $total += ($item->quantity * $this->getItemPrice($item));
        }
        return $total;
    }

    /**
     * Helper: Get price for a cart item
     */
    private function getItemPrice($item): float
    {
        // If item has price attribute
        if (isset($item->price) && $item->price > 0) {
            return (float) $item->price;
        }

        // Fallback: calculate from product
        $product = $item->product ?? AwProduct::find($item->product_id);
        if (!$product) {
            return 0;
        }

        return $this->getProductPrice($product, $item->variant_id ?? null, $item->unit_id ?? null);
    }

    /**
     * Helper: Get product price considering variant and unit
     */
    private function getProductPrice(AwProduct $product, ?int $variantId = null, ?int $unitId = null): float
    {
        // For bundles, use bundle total
        if ($product->product_type === 'bundle' && $product->bundle) {
            return (float) $product->bundle->total;
        }

        // Look for specific price in aw_prices
        $priceQuery = $product->prices()
            ->where(function ($query) use ($variantId) {
                $query->where('variant_id', $variantId)
                    ->orWhereNull('variant_id');
            })
            ->where(function ($query) use ($unitId) {
                $query->where('unit_id', $unitId)
                    ->orWhereNull('unit_id');
            });

        $priceRecord = $priceQuery->orderByRaw('variant_id IS NULL, unit_id IS NULL')->first();

        if ($priceRecord) {
            return (float) $priceRecord->base_price;
        }

        return 0;
    }
}
