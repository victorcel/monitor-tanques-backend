<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TankReadingBatchRequest extends FormRequest
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
            'readings' => 'required|array|min:1',
            'readings.*.tank_id' => 'required|integer|exists:tanks,id',
            'readings.*.liquid_level' => 'required|numeric|min:0',
            'readings.*.temperature' => 'nullable|numeric',
            'readings.*.reading_timestamp' => 'nullable|date',
            'readings.*.raw_data' => 'nullable|array',
        ];
    }
}
