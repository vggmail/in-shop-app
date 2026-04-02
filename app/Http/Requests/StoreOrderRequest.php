<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'order_type' => 'required|in:Dine-in,Takeaway,Home Delivery',
            'table_number' => 'nullable|string|max:10',
            'delivery_address' => 'nullable|string|max:500',
            'customer_id' => 'nullable|exists:customers,id',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'coupon_id' => 'nullable|exists:coupons,id',
            'discount_amount' => 'nullable|numeric|min:0',
            'note' => 'nullable|string|max:500',
            'payment_method' => 'required|in:Cash,Card,UPI,PayU',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.variant_id' => 'nullable|exists:item_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.total' => 'nullable|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'grand_total' => 'required|numeric|min:0',
            'street_address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:10',
            'save_address' => 'nullable|boolean',
            'address_label' => 'nullable|string|max:50',
        ];
    }

    protected function prepareForValidation()
    {
        $items = $this->items;
        if (is_string($items)) {
            $items = json_decode($items, true);
        }

        if (is_array($items)) {
            $items = array_map(function($item) {
                // Ensure 'total' exists even if frontend missed it
                $item['total'] = $item['total'] ?? ($item['price'] * $item['quantity']);
                return $item;
            }, $items);
        }

        $this->merge([
            'items' => $items,
        ]);
    }
}
