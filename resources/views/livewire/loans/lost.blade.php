<div class="py-6 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">

        {{-- Header --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-base-content">Inventaris Buku Hilang</h1>
                <p class="text-sm text-base-content/60 mt-1">Daftar koleksi buku yang memerlukan penggantian</p>
            </div>
        </div>

        {{-- Stats / Dashboard --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            {{-- Card Total Buku Hilang (Premium Blue Aesthetic) --}}
            <div class="card border border-blue-500/20 shadow-sm p-6 flex flex-col justify-center rounded-xl relative overflow-hidden" style="background: rgba(59, 130, 246, 0.08); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px);">
                <div class="relative z-10">
                    <p class="text-xs font-bold text-blue-500 uppercase tracking-wider mb-1">Total Buku Hilang</p>
                    <div class="flex items-end gap-2 mt-1">
                        <p class="text-4xl font-bold text-blue-600 dark:text-blue-400">
                            {{ $lostLoans->total() }}
                        </p>
                        <p class="text-sm font-medium text-blue-600/70 dark:text-blue-400/70 mb-1">Buku</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabel Informasi Buku --}}
        <div class="card bg-base-100 shadow border border-base-300">
            <div class="overflow-x-auto">
                <table class="table table-zebra text-sm">
                    <thead class="bg-base-200">
                        <tr>
                            <th class="w-12">#</th>
                            <th>Judul Buku</th>
                            <th>Kode Buku / Barcode</th>
                            <th>Tanggal Dilaporkan</th>
                            <th>Nominal Penggantian</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($lostLoans as $loan)
                            @php
                                $fine = $loan->fines->where('fine_type', 'hilang')->first();
                            @endphp
                            <tr>
                                <td>{{ $lostLoans->firstItem() + $loop->index }}</td>
                                <td>
                                    <div class="font-semibold text-base-content">{{ $loan->book_title }}</div>
                                </td>
                                <td>
                                    <span class="font-mono text-xs bg-base-200 px-2 py-1 rounded border border-base-300">
                                        {{ $loan->book_code }}
                                    </span>
                                </td>
                                <td class="text-xs text-base-content/70">
                                    {{ \Carbon\Carbon::parse($loan->borrow_date)->format('d M Y') }}
                                </td>
                                <td class="font-bold text-error">
                                    Rp {{ $fine ? number_format($fine->book_price, 0, ',', '.') : '-' }}
                                </td>
                                <td class="text-center">
                                    <button 
                                        wire:click="confirmMarkAsAvailable({{ $loan->id }})" 
                                        class="btn btn-xs bg-blue-600 hover:bg-blue-700 text-white border-none shadow-sm">
                                        Tandai Tersedia
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-16 opacity-50">
                                    <div class="flex flex-col items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                                        <p class="text-sm">Tidak ada daftar buku yang hilang saat ini.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{-- Tambahkan Pagination Controls Di Sini --}}
            <div class="p-4 border-t border-base-300">
                {{ $lostLoans->links() }}
            </div>
        </div>

    {{-- MODAL KONFIRMASI TANDAI TERSEDIA --}}
    <flux:modal name="confirm-available-modal" class="md:w-[450px] p-6 text-center">
        <div class="flex justify-center mb-4">
            <div class="bg-blue-100 text-blue-600 rounded-full w-16 h-16 flex items-center justify-center text-3xl shadow-inner">
                📖
            </div>
        </div>
        <h3 class="font-black text-xl text-base-content mb-2">Tandai Buku Tersedia</h3>
        <p class="text-sm text-base-content/70 mb-6">
            Apakah Anda yakin buku <strong class="text-base-content">"{{ $selectedBookTitle }}"</strong> sudah diganti dan siap dikembalikan ke status <span class="badge badge-success badge-sm text-white">Tersedia</span>?
        </p>
        <div class="flex justify-end gap-2 border-t border-base-300 pt-4">
            <flux:modal.close>
                <button type="button" class="btn btn-ghost btn-sm text-base-content">
                    Batal
                </button>
            </flux:modal.close>
            <button wire:click="processMarkAsAvailable" type="button" class="btn btn-primary btn-sm text-white shadow-sm">
                Ya, Tandai Tersedia
            </button>
        </div>
    </flux:modal>

    </div>
</div>