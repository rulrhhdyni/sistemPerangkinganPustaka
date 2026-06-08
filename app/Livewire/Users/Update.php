<?php

namespace App\Livewire\Users;

use Livewire\Component;
use App\Models\User;
use App\Traits\WithToast;

class Update extends Component
{
    use WithToast;

    public $selectedId;
    
    public array $form = [
        'name' => '',
        'email' => '',
        'rfid_id' => '', // 1. Tambahkan rfid_id di state awal
        'is_admin' => false,
        'password' => '',
        'password_confirmation' => '',
    ];
    
    public bool $updatePassword = false;

    public function mount($user = null)
    {
        $user = User::find($user);
        if ($user) {
            $this->selectedId = $user->id;
            $this->form['name'] = $user->name;
            $this->form['email'] = $user->email;
            $this->form['rfid_id'] = $user->rfid_id; // 2. Muat data rfid_id lama
            $this->form['is_admin'] = (bool) $user->is_admin; // Pastikan formatnya boolean
        }
    }

    public function saveUser()
    {
        // 3. Tambahkan validasi dasar untuk menghindari error duplicate entry di database
        $this->validate([
            'form.name' => 'required|string|max:255',
            'form.email' => 'required|email|unique:users,email,' . $this->selectedId,
            'form.rfid_id' => 'nullable|string|max:255|unique:users,rfid_id,' . $this->selectedId,
        ]);

        if ($this->updatePassword) {
            $this->validate([
                'form.password' => 'required|string|min:8|confirmed',
                'form.password_confirmation' => 'required|string|min:8',
            ]);

            $this->form['password'] = bcrypt($this->form['password']);
        } else {
            // PENTING: Hapus password dari form jika tidak diupdate,
            // agar password di database tidak ter-update menjadi kosong (string kosong)
            unset($this->form['password']);
        }

        unset($this->form['password_confirmation']);

        // 4. Update data ke database
        User::where('id', $this->selectedId)->update($this->form);

        $this->reset(['form', 'updatePassword']);
        $this->dispatch('close-slideover', 'update-users');
        $this->dispatch('refreshUsers');
        $this->toast('User updated successfully.');
    }

    public function render()
    {
        return view('livewire.users.update');
    }
}