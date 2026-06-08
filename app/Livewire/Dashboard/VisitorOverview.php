<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Models\Visit;
use App\Models\Member; // Pastikan ini ditambahkan
use Carbon\Carbon;
use Livewire\Attributes\On;
use App\Traits\WithToast;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VisitorOverview extends Component
{
    use WithToast;
    public int $limit = 10;

    public function mount(){
        // $this->refresh();  
    }

    #[On('echo:visitors,.visitor.created')]
    public function refresh()
    {
        $this->getVisitorsProperty();
    }
   
    public function getVisitorsProperty()
    {
        // 1. Ambil data kunjungan lokal hari ini
        $visitors = Visit::query()
            ->whereDate('visit_date', Carbon::today())
            ->orderBy('id', 'desc') // terbaru dulu
            ->limit($this->limit)
            ->get();

        // 2. Tarik Data Identitas dari API
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
            Log::warning('[VisitorOverview] Gagal meload API: ' . $e->getMessage());
        }

        // 3. Proses Pencocokan (Sinkronisasi Nama)
        foreach ($visitors as $visit) {
            $rfidToSearch = null;

            if ($visit->visit_type === 'member') {
                // Kasus 1: Nama mengandung "RFID:"
                if (str_contains($visit->guest_name, 'RFID:')) {
                    $rfidToSearch = trim(str_replace('RFID:', '', $visit->guest_name));
                } 
                // Kasus 2: Identity berisi ID tabel lokal
                else {
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

            if ($visit->visit_type === 'member' && $apiData) {
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
                // Fallback jika bukan member atau tidak ketemu di API
                $visit->api_nama = $visit->guest_name ?? 'Guest Tidak Dikenal';
                $visit->api_identitas = $visit->guest_identity ?? '-';
            }
        }

        return $visitors;
    }

    public function render()
    {
        return view('livewire.dashboard.visitor-overview');
    }
}