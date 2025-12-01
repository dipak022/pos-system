<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'price' => (float) $this->price,
            'stock' => $this->stock,
            'trade_offer' => $this->when(
                $this->trade_offer_min_qty && $this->trade_offer_get_qty,
                [
                    'min_quantity' => $this->trade_offer_min_qty,
                    'get_quantity' => $this->trade_offer_get_qty,
                    'description' => "Buy {$this->trade_offer_min_qty} Get {$this->trade_offer_get_qty} Free",
                ]
            ),
            'discount' => $this->when($this->discount, [
                'percentage' => (float) $this->discount,
                'discounted_price' => $this->getDiscountedPrice(),
            ]),
            'offer_period' => $this->when(
                $this->discount_or_trade_offer_start_date && $this->discount_or_trade_offer_end_date,
                [
                    'start_date' => $this->discount_or_trade_offer_start_date?->toISOString(),
                    'end_date' => $this->discount_or_trade_offer_end_date?->toISOString(),
                ]
            ),
            'active_offer' => [
                'has_active_offer' => $this->hasActiveOffer(),
                'offer_type' => $this->getActiveOfferType(),
            ],
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
