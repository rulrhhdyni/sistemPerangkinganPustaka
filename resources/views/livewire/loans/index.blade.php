<div class="py-6 px-4 sm:px-6 lg:px-8" 
     x-data="{ 
        initFocus() {
            // Fungsi untuk memaksa kursor fokus ke kotak input yang ada
            this.$nextTick(() => {
                let inputBuku = document.getElementById('book-field');
                let inputRfid = document.getElementById('rfid-field');
                if (inputRfid) { inputRfid.focus(); }
                else if (inputBuku) { inputBuku.focus(); }
            });
        }
     }"
     x-init="initFocus(); Livewire.hook('morph.updated', () => { initFocus(); })">
     
    <div class="max-w-7xl mx-auto">
        <h1 class="text-2xl font-semibold mb-6">Sirkulasi Perpustakaan</h1>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Form Peminjaman --}}
            <div class="lg:col-span-1 card bg-base-100 shadow border border-base-300 p-5 h-fit">
                <h2 class="text-base font-semibold mb-4">
                    {{ $step == 1 ? 'Scan Barcode Buku' : ($step == 2 ? 'Scan Kartu RFID' : 'Preview Peminjaman') }}
                </h2>

                @if($step == 1)
                    {{-- Di sini kursor akan otomatis mengedip --}}
                    <flux:input id="book-field" wire:model.live.debounce.500ms="book_code" label="Kode Buku" />
                @elseif($step == 2)
                    <div class="p-3 bg-base-200 rounded text-sm mb-4">Buku: <strong>{{ $book_title }}</strong></div>
                    {{-- Begitu pindah ke sini, kursor OTOMATIS pindah ke sini tanpa diklik --}}
                    <flux:input id="rfid-field" wire:model.live.debounce.500ms="rfid_code" label="Tap Kartu RFID" />
                @else
                    <div class="space-y-4 text-sm mb-4">
                        <div class="p-4 bg-base-200 rounded border border-base-300">
                            <p class="font-bold text-xs uppercase mb-2">Informasi Buku</p>
                            <p>{{ $book_title }}</p>
                        </div>
                        
                        <div class="p-4 border border-primary/20 rounded bg-primary/5">
                            <p class="font-bold text-xs uppercase mb-2">Data Peminjam</p>
                            <div class="flex items-center gap-4">
                                @if(!empty($member_data->api_foto))
                                    <img src="{{ $member_data->api_foto }}" class="w-16 h-16 rounded-full object-cover border">
                                @endif
                                
                                <div class="flex-1">
                                    <p class="font-semibold text-base">{{ $member_name }}</p>
                                    <p class="text-xs text-base-content/70">Kelas: {{ $member_data->api_kelas ?? '-' }}</p>
                                    <p class="text-xs text-base-content/70">Alamat: {{ $member_data->api_alamat ?? '-' }}</p>
                                    <p class="text-xs text-base-content/70">Tipe: {{ $member_data->type ?? '-' }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="flex gap-2">
                            <flux:button wire:click="confirmLoan" variant="primary" class="flex-1">ACC Peminjaman</flux:button>
                            <flux:button wire:click="resetFlow" variant="danger" class="flex-1">Cancel</flux:button>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Tabel Pinjaman Aktif --}}
            <div class="lg:col-span-2 card bg-base-100 shadow border border-base-300">
                <div class="grid grid-cols-3 divide-x divide-base-300 border-b border-base-300">
                    <div class="p-4 text-center">
                        <p class="text-xs text-base-content/60">Total Dipinjam</p>
                        <p class="text-2xl font-semibold">{{ $activeLoans->count() }}</p>
                    </div>
                    <div class="p-4 text-center">
                        <p class="text-xs text-base-content/60">Terlambat</p>
                        <p class="text-2xl font-semibold text-error">{{ $activeLoans->filter(fn($l) => \Carbon\Carbon::parse($l->due_date)->startOfDay()->isPast())->count() }}</p>
                    </div>
                    <div class="p-4 text-center">
                        <p class="text-xs text-base-content/60">Tepat Waktu</p>
                        <p class="text-2xl font-semibold text-success">{{ $activeLoans->filter(fn($l) => !\Carbon\Carbon::parse($l->due_date)->startOfDay()->isPast())->count() }}</p>
                    </div>
                </div>

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
                                @endphp
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        <div class="font-medium">{{ $loan->api_nama ?? '-' }}</div>
                                        <div class="text-xs text-base-content/50">
                                            {{ $loan->member->type ?? '-' }} {{ (!empty($loan->api_kelas) && $loan->api_kelas !== '-') ? '| ' . $loan->api_kelas : '' }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="font-medium">{{ $loan->book_title }}</div>
                                        <div class="text-xs text-base-content/50">{{ $loan->book_code }}</div>
                                    </td>
                                    <td>
                                        <div class="{{ $isLate ? 'text-error font-semibold' : '' }}">{{ $dueDate->format('d M Y') }}</div>
                                    </td>
                                    <td>
                                        @if($isLate)
                                            <span class="badge badge-error badge-sm">Terlambat</span>
                                            <button wire:click="tagihDenda({{ $loan->id }})" wire:confirm="Data ini akan dimasukkan ke tagihan Denda dan status buku dikembalikan. Lanjutkan?" class="mt-1 flex items-center gap-1 text-[11px] font-semibold bg-error/10 text-error hover:bg-error hover:text-white border border-error/50 px-2 py-1 rounded transition-colors cursor-pointer w-max">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                                                Tagih: Rp {{ number_format($lateDays * 1000, 0, ',', '.') }}
                                            </button>
                                        @else
                                            <span class="badge badge-success badge-sm">Tepat Waktu</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($loan->extension_count >= 1)
                                            <span class="badge badge-ghost badge-sm text-xs">Diperpanjang</span>
                                        @else
                                            <button wire:click="extend({{ $loan->id }})" wire:confirm="Perpanjang 3 hari?" class="btn btn-xs btn-info">+3 Hari</button>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="flex gap-1 justify-center">
                                            <button wire:click="returnBook({{ $loan->id }})" wire:confirm="Kembalikan buku ini?" class="btn btn-xs btn-success">Kembalikan</button>
                                            <button wire:click="openLostModal({{ $loan->id }})" class="btn btn-xs btn-error">Hilang</button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-12 opacity-50">Tidak ada pinjaman aktif</td>
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
                <flux:text class="mt-1">Masukkan harga buku sebagai denda pengganti.</flux:text>
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