<?php

namespace App\Http\Requests\ReceiptRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class UpdateReceiptData extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Adjust authorization logic if needed
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [

            'customer_id' => 'nullable|exists:customers,id',
            'receipt_number' => 'nullable|integer',
            'type' => 'nullable|in:اقساط,نقدي',
            'total_price' => 'nullable|integer',
            'notes' => 'nullable|string',
            'receipt_date' => 'nullable|date|before_or_equal:now',
            // 'items.*.id' => 'required|integer',
            // 'items.*.description' => 'nullable|string|max:255',
            // 'items.*.quantity' => 'nullable|integer|min:1',
            // 'items.*.unit_price' => 'nullable|numeric|min:0',
        ];
    }

    /**
     * Handle failed validation and return a JSON response.
     *
     * @param Validator $validator
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'status' => 'error',
            'message' => 'فشل التحقق من صحة البيانات',
            'errors' => $validator->errors(),
        ], 422));
    }
}
