<?php

namespace App\Livewire\Users;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use App\Traits\WithToast;
use Livewire\Attributes\On;
use Flux\Flux;
class Index extends Component
{
    use WithPagination, WithToast;
    public $search = '';
    public $perPage = 10;
    public $selectedId = null;

    public function updatingPerPage()
    {
        $this->resetPage();
    }
    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function edit($id){
        $this->selectedId = $id;
        $this->dispatch('open-slideover', 'update-users');
    }

    public function openDelete($id)
    {
        $this->selectedId = $id;
        Flux::modal('confirm')->show();
        // $this->dispatch('open-modal', 'delete-user-modal');
    }
    public function deleteUser()
    {
        if ($this->selectedId) {
            $user = User::find($this->selectedId);
            if ($user) {
                $user->delete();
                $this->toast('User deleted successfully.', 'success');
            } else {
                $this->toast('User not found.', 'error');
            }
            $this->selectedId = null;
        }
        Flux::modal('confirm')->close();
    }

    #[On('refreshUsers')]
    public function query()
    {
        return User::query()
            ->where('id', '!=', auth()->user()->id)
            ->where('name', 'like', '%' . $this->search . '%')
            ->orWhere('email', 'like', '%' . $this->search . '%')
            ->paginate($this->perPage);
    }
    public function render()
    {
        $users = $this->query();
        return view('livewire.users.index', compact('users'))->title('Data Pengguna');
    }
}
