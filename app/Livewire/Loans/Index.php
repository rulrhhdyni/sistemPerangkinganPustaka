<?php

namespace App\Livewire\Loans;

use Livewire\Component;
use App\Models\Loan;
use App\Models\Member;
use App\Models\Fine;
use App\Traits\WithToast;
use Carbon\Carbon;
use Flux\Flux;

class Index extends Component
{
    use WithToast;

    public $rfid_number = '';
    public $book_code = '';
    public $book_title = '';

    // Untuk modal buku hilang
    public $lostLoanId = null;
    public $book_price = '';

    public function processLoan()
    {
        $member = Member::where('slims_member_id', $this->rfid_number)->first();

        if (!$member) {
            $this->toast('RFID Santri tidak ditemukan!', 'error');
            $this->reset('rfid_number');
            return;
        }

        Loan::create([
            'member_id' => $member->id,
            'book_code' => $this->book_code,
            'book_title' => $this->book_title,
            'borrow_date' => now(),
            'due_date' => now()->addDays(7),
            'status' => 'dipinjam'
        ]);

        $this->toast('Berhasil: Peminjaman oleh ' . $member->name);
        $this->reset(['rfid_number', 'book_code', 'book_title']);
    }

    public function returnBook($loanId)
    {
        $loan = Loan::findOrFail($loanId);
        $today = Carbon::today();
        $dueDate = Carbon::parse($loan->due_date)->startOfDay();
        $fineAmount = 0;

        if ($today->gt($dueDate)) {
            $lateDays = (int) $today->diffInDays($dueDate);
            $fineAmount = $lateDays * 1000;
        }

        $loan->update([
            'return_date' => Carbon::now(),
            'status' => 'kembali'
        ]);

        if ($fineAmount > 0) {
            Fine::create([
                'loan_id' => $loan->id,
                'member_id' => $loan->member_id,
                'total_fines' => $fineAmount,
                'fine_type' => 'keterlambatan',
                'payment_status' => 'belum_bayar'
            ]);
            $this->toast('Buku dikembalikan. Denda: Rp ' . number_format($fineAmount, 0, ',', '.'), 'warning');
        } else {
            $this->toast('Buku berhasil dikembalikan tepat waktu!');
        }
    }

    public function extend($loanId)
    {
        $loan = Loan::findOrFail($loanId);

        if ($loan->extension_count >= 1) {
            $this->toast('Buku ini sudah pernah diperpanjang sebelumnya!', 'error');
            return;
        }

        $loan->update([
            'due_date' => Carbon::parse($loan->due_date)->addDays(3),
            'extension_count' => $loan->extension_count + 1
        ]);

        $this->toast('Waktu pinjam berhasil diperpanjang 3 hari.');
    }

    public function openLostModal($loanId)
    {
        $this->lostLoanId = $loanId;
        $this->book_price = '';
        Flux::modal('lost-book')->show();
    }

    public function reportLost()
{
    $this->validate([
        'book_price' => 'required|numeric|min:100',
    ], [
        'book_price.required' => 'Harga buku wajib diisi!',
        'book_price.numeric' => 'Harga buku harus berupa angka!',
        'book_price.min' => 'Harga buku minimal Rp 100!',
    ]);

    $loan = Loan::findOrFail($this->lostLoanId);
    $loan->update(['status' => 'hilang']);

    Fine::create([
        'loan_id' => $loan->id,
        'member_id' => $loan->member_id,
        'total_fines' => (int) $this->book_price,
        'fine_type' => 'hilang',
        'book_price' => (int) $this->book_price,
        'payment_status' => 'belum_bayar'
    ]);

    Flux::modal('lost-book')->close();
    $this->toast('Buku dilaporkan hilang. Denda: Rp ' . number_format((float)$this->book_price, 0, ',', '.'), 'warning');
    $this->reset(['lostLoanId', 'book_price']);
}
    public function render()
    {
        return view('livewire.loans.index', [
            'activeLoans' => Loan::with('member')->where('status', 'dipinjam')->latest()->get()
        ]);
    }
}