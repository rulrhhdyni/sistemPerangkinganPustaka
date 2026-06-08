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

    // --- Pencarian & Paginasi Tabel Pinjaman Aktif ---
    public $searchLoan    = '';   
    public $loanPage      = 1;   
    public $perPage       = 10;  

    // --- Pencarian & Paginasi Daftar Koleksi Buku ---
    public $search        = '';   
    public $currentPage   = 1;   
    public $availableBooks = [];  
    public $totalBooksPages = 1; 

    public $lostLoanId  = null;
    public $book_price  = '';
    public $isManualPrice = true; // Penanda apakah harga harus diinput manual
    public $selectedBookDetail = null;
    public $selectedLoanId = null;
    public $selectedBookTitle = '';
    public $selectedFineAmount = 0;

    public function mount()
    {
        $this->loadApiBooks();
    }

    // ----- Pinjaman: search & pagination -----
    public function updatedSearchLoan()
    {
        $this->loanPage = 1;
    }

    public function loanNextPage()
    {
        $this->loanPage++;
    }

    public function loanPrevPage()
    {
        if ($this->loanPage > 1) {
            $this->loanPage--;
        }
    }

    // ----- Koleksi Buku: search & pagination -----
    public function updatedSearch()
    {
        $this->currentPage = 1;
        $this->loadApiBooks();
    }

    public function nextPage()
    {
        if ($this->currentPage < $this->totalBooksPages) {
            $this->currentPage++;
            $this->loadApiBooks();
        }
    }

    public function prevPage()
    {
        if ($this->currentPage > 1) {
            $this->currentPage--;
            $this->loadApiBooks();
        }
    }


    public function updatedRfidCode($value)
    {
        $cleanRfid = trim((string) $value);
        $member    = Member::where('rfid_code', $cleanRfid)->first();

        if ($member) {
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
                        return;
                    }
                }
            } catch (\Exception $e) {
                Log::error('[Loans] Gagal tarik data API saat scan RFID: ' . $e->getMessage());
            }

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
        $inputBarcode = trim((string) $value);
        Log::info('Mencari barcode: ' . $inputBarcode);
        
        $apiUrl = 'http://192.168.4.253:8089/bulian/cek_buku.php?q=' . urlencode($inputBarcode);
        
        try {
            $response = Http::timeout(5)->get($apiUrl);
            Log::info('Status API: ' . $response->status());

            if ($response->successful()) {
                // 💡 PERUBAHAN: Karena API sekarang JSON terstruktur, kita ambil "data"-nya
                $resData = $response->json();
                $books = $resData['data'] ?? [];
                
                if (is_array($books) && count($books) > 0) {
                    $book = collect($books)->firstWhere('item_code', $inputBarcode);

                    if ($book) {
                        $loanedBook = Loan::where('book_code', $inputBarcode)
                                          ->where('status', 'dipinjam')
                                          ->exists();
                                          
                        $lostBook = Book::where('book_code', $inputBarcode)
                                        ->where('status', 'hilang')
                                        ->exists();

                        if ($lostBook) {
                            $this->toast('Buku ini berstatus HILANG dan tidak dapat dipinjam!', 'error');
                            $this->reset('book_code');
                        } elseif ($loanedBook) {
                            $this->toast('Buku sedang dipinjam!', 'error');
                            $this->reset('book_code');
                        } else {
                            $this->book_title = $book['title'];
                            $this->book_code  = $inputBarcode;
                            $this->book_id    = $book['biblio_id'];
                            $this->step       = 2;
                            $this->toast('Buku: ' . $book['title'] . ' siap dipinjam.', 'success');
                        }
                    } else {
                        $this->toast('Barcode ' . $inputBarcode . ' tidak ada di daftar koleksi!', 'error');
                    }
                } else {
                    $this->toast('Data buku kosong di SLiMS!', 'error');
                }
            }
        } catch (\Exception $e) {
            Log::error('Error API: ' . $e->getMessage());
            $this->toast('Gagal koneksi ke SLiMS', 'error');
        }
    }

    public function confirmLoan()
    {
        $book = Book::firstOrCreate(
            ['book_code' => $this->book_code],
            [
                'title'  => $this->book_title,
                'status' => 'tersedia' 
            ]
        );

        Loan::create([
            'member_id'   => $this->member_id,
            'book_code'   => $this->book_code,
            'book_title'  => $this->book_title,
            'borrow_date' => now(),
            'due_date'    => now()->addDays(7),
            'status'      => 'dipinjam',
        ]);

        $book->update(['status' => 'dipinjam']);

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

    public function confirmReturn($loanId)
    {
        $loan = Loan::findOrFail($loanId);
        $this->selectedLoanId = $loan->id;
        $this->selectedBookTitle = $loan->book_title;
        Flux::modal('confirm-return-modal')->show();
    }

    public function processReturn()
    {
        if ($this->selectedLoanId) {
            $this->returnBook($this->selectedLoanId);
            $this->reset(['selectedLoanId', 'selectedBookTitle']);
            Flux::modal('confirm-return-modal')->close();
        }
    }

    public function confirmExtend($loanId)
    {
        $loan = Loan::findOrFail($loanId);
        $this->selectedLoanId = $loan->id;
        $this->selectedBookTitle = $loan->book_title;
        Flux::modal('confirm-extend-modal')->show();
    }

    public function processExtend()
    {
        if ($this->selectedLoanId) {
            $this->extend($this->selectedLoanId);
            $this->reset(['selectedLoanId', 'selectedBookTitle']);
            Flux::modal('confirm-extend-modal')->close();
        }
    }

    public function confirmTagihDenda($loanId, $fineAmount)
    {
        $loan = Loan::findOrFail($loanId);
        $this->selectedLoanId = $loan->id;
        $this->selectedBookTitle = $loan->book_title;
        $this->selectedFineAmount = $fineAmount;
        Flux::modal('confirm-tagih-modal')->show();
    }

    public function processTagihDenda()
    {
        if ($this->selectedLoanId) {
            $this->tagihDenda($this->selectedLoanId);
            $this->reset(['selectedLoanId', 'selectedBookTitle', 'selectedFineAmount']);
            Flux::modal('confirm-tagih-modal')->close();
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
        $loan = Loan::findOrFail($loanId);
        
        // Reset state awal
        $this->book_price = '';
        $this->isManualPrice = true;

        // Cek harga buku ke API SLiMS berdasarkan barcode (book_code)
        try {
            $apiUrl = 'http://192.168.4.253:8089/bulian/cek_buku.php?q=' . urlencode($loan->book_code);
            $response = Http::timeout(5)->get($apiUrl);
            
            if ($response->successful()) {
                $resData = $response->json();
                $books = $resData['data'] ?? [];
                
                if (is_array($books) && count($books) > 0) {
                    // Cari array buku yang memiliki barcode (item) yang persis sama
                    $book = collect($books)->first(function($b) use ($loan) {
                        return in_array($loan->book_code, $b['items'] ?? []);
                    });

                    // Jika buku ditemukan dan harganya LEBIH DARI 0
                    if ($book && (int) ($book['price'] ?? 0) > 0) {
                        $this->book_price    = (int) $book['price'];
                        $this->isManualPrice = false; // Matikan input manual
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('[Loans] Error cek harga saat lapor hilang: ' . $e->getMessage());
        }

        Flux::modal('lost-book')->show();
    }

    public function reportLost()
    {
        $this->validate([
            'book_price' => 'required|numeric|min:100',
        ]);

        $loan = Loan::findOrFail($this->lostLoanId);
        
        $loan->update(['status' => 'hilang']);

        $book = Book::updateOrCreate(
            ['book_code' => $loan->book_code],
            ['status' => 'hilang', 'title' => $loan->book_title]
        );

        Fine::create([
            'loan_id'        => $loan->id,
            'member_id'      => $loan->member_id,
            'total_fines'    => (int) $this->book_price,
            'fine_type'      => 'hilang',
            'book_price'     => (int) $this->book_price,
            'payment_status' => 'belum_bayar',
        ]);

        Flux::modal('lost-book')->close();
        $this->toast('Buku dilaporkan hilang.', 'success');
        $this->reset('book_price');
    }

    public function render()
    {
        $allLoans = Loan::with('member')->where('status', 'dipinjam')->latest()->get();

        $apiMembers = collect();
        try {
            $response = Http::withToken(config('services.ibs_api.token'))
                            ->timeout(15)
                            ->get(config('services.ibs_api.url') . 'master-simpanans', ['per_page' => 5000, 'limit' => 5000]);

            if ($response->successful()) {
                $dataArray = $response->json('data.data') ?? [];
                if (empty($dataArray) && !empty($response->json('data'))) {
                    $dataArray = $response->json('data'); 
                    if (isset($dataArray['data'])) $dataArray = $dataArray['data'];
                }
                $apiMembers = collect($dataArray)->keyBy(fn($item) => (int) ($item['rfid_code'] ?? 0));
            }
        } catch (\Exception $e) {
            Log::warning('[Loans] Gagal meload nama API untuk tabel: ' . $e->getMessage());
        }

        foreach ($allLoans as $loan) {
            $rfid    = $loan->member->rfid_code ?? null;
            $apiData = $apiMembers->get((int) $rfid);

            if ($apiData) {
                $nasabah  = $apiData['nasabah'] ?? [];
                $isSantri = strtolower($nasabah['jenis_nasabah']['nama_komponen'] ?? '') === 'santri';
                $nama     = $isSantri
                    ? ($nasabah['detail_santri']['nama_lengkap'] ?? $nasabah['nama'])
                    : ($nasabah['detail_pegawai']['nama_pegawai'] ?? $nasabah['nama']);
                $loan->api_nama  = $nama;
                $loan->api_kelas = $isSantri ? ($nasabah['detail_santri']['kelas'] ?? '-') : '-';
            } else {
                $loan->api_nama  = 'RFID: ' . ($rfid ?? 'Tidak Dikenal');
                $loan->api_kelas = '-';
            }
        }

        $keyword = trim($this->searchLoan);
        if ($keyword !== '') {
            $allLoans = $allLoans->filter(function ($loan) use ($keyword) {
                return str_contains(strtolower($loan->api_nama ?? ''), strtolower($keyword))
                    || str_contains(strtolower($loan->book_title ?? ''), strtolower($keyword))
                    || str_contains(strtolower($loan->book_code ?? ''), strtolower($keyword));
            })->values();
        }

        $totalLoans     = $allLoans->count();
        $totalLoanPages = max(1, (int) ceil($totalLoans / $this->perPage));
        if ($this->loanPage > $totalLoanPages) {
            $this->loanPage = $totalLoanPages;
        }
        $activeLoans = $allLoans->forPage($this->loanPage, $this->perPage);

        return view('livewire.loans.index', [
            'activeLoans'    => $activeLoans,
            'allLoansCount'  => $totalLoans,
            'totalLoanPages' => $totalLoanPages,
            'books'          => $this->availableBooks,
        ])->title('Data Peminjaman');
    }

    private function loadApiBooks()
    {
        try {
            $limit = 10;
            $offset = ($this->currentPage - 1) * $limit;

            $url = 'http://192.168.4.253:8089/bulian/cek_buku.php'
                 . '?limit=' . $limit
                 . '&offset=' . $offset
                 . '&q=' . urlencode($this->search);

            $response = Http::timeout(5)->get($url);

            if ($response->successful()) {
                // 💡 PERUBAHAN: Karena API sekarang JSON terstruktur, kita ambil "data" dan "total"
                $result = $response->json();

                if (is_array($result) && isset($result['data'])) {
                    $this->availableBooks = $result['data'];
                    
                    // Hitung secara presisi berapa total halamannya (15 buku / 10 limit = 2 halaman)
                    $totalCount = $result['total'] ?? 0;
                    $this->totalBooksPages = max(1, (int) ceil($totalCount / $limit));
                } else {
                    $this->availableBooks  = [];
                    $this->totalBooksPages = 1;
                }
            }
        } catch (\Exception $e) {
            $this->availableBooks  = [];
            $this->totalBooksPages = 1;
        }
    }

    /**
     * Buka Modal Detail Buku dan Cek Status Eksemplar
     */
    public function openBookDetail($biblio_id)
    {
        $book = collect($this->availableBooks)->firstWhere('biblio_id', $biblio_id);
        
        if ($book) {
            $itemCodes = $book['items'] ?? [];
            
            // Tarik status lokal dari tabel books
            $localBooks = Book::whereIn('book_code', $itemCodes)->get()->keyBy('book_code');
            
            $detailedItems = [];
            foreach ($itemCodes as $code) {
                // Jika belum tercatat di DB lokal (Book), maka statusnya masih di perpustakaan (tersedia)
                $localStatus = $localBooks->has($code) ? $localBooks->get($code)->status : 'tersedia';
                
                $detailedItems[] = [
                    'code'   => $code,
                    'status' => $localStatus
                ];
            }
            
            $book['detailed_items'] = $detailedItems;
            $this->selectedBookDetail = $book;
            
            Flux::modal('book-detail')->show();
        }
    }

    /**
     * Jika admin klik "Pinjam" langsung dari dalam modal
     */
    public function selectItemToBorrow($itemCode)
    {
        if ($this->selectedBookDetail) {
            $this->book_code  = $itemCode;
            $this->book_title = $this->selectedBookDetail['title'];
            $this->book_id    = $this->selectedBookDetail['biblio_id'];
            $this->step       = 2; // Pindah ke step konfirmasi peminjaman
            
            Flux::modal('book-detail')->close();
            $this->toast('Buku siap dipinjam.', 'success');
        }
    }
}