<?php

namespace App\Livewire\Member;

use Livewire\Component;
use App\Models\Member;

class Create extends Component
{
    public $form = [
        'id_server' => '',
        'name' => '',
        'email' => '',
        'phone' => '',
        'address' => '',
        'type' => '',
    ];

    public $types = [];

    public function mount()
    {
        $this->types = $this->getTypesProperty();
    }

    protected function getTypesProperty()
    {
        return Member::select('type')->distinct()->pluck('type');
    }

    public function saveMember()
    {
        $this->validate(
            [
                'form.id_server' => 'required|unique:members,slims_member_id|string|max:100',
                'form.name' => 'required|string|max:255',
                'form.email' => 'required|email|unique:members,email',
                'form.phone' => 'nullable|string|max:20',
                'form.address' => 'nullable|string|max:500',
                'form.type' => 'required|string|max:100',
            ],
            [
                'form.id_server.required' => 'The slims member id field is required.',
                'form.id.unique' => 'The slims member id has already been taken.',
                'form.name.required' => 'The name field is required.',
                'form.email.required' => 'The email field is required.',
                'form.email.email' => 'The email must be a valid email address.',
                'form.email.unique' => 'The email has already been taken.',
                'form.type.required' => 'The type field is required.',
            ]
        );

        $this->form['register_date'] = now();
        $this->form['expire_date'] = now()->addYear();

        Member::create($this->form);

        $this->reset('form');

        $this->dispatch('close-slideover', 'create-member');

        session()->flash('success', 'Member created successfully');
    }

    public function render()
    {
        return view('livewire.member.create');
    }
}
