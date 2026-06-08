<div class="py-6 px-4 sm:px-6 lg:px-8"
     x-data="{
        initFocus() {
            this.$nextTick(() => {
                let inputBuku = document.getElementById('book-field');
                let inputRfid = document.getElementById('rfid-field');
                if (inputRfid) { inputRfid.focus(); }
                else if (inputBuku) { inputBuku.focus(); }
            });
        }
     }"
     x-init="initFocus(); Livewire.hook('morph.updated', () => { initFocus(); })">

    <div class="max-w-7xl mx-auto space-y-6">

        <h1 class="text-2xl font-semibold">Sirkulasi Perpustakaan</h1>

        {{-- Baris 1: Form + Tabel Pinjaman Aktif --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">

            {{-- Form Peminjaman --}}
            <div class="lg:col-span-1 card bg-base-100 shadow border border-base-300 p-5">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-base-content/60 mb-4">
                    {{ $step == 1 ? '📖 Scan Barcode Buku' : ($step == 2 ? '💳 Scan Kartu RFID' : '✅ Preview Peminjaman') }}
                </h2>

                @if($step == 1)
                    <flux:input id="book-field" wire:model.live.debounce.500ms="book_code" label="Kode Buku" placeholder="Scan atau ketik kode buku..." />
                    
                @elseif($step == 2)
                    <div class="p-3 bg-base-200 rounded-lg text-sm mb-4 border border-base-300">
                        <span class="text-xs text-base-content/50 uppercase font-semibold">Buku</span>
                        <p class="font-semibold mt-0.5">{{ $book_title }}</p>
                    </div>
                    <flux:input id="rfid-field" wire:model.live.debounce.500ms="rfid_code" label="Tap Kartu RFID" placeholder="Dekatkan kartu RFID..." />
                @else
                    <div class="space-y-3 text-sm">
                        <div class="p-3 bg-base-200 rounded-lg border border-base-300">
                            <p class="text-xs font-semibold uppercase text-base-content/50 mb-1">Informasi Buku</p>
                            <p class="font-medium">{{ $book_title }}</p>
                        </div>

                        <div class="p-3 rounded-lg border border-primary/30 bg-primary/5">
                            <p class="text-xs font-semibold uppercase text-base-content/50 mb-2">Data Peminjam</p>
                            <div class="flex items-center gap-3">
                                @if(!empty($member_data->api_foto))
                                    <img src="{{ $member_data->api_foto }}" class="w-14 h-14 rounded-full object-cover border-2 border-primary/20 shrink-0">
                                @endif
                                <div class="space-y-0.5">
                                    <p class="font-semibold">{{ $member_name }}</p>
                                    <p class="text-xs text-base-content/60">Kelas: {{ $member_data->api_kelas ?? '-' }}</p>
                                    <p class="text-xs text-base-content/60">Alamat: {{ $member_data->api_alamat ?? '-' }}</p>
                                    <p class="text-xs text-base-content/60">Tipe: {{ $member_data->type ?? '-' }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="flex gap-2 pt-1">
                            <flux:button wire:click="confirmLoan" variant="primary" class="flex-1">ACC Peminjaman</flux:button>
                            <flux:button wire:click="resetFlow" variant="danger" class="flex-1">Cancel</flux:button>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Tabel Pinjaman Aktif --}}
            <div class="lg:col-span-2 card bg-base-100 shadow border border-base-300 overflow-hidden">

                {{-- Statistik --}}
                <div class="grid grid-cols-3 divide-x divide-base-300 border-b border-base-300">
                    <div class="p-4 text-center">
                        <p class="text-xs text-base-content/50 uppercase font-medium mb-1">Total Dipinjam</p>
                        <p class="text-2xl font-bold">{{ $allLoansCount }}</p>
                    </div>
                    <div class="p-4 text-center">
                        <p class="text-xs text-base-content/50 uppercase font-medium mb-1">Terlambat</p>
                        <p class="text-2xl font-bold text-error">
                            {{ $activeLoans->filter(fn($l) => \Carbon\Carbon::parse($l->due_date)->startOfDay()->isPast())->count() }}
                        </p>
                    </div>
                    <div class="p-4 text-center">
                        <p class="text-xs text-base-content/50 uppercase font-medium mb-1">Tepat Waktu</p>
                        <p class="text-2xl font-bold text-success">
                            {{ $activeLoans->filter(fn($l) => !\Carbon\Carbon::parse($l->due_date)->startOfDay()->isPast())->count() }}
                        </p>
                    </div>
                </div>

                {{-- Search Pinjaman --}}
                <div class="p-3 border-b border-base-300">
                    <flux:input wire:model.live.debounce.400ms="searchLoan"
                                placeholder="🔍 Cari nama peminjam, judul, atau kode buku..."
                                class="w-full" />
                </div>

                {{-- Tabel --}}
                <div class="overflow-x-auto">
                    <table class="table table-zebra text-sm w-full">
                        <thead class="bg-base-200 text-xs uppercase tracking-wide">
                            <tr>
                                <th class="w-8">#</th>
                                <th>Peminjam</th>
                                <th>Buku</th>
                                <th>Tgl Pinjam</th>  {{-- ✅ Kolom Baru --}}
                                <th>Tgl Kembali</th>
                                <th>Status</th>
                                <th>Perpanjang</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($activeLoans as $loan)
                                @php
                                    $dueDate  = \Carbon\Carbon::parse($loan->due_date)->startOfDay();
                                    $loanDate = \Carbon\Carbon::parse($loan->created_at)->startOfDay(); // ✅ Kolom Baru
                                    $today    = \Carbon\Carbon::today();
                                    $isLate   = $today->gt($dueDate);
                                    $lateDays = $isLate ? $dueDate->diffInDays($today) : 0;
                                @endphp
                                <tr>
                                    <td class="text-base-content/50">{{ $loop->iteration }}</td>

                                    <td>
                                        <div class="font-medium leading-tight">{{ $loan->api_nama ?? '-' }}</div>
                                        <div class="text-xs text-base-content/50 mt-0.5">
                                            {{ $loan->member->type ?? '-' }}
                                            {{ (!empty($loan->api_kelas) && $loan->api_kelas !== '-') ? '· ' . $loan->api_kelas : '' }}
                                        </div>
                                    </td>

                                    <td>
                                        <div class="font-medium leading-tight">{{ $loan->book_title }}</div>
                                        <div class="text-xs font-mono text-primary mt-0.5">{{ $loan->book_code }}</div>
                                    </td>

                                    {{-- ✅ Kolom Baru: Tanggal Pinjam --}}
                                    <td class="text-xs text-base-content/70 whitespace-nowrap">
                                        {{ $loanDate->format('d M Y') }}
                                    </td>

                                    <td class="whitespace-nowrap">
                                        <span class="{{ $isLate ? 'text-error font-semibold' : '' }} text-sm">
                                            {{ $dueDate->format('d M Y') }}
                                        </span>
                                    </td>

                                    <td>
                                        @if($isLate)
                                            <span class="badge badge-error badge-sm text-white">Terlambat</span>
                                            <button
                                                wire:click="confirmTagihDenda({{ $loan->id }}, {{ $lateDays * 1000 }})"
                                                class="mt-1.5 flex items-center gap-1 text-[11px] font-semibold bg-error/10 text-error hover:bg-error hover:text-white border border-error/40 px-2 py-1 rounded-md transition-colors cursor-pointer w-max">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                                                    <polyline points="22 4 12 14.01 9 11.01"/>
                                                </svg>
                                                Tagih: Rp {{ number_format($lateDays * 1000, 0, ',', '.') }}
                                            </button>
                                        @else
                                            <span class="badge badge-success badge-sm text-white">Tepat Waktu</span>
                                        @endif
                                    </td>

                                    <td>
                                        @if($loan->extension_count >= 1)
                                            <span class="badge badge-ghost badge-sm">Diperpanjang</span>
                                        @else
                                            <button
                                                wire:click="confirmExtend({{ $loan->id }})"
                                                class="btn btn-xs btn-info">
                                                +3 Hari
                                            </button>
                                        @endif
                                    </td>

                                    <td class="text-center">
                                        <div class="flex gap-1 justify-center">
                                            <button wire:click="confirmReturn({{ $loan->id }})" class="btn btn-xs btn-success">Kembalikan</button>
                                            <button wire:click="openLostModal({{ $loan->id }})" class="btn btn-xs btn-error">Hilang</button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-16 opacity-40">
                                        <p class="text-3xl mb-2">📚</p>
                                        <p>Tidak ada pinjaman aktif</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination Tabel Pinjaman --}}
                <div class="p-3 border-t border-base-300 flex items-center justify-between">
                    <p class="text-xs text-base-content/50">
                        Halaman {{ $loanPage }} dari {{ $totalLoanPages }}
                        &nbsp;·&nbsp; Total: {{ $allLoansCount }} pinjaman aktif
                    </p>
                    <div class="flex gap-2">
                        <button wire:click="loanPrevPage"
                                @class(['btn btn-sm btn-outline', 'btn-disabled opacity-40' => $loanPage <= 1])>
                            ← Prev
                        </button>
                        <button wire:click="loanNextPage"
                                @class(['btn btn-sm btn-outline', 'btn-disabled opacity-40' => $loanPage >= $totalLoanPages])>
                            Next →
                        </button>
                    </div>
                </div>
            </div>
        </div>
<div class="card bg-base-100 shadow border border-base-300 overflow-hidden">
            <div class="p-4 border-b border-base-300 flex flex-col md:flex-row justify-between items-center gap-3">
                <h2 class="font-semibold text-lg">Daftar Koleksi Buku</h2>
                <flux:input wire:model.live.debounce.500ms="search" placeholder="Cari judul atau pengarang..." class="w-full md:w-72" />
            </div>

            <div class="overflow-x-auto">
                <table class="table table-zebra text-sm w-full">
                    <thead class="bg-base-200 text-xs uppercase tracking-wide">
                        <tr>
                            <th>Cover</th>
                            <th>Judul Buku</th>
                            <th>Author</th>
                            <th>ISBN</th>
                            <th class="text-center">Aksi</th>
                            <th class="text-right">Harga (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($books as $book)
                            @if(is_array($book))
                                <tr>
                                    <td>
                                        @if(!empty($book['image_full_path']))
                                            <img src="{{ $book['image_full_path'] }}" class="w-12 h-16 object-cover rounded shadow">
                                        @else
                                            <div class="w-12 h-16 bg-base-200 rounded shadow flex items-center justify-center text-xs text-base-content/50">No Cover</div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="font-bold text-base-content">{{ $book['title'] ?? 'Tanpa Judul' }}</div>
                                        <div class="text-xs text-base-content/60 mt-0.5">Tahun: {{ $book['publish_year'] ?? '-' }}</div>
                                    </td>
                                    <td>{{ $book['author'] ?? '-' }}</td>
                                    <td>{{ $book['isbn_issn'] ?? '-' }}</td>
                                    
                                    {{-- TOMBOL LIHAT DETAIL --}}
                                    <td class="text-center">
                                        <button wire:click="openBookDetail('{{ $book['biblio_id'] }}')" class="btn btn-sm btn-info btn-outline font-bold">
                                            Lihat Detail
                                        </button>
                                    </td>
                                    
                                    <td class="text-right">{{ number_format($book['price'] ?? 0, 0, ',', '.') }}</td>
                                </tr>
                            @endif
                        @empty
                            <tr><td colspan="6" class="text-center py-8 text-base-content/50">Data buku tidak ditemukan</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-base-300 flex items-center justify-between">
                <p class="text-xs text-base-content/50">
                    Halaman {{ $currentPage }} dari {{ $totalBooksPages }}
                    &nbsp;·&nbsp; Menampilkan 10 judul per halaman
                </p>
                <div class="flex gap-2">
                    <button wire:click="prevPage"
                            @class(['btn btn-sm btn-outline', 'btn-disabled opacity-40' => $currentPage <= 1])>
                        ← Prev
                    </button>
                    <button wire:click="nextPage"
                            @class(['btn btn-sm btn-outline', 'btn-disabled opacity-40' => $currentPage >= $totalBooksPages])>
                        Next →
                    </button>
                </div>
            </div>
        </div>

    </div>

    {{-- MODAL LAPOR BUKU HILANG --}}
    <flux:modal name="lost-book" class="md:w-[450px] p-6">
        <h3 class="font-black text-xl text-base-content mb-2">🚨 Lapor Buku Hilang</h3>

        {{-- KONDISI 1: Harga Sudah Terdaftar di SLiMS --}}
        @if(!$isManualPrice)
            <div class="bg-info/10 border border-info/30 p-4 rounded-xl mb-4 text-sm">
                <p class="text-base-content/80 font-medium">Harga buku ini otomatis terdeteksi dari sistem SLiMS sebesar:</p>
                <p class="text-2xl font-black mt-1 text-base-content">Rp {{ number_format((int)$book_price, 0, ',', '.') }}</p>
                <p class="mt-2 text-xs text-base-content/60">Harga ini akan otomatis dikalkulasikan sebagai tanggungan denda ganti rugi.</p>
            </div>

        {{-- KONDISI 2: Harga Rp 0 / Kosong di SLiMS --}}
        @else
            <div class="bg-warning/10 border border-warning/30 p-4 rounded-xl mb-4 text-sm">
                <p class="font-bold text-base-content">⚠️ Perhatian!</p>
                <p class="text-base-content/70 mt-0.5">Harga buku ini belum diatur (Rp 0) di SLiMS. Silakan masukkan nominal denda ganti rugi secara manual.</p>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-bold text-base-content mb-2">Nominal Ganti Rugi (Rp)</label>
                <input type="number"
                       wire:model="book_price"
                       class="input input-bordered w-full"
                       placeholder="Contoh: 150000">
                @error('book_price')
                    <span class="text-error text-xs mt-1 block">{{ $message }}</span>
                @enderror
            </div>
        @endif

        <div class="flex justify-end gap-2 mt-6 border-t border-base-300 pt-4">
            {{-- Tombol Batal: pakai flux:modal.close agar benar-benar menutup modal --}}
            <flux:modal.close>
                <button type="button" class="btn btn-ghost btn-sm text-base-content">
                    Batal
                </button>
            </flux:modal.close>

            <button wire:click="reportLost"
                    type="button"
                    class="btn btn-error btn-sm text-white shadow-sm">
                Konfirmasi Hilang
            </button>
        </div>
    </flux:modal>

 {{-- MODAL DETAIL BUKU & DAFTAR EKSEMPLAR --}}
<flux:modal name="book-detail" class="md:max-w-2xl w-full p-0 overflow-hidden rounded-2xl">
    @if($selectedBookDetail)
        <div class="flex flex-col">

            {{-- Header: Cover + Info Utama --}}
            <div class="flex gap-4 p-5 bg-base-200/50 border-b border-base-200">

                {{-- Cover kecil --}}
                <div class="flex-shrink-0">
                    @if(!empty($selectedBookDetail['image_full_path']))
                        <img src="{{ $selectedBookDetail['image_full_path'] }}" alt="Cover"
                             class="w-16 h-24 object-cover rounded-lg shadow-md bg-base-200">
                    @else
                        <div class="w-16 h-24 bg-base-300 flex flex-col items-center justify-center rounded-lg border border-base-300 text-base-content/40 shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mb-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                            <span class="text-[8px] font-bold uppercase tracking-widest">No Cover</span>
                        </div>
                    @endif
                </div>

                {{-- Info Utama di samping cover --}}
                <div class="flex-1 min-w-0 flex flex-col justify-center gap-2">
                    <h3 class="font-black text-base text-base-content leading-snug line-clamp-2">
                        {{ $selectedBookDetail['title'] }}
                    </h3>
                    <div class="grid grid-cols-2 gap-x-4 gap-y-1 text-xs text-base-content/70">
                        <p><span class="font-semibold text-base-content">Penulis:</span> {{ $selectedBookDetail['author'] ?? '-' }}</p>
                        <p><span class="font-semibold text-base-content">Tahun:</span> {{ $selectedBookDetail['publish_year'] ?? '-' }}</p>
                        <p><span class="font-semibold text-base-content">ISBN/ISSN:</span> {{ $selectedBookDetail['isbn_issn'] ?? '-' }}</p>
                        <p><span class="font-semibold text-base-content">Harga:</span> Rp {{ number_format($selectedBookDetail['price'] ?? 0, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>

            {{-- Daftar Eksemplar --}}
            <div class="p-5">
                <h4 class="font-bold text-base-content uppercase tracking-wider text-[10px] mb-3 flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-blue-500 inline-block"></span>
                    Daftar Kode Eksemplar Fisik
                </h4>

                <div class="space-y-2 max-h-56 overflow-y-auto pr-1 custom-scrollbar">
                    @forelse($selectedBookDetail['detailed_items'] ?? [] as $item)
                        <div class="flex justify-between items-center px-3 py-2.5 bg-base-100 border border-base-200 rounded-xl hover:border-blue-300 transition-colors">
                            <div class="flex items-center gap-2.5">
                                <span class="font-mono font-bold text-sm bg-base-200 px-2.5 py-0.5 rounded-lg text-base-content">
                                    {{ $item['code'] }}
                                </span>

                                @if($item['status'] == 'tersedia')
                                    <span class="badge badge-xs bg-success/10 text-success border-success/20">Tersedia</span>
                                @elseif($item['status'] == 'dipinjam')
                                    <span class="badge badge-xs bg-warning/10 text-warning border-warning/20">Dipinjam</span>
                                @else
                                    <span class="badge badge-xs bg-error/10 text-error border-error/20">Hilang</span>
                                @endif
                            </div>

                            @if($item['status'] == 'tersedia')
                                <button wire:click="selectItemToBorrow('{{ $item['code'] }}')"
                                        class="btn btn-xs bg-blue-600 hover:bg-blue-700 text-white border-none rounded-lg shadow-sm">
                                    Pilih Pinjam
                                </button>
                            @endif
                        </div>
                    @empty
                        <div class="text-center py-6 bg-base-200 rounded-xl text-base-content/50 text-sm">
                            Tidak ada data eksemplar fisik untuk judul ini.
                        </div>
                    @endforelse
                </div>
            </div>

        </div>
    @endif
</flux:modal>

    {{-- MODAL KONFIRMASI PENGEMBALIAN BUKU --}}
    <flux:modal name="confirm-return-modal" class="md:w-[450px] p-6 text-center">
        <div class="flex justify-center mb-4">
            <div class="bg-success/10 text-success rounded-full w-16 h-16 flex items-center justify-center text-3xl shadow-inner">
                📥
            </div>
        </div>
        <h3 class="font-black text-xl text-base-content mb-2">Konfirmasi Pengembalian</h3>
        <p class="text-sm text-base-content/70 mb-6">
            Apakah Anda yakin ingin mengembalikan buku <strong class="text-base-content">"{{ $selectedBookTitle }}"</strong>?
        </p>
        <div class="flex justify-end gap-2 border-t border-base-300 pt-4">
            <flux:modal.close>
                <button type="button" class="btn btn-ghost btn-sm text-base-content">
                    Batal
                </button>
            </flux:modal.close>
            <button wire:click="processReturn" type="button" class="btn btn-success btn-sm text-white shadow-sm">
                Ya, Kembalikan
            </button>
        </div>
    </flux:modal>

    {{-- MODAL KONFIRMASI PERPANJANG --}}
    <flux:modal name="confirm-extend-modal" class="md:w-[450px] p-6 text-center">
        <div class="flex justify-center mb-4">
            <div class="bg-info/10 text-info rounded-full w-16 h-16 flex items-center justify-center text-3xl shadow-inner">
                📅
            </div>
        </div>
        <h3 class="font-black text-xl text-base-content mb-2">Perpanjang Peminjaman</h3>
        <p class="text-sm text-base-content/70 mb-6">
            Apakah Anda yakin ingin memperpanjang waktu peminjaman buku <strong class="text-base-content">"{{ $selectedBookTitle }}"</strong> selama 3 hari?
        </p>
        <div class="flex justify-end gap-2 border-t border-base-300 pt-4">
            <flux:modal.close>
                <button type="button" class="btn btn-ghost btn-sm text-base-content">
                    Batal
                </button>
            </flux:modal.close>
            <button wire:click="processExtend" type="button" class="btn btn-info btn-sm text-white shadow-sm">
                Ya, Perpanjang
            </button>
        </div>
    </flux:modal>

    {{-- MODAL KONFIRMASI TAGIH DENDA --}}
    <flux:modal name="confirm-tagih-modal" class="md:w-[450px] p-6 text-center">
        <div class="flex justify-center mb-4">
            <div class="bg-error/10 text-error rounded-full w-16 h-16 flex items-center justify-center text-3xl shadow-inner">
                💸
            </div>
        </div>
        <h3 class="font-black text-xl text-base-content mb-2">Tagih Denda</h3>
        <p class="text-sm text-base-content/70 mb-6">
            Buku <strong class="text-base-content">"{{ $selectedBookTitle }}"</strong> terlambat dikembalikan.<br/>
            Data ini akan dimasukkan ke tagihan Denda sebesar <strong class="text-error">Rp {{ number_format((int)$selectedFineAmount, 0, ',', '.') }}</strong> dan status buku dikembalikan. Lanjutkan?
        </p>
        <div class="flex justify-end gap-2 border-t border-base-300 pt-4">
            <flux:modal.close>
                <button type="button" class="btn btn-ghost btn-sm text-base-content">
                    Batal
                </button>
            </flux:modal.close>
            <button wire:click="processTagihDenda" type="button" class="btn btn-error btn-sm text-white shadow-sm">
                Ya, Tagih Denda
            </button>
        </div>
    </flux:modal>

</div>