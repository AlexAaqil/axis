<?php

namespace App\Livewire\Tasks;

use Livewire\Component;
use App\Models\Tasks\Task;
use Livewire\Attributes\Url;

class Search extends Component
{
    #[Url(as: 'q')]
    public $search = '';

    public function render()
    {
        $results = [];
        if (strlen($this->search) > 2) {
            $results = auth()->user()->tasks()->where('title', 'like', '%' . $this->search . '%')->get();
        }

        return view('livewire.tasks.search', compact('results'))->layout('layouts.app');
    }
}
