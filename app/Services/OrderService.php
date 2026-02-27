<?php

namespace App\Services;

use App\Models\AwOrder;
use App\Models\AwOrderItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Exception;

class OrderService
{
    /**
     * Create a new order
     */
    public function createOrder(array $orderData, array $items, ?int $createdBy = null): AwOrder
    {
        return DB::transaction(function () use ($orderData, $items, $createdBy) {
            // Generate order number
            $orderData['order_number'] = AwOrder::generateOrderNumber();

            // Set source based on creator
            if ($createdBy) {
                $orderData['created_by'] = $createdBy;
                $orderData['source'] = AwOrder::SOURCE_ADMIN;
            } else {
                $orderData['source'] = AwOrder::SOURCE_CUSTOMER;
            }

            // Set default status
            $orderData['status'] = $orderData['status'] ?? AwOrder::STATUS_PENDING;
            $orderData['payment_status'] = $orderData['payment_status'] ?? AwOrder::PAYMENT_UNPAID;

            // Create the order
            $order = AwOrder::create($orderData);

            // Add items
            $this->addOrderItems($order, $items);

            // Calculate totals
            $order->recalculateTotals();

            // Log initial status
            $order->logStatusChange('', 'Order created', $createdBy);

            return $order->fresh(['items', 'customer', 'statusHistory']);
        });
    }

    /**
     * Update an existing order
     */
    public function updateOrder(AwOrder $order, array $orderData): AwOrder
    {
        return DB::transaction(function () use ($order, $orderData) {
            $order->update($orderData);
            return $order->fresh();
        });
    }

    /**
     * Add items to an order
     */
    public function addOrderItems(AwOrder $order, array $items): void
    {
        foreach ($items as $itemData) {
            $this->addOrderItem($order, $itemData);
        }
    }

    /**
     * Add a single item to an order
     */
    public function addOrderItem(AwOrder $order, array $itemData): AwOrderItem
    {
        $product = \App\Models\AwProduct::find($itemData['product_id']);

        $item = $order->items()->create([
            'product_id' => $itemData['product_id'],
            'variant_id' => $itemData['variant_id'] ?? null,
            'unit_id' => $itemData['unit_id'],
            'warehouse_id' => $itemData['warehouse_id'] ?? null,
            'product_name' => $product->name ?? $itemData['product_name'] ?? 'Unknown Product',
            'sku' => $product->sku ?? $itemData['sku'] ?? '',
            'quantity' => $itemData['quantity'],
            'unit_price' => $itemData['unit_price'],
            'tax_amount' => $itemData['tax_amount'] ?? 0,
            'discount_amount' => $itemData['discount_amount'] ?? 0,
            'total' => ($itemData['unit_price'] * $itemData['quantity']) - ($itemData['discount_amount'] ?? 0),
            'is_bundle_parent' => $itemData['is_bundle_parent'] ?? false,
            'parent_item_id' => $itemData['parent_item_id'] ?? null,
            'is_free_gift' => $itemData['is_free_gift'] ?? false,
            'promotion_id' => $itemData['promotion_id'] ?? null,
            'tax_slab_id' => $itemData['tax_slab_id'] ?? null,
        ]);

        return $item;
    }

    /**
     * Update an order item
     */
    public function updateOrderItem(AwOrderItem $item, array $itemData): AwOrderItem
    {
        return DB::transaction(function () use ($item, $itemData) {
            $quantity = $itemData['quantity'] ?? $item->quantity;
            $unitPrice = $itemData['unit_price'] ?? $item->unit_price;
            $discountAmount = $itemData['discount_amount'] ?? $item->discount_amount;
            $taxAmount = $itemData['tax_amount'] ?? $item->tax_amount;

            $item->update([
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discountAmount,
                'total' => ($unitPrice * $quantity) - $discountAmount,
                'tax_slab_id' => $itemData['tax_slab_id'] ?? $item->tax_slab_id,
            ]);

            // Recalculate order totals
            $item->order->recalculateTotals();

            return $item->fresh();
        });
    }

    /**
     * Remove an item from an order
     */
    public function removeOrderItem(AwOrderItem $item): bool
    {
        return DB::transaction(function () use ($item) {
            $order = $item->order;
            $item->delete();
            $order->recalculateTotals();
            return true;
        });
    }

    /**
     * Update order status
     */
    public function updateStatus(AwOrder $order, string $newStatus, ?string $comment = null, ?int $changedBy = null): AwOrder
    {
        return DB::transaction(function () use ($order, $newStatus, $comment, $changedBy) {
            $previousStatus = $order->status;

            // Update timestamps based on status
            $timestamps = $this->getStatusTimestampField($newStatus);
            $updateData = ['status' => $newStatus];

            if ($timestamps) {
                $updateData[$timestamps] = now();
            }

            // Handle rejection notes
            if ($newStatus === AwOrder::STATUS_REJECTED && $comment) {
                $updateData['rejection_notes'] = $comment;
            }

            $order->update($updateData);

            // Log the status change
            $order->logStatusChange($previousStatus, $comment, $changedBy);

            return $order->fresh(['statusHistory']);
        });
    }

    /**
     * Get timestamp field for status
     */
    private function getStatusTimestampField(string $status): ?string
    {
        return match ($status) {
            AwOrder::STATUS_CONFIRMED => 'confirmed_at',
            AwOrder::STATUS_PROCESSING => 'processed_at',
            AwOrder::STATUS_SHIPPED => 'shipped_at',
            AwOrder::STATUS_DELIVERED => 'delivered_at',
            AwOrder::STATUS_CANCELLED => 'cancelled_at',
            AwOrder::STATUS_RETURNED => 'returned_at',
            AwOrder::STATUS_REJECTED => 'rejected_at',
            default => null,
        };
    }

    /**
     * Bulk update order status
     */
    public function bulkUpdateStatus(array $orderIds, string $newStatus, ?string $comment = null, ?int $changedBy = null): int
    {
        $updated = 0;

        foreach ($orderIds as $orderId) {
            try {
                $order = AwOrder::find($orderId);
                if ($order && array_key_exists($newStatus, $order->getAllowedNextStatuses())) {
                    $this->updateStatus($order, $newStatus, $comment, $changedBy);
                    $updated++;
                }
            } catch (Exception $e) {
                // Log error but continue with other orders
                logger()->error("Failed to update order {$orderId}: " . $e->getMessage());
            }
        }

        return $updated;
    }

    /**
     * Calculate order totals from items
     */
    public function calculateTotals(array $items): array
    {
        $subTotal = 0;
        $taxTotal = 0;
        $discountTotal = 0;

        foreach ($items as $item) {
            $lineTotal = ($item['unit_price'] * $item['quantity']);
            $subTotal += $lineTotal;
            $taxTotal += $item['tax_amount'] ?? 0;
            $discountTotal += $item['discount_amount'] ?? 0;
        }

        return [
            'sub_total' => $subTotal,
            'tax_total' => $taxTotal,
            'discount_total' => $discountTotal,
            'grand_total' => ($subTotal + $taxTotal) - $discountTotal,
        ];
    }

    /**
     * Update payment information
     */
    public function updatePayment(AwOrder $order, float $amountPaid, ?string $paymentMethod = null): AwOrder
    {
        return DB::transaction(function () use ($order, $amountPaid, $paymentMethod) {
            $totalPaid = $order->amount_paid + $amountPaid;
            $amountDue = $order->grand_total - $totalPaid - $order->credit_utilization;

            $paymentStatus = match (true) {
                $amountDue <= 0 => AwOrder::PAYMENT_PAID,
                $totalPaid > 0 => AwOrder::PAYMENT_PARTIALLY_PAID,
                default => AwOrder::PAYMENT_UNPAID,
            };

            $order->update([
                'amount_paid' => $totalPaid,
                'amount_due' => max(0, $amountDue),
                'payment_status' => $paymentStatus,
                'payment_method' => $paymentMethod ?? $order->payment_method,
            ]);

            return $order->fresh();
        });
    }

    /**
     * Get order statistics for dashboard
     */
    public function getOrderStats(?string $startDate = null, ?string $endDate = null): array
    {
        $query = AwOrder::query();

        if ($startDate && $endDate) {
            $query->dateRange($startDate, $endDate);
        }

        return [
            'total_orders' => $query->count(),
            'pending_orders' => (clone $query)->status(AwOrder::STATUS_PENDING)->count(),
            'processing_orders' => (clone $query)->status([AwOrder::STATUS_CONFIRMED, AwOrder::STATUS_PROCESSING, AwOrder::STATUS_PACKED])->count(),
            'shipped_orders' => (clone $query)->status(AwOrder::STATUS_SHIPPED)->count(),
            'delivered_orders' => (clone $query)->status(AwOrder::STATUS_DELIVERED)->count(),
            'total_revenue' => (clone $query)->status(AwOrder::STATUS_DELIVERED)->sum('grand_total'),
            'admin_orders' => (clone $query)->source(AwOrder::SOURCE_ADMIN)->count(),
            'customer_orders' => (clone $query)->source(AwOrder::SOURCE_CUSTOMER)->count(),
        ];
    }
}
