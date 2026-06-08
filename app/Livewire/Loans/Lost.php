<?php

namespace App\Livewire\Loans;

use Livewire\Component;
use App\Models\Loan;
use App\Models\Book;
use App\Traits\WithToast;
use Livewire\WithPagination;
use Flux\Flux;

class Lost extends Component
{
    use WithToast, WithPagination;

    public $selectedLoanId = null;
    public $selectedBookTitle = '';

    public function confirmMarkAsAvailable($loanId)
    {
        $loan = Loan::findOrFail($loanId);
        $this->selectedLoanId = $loan->id;
        $this->selectedBookTitle = $loan->book_title;
        Flux::modal('confirm-available-modal')->show();
    }

    public function processMarkAsAvailable()
    {
        if ($this->selectedLoanId) {
            $this->markAsAvailable($this->selectedLoanId);
            $this->reset(['selectedLoanId', 'selectedBookTitle']);
            Flux::modal('confirm-available-modal')->close();
        }
    }

    /**
     * Fungsi untuk mengubah status buku kembali menjadi tersedia
     * saat buku yang hilang sudah diganti dengan buku fisik yang baru.
     */
    public function markAsAvailable($loanId)
    {
        $loan = Loan::findOrFail($loanId);

        // 1. Update status buku di database lokal kembali menjadi tersedia
        Book::where('book_code', $loan->book_code)->update(['status' => 'tersedia']);

        // 2. Gunakan 'kembali' karena 'diganti' tidak dikenali oleh ENUM database.
        // Ini akan menyelesaikan transaksi pinjaman dan menghilangkannya dari halaman ini.
        $loan->update(['status' => 'kembali']);

        $this->toast('Status buku berhasil dikembalikan menjadi Tersedia.', 'success');
    }
   public function render()
    {
        // Gunakan paginate() alih-alih get()
        $lostLoans = Loan::with('fines')
            ->where('status', 'hilang')
            ->latest()
            ->paginate(10); // Ubah get() menjadi paginate(10)

        return view('livewire.loans.lost', [
            'lostLoans' => $lostLoans
        ])->title('Laporan Buku Hilang');
    }
}