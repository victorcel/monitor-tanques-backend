<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TankUpdateRequest extends FormRequest
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
            'name' => 'sometimes|required|string|max:255',
            'capacity' => 'sometimes|required|numeric|min:0',
            'height' => 'sometimes|required|numeric|min:0',
            'location' => 'nullable|string|max:255',
            'diameter' => 'nullable|numeric|min:0',
            'is_active' => 'sometimes|required|boolean',
        ];
    }
}
