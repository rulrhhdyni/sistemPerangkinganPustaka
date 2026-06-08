<?php

namespace App\Livewire\Visitor;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Visit;
use App\Models\Member;
use App\Traits\WithToast;
use Carbon\Carbon;
use Flux\Flux;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
    }

    public function updatedPeriode($value)
    {
        if ($value === 'range_date') {
            Flux::modal('range-rekap-modal')->show();
        } else {
            $this->rangeDate = null;
            $this->resetPage();
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

        // Tarik Data Identitas dari API
        $apiMembers = collect();
        try {
            $response = Http::withToken(config('services.ibs_api.token'))
                            ->timeout(10)
                            ->get(config('services.ibs_api.url') . 'master-simpanans', [
                                'per_page' => 5000, 
                                'limit'    => 5000
                            ]);

            if ($response->successful()) {
                $dataArray = $response->json('data.data') ?? [];
                if (empty($dataArray) && !empty($response->json('data'))) {
                     $dataArray = $response->json('data'); 
                     if (isset($dataArray['data'])) $dataArray = $dataArray['data'];
                }
                
                $apiMembers = collect($dataArray)->keyBy(function ($item) {
                    return (int) ($item['rfid_code'] ?? 0);
                });
            }
        } catch (\Exception $e) {
            Log::warning('[VisitorIndex] Gagal meload API: ' . $e->getMessage());
        }

        // Mapping Data Lokal dengan API
        foreach ($data->items() as $visit) {
            $rfidToSearch = null;
            // Deteksi tipe (menangani perbedaan kolom mode normal 'visit_type' dan mode rekap 'type')
            $currentType = $visit->type ?? $visit->visit_type;

            if ($currentType === 'member') {
                if (str_contains($visit->guest_name, 'RFID:')) {
                    $rfidToSearch = trim(str_replace('RFID:', '', $visit->guest_name));
                } else {
                    $localMember = Member::find($visit->guest_identity);
                    if ($localMember) {
                        $rfidToSearch = $localMember->rfid_code;
                    } else {
                        $rfidToSearch = $visit->guest_identity;
                    }
                }
            }

            // Cari di API menggunakan cast integer
            $apiData = $apiMembers->get((int) $rfidToSearch);

            if ($currentType === 'member' && $apiData) {
                $nasabah       = $apiData['nasabah'] ?? [];
                $detailSantri  = $nasabah['detail_santri'] ?? [];
                $detailPegawai = $nasabah['detail_pegawai'] ?? [];
                $isSantri      = strtolower($nasabah['jenis_nasabah']['nama_komponen'] ?? '') === 'santri';
                
                $visit->api_nama = $isSantri
                    ? ($detailSantri['nama_lengkap'] ?? ($nasabah['nama'] ?? '-'))
                    : ($detailPegawai['nama_pegawai'] ?? ($nasabah['nama'] ?? '-'));
                    
                $kelas = $isSantri ? ($detailSantri['kelas'] ?? '-') : 'Pegawai / Umum';
                $visit->api_identitas = 'Kelas: ' . $kelas;
                
            } else {
                $visit->api_nama = $visit->guest_name ?? 'Guest Tidak Dikenal';
                $visit->api_identitas = $visit->guest_identity ?? '-';
            }
        }

        return view('livewire.visitor.index', [
            'visits' => $data,
        ])->title('Data Kunjungan');
    }
}