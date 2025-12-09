<?php

namespace App\Http\Requests\Orders;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if (!auth()->check() || !auth()->user()?->profile) {
            return false;
        }

        $order = $this->route('order');
        
        // Solo el vendedor puede actualizar un pedido
        if ($order->seller_profile_id !== auth()->user()->profile->id) {
            return false;
        }

        // Solo se puede actualizar si el pedido está pendiente
        return $order->status === 'pending';
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
            'quantity' => ['sometimes', 'required', 'integer', 'min:1'],
            'unit_price' => ['sometimes', 'required', 'numeric', 'min:0'],
            'delivery_method' => ['sometimes', 'required', 'in:' . implode(',', $deliveryMethods)],
            'pickup_location' => ['sometimes', 'required', 'in:' . implode(',', $pickupLocations)],
            'pickup_address' => ['sometimes', 'required_if:pickup_location,other', 'nullable', 'string', 'max:500'],
            'delivery_address' => ['sometimes', 'required_if:delivery_method,seller_transport,external_delivery,corralx_delivery', 'nullable', 'string', 'max:500'],
            'pickup_notes' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'delivery_cost' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'delivery_cost_currency' => ['sometimes', 'nullable', 'string', 'size:3'],
            'delivery_provider' => ['sometimes', 'nullable', 'string', 'max:255'],
            'delivery_tracking_number' => ['sometimes', 'nullable', 'string', 'max:255'],
            'expected_pickup_date' => ['sometimes', 'nullable', 'date', 'after_or_equal:today'],
            'seller_notes' => ['sometimes', 'nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'quantity.min' => 'La cantidad debe ser mayor a 0.',
            'unit_price.min' => 'El precio unitario debe ser mayor o igual a 0.',
            'delivery_method.in' => 'Método de entrega inválido.',
            'pickup_location.in' => 'Ubicación de recogida inválida.',
            'pickup_address.required_if' => 'La dirección de recogida es requerida cuando se selecciona "otra ubicación".',
            'delivery_address.required_if' => 'La dirección de entrega es requerida para este método de delivery.',
            'expected_pickup_date.after_or_equal' => 'La fecha esperada no puede ser en el pasado.',
        ];
    }
}



