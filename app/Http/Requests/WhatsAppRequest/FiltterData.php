<?php

namespace App\Http\Requests\WhatsAppRequest;

use Illuminate\Foundation\Http\FormRequest;

class FiltterData extends FormRequest
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
            'to'     => 'nullable|string',
            'type'   => 'nullable|string',
            'page' => 'nullable|integer',
            'ack'    => 'nullable|string',
            'status' => 'nullable|string|in:queue,sent,unsent,invalid,expired',
        ];
    }
}
