<div class="tasks_list">
    <div class="py-6"> <!-- Reduced top padding from py-12 to py-6 -->
        <div class="">
            <div class="grid grid-cols-12 gap-6 max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="list col-span-8 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="header bg-white mb-4 p-6 sm:rounded-lg">
                        <p class="mb-4">Tasks</p>
                        <div class="stats flex justify-between gap-4">    
                            @foreach($tasks_by_status as $status)
                                <p class="text-center">
                                <span 
                                    @class([
                                        "flex flex-col items-center justify-center text-center rounded-full h-16 w-16 py-4 px-4 text-lg text-gray-700 border-2",
                                        'border-yellow-500' => $status->status->name === 'NOT_STARTED',
                                        'border-blue-500' => $status->status->name === 'IN_PROGRESS',
                                        'border-green-500' => $status->status->name === 'COMPLETE'
                                    ])
                                >
                                        {{ $status->count }}
                                    </span>
                                    <span class="text-sm mt-4">
                                        {{ $status->status->label() }}
                                    </span>
                                </p>
                            @endforeach
                        </div>
                    </div>

                    <div class="tasks_wrapper bg-gray-50 p-6 min-h-screen">
                        @forelse($tasks as $task)
                            <div class="task p-6 mb-3 bg-white sm:rounded-lg">
                                <div class="header flex justify-between gap-4 mb-2">
                                    <div class="flex items-center gap-2">
                                        <p class="title font-bold">{{ $task->title }}</p>
                                        <span @class([
                                            'px-2 py-1 text-xs rounded-md',
                                            'bg-red-100 text-red-800' => $task->priority->name === 'HIGH',
                                            'bg-yellow-100 text-yellow-800' => $task->priority->name === 'MEDIUM',
                                            'bg-blue-100 text-blue-800' => $task->priority->name === 'LOW',
                                        ])>
                                            {{ $task->priority->label() }}
                                        </span>
                                    </div>
                                    <p @class([
                                        'date',
                                        'text-red-600 font-medium' => $task->deadline < now(),
                                        'text-gray-600' => $task->deadline >= now()
                                    ])>
                                        {{ $task->deadline->diffForHumans() }}
                                        @if($task->deadline < now())
                                            <span class="text-xs">(overdue)</span>
                                        @endif
                                    </p>
                                </div>

                                <div class="body">
                                    <p class="description font-light">{{ $task->description }}</p>
                                </div>

                                <div class="actions flex justify-between items-center gap-6 mt-4">
                                    <div class="statuses mt-4 flex gap-6">
                                        @foreach(\App\Enums\TaskStatus::cases() as $status)
                                            <button type="button" wire:click="changeStatus({{ $task->id }}, '{{ $status->value }}')" class="border border-gray-300 text-sm rounded-md px-2 py-2 bg-white-400 disabled:opacity-25 transition ease-in-out duration-150" @disabled($status->value === $task->status->value)>
                                                {{ $status->label() }}
                                            </button>
                                        @endforeach
                                    </div>

                                    <div class="edits">
                                        <x-primary-button wire:click="$dispatch('edit-task', {taskId : {{ $task->id }} })" class="bg-green-500 hover:bg-green-700">Edit</x-primary-button>

                                        <x-danger-button
                                            x-data=""
                                            x-on:click.prevent="$wire.set('deleting_task_id', {{ $task->id }}); $dispatch('open-modal', 'confirm-task-deletion')"
                                        >{{ __('Delete') }}</x-danger-button>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p>No tasks added yet</p>
                        @endforelse

                        <div class="mt-2 mb-6 p-6">
                            {{ $tasks->links() }}
                        </div>

                        <x-modal 
                            name="confirm-task-deletion" 
                            :show="$deleting_task_id !== null" 
                            focusable
                        >
                            <form wire:submit.prevent="deleteTask" @submit="$dispatch('close-modal', 'confirm-task-deletion')" class="p-6">
                                <h2 class="text-lg font-medium text-gray-900">
                                    {{ __('Are you sure you want to delete this task?') }}
                                </h2>

                                <p class="mt-1 text-sm text-gray-600">
                                    {{ __('Once you delete this task, it cannot be recovered.') }}
                                </p>

                                <div class="mt-6 flex justify-end">
                                    <x-secondary-button x-on:click="$dispatch('close-modal', 'confirm-task-deletion')">
                                        {{ __('Cancel') }}
                                    </x-secondary-button>

                                    <x-danger-button class="ms-3">
                                        {{ __('Delete Task') }}
                                    </x-danger-button>
                                </div>
                            </form>
                        </x-modal>
                    </div>
                </div>

                <div class="form col-span-4">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 sticky top-6">
                        <x-livewire-notifications />

                        <form wire:submit.prevent="saveTask">
                            <div class="inputs mt-2">
                                <label for="title">Title</label>
                                <input type="text" wire:model="form.title" class="w-full rounded-lg">
                                <x-input-error field="form.title" />
                            </div>

                            <div class="inputs mt-2">
                                <label for="description">Description</label>
                                <input type="text" wire:model="form.description" class="w-full rounded-lg">
                                <x-input-error field="form.description" />
                            </div>

                            <div class="inputs mt-2">
                                <label for="status">Status</label>
                                <select wire:model="form.status" class="w-full rounded-lg">
                                    @foreach(\App\Enums\TaskStatus::cases() as $status)
                                        <option value="{{ $status->value }}">{{ $status->name }}</option>
                                    @endforeach
                                </select>
                                <x-input-error field="form.status" />
                            </div>

                            <div class="inputs mt-2">
                                <label for="priority">Priority</label>
                                <select wire:model="form.priority" class="w-full rounded-lg">
                                    @foreach(\App\Enums\TaskPriority::cases() as $priority)
                                        <option value="{{ $priority->value }}">{{ $priority->name }}</option>
                                    @endforeach
                                </select>
                                <x-input-error field="form.priority" />
                            </div>

                            <div class="inputs mt-2">
                                <label for="deadline">Deadline</label>
                                <input type="date" wire:model="form.deadline" class="w-full rounded-lg">
                                <x-input-error field="form.deadline" />
                            </div>

                            <x-primary-button class="mt-5">
                                {{ $form->is_editing ? 'Update' : 'Save' }}
                            </x-primary-button>

                            <div wire:loading>
                                {{ $form->is_editing ? 'Updating task...' : 'Saving task...' }}
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
