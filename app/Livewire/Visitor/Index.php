<?php

namespace App\Livewire\Visitor;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Visit;
use App\Traits\WithToast;
use Carbon\Carbon;
use Flux\Flux;
use Illuminate\Support\Facades\DB;

class Index extends Component
{

    use WithPagination, WithToast;
    public $search = '';
    public $perPage = 10;

    public array $periodeList = [
        'today' => 'Hari Ini',
        'this_week' => 'Minggu Ini',
        'this_month' => 'Bulan Ini',
        'this_year' => 'Tahun Ini',
        'range_date' => 'Semester / Range',
    ];

    public array $typeList = [
        null => 'Semua',
        'member' => 'Member',
        'guest' => 'Guest',
    ];

    public ?string $periode = null;
    public ?string $type = null;

    public ?array $rangeDate = [];

    public $selectedId = null;

    public function updatingPerPage()
    {
        $this->resetPage();
    }
    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function clearRekap()
    {
        $this->periode = null;
        $this->rangeDate = null;
        $this->type = null;
        $this->search = '';
        $this->resetPage();
    }

    protected function query()
    {
        return Visit::query()
            ->with('member')
            ->when(
                $this->type,
                fn($q) => $q->where('visit_type', $this->type)
            )
            ->when(
                $this->search,
                fn($q) =>
                $q->where(function ($q) {
                    $q->where('guest_name', 'like', '%' . $this->search . '%')
                        ->orWhere('guest_identity', 'like', '%' . $this->search . '%')
                        ->orWhere('guest_phone', 'like', '%' . $this->search . '%');
                })
            )
            ->orderByDesc('visit_date')
            ->paginate($this->perPage);
    }


    protected function rekapVisitorIndividu()
    {
        $memberQuery = Visit::memberRekap($this->periode, $this->rangeDate);
        $guestQuery = Visit::guestRekap($this->periode, $this->rangeDate);

        $union = $memberQuery->unionAll($guestQuery);

        $data = DB::query()
            ->fromSub($union, 'rekap')
            ->select('*')
            ->when(
                $this->type,
                fn($q) => $q->where('type', $this->type)
            )
            ->when(
                $this->search,
                fn($q) => $q->where(function ($q) {
                    $q->where('guest_name', 'like', '%' . $this->search . '%')
                        ->orWhere('guest_identity', 'like', '%' . $this->search . '%')
                        ->orWhere('guest_phone', 'like', '%' . $this->search . '%');
                })
            )

            ->orderByDesc('jml')
            ->paginate($this->perPage);
        return $data;
        // return ['periode'=>$this->periode,'range'=>$this->rangeDate,'union' => $union->get(), 'data' => $data->get(),'paginate'=> $data->paginate($this->perPage) ];
    }



    public function updatedPeriode($value)
    {
        if ($value === 'range_date') {
            Flux::modal('range-rekap-modal')->show();
        } else {
            $this->rangeDate = null;
            $this->resetPage();
            // dump($this->rekapVisitorIndividu());
        }
    }

    public function setRangeDate()
    {
        $this->validate([
            'rangeDate.start' => 'required|date',
            'rangeDate.end' => 'required|date|after_or_equal:rangeDate.start',
        ]);

        // Normalisasi format
        $this->rangeDate = [
            'start' => Carbon::parse($this->rangeDate['start'])->startOfDay()->toDateTimeString(),
            'end' => Carbon::parse($this->rangeDate['end'])->endOfDay()->toDateTimeString(),
        ];

        // Reset pagination supaya tidak lompat halaman
        $this->resetPage();

        // Tutup modal
        Flux::modal('range-rekap-modal')->close();
    }


    public function render()
    {
        $data = $this->periode 
            ? $this->rekapVisitorIndividu() // REKAP MODE
            : $this->query();               // NORMAL MODE

        return view('livewire.visitor.index', [
            'visits' => $data,
        ]);
    }
}

