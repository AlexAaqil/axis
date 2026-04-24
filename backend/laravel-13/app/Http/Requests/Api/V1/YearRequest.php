<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class YearRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'year' => [
                'required',
                'integer',
                'digits:4',
                Rule::unique('years', 'year')->ignore($this->route('year')),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'year.required' => 'The year field is required',
            'year.integer' => 'The year must be an integer',
            'year.digits' => 'The year must be exactly 4 digits (e.g., 2025)',
            'year.unique' => 'This year already exists',
        ];
    }

    /**
     * Cast string (year) to int
     */
    protected function prepareForValidation(): void
    {
        // If year comes as a string '2026', cast to int 2026
        if ($this->has('year')) {
            $this->merge([
                'year' => (int) $this->year,
            ]);
        }
    }
}
