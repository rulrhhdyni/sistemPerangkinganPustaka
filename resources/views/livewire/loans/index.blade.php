<div class="py-6 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <h1 class="text-2xl font-semibold mb-6">Sirkulasi Perpustakaan</h1>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Form Peminjaman --}}
            <div class="lg:col-span-1 card bg-base-100 shadow border border-base-300 p-5 h-fit">
                <h2 class="text-base font-semibold mb-4">Form Peminjaman</h2>
                <form wire:submit.prevent="processLoan" class="space-y-4">
                    <flux:input wire:model="rfid_number" label="Tap Kartu RFID" autofocus />
                    <flux:input wire:model="book_code" label="Barcode Buku" />
                    <flux:input wire:model="book_title" label="Judul Buku" />
                    <flux:button type="submit" variant="primary" class="w-full">
                        Simpan Peminjaman
                    </flux:button>
                </form>
            </div>

            {{-- Tabel Pinjaman Aktif --}}
            <div class="lg:col-span-2 card bg-base-100 shadow border border-base-300">

                {{-- Stats --}}
                <div class="grid grid-cols-3 divide-x divide-base-300 border-b border-base-300">
                    <div class="p-4 text-center">
                        <p class="text-xs text-base-content/60">Total Dipinjam</p>
                        <p class="text-2xl font-semibold">{{ $activeLoans->count() }}</p>
                    </div>
                    <div class="p-4 text-center">
                        <p class="text-xs text-base-content/60">Terlambat</p>
                        <p class="text-2xl font-semibold text-error">
                            {{ $activeLoans->filter(fn($l) => \Carbon\Carbon::parse($l->due_date)->startOfDay()->isPast())->count() }}
                        </p>
                    </div>
                    <div class="p-4 text-center">
                        <p class="text-xs text-base-content/60">Tepat Waktu</p>
                        <p class="text-2xl font-semibold text-success">
                            {{ $activeLoans->filter(fn($l) => !\Carbon\Carbon::parse($l->due_date)->startOfDay()->isPast())->count() }}
                        </p>
                    </div>
                </div>

                {{-- Tabel --}}
                <div class="overflow-x-auto">
                    <table class="table table-zebra text-sm">
                        <thead class="bg-base-200">
                            <tr>
                                <th>#</th>
                                <th>Peminjam</th>
                                <th>Buku</th>
                                <th>Tgl Kembali</th>
                                <th>Status</th>
                                <th>Perpanjang</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($activeLoans as $loan)
                                @php
                                    $dueDate = \Carbon\Carbon::parse($loan->due_date)->startOfDay();
                                    $today = \Carbon\Carbon::today();
                                    $isLate = $today->gt($dueDate);
                                    $lateDays = $isLate ? $dueDate->diffInDays($today) : 0;
                                    $daysLeft = !$isLate ? $today->diffInDays($dueDate) : 0;
                                @endphp
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        <div class="font-medium">{{ $loan->member->name ?? '-' }}</div>
                                        <div class="text-xs text-base-content/50">{{ $loan->member->slims_member_id ?? '' }}</div>
                                    </td>
                                    <td>
                                        <div class="font-medium">{{ $loan->book_title }}</div>
                                        <div class="text-xs text-base-content/50">{{ $loan->book_code }}</div>
                                    </td>
                                    <td>
                                        <div class="{{ $isLate ? 'text-error font-semibold' : '' }}">
                                            {{ $dueDate->format('d M Y') }}
                                        </div>
                                        @if($isLate)
                                            <div class="text-xs text-error">{{ $lateDays }} hari terlambat</div>
                                        @else
                                            <div class="text-xs text-success">{{ $daysLeft }} hari lagi</div>
                                        @endif
                                    </td>
                                    <td>
                                        @if($isLate)
                                            <span class="badge badge-error badge-sm">Terlambat</span>
                                            <div class="text-xs text-error mt-1">
                                                Denda: Rp {{ number_format($lateDays * 1000, 0, ',', '.') }}
                                            </div>
                                        @else
                                            <span class="badge badge-success badge-sm">Tepat Waktu</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($loan->extension_count >= 1)
                                            <span class="badge badge-ghost badge-sm text-xs">Sudah diperpanjang</span>
                                        @else
                                            <button wire:click="extend({{ $loan->id }})"
                                                wire:confirm="Perpanjang pinjaman 3 hari?"
                                                class="btn btn-xs btn-info">
                                                +3 Hari
                                            </button>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="flex gap-1 justify-center">
                                            <button wire:click="returnBook({{ $loan->id }})"
                                                wire:confirm="Kembalikan buku ini?"
                                                class="btn btn-xs btn-success">
                                                Kembalikan
                                            </button>
                                            <button wire:click="openLostModal({{ $loan->id }})"
                                                class="btn btn-xs btn-error">
                                                Hilang
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-12 opacity-50">
                                        <div class="flex flex-col items-center gap-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                                            <p class="text-sm">Tidak ada pinjaman aktif</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    {{-- Modal Buku Hilang --}}
    <flux:modal name="lost-book" class="min-w-[24rem]">
        <div class="space-y-4">
            <div>
                <flux:heading size="lg">🚨 Laporkan Buku Hilang</flux:heading>
                <flux:text class="mt-1">
                    Masukkan harga buku sebagai denda pengganti.
                </flux:text>
            </div>
            <div>
                <flux:input
                    wire:model="book_price"
                    label="Harga Buku (Rp)"
                    type="number"
                    placeholder="Contoh: 50000"
                    min="100"
                    step="1000"
                />
                <p class="text-xs text-base-content/50 mt-1">
                   Masukkan angka tanpa titik/koma. Contoh: 50000 (untuk Rp 50.000)
                </p>
                @error('book_price')
                   <p class="text-error text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex gap-2 justify-end">
                <flux:modal.close>
                    <flux:button variant="ghost">Batal</flux:button>
                </flux:modal.close>
                <flux:button variant="danger" wire:click="reportLost">
                    Laporkan Hilang
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>