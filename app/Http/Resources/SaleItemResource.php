<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SaleItemResource extends JsonResource
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
            'product' => new ProductResource($this->whenLoaded('product')),
            'quantity' => $this->quantity,
            'free_quantity' => $this->free_quantity,
            'unit_price' => (float) $this->unit_price,
            'discount_percentage' => $this->discount_percentage ? (float) $this->discount_percentage : null,
            'discount_amount' => (float) $this->discount_amount,
            'subtotal' => (float) $this->subtotal,
            'offer_type' => $this->offer_type,
            'offer_details' => $this->offer_details,
        ];
    }
}
