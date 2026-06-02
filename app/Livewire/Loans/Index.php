<?php

namespace App\Livewire\Loans;

use Livewire\Component;
use App\Models\Loan;
use App\Models\Member;
use App\Models\Book;
use App\Models\Fine;
use App\Traits\WithToast;
use Carbon\Carbon;
use Flux\Flux;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Index extends Component
{
    use WithToast;

    public $step        = 1;
    public $rfid_code   = '';
    public $book_code   = '';
    public $book_title  = '';
    public $book_id     = null;
    public $member_name = '';
    public $member_id   = null;
    public $member_data = null;

    public $lostLoanId  = null;
    public $book_price  = '';

    /**
     * Saat RFID di-scan — cari member dari DB lokal, lalu tarik detailnya dari API.
     */
    public function updatedRfidCode($value)
    {
        $cleanRfid = trim((string) $value);
        $member    = Member::where('rfid_code', $cleanRfid)->first();

        if ($member) {
            // 💡 PERBAIKAN: Tarik detail dari API pusat karena DB lokal hanya punya RFID
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

                    // Pencocokan menggunakan integer agar angka 0 di depan diabaikan
                    $apiData = collect($dataArray)->first(function ($val) use ($member) {
                        return (int) ($val['rfid_code'] ?? 0) === (int) $member->rfid_code;
                    });

                    if ($apiData) {
                        $nasabah       = $apiData['nasabah'] ?? [];
                        $detailSantri  = $nasabah['detail_santri'] ?? [];
                        $detailPegawai = $nasabah['detail_pegawai'] ?? [];
                        $isSantri      = strtolower($nasabah['jenis_nasabah']['nama_komponen'] ?? '') === 'santri';

                        $nama = $isSantri
                            ? ($detailSantri['nama_lengkap'] ?? ($nasabah['nama'] ?? '-'))
                            : ($detailPegawai['nama_pegawai'] ?? ($nasabah['nama'] ?? '-'));

                        $fotoRaw = $nasabah['foto'] ?? null;
                        $fotoBaseUrl = 'http://192.168.2.46/api_ibs/public/';
                        $foto = $fotoRaw 
                            ? (str_starts_with($fotoRaw, 'http') ? $fotoRaw : $fotoBaseUrl . ltrim($fotoRaw, '/')) 
                            : 'https://ui-avatars.com/api/?name=' . urlencode($nama) . '&background=random';

                        $kelas  = $isSantri ? ($detailSantri['kelas'] ?? '-') : '-';
                        $alamat = $isSantri ? ($detailSantri['tempat_tinggal_alamat'] ?? '-') : ($detailPegawai['alamat'] ?? '-');

                        $this->member_name = $nama;
                        $this->member_id   = $member->id;
                        $this->member_data = (object) [
                            'api_foto'   => $foto,
                            'api_kelas'  => $kelas,
                            'api_alamat' => $alamat,
                            'type'       => $member->type,
                        ];
                        $this->step = 3;
                        return; // Berhasil, keluar dari fungsi
                    }
                }
            } catch (\Exception $e) {
                Log::error('[Loans] Gagal tarik data API saat scan RFID: ' . $e->getMessage());
            }

            // Fallback jika API sedang mati / data RFID tidak ketemu di pusat
            $this->member_name = 'RFID: ' . $member->rfid_code;
            $this->member_id   = $member->id;
            $this->member_data = (object) [
                'api_foto'   => 'https://ui-avatars.com/api/?name=Unknown&background=random',
                'api_kelas'  => '-',
                'api_alamat' => '-',
                'type'       => $member->type,
            ];
            $this->step = 3;

        } else {
            $this->toast('RFID tidak terdaftar di perpustakaan!', 'error');
            $this->reset('rfid_code');
        }
    }

    public function updatedBookCode($value)
    {
        $book = Book::where('book_code', trim($value))->first();
        if ($book && $book->status === 'tersedia') {
            $this->book_title = $book->title;
            $this->book_id    = $book->id;
            $this->step       = 2;
        } else {
            $this->toast('Buku tidak ditemukan/sedang dipinjam!', 'error');
            $this->reset('book_code');
        }
    }

    public function confirmLoan()
    {
        Loan::create([
            'member_id'  => $this->member_id,
            'book_code'  => $this->book_code,
            'book_title' => $this->book_title,
            'borrow_date'=> now(),
            'due_date'   => now()->addDays(7),
            'status'     => 'dipinjam',
        ]);

        Book::find($this->book_id)->update(['status' => 'dipinjam']);
        $this->toast('Peminjaman berhasil!');
        $this->resetFlow();
    }

    public function resetFlow()
    {
        $this->reset(['step', 'book_code', 'book_title', 'rfid_code',
                      'member_name', 'member_id', 'member_data', 'book_id']);
    }

    public function tagihDenda($loanId)
    {
        $loan    = Loan::findOrFail($loanId);
        $dueDate = Carbon::parse($loan->due_date)->startOfDay();
        $today   = Carbon::today();

        if ($today->gt($dueDate)) {
            $lateDays   = $dueDate->diffInDays($today);
            $fineAmount = $lateDays * 1000;

            Fine::firstOrCreate(
                ['loan_id' => $loan->id, 'payment_status' => 'belum_bayar'],
                [
                    'member_id'   => $loan->member_id,
                    'total_fines' => $fineAmount,
                    'fine_type'   => 'keterlambatan',
                ]
            );

            return redirect()->to('/fines');
        }
    }

    public function returnBook($loanId)
    {
        $loan    = Loan::findOrFail($loanId);
        $dueDate = Carbon::parse($loan->due_date)->startOfDay();
        $today   = Carbon::today();

        $loan->update(['return_date' => now(), 'status' => 'kembali']);
        Book::where('book_code', $loan->book_code)->update(['status' => 'tersedia']);

        if ($today->gt($dueDate)) {
            $lateDays = $dueDate->diffInDays($today);
            Fine::create([
                'loan_id'        => $loan->id,
                'member_id'      => $loan->member_id,
                'total_fines'    => $lateDays * 1000,
                'fine_type'      => 'keterlambatan',
                'payment_status' => 'belum_bayar',
            ]);
            $this->toast('Buku dikembalikan. Denda keterlambatan ditambahkan!', 'warning');
        } else {
            $this->toast('Buku dikembalikan tepat waktu.');
        }
    }

    public function extend($loanId)
    {
        $loan = Loan::findOrFail($loanId);
        $loan->update([
            'due_date'        => Carbon::parse($loan->due_date)->addDays(3),
            'extension_count' => $loan->extension_count + 1,
        ]);
        $this->toast('Waktu diperpanjang.');
    }

    public function openLostModal($loanId)
    {
        $this->lostLoanId = $loanId;
        Flux::modal('lost-book')->show();
    }

    public function reportLost()
    {
        $this->validate([
            'book_price' => 'required|numeric|min:100',
        ]);

        $loan = Loan::findOrFail($this->lostLoanId);
        $loan->update(['status' => 'hilang']);

        // Update status buku
        Book::where('book_code', $loan->book_code)->update(['status' => 'hilang']);

        // Buat record denda buku hilang
        Fine::create([
            'loan_id'        => $loan->id,
            'member_id'      => $loan->member_id,
            'total_fines'    => (int) $this->book_price,
            'fine_type'      => 'hilang',
            'book_price'     => (int) $this->book_price,
            'payment_status' => 'belum_bayar',
        ]);

        Flux::modal('lost-book')->close();
        $this->toast('Buku dilaporkan hilang dan denda ditambahkan.');
        $this->reset('book_price');
    }

    public function render()
    {
        $loans = Loan::with('member')->where('status', 'dipinjam')->latest()->get();

        // 💡 PERBAIKAN: Tarik data API untuk tabel "Peminjaman Aktif"
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
                
                $apiMembers = collect($dataArray)->keyBy(function ($item) {
                    return (int) ($item['rfid_code'] ?? 0);
                });
            }
        } catch (\Exception $e) {
            Log::warning('[Loans] Gagal meload nama API untuk tabel: ' . $e->getMessage());
        }

        foreach ($loans as $loan) {
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
                
                // Tambahkan mapping kelas
                $kelas = $isSantri ? ($detailSantri['kelas'] ?? '-') : '-';
                
                $loan->api_nama  = $nama;
                $loan->api_kelas = $kelas; // ✅ Menyimpan kelas ke variabel
            } else {
                $loan->api_nama  = 'RFID: ' . ($rfid ?? 'Tidak Dikenal');
                $loan->api_kelas = '-';
            }
        }

        return view('livewire.loans.index', ['activeLoans' => $loans]);
    }
}