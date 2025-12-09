<?php

namespace App\Http\Requests\Orders;

use Illuminate\Foundation\Http\FormRequest;

class SubmitReviewRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'product_rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'product_comment' => ['nullable', 'string', 'max:1000'],
            'seller_rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'seller_comment' => ['nullable', 'string', 'max:1000'],
            'buyer_rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'buyer_comment' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
