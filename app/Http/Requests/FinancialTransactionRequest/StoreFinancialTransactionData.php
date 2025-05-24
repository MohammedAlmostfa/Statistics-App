<?php

namespace App\Http\Requests\FinancialTransactionRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreFinancialTransactionData extends FormRequest
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
            'transaction_date' => 'required|date',
            'agent_id' => 'required|integer|exists:agents,id',
            'total_amount' => 'required|integer',
            'discount_amount' => 'required|integer',
            'paid_amount' => 'required|integer',
            'description' => 'nullable|string',

            'products' => 'required|array',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.selling_price' => 'required|numeric',
            'products.*.dollar_buying_price' => 'required|numeric',
            'products.*.installment_price' => 'required|integer',
            'products.*.dollar_exchange' => 'required|integer:',
            'products.*.quantity' => 'required|integer:',

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
            'status'  => 'error',
            'message' => 'Validation failed.',
            'errors'  => $validator->errors(),
        ], 422));
    }
}
