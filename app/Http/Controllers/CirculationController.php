<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Loan;
use App\Models\Member;
use Carbon\Carbon;
use App\Models\Fine;

class CirculationController extends Controller
{

    public function index()
    {
        // Mengambil pinjaman yang statusnya masih 'dipinjam'
        $activeLoans = Loan::with('member')->where('status', 'dipinjam')->get();
        
        return view('loans.index', compact('activeLoans'));
    }
    // 1. PROSES PEMINJAMAN
    public function storeLoan(Request $request)
    {
        // Cari santri berdasarkan RFID
        $member = Member::where('rfid_number', $request->rfid_number)->first();

        if (!$member) {
            return back()->with('error', 'Kartu RFID Santri tidak terdaftar!');
        }

        // Cek apakah santri punya denda yang belum lunas
        // (Opsional: Santri tidak boleh pinjam jika denda > 0)

        Loan::create([
            'member_id' => $member->id,
            'book_title' => $request->book_title, // Nanti diganti SLiMS
            'book_code' => $request->book_code,
            'borrow_date' => Carbon::now(),
            'due_date' => Carbon::now()->addDays(7), // Pinjam 7 hari
            'status' => 'dipinjam'
        ]);

        return back()->with('success', 'Buku berhasil dipinjam oleh ' . $member->name);
    }

    // 2. PROSES PERPANJANGAN (Limit 1x, Tambah 3 Hari)
    public function extend(Loan $loan)
    {
        if ($loan->extension_count >= 1) {
            return back()->with('error', 'Buku ini sudah pernah diperpanjang sebelumnya.');
        }

        $loan->update([
            'due_date' => Carbon::parse($loan->due_date)->addDays(3),
            'extension_count' => $loan->extension_count + 1
        ]);

        return back()->with('success', 'Waktu pinjam berhasil diperpanjang 3 hari.');
    }

    // 3. PROSES PENGEMBALIAN & HITUNG DENDA
    public function returnBook(Request $request, Loan $loan)
    {
        $today = Carbon::now();
        $dueDate = Carbon::parse($loan->due_date);
        $fineAmount = 0;

        // Hitung denda jika terlambat (Misal: Rp 1.000 per hari)
    if ($today->startOfDay()->gt($dueDate->startOfDay())) {
       $lateDays = $dueDate->startOfDay()->diffInDays($today->startOfDay());
       $fineAmount = $lateDays * 1000;
    }

        $loan->update([
            'return_date' => $today,
            'status' => 'kembali'
        ]);

        if ($fineAmount > 0) {
            // Catat ke tabel fines
            \App\Models\Fine::create([
                'loan_id' => $loan->id,
                'member_id' => $loan->member_id,
                'total_fines' => $fineAmount,
                'payment_status' => 'belum_bayar'
            ]);
            return back()->with('warning', 'Buku dikembalikan. Santri terkena denda: Rp ' . number_format($fineAmount));
        }

        return back()->with('success', 'Buku berhasil dikembalikan tepat waktu.');
    }

// Proses Bayar Denda
public function payFine(Fine $fine)
{
    $fine->update(['payment_status' => 'lunas']);
    return redirect()->back()->with('success', 'Denda berhasil dibayar!');
}

// Tampilan Surat Bebas Pustaka
public function clearance()
{
    $members = Member::with([
        'loans',
        'fines'
    ])->get();
    return view('loans.clearance', compact('members'));
}

// Fungsi Cek Kelayakan Bebas Pustaka
public function checkClearance($id)
{
    $member = Member::findOrFail($id);
    
    // Cek pinjaman aktif
    $activeLoans = Loan::where('member_id', $id)->where('status', 'dipinjam')->count();
    // Cek denda belum lunas
    $unpaidFines = Fine::where('member_id', $id)->where('payment_status', 'belum_bayar')->count();

    $isClear = ($activeLoans == 0 && $unpaidFines == 0);

    return view('loans.clearance_print', compact('member', 'isClear', 'activeLoans', 'unpaidFines'));
}
    
}