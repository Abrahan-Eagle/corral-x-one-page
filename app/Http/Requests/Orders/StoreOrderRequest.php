<?php

namespace App\Http\Requests\Orders;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()?->profile !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $deliveryMethods = ['buyer_transport', 'seller_transport', 'external_delivery', 'corralx_delivery'];
        $pickupLocations = ['ranch', 'other'];

        return [
            'product_id' => ['required', 'exists:products,id'],
            'conversation_id' => ['nullable', 'exists:conversations,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'unit_price' => ['nullable', 'numeric', 'min:0'],
            'delivery_method' => ['required', 'in:' . implode(',', $deliveryMethods)],
            'pickup_location' => ['required', 'in:' . implode(',', $pickupLocations)],
            'pickup_address' => ['required_if:pickup_location,other', 'nullable', 'string', 'max:500'],
            'delivery_address' => ['required_if:delivery_method,seller_transport,external_delivery,corralx_delivery', 'nullable', 'string', 'max:500'],
            'pickup_notes' => ['nullable', 'string', 'max:1000'],
            'delivery_cost' => ['nullable', 'numeric', 'min:0'],
            'delivery_cost_currency' => ['nullable', 'string', 'size:3'],
            'delivery_provider' => ['nullable', 'string', 'max:255'],
            'delivery_tracking_number' => ['nullable', 'string', 'max:255'],
            'expected_pickup_date' => ['nullable', 'date', 'after_or_equal:today'],
            'buyer_notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
