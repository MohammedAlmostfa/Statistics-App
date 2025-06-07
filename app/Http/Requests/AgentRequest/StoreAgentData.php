<?php

namespace App\Http\Requests\AgentRequest;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

use Illuminate\Foundation\Http\FormRequest;

class StoreAgentData extends FormRequest
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
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('agents')->where(fn($query) => $query->where('status', '!=', 1)),
            ],
            'phone' => [
                'nullable',
                'max:20',
                Rule::unique('agents')->where(fn($query) => $query->where('status', '!=', 1)),
            ],
            'notes' => 'nullable|string|max:1000',
            'type' => 'nullable|in:دينار,دولار',
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
