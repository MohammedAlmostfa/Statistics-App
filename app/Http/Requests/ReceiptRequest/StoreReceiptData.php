<?php

namespace App\Http\Requests\ReceiptRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class StoreReceiptData extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Always allow the request to proceed.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * Validation rules for creating a receipt:
     * - customer_name (required, string)
     * - total_amount (required, numeric)
     * - receipt_number (required, string, unique)
     * - receipt_date (required, date)
     * - items (array containing receipt item details)
     */
    public function rules(): array
    {
        return [
            'customer_name' => 'required|string|max:255',
            'total_price' => 'required|numeric|min:0',
            'receipt_number' => 'required|numeric|unique:receipts,receipt_number',
            'type'=>'required|string',
            'receipt_date' => 'nullable|date|before_or_equal:now',
            'items' => 'required|array',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',

        ];
    }
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'status' => 'error',
            'message' => 'فشل التحقق من صحة البيانات',
            'errors' => $validator->errors(),
        ], 422));
    }
}
