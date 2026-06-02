<?php

namespace App\Livewire\Member;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Member;
use App\Service\ApiSyncService;
use App\Traits\WithToast;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Index extends Component
{
    use WithPagination, WithToast;

    protected $paginationTheme = 'tailwind';

    public $search = '';
    public $perPage = 10;
    public $filterType = '';
    public $types = [];

    protected $queryString = [
        'search'     => ['except' => ''],
        'filterType' => ['except' => ''],
        'perPage'    => ['except' => 10],
    ];

    public function mount()
    {
        $this->types = $this->getTypesProperty();
    }

    protected function getTypesProperty()
    {
        return Member::select('type')->whereNotNull('type')->distinct()->pluck('type');
    }

    public function updatingFilterType() { $this->resetPage(); }
    public function updatingPerPage()    { $this->resetPage(); }
    public function updatingSearch()     { $this->resetPage(); }

   public function syncApiData(ApiSyncService $syncService)
    {
        $result = $syncService->sync();

        if ($result['failed']) {
            $this->toast($result['message'], 'error');
        } else {
            // Refresh opsi filter (Tipe) jika ada perubahan
            $this->types = $this->getTypesProperty();

            // Jika tidak ada perubahan, munculkan warna biru (info), jika ada munculkan hijau (success)
            if ($result['synced'] == 0) {
                $this->toast($result['message'], 'info');
            } else {
                $this->toast("Sync Selesai! " . $result['message']);
            }
        }
    }

    public function render()
    {
        // 1. Ambil data dari tabel lokal e-pustaka
        $members = Member::query()
            ->when($this->filterType, fn($q) => $q->where('type', $this->filterType))
            ->when($this->search, fn($q) => $q->where('rfid_code', 'like', "%{$this->search}%"))
            ->orderBy('id', 'desc')
            ->paginate($this->perPage);

        // 2. Tarik data profil dari API IBS
        $apiMembers = collect();
        try {
            $response = Http::withToken(config('services.ibs_api.token'))
                            ->timeout(15)
                            ->get(config('services.ibs_api.url') . 'master-simpanans', [
                                'per_page' => 5000, 
                                'limit'    => 5000
                            ]);

            if ($response->successful()) {
                // ✅ KUNCI PERBAIKAN: Gunakan data.data karena JSON dibungkus paginate()
                $dataArray = $response->json('data.data') ?? [];
                
                // Jika server tidak mem-paginate dan langsung return array, antisipasi dengan ini:
                if (empty($dataArray) && !empty($response->json('data'))) {
                     $dataArray = $response->json('data'); 
                     // Fallback jika suatu saat API nya diganti tidak pakai paginate
                     if (isset($dataArray['data'])) $dataArray = $dataArray['data'];
                }

                $apiMembers = collect($dataArray)->keyBy('rfid_code');
            }
        } catch (\Exception $e) {
            Log::warning('[Member Index] Gagal load API: ' . $e->getMessage());
        }

        // 3. Gabungkan (Mapping) Data
        $fotoBaseUrl = 'http://192.168.2.46/api_ibs/public/';
        
        foreach ($members as $member) {
            $apiData = $apiMembers->get($member->rfid_code);

            if ($apiData) {
                // Ambil object utama nasabah
                $nasabah  = $apiData['nasabah'] ?? [];
                
                // Ambil detail spesifiknya
                $detailSantri  = $nasabah['detail_santri'] ?? [];
                $detailPegawai = $nasabah['detail_pegawai'] ?? [];
                
                // Cek apakah dia santri atau bukan
                $isSantri = strtolower($nasabah['jenis_nasabah']['nama_komponen'] ?? '') === 'santri';

                // 1. Mapping Nama
                $member->api_nama = $nasabah['nama'] ?? '-';

                // 2. Mapping Foto
                $fotoRaw = $nasabah['foto'] ?? null;
                $member->api_foto = $fotoRaw 
                    ? (str_starts_with($fotoRaw, 'http') ? $fotoRaw : $fotoBaseUrl . ltrim($fotoRaw, '/')) 
                    : 'https://ui-avatars.com/api/?name=' . urlencode($member->api_nama) . '&background=random';

                // 3. Mapping Kelas (Hanya untuk Santri)
                $member->api_kelas = $isSantri ? ($detailSantri['kelas'] ?? '-') : '-';

                // 4. Mapping Kontak (Hanya untuk Pegawai, Santri dikosongkan)
                $member->api_phone = $isSantri 
                    ? '-' 
                    : ($detailPegawai['no_hp'] ?? ($nasabah['no_telp'] ?? '-'));

            } else {
                // Fallback jika API gagal / RFID belum ada di server pusat
                $member->api_nama  = 'RFID: ' . ($member->rfid_code ?? '-');
                $member->api_foto  = 'https://ui-avatars.com/api/?name=Unknown&background=random';
                $member->api_kelas = '-';
                $member->api_phone = '-';
            }
        }

        return view('livewire.member.index', ['members' => $members])->title('Data Members');
    }
}