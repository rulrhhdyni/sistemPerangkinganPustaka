<?php

namespace App\Livewire\Loans;

use Livewire\Component;
use App\Models\Loan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Lost extends Component
{
    public function render()
    {
        // Ambil data peminjaman dengan status hilang
        $lostLoans = Loan::with(['member', 'fines'])
            ->where('status', 'hilang')
            ->latest()
            ->get();

        // Tarik data API untuk mendapatkan Nama Peminjam
        $apiMembers = collect();
        try {
            $response = Http::withToken(config('services.ibs_api.token'))
                            ->timeout(15)
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
                
                // Mapping by RFID integer
                $apiMembers = collect($dataArray)->keyBy(function ($item) {
                    return (int) ($item['rfid_code'] ?? 0);
                });
            }
        } catch (\Exception $e) {
            Log::warning('[Lost] Gagal meload nama API untuk tabel: ' . $e->getMessage());
        }

        // Proses pencocokan nama
        foreach ($lostLoans as $loan) {
            $rfid = $loan->member->rfid_code ?? null;
            $apiData = $apiMembers->get((int) $rfid);

            if ($apiData) {
                $nasabah       = $apiData['nasabah'] ?? [];
                $detailSantri  = $nasabah['detail_santri'] ?? [];
                $detailPegawai = $nasabah['detail_pegawai'] ?? [];
                $isSantri      = strtolower($nasabah['jenis_nasabah']['nama_komponen'] ?? '') === 'santri';
                
                $nama = $isSantri
                    ? ($detailSantri['nama_lengkap'] ?? ($nasabah['nama'] ?? '-'))
                    : ($detailPegawai['nama_pegawai'] ?? ($nasabah['nama'] ?? '-'));
                
                $loan->api_nama = $nama;
            } else {
                $loan->api_nama = 'RFID: ' . ($rfid ?? 'Tidak Dikenal');
            }
        }

        return view('livewire.loans.lost', [
            'lostLoans' => $lostLoans
        ])->title('Laporan Buku Hilang');
    }
}