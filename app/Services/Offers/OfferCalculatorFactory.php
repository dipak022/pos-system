<?php

namespace App\Services\Offers;

use App\Models\Product;

/**
 * Interface for offer calculation strategy
 */
interface OfferStrategyInterface
{
    /**
     * Calculate offer for a product with given quantity
     *
     * @param Product $product
     * @param int $quantity
     * @return array
     */
    public function calculate(Product $product, int $quantity): array;

    /**
     * Check if this strategy can be applied
     *
     * @param Product $product
     * @return bool
     */
    public function canApply(Product $product): bool;
}

/**
 * Discount Offer Strategy
 */
class DiscountOfferStrategy implements OfferStrategyInterface
{
    public function calculate(Product $product, int $quantity): array
    {
        $originalPrice = (float) $product->price;
        $discountPercentage = (float) $product->discount;
        $discountedPrice = $product->getDiscountedPrice();
        $discountAmountPerUnit = $originalPrice - $discountedPrice;
        $totalDiscountAmount = $discountAmountPerUnit * $quantity;
        $subtotal = $discountedPrice * $quantity;

        return [
            'offer_type' => 'discount',
            'unit_price' => $originalPrice,
            'discounted_unit_price' => $discountedPrice,
            'quantity' => $quantity,
            'free_quantity' => 0,
            'discount_percentage' => $discountPercentage,
            'discount_amount' => round($totalDiscountAmount, 2),
            'subtotal' => round($subtotal, 2),
            'offer_details' => sprintf(
                '%d%% discount applied',
                $discountPercentage
            ),
        ];
    }

    public function canApply(Product $product): bool
    {
        return $product->hasActiveDiscount();
    }
}

/**
 * Trade Offer Strategy (Buy X Get Y Free)
 */
class TradeOfferStrategy implements OfferStrategyInterface
{
    public function calculate(Product $product, int $quantity): array
    {
        $price = (float) $product->price;
        $freeQuantity = $product->calculateFreeQuantity($quantity);
        $totalQuantityReceived = $quantity + $freeQuantity;
        $subtotal = $price * $quantity;

        // The discount amount is the value of free items
        $discountAmount = $price * $freeQuantity;

        return [
            'offer_type' => 'trade_offer',
            'unit_price' => $price,
            'discounted_unit_price' => $price,
            'quantity' => $quantity,
            'free_quantity' => $freeQuantity,
            'total_quantity_received' => $totalQuantityReceived,
            'discount_percentage' => null,
            'discount_amount' => round($discountAmount, 2),
            'subtotal' => round($subtotal, 2),
            'offer_details' => sprintf(
                'Buy %d Get %d Free (Total: %d items, Paid for: %d)',
                $product->trade_offer_min_qty,
                $product->trade_offer_get_qty,
                $totalQuantityReceived,
                $quantity
            ),
        ];
    }

    public function canApply(Product $product): bool
    {
        return $product->hasActiveTradeOffer();
    }
}

/**
 * No Offer Strategy (Regular Price)
 */
class NoOfferStrategy implements OfferStrategyInterface
{
    public function calculate(Product $product, int $quantity): array
    {
        $price = (float) $product->price;
        $subtotal = $price * $quantity;

        return [
            'offer_type' => null,
            'unit_price' => $price,
            'discounted_unit_price' => $price,
            'quantity' => $quantity,
            'free_quantity' => 0,
            'discount_percentage' => null,
            'discount_amount' => 0,
            'subtotal' => round($subtotal, 2),
            'offer_details' => 'No active offer',
        ];
    }

    public function canApply(Product $product): bool
    {
        return true; // Always can apply as fallback
    }
}

/**
 * Offer Calculator Factory
 */
class OfferCalculatorFactory
{
    /**
     * Available strategies
     *
     * @var array
     */
    private array $strategies;

    public function __construct()
    {
        $this->strategies = [
            new DiscountOfferStrategy(),
            new TradeOfferStrategy(),
            new NoOfferStrategy(), // Fallback
        ];
    }

    /**
     * Get appropriate strategy for product
     *
     * @param Product $product
     * @return OfferStrategyInterface
     */
    public function getStrategy(Product $product): OfferStrategyInterface
    {
        foreach ($this->strategies as $strategy) {
            if ($strategy->canApply($product)) {
                return $strategy;
            }
        }

        // Should never reach here, but return NoOfferStrategy as ultimate fallback
        return new NoOfferStrategy();
    }
}
