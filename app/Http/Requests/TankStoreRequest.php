<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TankStoreRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'serial_number' => 'required|string|max:255|unique:tanks',
            'capacity' => 'required|numeric|min:0',
            'height' => 'required|numeric|min:0',
            'location' => 'nullable|string|max:255',
            'diameter' => 'nullable|numeric|min:0',
        ];
    }
}
