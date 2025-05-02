<?php

namespace App\Livewire\Tasks;

use Livewire\Component;
use App\Models\Tasks\Task;
use App\Livewire\Tasks\TaskForm;
use App\Enums\TaskStatus;
use Livewire\WithPagination;
use Livewire\Attributes\On; 

class Tasks extends Component
{
    use WithPagination;

    public TaskForm $form;

    public ?int $deleting_task_id = null;

    public function saveTask()
    {
        $this->form->validate();

        $this->form->createTask();
    }

    #[On('edit-task')]
    public function editTask($taskId)
    {
        $task = Task::findOrFail($taskId);

        $this->form->fillFormData($task);
    }

    public function deleteTask()
    {
        Task::findOrFail($this->deleting_task_id)->delete();

        $this->dispatch('task-deleted');

        session()->flash('success', 'Task deleted successfully');

        $this->deleting_task_id = null;
    }

    public function changeStatus($id, $status)
    {
        $task = Task::findOrFail($id);

        $task->update([
            'status' => $status
        ]);
    }

    public function render()
    {
        $tasks = auth()->user()->tasks()->latest()->paginate(5);
        $tasks_by_status = TaskStatus::userGroupedStatuses();

        return view('livewire.tasks.tasks', compact('tasks', 'tasks_by_status'))
            ->layout('layouts.app');
    }
}
