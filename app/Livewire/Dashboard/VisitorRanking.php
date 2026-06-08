<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Models\Visit;
use App\Models\Member; // Pastikan ini ditambahkan
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VisitorRanking extends Component
{
    public string $periode = 'this_month'; // month | year

    public function getTopThreeProperty()
    {
        $member = Visit::memberRekap($this->periode);
        $guest = Visit::guestRekap($this->periode);

        $topVisitors = DB::query()
            ->fromSub($member->unionAll($guest), 'rekap')
            ->select(
                'guest_name',
                'guest_identity',
                'type',
                DB::raw('SUM(jml) as total')
            )
            ->groupBy('guest_name', 'guest_identity', 'type')
            ->orderByDesc('total')
            ->limit(3)
            ->get();

        // Tarik Data API
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
            Log::warning('[VisitorRanking] Gagal meload API: ' . $e->getMessage());
        }

        // Proses Sinkronisasi Nama
        foreach ($topVisitors as $row) {
            $rfidToSearch = null;

            if ($row->type === 'member') {
                // Kasus 1: Nama mengandung "RFID:"
                if (str_contains($row->guest_name, 'RFID:')) {
                    $rfidToSearch = trim(str_replace('RFID:', '', $row->guest_name));
                } 
                // Kasus 2: Identity berisi ID tabel lokal (misal: 14)
                else {
                    $localMember = Member::find($row->guest_identity);
                    if ($localMember) {
                        $rfidToSearch = $localMember->rfid_code;
                    } else {
                        $rfidToSearch = $row->guest_identity;
                    }
                }
            }

            // Cari di API menggunakan cast integer
            $apiData = $apiMembers->get((int) $rfidToSearch);

            if ($row->type === 'member' && $apiData) {
                $nasabah       = $apiData['nasabah'] ?? [];
                $detailSantri  = $nasabah['detail_santri'] ?? [];
                $detailPegawai = $nasabah['detail_pegawai'] ?? [];
                $isSantri      = strtolower($nasabah['jenis_nasabah']['nama_komponen'] ?? '') === 'santri';
                
                $row->api_nama = $isSantri
                    ? ($detailSantri['nama_lengkap'] ?? ($nasabah['nama'] ?? '-'))
                    : ($detailPegawai['nama_pegawai'] ?? ($nasabah['nama'] ?? '-'));
                    
                $kelas = $isSantri ? ($detailSantri['kelas'] ?? '-') : 'Pegawai / Umum';
                $row->api_identitas = 'Kelas: ' . $kelas;
                
            } else {
                $row->api_nama = $row->guest_name ?? 'Guest Tidak Dikenal';
                $row->api_identitas = $row->guest_identity ?? '-';
            }
        }

        return $topVisitors;
    }

    public function render()
    {
        return view('livewire.dashboard.visitor-ranking');
    }
}