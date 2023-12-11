<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateInvoiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // 'order_id' => 'required|string|unique:orders,order_id,',
            'user_id' => 'required|exists:users,id',
            // 'order_date' => 'required|date',
            'client_id' => 'required|numeric|exists:clients,id',
            'client_name' => 'required|string',
            'client_address' => 'required|string',
            'client_email' => 'required|string',
            'client_phone' => 'required|string',
            'client_alternate_phone' => 'sometimes|string' ,
            // 'total_amount' => 'required|numeric',
            'additional_information' => 'sometimes|string',
            'delivery_amount' => 'required|numeric',
            // 'status' => 'required|string',
            'invoice_items' => 'required|array',
            // 'order_items.*.order_id' => 'required|unique:order_items,order_id',
            'invoice_items.*.product_id' => 'required|numeric|exists:products,id',
            'invoice_items.*.quantity' => 'required|numeric',
            'invoice_items.*.price' => 'required|numeric'
        ];
    }
}

