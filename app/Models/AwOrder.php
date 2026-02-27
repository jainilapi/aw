<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class AwOrder extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    /**
     * Order status constants
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_PACKED = 'packed';
    public const STATUS_SHIPPED = 'shipped';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_RETURNED = 'returned';

    /**
     * Payment status constants
     */
    public const PAYMENT_UNPAID = 'unpaid';
    public const PAYMENT_PARTIALLY_PAID = 'partially_paid';
    public const PAYMENT_PAID = 'paid';
    public const PAYMENT_REFUNDED = 'refunded';

    /**
     * Order source constants
     */
    public const SOURCE_CUSTOMER = 'customer';
    public const SOURCE_ADMIN = 'admin';

    protected $casts = [
        'is_b2b' => 'boolean',
        'confirmed_at' => 'datetime',
        'processed_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'returned_at' => 'datetime',
        'rejected_at' => 'datetime',
        'sub_total' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'shipping_total' => 'decimal:2',
        'discount_total' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'credit_utilization' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'amount_due' => 'decimal:2',
        // Multi-currency support
        'exchange_rate_at_order' => 'decimal:6',
        'converted_sub_total' => 'decimal:2',
        'converted_grand_total' => 'decimal:2',
    ];

    /**
     * Get all available statuses with labels
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_CONFIRMED => 'Confirmed',
            self::STATUS_PROCESSING => 'Processing',
            self::STATUS_PACKED => 'Packed',
            self::STATUS_SHIPPED => 'Shipped',
            self::STATUS_DELIVERED => 'Delivered',
            self::STATUS_CANCELLED => 'Cancelled',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_RETURNED => 'Returned',
        ];
    }

    /**
     * Get all payment statuses with labels
     */
    public static function getPaymentStatuses(): array
    {
        return [
            self::PAYMENT_UNPAID => 'Unpaid',
            self::PAYMENT_PARTIALLY_PAID => 'Partially Paid',
            self::PAYMENT_PAID => 'Paid',
            self::PAYMENT_REFUNDED => 'Refunded',
        ];
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return self::getStatuses()[$this->status] ?? ucfirst($this->status);
    }

    /**
     * Get status badge HTML
     */
    public function getStatusBadgeAttribute(): string
    {
        $colors = [
            self::STATUS_PENDING => 'warning',
            self::STATUS_CONFIRMED => 'info',
            self::STATUS_PROCESSING => 'primary',
            self::STATUS_PACKED => 'secondary',
            self::STATUS_SHIPPED => 'info',
            self::STATUS_DELIVERED => 'success',
            self::STATUS_CANCELLED => 'danger',
            self::STATUS_REJECTED => 'dark',
            self::STATUS_RETURNED => 'warning',
        ];

        $color = $colors[$this->status] ?? 'secondary';
        $label = $this->status_label;

        return "<span class=\"badge bg-{$color}\">{$label}</span>";
    }

    /**
     * Get payment status label
     */
    public function getPaymentStatusLabelAttribute(): string
    {
        return self::getPaymentStatuses()[$this->payment_status] ?? ucfirst($this->payment_status);
    }

    /**
     * Get payment status badge HTML
     */
    public function getPaymentStatusBadgeAttribute(): string
    {
        $colors = [
            self::PAYMENT_UNPAID => 'danger',
            self::PAYMENT_PARTIALLY_PAID => 'warning',
            self::PAYMENT_PAID => 'success',
            self::PAYMENT_REFUNDED => 'info',
        ];

        $color = $colors[$this->payment_status] ?? 'secondary';
        $label = $this->payment_status_label;

        return "<span class=\"badge bg-{$color}\">{$label}</span>";
    }

    /**
     * Get source label
     */
    public function getSourceLabelAttribute(): string
    {
        return $this->source === self::SOURCE_ADMIN ? 'Admin Created' : 'Customer Placed';
    }

    /**
     * Get source badge HTML
     */
    public function getSourceBadgeAttribute(): string
    {
        $color = $this->source === self::SOURCE_ADMIN ? 'info' : 'primary';
        $label = $this->source_label;

        return "<span class=\"badge bg-{$color}\">{$label}</span>";
    }

    /**
     * Check if order can be edited
     */
    public function isEditable(): bool
    {
        return in_array($this->status, [
            self::STATUS_PENDING,
            self::STATUS_CONFIRMED,
            self::STATUS_PROCESSING,
        ]);
    }

    /**
     * Check if order can be cancelled
     */
    public function isCancellable(): bool
    {
        return in_array($this->status, [
            self::STATUS_PENDING,
            self::STATUS_CONFIRMED,
            self::STATUS_PROCESSING,
            self::STATUS_PACKED,
        ]);
    }

    /**
     * Get allowed next statuses
     */
    public function getAllowedNextStatuses(): array
    {
        $transitions = [
            self::STATUS_PENDING => [self::STATUS_CONFIRMED, self::STATUS_CANCELLED, self::STATUS_REJECTED],
            self::STATUS_CONFIRMED => [self::STATUS_PROCESSING, self::STATUS_CANCELLED, self::STATUS_REJECTED],
            self::STATUS_PROCESSING => [self::STATUS_PACKED, self::STATUS_CANCELLED],
            self::STATUS_PACKED => [self::STATUS_SHIPPED, self::STATUS_CANCELLED],
            self::STATUS_SHIPPED => [self::STATUS_DELIVERED, self::STATUS_RETURNED],
            self::STATUS_DELIVERED => [self::STATUS_RETURNED],
            self::STATUS_CANCELLED => [],
            self::STATUS_REJECTED => [],
            self::STATUS_RETURNED => [],
        ];

        $allowed = $transitions[$this->status] ?? [];
        $statuses = self::getStatuses();

        return array_intersect_key($statuses, array_flip($allowed));
    }

    // ==================== RELATIONSHIPS ====================

    public function items(): HasMany
    {
        return $this->hasMany(AwOrderItem::class, 'order_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(AwOrderStatusHistory::class, 'order_id')->orderBy('created_at', 'desc');
    }

    public function shippingCountry(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Country::class, 'shipping_country_id');
    }

    public function shippingState(): BelongsTo
    {
        return $this->belongsTo(\App\Models\State::class, 'shipping_state_id');
    }

    public function shippingCity(): BelongsTo
    {
        return $this->belongsTo(\App\Models\City::class, 'shipping_city_id');
    }

    public function billingCountry(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Country::class, 'billing_country_id');
    }

    public function billingState(): BelongsTo
    {
        return $this->belongsTo(\App\Models\State::class, 'billing_state_id');
    }

    public function billingCity(): BelongsTo
    {
        return $this->belongsTo(\App\Models\City::class, 'billing_city_id');
    }

    /**
     * Get the currency used for this order
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(AwCurrency::class, 'currency_id');
    }

    // ==================== SCOPES ====================

    /**
     * Scope for filtering by status
     */
    public function scopeStatus(Builder $query, string|array $status): Builder
    {
        if (is_array($status)) {
            return $query->whereIn('status', $status);
        }
        return $query->where('status', $status);
    }

    /**
     * Scope for filtering by payment status
     */
    public function scopePaymentStatus(Builder $query, string $status): Builder
    {
        return $query->where('payment_status', $status);
    }

    /**
     * Scope for filtering by source
     */
    public function scopeSource(Builder $query, string $source): Builder
    {
        return $query->where('source', $source);
    }

    /**
     * Scope for filtering by date range
     */
    public function scopeDateRange(Builder $query, string $startDate, string $endDate): Builder
    {
        return $query->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59']);
    }

    /**
     * Scope for filtering by customer
     */
    public function scopeForCustomer(Builder $query, int $customerId): Builder
    {
        return $query->where('customer_id', $customerId);
    }

    /**
     * Scope for searching by order number
     */
    public function scopeOrderNumber(Builder $query, string $orderNumber): Builder
    {
        return $query->where('order_number', 'like', "%{$orderNumber}%");
    }

    // ==================== HELPER METHODS ====================

    /**
     * Calculate and update grand total
     */
    public function calculateGrandTotal(): float
    {
        return ($this->sub_total + $this->tax_total + $this->shipping_total) - $this->discount_total;
    }

    /**
     * Recalculate all totals from items
     */
    public function recalculateTotals(): void
    {
        $items = $this->items()->get();

        $subTotal = $items->sum('total');
        $taxTotal = $items->sum('tax_amount');
        $discountTotal = $items->sum('discount_amount');

        $this->sub_total = $subTotal;
        $this->tax_total = $taxTotal;
        $this->discount_total = $discountTotal;
        $this->grand_total = ($subTotal + $taxTotal + $this->shipping_total) - $discountTotal;
        $this->amount_due = $this->grand_total - $this->amount_paid - $this->credit_utilization;

        $this->save();
    }

    /**
     * Log status change
     */
    public function logStatusChange(string $previousStatus, ?string $comment = null, ?int $changedBy = null): void
    {
        $this->statusHistory()->create([
            'status' => $this->status,
            'previous_status' => $previousStatus,
            'comment' => $comment,
            'changed_by' => $changedBy ?? auth()->id(),
        ]);
    }

    /**
     * Get formatted shipping address
     */
    public function getFormattedShippingAddressAttribute(): string
    {
        $parts = array_filter([
            $this->shipping_address_line_1,
            $this->shipping_address_line_2,
            $this->shippingCity?->name,
            $this->shippingState?->name,
            $this->shipping_zipcode,
            $this->shippingCountry?->name,
        ]);

        return implode(', ', $parts);
    }

    /**
     * Get formatted billing address
     */
    public function getFormattedBillingAddressAttribute(): string
    {
        $parts = array_filter([
            $this->billing_address_line_1,
            $this->billing_address_line_2,
            $this->billingCity?->name,
            $this->billingState?->name,
            $this->billing_zipcode,
            $this->billingCountry?->name,
        ]);

        return implode(', ', $parts);
    }

    /**
     * Get items count
     */
    public function getItemsCountAttribute(): int
    {
        return $this->items()->count();
    }

    /**
     * Generate unique order number
     */
    public static function generateOrderNumber(): string
    {
        $prefix = 'ORD';
        $date = now()->format('Ymd');

        $lastOrder = static::withTrashed()
            ->where('order_number', 'like', "{$prefix}{$date}%")
            ->orderBy('order_number', 'desc')
            ->first();

        if ($lastOrder) {
            $lastNumber = (int) substr($lastOrder->order_number, -5);
            $newNumber = str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '00001';
        }

        return "{$prefix}{$date}{$newNumber}";
    }
}