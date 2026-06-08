<?php

namespace App\Livewire\Loans;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Member;
use App\Models\Loan;
use App\Models\Fine;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class Clearance extends Component
{
    use WithPagination;

    public $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function downloadPdf($memberId)
    {
        $member = Member::findOrFail($memberId);

        // 1. Validasi Keamanan Ulang (mencegah bypass)
        $activeLoans = Loan::where('member_id', $member->id)->where('status', 'dipinjam')->count();
        $unpaidFines = Fine::where('member_id', $member->id)->where('payment_status', 'belum_bayar')->count();

        if ($activeLoans > 0 || $unpaidFines > 0) {
            return; 
        }

        // 2. Tarik Data Spesifik dari API untuk Member ini
        $nama = '-';
        $kelas = '-';
        $identitas = $member->rfid_code;

        try {
            $response = Http::withToken(config('services.ibs_api.token'))
                            ->timeout(10)
                            ->get(config('services.ibs_api.url') . 'master-simpanans', [
                                'per_page' => 5000, 
                                'limit'    => 5000
                            ]);

            if ($response->successful()) {
                $dataArray = $response->json('data.data') ?? ($response->json('data')['data'] ?? $response->json('data'));
                $apiMembers = collect($dataArray)->keyBy(fn($item) => (string) ($item['rfid_code'] ?? ''));
                
                $apiData = $apiMembers->get((string) $member->rfid_code);

                if ($apiData) {
                    $nasabah = $apiData['nasabah'] ?? [];
                    $isSantri = strtolower($nasabah['jenis_nasabah']['nama_komponen'] ?? '') === 'santri';
                    
                    $nama = $isSantri 
                        ? ($nasabah['detail_santri']['nama_lengkap'] ?? ($nasabah['nama'] ?? '-'))
                        : ($nasabah['detail_pegawai']['nama_pegawai'] ?? ($nasabah['nama'] ?? '-'));
                        
                    $kelas = $isSantri ? ($nasabah['detail_santri']['kelas'] ?? '-') : 'Pegawai/Umum';
                    
                    // MENGAMBIL IDENTITAS DARI ID SERVER NASABAH
                    $identitas = $nasabah['id_server'] ?? $nasabah['id'] ?? $member->rfid_code;
                }
            }
        } catch (\Exception $e) {
            Log::warning('[Clearance PDF] Gagal meload API: ' . $e->getMessage());
        }

        // 3. Generate PDF menggunakan View
        $data = [
            'nama' => $nama,
            'kelas' => $kelas,
            'identitas' => $identitas,
        ];

        $pdf = Pdf::loadView('pdf.bebas-pustaka', $data)->setPaper('a4', 'portrait');

        $fileName = 'Surat_Bebas_Pustaka_' . str_replace(' ', '_', $nama) . '.pdf';
        return response()->streamDownload(fn () => print($pdf->output()), $fileName);
    }

    public function render()
    {
        // 1. Tarik Data API Massal untuk Tampilan Tabel & Logika Pencarian
        $apiMembers = collect();
        try {
            $response = Http::withToken(config('services.ibs_api.token'))->timeout(15)->get(config('services.ibs_api.url') . 'master-simpanans', [
                'per_page' => 5000, 
                'limit'    => 5000
            ]);
            if ($response->successful()) {
                $dataArray = $response->json('data.data') ?? ($response->json('data')['data'] ?? $response->json('data'));
                $apiMembers = collect($dataArray)->keyBy(fn($item) => (string) ($item['rfid_code'] ?? ''));
            }
        } catch (\Exception $e) {}

        // 2. Logika Pencarian Cerdas (Filter API dulu, baru query lokal)
        $matchingRfids = [];
        if (!empty($this->search)) {
            $searchTerm = strtolower($this->search);
            foreach ($apiMembers as $rfid => $data) {
                $nasabah = $data['nasabah'] ?? [];
                $isSantri = strtolower($nasabah['jenis_nasabah']['nama_komponen'] ?? '') === 'santri';
                $nama = $isSantri 
                    ? strtolower($nasabah['detail_santri']['nama_lengkap'] ?? $nasabah['nama'] ?? '')
                    : strtolower($nasabah['detail_pegawai']['nama_pegawai'] ?? $nasabah['nama'] ?? '');
                
                $idServer = strtolower($nasabah['id_server'] ?? $nasabah['id'] ?? '');

                // Cari kecocokan di Nama, RFID, atau ID Server Nasabah
                if (str_contains($nama, $searchTerm) || str_contains((string)$rfid, $searchTerm) || str_contains($idServer, $searchTerm)) {
                    $matchingRfids[] = (string)$rfid;
                }
            }
        }

        // 3. Query Database Lokal
        $members = Member::query()
            ->when($this->search, function ($query) use ($matchingRfids) {
                // Gunakan whereIn dari hasil pencarian API di atas
                $query->whereIn('rfid_code', $matchingRfids)
                      ->orWhere('rfid_code', 'like', "%{$this->search}%");
            })
            ->addSelect([
                'active_loans' => Loan::selectRaw('count(*)')
                    ->whereColumn('member_id', 'members.id')
                    ->where('status', 'dipinjam'),
                
                'unpaid_fines' => Fine::selectRaw('sum(total_fines)')
                    ->whereColumn('member_id', 'members.id')
                    ->where('payment_status', 'belum_bayar'),
            ])
            ->orderBy('id', 'desc')
            ->paginate(15);

        // 4. Mapping Data API ke Variabel Tabel
        foreach ($members->items() as $member) {
            $apiData = $apiMembers->get((string) $member->rfid_code);
            if ($apiData) {
                $nasabah = $apiData['nasabah'] ?? [];
                $isSantri = strtolower($nasabah['jenis_nasabah']['nama_komponen'] ?? '') === 'santri';
                
                $member->api_nama = $isSantri ? ($nasabah['detail_santri']['nama_lengkap'] ?? $nasabah['nama']) : ($nasabah['detail_pegawai']['nama_pegawai'] ?? $nasabah['nama']);
                
                // MENGAMBIL IDENTITAS DARI ID SERVER NASABAH
                $member->api_identitas = $nasabah['id_server'] ?? $nasabah['id'] ?? $member->rfid_code;
                
                $member->api_kelas = $isSantri ? ($nasabah['detail_santri']['kelas'] ?? '-') : '-';
                
                // Ambil No Telepon agar tidak error 'display_phone' di blade
                $member->api_phone = $nasabah['no_telepon'] ?? $nasabah['no_hp'] ?? '-';
            } else {
                $member->api_nama = 'Data Tidak Dikenal API';
                $member->api_identitas = $member->rfid_code;
                $member->api_kelas = '-';
                $member->api_phone = '-';
            }
        }

        return view('livewire.loans.clearance', compact('members'))->title('Surat Bebas Pustaka');
    }
}