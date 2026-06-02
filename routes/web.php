<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;

use App\Livewire\Member\Index as MemberIndex;
use App\Http\Controllers\CirculationController;
use App\Livewire\Visitor\Index as VisitorIndex;
use App\Livewire\Users\Index as UsersIndex;
use App\Livewire\Loans\Index as LoanIndex;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/force-logout', function() {
    \Illuminate\Support\Facades\Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/login');
});

Route::middleware('web')->post('/rfid-absensi', function (\Illuminate\Http\Request $request) {
    $rfid = $request->input('rfid');
    
    // Cek apakah admin
    $user = \App\Models\User::where('rfid_id', $rfid)->first();
    if ($user) {
        \Illuminate\Support\Facades\Auth::login($user, true);
        $request->session()->regenerate();
        return response()->json(['type' => 'admin', 'redirect' => '/dashboard']);
    }
    
        // Cek apakah member/santri
    $rfid = trim($request->rfid);
    $member = \App\Models\Member::where('rfid_code', $rfid)->first();

    if ($member) {
        // 1. Ambil data nama dan kontak dari database lokal yang sudah tersinkronisasi
        $memberName = $member->display_name;
        $memberPhone = $member->display_phone;

        // 2. Simpan kunjungan dengan data lokal
        \App\Models\Visit::create([
            'member_id'      => $member->id,
            'guest_name'     => $memberName,
            'guest_phone'    => $memberPhone,
            'guest_identity' => $member->id_server, // Menggunakan id_server sebagai identitas
            'visit_type'     => 'member',
            'visit_date'     => now()->toDateString(),
            'visit_time'     => now()->toTimeString(),
        ]);

        \App\Events\VisitorCreated::dispatch();
        return response()->json(['type' => 'member', 'name' => $memberName]);
    }
        
        return response()->json(['type' => 'unknown']);
    })->name('rfid.absensi');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::get('members', MemberIndex::class)->name('members.index');
    Route::get('visitors', VisitorIndex::class)->name('visitors.index');
    Route::get('users', UsersIndex::class)->name('users.index');

    Route::get('loans', LoanIndex::class)->name('loan.index');
    Route::get('/lost-books', \App\Livewire\Loans\Lost::class)->name('lost.index');
    
    // Denda
    Route::get('/fines', \App\Livewire\Loans\Fines::class)->name('fine.index');
    Route::post('/fines/{fine}/pay', [CirculationController::class, 'payFine'])->name('fine.pay');
    // Bebas Pustaka
    Route::get('/clearance', [CirculationController::class, 'clearance'])->name('clearance.index');
    Route::get('/clearance/{id}', [CirculationController::class, 'checkClearance'])->name('clearance.check');

    // --- FITUR SIRKULASI (Peminjaman, Pengembalian, Perpanjangan) ---
    // Route::prefix('loans')->group(function () {
    //     Route::post('/store', [CirculationController::class, 'storeLoan'])->name('loan.store');
    //     Route::post('/extend/{loan}', [CirculationController::class, 'extend'])->name('loan.extend');
    //     Route::post('/return/{loan}', [CirculationController::class, 'returnBook'])->name('loan.return');
    // });

    // --- FITUR SURAT BEBAS PUSTAKA ---
    Route::get('/clearance/{member_id}', [CirculationController::class, 'generateBebasPustaka'])->name('loan.clearance');

    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('profile.edit');
    Volt::route('settings/password', 'settings.password')->name('user-password.edit');
    Volt::route('settings/appearance', 'settings.appearance')->name('appearance.edit');

    Volt::route('settings/two-factor', 'settings.two-factor')
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');
});