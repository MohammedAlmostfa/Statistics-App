<?php

namespace App\Http\Requests\ReceiptRequest;

use App\Rules\FirstInstallmentAmountValid;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use PhpParser\NodeVisitor\FirstFindingVisitor;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Rules\AvailableQuantityStore; // تأكد من أن هذا المستورد صحيح
use App\Models\Product; // استورد نموذج المنتج لاستخدامه داخل الـ closure
use App\Rules\AvailableQuantity; // تأكد من أن هذا المستورد صحيح إذا كان AvailableQuantity ما يزال مستخدماً

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
                'required',
                'integer',
                'min:1',
                function ($attribute, $value, $fail) {
                    $index = explode('.', $attribute)[1];
                    $productId = $this->input("products.{$index}.product_id");

                    $rule = new AvailableQuantityStore((int)$productId);
                    if (!$rule->passes($attribute, $value)) {
                        $fail($rule->message());
                    }
                }
            ],

            'products.*.pay_cont' => 'required_if:type,اقساط|nullable|integer|min:1',
            'products.*.installment' => 'required_if:type,اقساط|nullable|integer|min:1',
            'products.*.installment_type' => 'required_if:type,اقساط|nullable|in:,يومي,شهري,اسبوعي',

            'products.*.first_pay' => [
                'required_if:type,اقساط',
                'nullable',
                'integer',
                'min:1',
                function ($attribute, $value, $fail) {
                    $index = explode('.', $attribute)[1];
                    $productId = $this->input("products.{$index}.product_id");
                    $quantity = $this->input("products.{$index}.quantity");

                    $rule = new FirstInstallmentAmountValid((int) $productId, (int) $quantity);
                    if (!$rule->passes($attribute, $value)) {
                        $fail($rule->message());
                    }
                }
            ],
        ];
    }

    /**
     * Handle a failed validation attempt.

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
