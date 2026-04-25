<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Illuminate\Support\Carbon;
use App\Models\Quarter;
use App\Enums\Priority;
use App\Enums\Status;

class ObjectiveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $objectiveId = $this->route('objective');

        return [
            'label' => ['required', 'string', 'max:255', 'min:3'],
            'description' => ['nullable', 'string', 'max:5000'],
            'color' => ['nullable', 'string', 'regex:/^#[a-fA-F0-9]{6}$/'],
            'icon' => ['nullable', 'string', 'max:50'],
            'sort_order' => [
                'required',
                'integer',
                'min:0',
                'max:9999',
                Rule::unique('objectives', 'sort_order')
                    ->where('quarter_id', $this->quarter_id)
                    ->ignore($objectiveId),
            ],
            'priority' => [
                'required',
                'integer',
                Rule::in(Priority::values())
            ],
            'status' => [
                'required',
                'integer',
                Rule::in(Status::values())
            ],
            'start_date' => ['nullable', 'date', 'before_or_equal:due_date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'completed_at' => [
                'nullable',
                'date',
                'after_or_equal:start_date',
                'required_if:status,' . Status::DONE->value,
            ],
            'quarter_id' => ['required', 'integer', 'exists:quarters,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'label.required' => 'An objective label is required',
            'label.min' => 'The label must be at least 3 characters',
            'color.regex' => 'The color must be a valid hex code (e.g., #3B82F6)',
            'sort_order.unique' => 'This sort order is already used in this quarter',
            'priority.in' => 'Priority must be 0 (Low), 1 (Medium), 2 (High), or 3 (Urgent)',
            'status.in' => 'Status must be 0 (Todo), 1 (Doing), 2 (Done), or 3 (Archived)',
            'completed_at.required_if' => 'The completed date is required when status is completed',
            'quarter_id.exists' => 'The selected quarter does not exist',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('color') && !str_starts_with($this->color, '#')) {
            $this->merge(['color' => '#' . ltrim($this->color, '#')]);
        }
    }

    protected function passedValidation(): void
    {
        $this->validateQuarterDateRange();
        $this->validateStatusTransition();
    }

    private function validateQuarterDateRange(): void
    {
        $quarter = Quarter::find($this->quarter_id);
        
        if (!$quarter) {
            return;
        }

        if ($this->filled('start_date')) {
            $startDate = Carbon::parse($this->start_date);
            $quarterStart = $quarter->start_date instanceof Carbon ? $quarter->start_date : Carbon::parse($quarter->start_date);
            
            if ($startDate->lt($quarterStart)) {
                throw ValidationException::withMessages([
                    'start_date' => ["Start date cannot be before quarter start ({$quarterStart->toDateString()})"],
                ]);
            }
        }
        
        if ($this->filled('due_date')) {
            $dueDate = Carbon::parse($this->due_date);
            $quarterEnd = $quarter->end_date instanceof Carbon ? $quarter->end_date : Carbon::parse($quarter->end_date);
            
            if ($dueDate->gt($quarterEnd)) {
                throw ValidationException::withMessages([
                    'due_date' => ["Due date cannot be after quarter end ({$quarterEnd->toDateString()})"],
                ]);
            }
        }
    }

    private function validateStatusTransition(): void
    {
        $objective = $this->route('objective');
        
        if (!$objective || !$this->has('status')) {
            return;
        }

        // FIXED: Get integer values from enums
        $oldStatusValue = $objective->status instanceof Status 
            ? $objective->status->value 
            : (int) $objective->status;
            
        $newStatusValue = (int) $this->status;

        $oldStatus = Status::tryFrom($oldStatusValue);
        $newStatus = Status::tryFrom($newStatusValue);

        if (!$oldStatus || !$newStatus) {
            return;
        }

        if (!in_array($newStatus->value, $oldStatus->allowedTransitions())) {
            throw ValidationException::withMessages([
                'status' => ["Cannot change status from {$oldStatus->label()} to {$newStatus->label()}"],
            ]);
        }

        if ($oldStatus->isTerminal() && $oldStatus !== $newStatus) {
            throw ValidationException::withMessages([
                'status' => ["Cannot change status from {$oldStatus->label()} as it is a terminal state"],
            ]);
        }
    }

    public function attributes(): array
    {
        return [
            'label' => 'objective label',
            'description' => 'description',
            'color' => 'color',
            'icon' => 'icon',
            'sort_order' => 'sort order',
            'priority' => 'priority',
            'status' => 'status',
            'start_date' => 'start date',
            'due_date' => 'due date',
            'completed_at' => 'completed date',
            'quarter_id' => 'quarter',
        ];
    }
}