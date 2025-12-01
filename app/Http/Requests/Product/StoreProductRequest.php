<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use App\Helpers\ApiResponse;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'trade_offer_min_qty' => ['nullable', 'integer', 'min:1', 'required_with:trade_offer_get_qty'],
            'trade_offer_get_qty' => ['nullable', 'integer', 'min:1', 'required_with:trade_offer_min_qty'],
            'discount' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'discount_or_trade_offer_start_date' => ['nullable', 'date', 'required_with:discount_or_trade_offer_end_date'],
            'discount_or_trade_offer_end_date' => ['nullable', 'date', 'after:discount_or_trade_offer_start_date', 'required_with:discount_or_trade_offer_start_date'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $hasDiscount = $this->filled('discount') && $this->discount > 0;
            $hasTradeOffer = $this->filled('trade_offer_min_qty') && $this->filled('trade_offer_get_qty');

            // Check if both discount and trade offer are provided
            if ($hasDiscount && $hasTradeOffer) {
                $validator->errors()->add(
                    'discount',
                    'Cannot have both discount and trade offer at the same time'
                );
            }

            // Check if dates are provided but no offer
            if ($this->filled('discount_or_trade_offer_start_date') && !$hasDiscount && !$hasTradeOffer) {
                $validator->errors()->add(
                    'discount_or_trade_offer_start_date',
                    'Please provide either a discount or trade offer'
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Product name is required',
            'price.required' => 'Product price is required',
            'price.min' => 'Price cannot be negative',
            'stock.required' => 'Stock quantity is required',
            'stock.min' => 'Stock cannot be negative',
            'discount.max' => 'Discount cannot exceed 100%',
            'trade_offer_min_qty.required_with' => 'Minimum quantity is required for trade offer',
            'trade_offer_get_qty.required_with' => 'Get quantity is required for trade offer',
            'discount_or_trade_offer_end_date.after' => 'End date must be after start date',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            ApiResponse::error(
                'Validation failed',
                $validator->errors(),
                422
            )
        );
    }
}
