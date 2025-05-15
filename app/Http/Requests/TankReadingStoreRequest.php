<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TankReadingStoreRequest extends FormRequest
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
            'tank_id' => 'required|integer|exists:tanks,id',
            'liquid_level' => 'required|numeric|min:0',
            'temperature' => 'nullable|numeric',
            'reading_timestamp' => 'nullable|date',
            'raw_data' => 'nullable|array',
        ];
    }
}
