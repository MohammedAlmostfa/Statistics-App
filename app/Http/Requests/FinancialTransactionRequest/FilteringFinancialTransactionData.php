<?php

namespace App\Http\Requests\FinancialTransactionRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class FilteringFinancialTransactionData extends FormRequest
{
    /**

     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
    public function prepareForValidation()
    {
        if (!empty($this->transaction_date)) {
            // If only the year is provided, append "-01-01"
            if (preg_match('/^\d{4}$/', $this->transaction_date)) {
                $this->merge(['transaction_date' => $this->transaction_date . '-01-01']);
            }
            // If year and month are provided, append "-01"
            elseif (preg_match('/^\d{4}-\d{2}$/', $this->transaction_date)) {
                $this->merge(['transaction_date' => $this->transaction_date . '-01']);
            }
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
     'transaction_date' => 'nullable|date',


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
