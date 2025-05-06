<?php

namespace App\Http\Requests\ReceiptRequest;

use App\Rules\AvailableQuantity;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule as ValidationRule;

class StoreReceiptData extends FormRequest
{
    // ... authorize() ...

    public function rules(): array
    {
        return [
            'customer_id' => 'required|exists:customers,id',
            'receipt_number' => 'required|integer|unique:receipts,receipt_number',
            'type' => 'required|in:اقساط,نقدي',
            'total_price' => 'required|integer',
            'notes' => 'nullable|string',
            'receipt_date' => 'nullable|date|before_or_equal:now',

            'products' => 'required|array',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.description' => 'nullable|string|max:255',

            'products.*.quantity' => [
                'nullable',
                'integer',
                'min:1',
                function ($attribute, $value, $fail) {
                    $index = explode('.', $attribute)[1];
                    $productId = $this->input("products.{$index}.product_id");

                    if (!is_numeric($productId)) {
                        return;
                    }
                    $rule = new AvailableQuantity((int)$productId);
                    if (!$rule->passes($attribute, $value)) {
                        $fail($rule->message());
                    }
                }
            ],
            'products.*.pay_cont' => 'required_if:type,اقساط|nullable|integer|min:1',
            'products.*.installment' => 'required_if:type,اقساط|nullable|integer|min:1',
            'products.*.installment_type' => 'required_if:type,اقساط|nullable|in:,يومي,شهري,اسبوعي',
            'products.*.amount' => 'required_if:type,اقساط|nullable|integer|min:1',
        ];
    }

    /**
     * Handle a failed validation attempt.
     * This method is called when validation fails.
     * Logs failed attempts and throws validation exception.
     * @param \Illuminate\Validation\Validator $validator
     * @return void
     *
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
