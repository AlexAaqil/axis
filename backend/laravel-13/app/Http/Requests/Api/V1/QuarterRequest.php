<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class QuarterRequest extends FormRequest
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
            'label' => [
                'required',
                'string',
                Rule::unique('quarters', 'label')
                    ->where('year_id', $this->year_id)
                    ->ignore($this->route('quarter'))
            ],
            'start_date' => [
                'required',
                'date',
                'before:end_date',
            ],
            'end_date' => [
                'required',
                'date',
                'after:start_date',
            ],
            'year_id' => [
                'required',
                'exists:years,id',
                'integer',
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'label.required' => 'A quarter label (Q1, Q2, Q3, Q4) is required',
            'label.unique' => 'This year already has a quarter with this label',
            'start_date.before' => 'Start date must be before end date',
            'end_date.after' => 'End date must be after start date',
            'year_id.exists' => 'The selected year does not exist',
        ];
    }
}
