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
            'customer_id'     => 'nullable|exists:customers,id',
            'type'            => 'nullable|in:اقساط,نقدي',
            'total_price'     => 'required|integer|min:0',
            'notes'           => 'nullable|string',
            'receipt_date'    => 'nullable|date|before_or_equal:now',

            'products'                        => 'required_if:type,اقساط|nullable|array',
            'products.*.product_id'           => 'required_if:type,اقساط|required|exists:products,id',
            'products.*.description'          => 'nullable|string|max:255',
            'products.*.quantity'             => 'nullable|integer|min:1',

            'products.*.pay_cont'             => 'required_if:type,اقساط|nullable|integer|min:1',
            'products.*.installment'          => 'required_if:type,اقساط|nullable|integer|min:1',
            'products.*.installment_type'     => 'required_if:type,اقساط|nullable|in:يومي,شهري,اسبوعي',
            'products.*.amount'               => 'required_if:type,اقساط|nullable|integer|min:1',
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
