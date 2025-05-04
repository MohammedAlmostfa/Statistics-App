<?php

namespace App\Http\Requests\InstallmentPaymentRequest;

use App\Models\Installment;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\ValidInstallmentPaymentAmount;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Validation\Validator;

class StoreInstallmentPaymentData extends FormRequest
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
            'payment_date'=>'required|date',
        'amount' => ['required', 'numeric', new ValidInstallmentPaymentAmount(Installment::findOrFail($this->route('id')))],

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
