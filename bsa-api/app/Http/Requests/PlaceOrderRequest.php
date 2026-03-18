<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PlaceOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone'              => ['required', 'string', 'regex:/^9[78]\d{8}$/'],
            'name'               => ['required', 'string', 'max:100'],
            'address'            => ['required', 'string', 'max:255'],
            'city'               => ['required', 'string', 'max:100'],
            'delivery_note'      => ['nullable', 'string', 'max:500'],
            'latitude'           => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'          => ['nullable', 'numeric', 'between:-180,180'],
            'formatted_address'  => ['nullable', 'string', 'max:500'],
            'nearest_landmark'   => ['nullable', 'string', 'max:255'],
            'email'              => ['nullable', 'email', 'max:255'],
            'payment_method'     => ['required', 'string', 'in:ESEWA,COD'],
            'items'                 => ['required', 'array', 'min:1'],
            'items.*.product_id'    => ['required', 'uuid', 'exists:products,id'],
            'items.*.variant_id'    => ['required', 'uuid', 'exists:product_variants,id'],
            'items.*.quantity'      => ['required', 'integer', 'min:1', 'max:5'],
            'items.*.price'         => ['required', 'integer', 'min:1'],
        ];
    }
}
