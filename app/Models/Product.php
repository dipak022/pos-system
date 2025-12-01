<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'price',
        'stock',
        'trade_offer_min_qty',
        'trade_offer_get_qty',
        'discount',
        'discount_or_trade_offer_start_date',
        'discount_or_trade_offer_end_date',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
        'discount' => 'decimal:2',
        'discount_or_trade_offer_start_date' => 'datetime',
        'discount_or_trade_offer_end_date' => 'datetime',
    ];

    /**
     * Get sale items for this product
     */
    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    /**
     * Check if product has an active offer (discount or trade offer)
     */
    public function hasActiveOffer(): bool
    {
        if (!$this->discount_or_trade_offer_start_date || !$this->discount_or_trade_offer_end_date) {
            return false;
        }

        $now = Carbon::now();
        return $now->between(
            $this->discount_or_trade_offer_start_date,
            $this->discount_or_trade_offer_end_date
        );
    }

    /**
     * Check if product has active discount
     */
    public function hasActiveDiscount(): bool
    {
        return $this->hasActiveOffer() && $this->discount !== null && $this->discount > 0;
    }

    /**
     * Check if product has active trade offer
     */
    public function hasActiveTradeOffer(): bool
    {
        return $this->hasActiveOffer()
            && $this->trade_offer_min_qty !== null
            && $this->trade_offer_get_qty !== null;
    }

    /**
     * Get active offer type
     */
    public function getActiveOfferType(): ?string
    {
        if (!$this->hasActiveOffer()) {
            return null;
        }

        if ($this->hasActiveDiscount()) {
            return 'discount';
        }

        if ($this->hasActiveTradeOffer()) {
            return 'trade_offer';
        }

        return null;
    }

    /**
     * Calculate discounted price
     */
    public function getDiscountedPrice(): float
    {
        if ($this->hasActiveDiscount()) {
            $discountAmount = ($this->price * $this->discount) / 100;
            return round($this->price - $discountAmount, 2);
        }

        return (float) $this->price;
    }

    /**
     * Calculate free quantity for trade offer
     */
    public function calculateFreeQuantity(int $quantity): int
    {
        if (!$this->hasActiveTradeOffer() || $quantity < $this->trade_offer_min_qty) {
            return 0;
        }

        // Calculate how many complete sets of the offer the customer gets
        $completeSets = intdiv($quantity, $this->trade_offer_min_qty);
        return $completeSets * $this->trade_offer_get_qty;
    }

    /**
     * Scope to get products with active offers
     */
    public function scopeWithActiveOffers($query)
    {
        $now = Carbon::now();
        return $query->where(function ($q) use ($now) {
            $q->whereNotNull('discount_or_trade_offer_start_date')
              ->whereNotNull('discount_or_trade_offer_end_date')
              ->where('discount_or_trade_offer_start_date', '<=', $now)
              ->where('discount_or_trade_offer_end_date', '>=', $now);
        });
    }

    /**
     * Scope to get in-stock products
     */
    public function scopeInStock($query)
    {
        return $query->where('stock', '>', 0);
    }
}
