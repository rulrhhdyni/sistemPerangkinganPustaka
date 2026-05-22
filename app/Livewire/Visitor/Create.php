<?php

namespace App\Livewire\Visitor;

use Livewire\Component;

use App\Models\Visit;
use App\Models\Member;
use Livewire\Attributes\On;
use App\Livewire\Dashboard\VisitorOverview;
use App\Events\VisitorCreated;
use Illuminate\Support\Facades\Broadcast;
class Create extends Component
{
    public $typeList = [
        'member' => 'Member',
        'guest' => 'Non Member',
    ];
    public $form = [
        'member_id' => '',
        'guest_name' => '',
        'guest_phone' => '',
        'guest_identity' => '',
        'purpose' => '',
        'visit_type' => 'member',
    ];

    public function save()
    {
        if ($this->form['visit_type'] == 'member') {
            $this->validate([
                'form.member_id' => 'required|exists:members,slims_member_id',
            ]);
            $dataMember = $this->getMemberInfo($this->form['member_id']);
            $this->form['member_id'] = $dataMember->id;
            $this->form['guest_name'] = $dataMember->name;
            $this->form['guest_phone'] = $dataMember->phone;
            $this->form['guest_identity'] = $dataMember->slims_member_id;
        } else {
            $this->validate([
                'form.guest_name' => 'required|string',
                'form.guest_phone' => 'required|numeric',
                'form.guest_identity' => 'required|numeric',
                'form.purpose' => 'required|string',

            ]);
            $this->form['member_id'] = null;
        }
        $this->validate([
            'form.visit_type' => 'required|in:member,guest',
        ]);
        $this->form['visit_date'] = now()->toDateString();
        $this->form['visit_time'] = now()->toTimeString();
        $create = Visit::create($this->form);
        $this->dispatch('openWelcomeModal');
        VisitorCreated::dispatch();
        // $this->dispatch('refreshUpdate')->to(VisitorOverview::class);
        $this->reset();
    }

    protected function getMemberInfo($id = null)
    {
        if ($this->form['visit_type'] == 'member' && $id) {
            return Member::where('slims_member_id', $id)->first();
        }
        return null;
    }
    #[On('barcodeScanned')]
    public function saveScanedBarcode($barcode)
    {
        $dataMember = $this->getMemberInfo($barcode);
        if ($dataMember) {
            $this->form['member_id'] = $dataMember->id;
            $this->form['guest_name'] = $dataMember->name;
            $this->form['guest_phone'] = $dataMember->phone;
            $this->form['guest_identity'] = $dataMember->slims_member_id;
            $this->form['visit_date'] = now()->toDateString();
            $this->form['visit_time'] = now()->toTimeString();
            $create = Visit::create($this->form);
            $this->dispatch('openWelcomeModal');
            VisitorCreated::dispatch();
        }else{
            $this->dispatch('openBarcodeGagalModal');
        }
    }
    public function render()
    {
        return view('livewire.visitor.create');
    }
}
