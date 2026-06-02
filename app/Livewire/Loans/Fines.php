<?php

namespace App\Livewire\Loans;

use Livewire\Component;
use App\Models\Fine;
use App\Models\Member;
use App\Models\TransaksiLokalPerpus;
use App\Traits\WithToast;
use Flux\Flux;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class Fines extends Component
{
    use WithToast;

    public $pin             = '';
    public $selectedFineId  = null;
    public $selectedFine    = null;
    public $paymentMethod   = 'rfid';
    public $member_api_data = null;
    public $keterangan      = '';
    public $errorMessage    = '';

    public function resetPaymentForm()
    {
        $this->selectedFineId  = null;
        $this->selectedFine    = null;
        $this->paymentMethod   = 'rfid';
        $this->member_api_data = null;
        $this->keterangan      = '';
        $this->errorMessage    = '';
    }

    public function openPaymentModal($fineId)
    {
        $this->resetPaymentForm();
        $this->selectedFineId = $fineId;
        $this->selectedFine   = Fine::with(['member', 'loan'])->findOrFail($fineId);

        $this->keterangan = "Pembayaran denda buku: " . ($this->selectedFine->loan->book_title ?? '-');

        // ✅ Hit API untuk mengambil seluruh data profil dan saldo secara Real-Time
        $this->fetchMemberDataFromApi();

        Flux::modal('payment-modal')->show();
    }

    /**
     * Tarik data profil dan saldo langsung dari API IBS berdasarkan rfid_code.
     */
    private function fetchMemberDataFromApi()
    {
        $member = $this->selectedFine->member ?? null;

        if (!$member || empty($member->rfid_code)) {
            $this->errorMessage = 'Data member atau kartu RFID tidak valid!';
            return;
        }

        try {
            $response = Http::withToken(config('services.ibs_api.token'))
                            ->timeout(15)
                            ->get(config('services.ibs_api.url') . 'master-simpanans', [
                                'per_page' => 5000, 
                                'limit'    => 5000
                            ]);

            if ($response->successful()) {
                // Tembus lapisan pagination JSON
                $dataArray = $response->json('data.data') ?? [];
                if (empty($dataArray) && !empty($response->json('data'))) {
                     $dataArray = $response->json('data'); 
                     if (isset($dataArray['data'])) $dataArray = $dataArray['data'];
                }

                $apiData = collect($dataArray)->firstWhere('rfid_code', $member->rfid_code);

                if ($apiData) {
                    $nasabah       = $apiData['nasabah'] ?? [];
                    $detailSantri  = $nasabah['detail_santri'] ?? [];
                    $detailPegawai = $nasabah['detail_pegawai'] ?? [];
                    $isSantri      = strtolower($nasabah['jenis_nasabah']['nama_komponen'] ?? '') === 'santri';

                    // Mapping Nama
                    $nama = $isSantri
                        ? ($detailSantri['nama_lengkap'] ?? ($nasabah['nama'] ?? '-'))
                        : ($detailPegawai['nama_pegawai'] ?? ($nasabah['nama'] ?? '-'));

                    // Mapping Foto
                    $fotoRaw = $nasabah['foto'] ?? null;
                    $fotoBaseUrl = 'http://192.168.2.46/api_ibs/public/';
                    $foto = $fotoRaw 
                        ? (str_starts_with($fotoRaw, 'http') ? $fotoRaw : $fotoBaseUrl . ltrim($fotoRaw, '/')) 
                        : 'https://ui-avatars.com/api/?name=' . urlencode($nama) . '&background=random';

                    // Mapping Alamat & Kelas
                    $alamat = $isSantri ? ($detailSantri['tempat_tinggal_alamat'] ?? '-') : ($detailPegawai['alamat'] ?? '-');
                    $kelas  = $isSantri ? ($detailSantri['kelas'] ?? '-') : '-';

                    $saldoTerkini = (int) ($apiData['saldo_efektif'] ?? 0);

                    $this->member_api_data = [
                        'nama'        => $nama,
                        'foto'        => $foto,
                        'alamat'      => $alamat,
                        'kelas'       => $kelas,
                        'no_rekening' => $apiData['no_rekening'] ?? '-',
                        'saldo'       => $saldoTerkini,
                        'sisa'        => $saldoTerkini - $this->selectedFine->total_fines,
                        'type'        => $member->type ?? '-',
                    ];
                    
                    return; // Selesai
                }
            }
            
            $this->errorMessage = 'Data profil RFID tidak ditemukan di server pusat.';

        } catch (\Exception $e) {
            Log::error('[Fines] Gagal tarik data API saat buka modal: ' . $e->getMessage());
            $this->errorMessage = 'Gagal menghubungi server pusat API.';
        }
    }

    public function processPayment()
    {
        $this->dispatch('console-log', ['message' => 'Fungsi processPayment terpanggil!']);
        if (!$this->selectedFine) return;

        $adminName = Auth::user()->name ?? 'Admin Perpus';

        // --- CASH ---
        if ($this->paymentMethod === 'cash') {
            $this->completePaymentTransaction('cash', $adminName);
            $this->toast('Pembayaran tunai berhasil dicatat!');
            return;
        }

        // --- CASHLESS ---
        if (!$this->member_api_data) {
            $this->errorMessage = 'Data rekening dari API tidak tersedia!';
            return;
        }

        $fineAmount = $this->selectedFine->total_fines;
        if ($this->member_api_data['saldo'] < $fineAmount) {
            $this->errorMessage = 'Saldo rekening peminjam tidak mencukupi!';
            return;
        }

        try {
            $response = Http::acceptJson()
                ->withToken(config('services.ibs_api.token'))
                ->timeout(15)
                ->post(config('services.ibs_api.url') . 'pembayaran-perpus', [
                    'rekening_pengirim' => $this->member_api_data['no_rekening'],
                    'rekening_penerima' => '04.101.040007960',
                    'nominal'           => (int) $fineAmount,
                    'nama_user'         => $adminName,
                    'keterangan'        => ($this->selectedFine->fine_type === 'hilang' ? '[HILANG] ' : '[DENDA] ') . $this->keterangan,
                    
                ]);

            if ($response->successful()) {
                $this->completePaymentTransaction('cashless', $adminName);
                $this->toast('Pembayaran via Cashless berhasil diproses di server IBS!');
            } else {
                $this->errorMessage = 'Gagal memotong saldo: ' . ($response->json('message') ?? 'Ditolak server.');
            }
        } catch (\Exception $e) {
            $this->errorMessage = 'Gagal menghubungi server API: ' . $e->getMessage();
        }
    }

    private function completePaymentTransaction($metode, $adminName)
    {
        TransaksiLokalPerpus::create([
            'fine_id'           => $this->selectedFine->id,
            'member_id'         => $this->selectedFine->member_id,
            'nominal'           => $this->selectedFine->total_fines,
            'metode_pembayaran' => $metode,
            'keterangan'        => $this->keterangan,
            'nama_petugas'      => $adminName,
        ]);

        $this->selectedFine->update(['payment_status' => 'lunas']);

        $loan = $this->selectedFine->loan;
        if ($loan && $loan->status === 'dipinjam') {
            $loan->update(['return_date' => now(), 'status' => 'kembali']);
            \App\Models\Book::where('book_code', $loan->book_code)->update(['status' => 'tersedia']);
        }

        Flux::modal('payment-modal')->close();
        $this->resetPaymentForm();
    }

    public function render()
    {
        $fines = Fine::with(['member', 'loan'])
                     ->where('payment_status', 'belum_bayar')
                     ->latest()
                     ->get();

        // ✅ Mengambil seluruh data API sekali saja untuk mapping nama ke tabel
        $apiMembers = collect();
        try {
            $response = Http::withToken(config('services.ibs_api.token'))
                            ->timeout(15)
                            ->get(config('services.ibs_api.url') . 'master-simpanans', [
                                'per_page' => 5000, 
                                'limit'    => 5000
                            ]);

            if ($response->successful()) {
                // Tembus lapisan pagination JSON
                $dataArray = $response->json('data.data') ?? [];
                if (empty($dataArray) && !empty($response->json('data'))) {
                     $dataArray = $response->json('data'); 
                     if (isset($dataArray['data'])) $dataArray = $dataArray['data'];
                }
                
                $apiMembers = collect($dataArray)->keyBy('rfid_code');
            }
        } catch (\Exception $e) {
            Log::warning('[Fines] Gagal meload nama API untuk tabel: ' . $e->getMessage());
        }

        // Mapping dinamis nama dari API ke daftar denda
        foreach ($fines as $fine) {
            $rfid = $fine->member->rfid_code ?? null;
            $apiData = $apiMembers->get($rfid);

            if ($apiData) {
                $nasabah       = $apiData['nasabah'] ?? [];
                $detailSantri  = $nasabah['detail_santri'] ?? [];
                $detailPegawai = $nasabah['detail_pegawai'] ?? [];
                $isSantri      = strtolower($nasabah['jenis_nasabah']['nama_komponen'] ?? '') === 'santri';
                
                $nama = $isSantri
                    ? ($detailSantri['nama_lengkap'] ?? ($nasabah['nama'] ?? '-'))
                    : ($detailPegawai['nama_pegawai'] ?? ($nasabah['nama'] ?? '-'));
                
                $fine->api_nama = $nama;
            } else {
                $fine->api_nama = 'RFID: ' . ($rfid ?? 'Tidak Dikenal');
            }
        }

        return view('livewire.loans.fines', ['fines' => $fines])->title('Data Denda');
    }
}