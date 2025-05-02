<?php

namespace App\Livewire\Tasks;

use Livewire\Attributes\Rule;
use Livewire\Form;
use App\Models\Tasks\Task;

class TaskForm extends Form
{
    public ?Task $task;
    public $is_editing = false;

	public $title = '';
    public $description = '';
    public $status = '';
    public $priority = '';
    public $deadline = '';

    public function rules(): array
    {
        return [
            'title' => 'required|min:2|max:200',
            'description' => 'required|min:5|max:200',
            'status' => 'required',
            'priority' => 'required',
            'deadline' => 'required|date',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'You must enter a title',
            'deadline.date' => 'The deadline must be a valid date',
        ];
    }

    public function formData(): array
    {
    	return [
	        'title' => $this->title,
	        'description' => $this->description,
	        'status' => $this->status,
	        'priority' => $this->priority,
	        'deadline' => $this->deadline,
	    ];
    }

    public function fillFormData(Task $task)
    {
        $this->task = $task;
        $this->is_editing = true;
        $this->title = $task->title;
        $this->description = $task->description;
        $this->status = $task->status;
        $this->priority = $task->priority;
        $this->deadline = $task->deadline->format('Y-m-d');
    }

    public function createTask()
    {
        if ($this->is_editing) {
            $this->task->update($this->formData());
            $this->reset();
            $this->is_editing = false;
            request()->session()->flash('success', 'Task has been updated');
        } else {
            auth()->user()->tasks()->create($this->formData());
            $this->reset();
            request()->session()->flash('success', 'Task has been created');
        }

    }
}