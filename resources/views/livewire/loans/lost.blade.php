<div class="py-6 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-6">
            <div>
                <h1 class="text-2xl font-semibold">Laporan Buku Hilang</h1>
                <p class="text-sm text-base-content/60 mt-1">Daftar riwayat buku yang dilaporkan hilang</p>
            </div>
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <div class="card bg-base-100 border border-base-300 shadow-sm p-4">
                <p class="text-xs text-base-content/60 mb-1">Total Buku Hilang</p>
                <p class="text-2xl font-semibold text-error">{{ $lostLoans->count() }}</p>
            </div>
            <div class="card bg-base-100 border border-base-300 shadow-sm p-4">
                <p class="text-xs text-base-content/60 mb-1">Denda Belum Dibayar</p>
                <p class="text-2xl font-semibold text-warning">
                    Rp {{ number_format($lostLoans->sum(fn($l) => $l->fines->where('fine_type','hilang')->where('payment_status','belum_bayar')->sum('total_fines')), 0, ',', '.') }}
                </p>
            </div>
            <div class="card bg-base-100 border border-base-300 shadow-sm p-4">
                <p class="text-xs text-base-content/60 mb-1">Denda Sudah Lunas</p>
                <p class="text-2xl font-semibold text-success">
                    Rp {{ number_format($lostLoans->sum(fn($l) => $l->fines->where('fine_type','hilang')->where('payment_status','lunas')->sum('total_fines')), 0, ',', '.') }}
                </p>
            </div>
        </div>

        {{-- Tabel --}}
        <div class="card bg-base-100 shadow border border-base-300">
            <div class="overflow-x-auto">
                <table class="table table-zebra text-sm">
                    <thead class="bg-base-200">
                        <tr>
                            <th>#</th>
                            <th>Peminjam</th>
                            <th>Buku</th>
                            <th>Tgl Pinjam</th>
                            <th>Harga Buku (Denda)</th>
                            <th>Status Denda</th>
                            <th class="text-center">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($lostLoans as $loan)
                            @php
                                $fine = $loan->fines->where('fine_type', 'hilang')->first();
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-full bg-error/10 flex items-center justify-center text-error font-semibold text-xs">
                                            {{ strtoupper(substr($loan->api_nama ?? 'X', 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="font-medium">{{ $loan->api_nama ?? '-' }}</div>
                                            <div class="text-xs text-base-content/50">RFID: {{ $loan->member->rfid_code ?? '-' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="font-medium">{{ $loan->book_title }}</div>
                                    <div class="text-xs text-base-content/50">{{ $loan->book_code }}</div>
                                </td>
                                <td class="text-xs">
                                    {{ \Carbon\Carbon::parse($loan->borrow_date)->format('d M Y') }}
                                </td>
                                <td class="font-semibold text-error">
                                    Rp {{ $fine ? number_format($fine->book_price, 0, ',', '.') : '-' }}
                                </td>
                                <td>
                                    @if($fine && $fine->payment_status === 'lunas')
                                        <span class="badge badge-success badge-sm">✓ Lunas</span>
                                    @else
                                        <span class="badge badge-warning badge-sm">Belum Bayar</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($fine && $fine->payment_status === 'lunas')
                                        <span class="badge badge-ghost badge-sm text-xs font-semibold">✓ Selesai</span>
                                    @elseif($fine)
                                        {{-- Mengarahkan admin ke halaman menu denda --}}
                                        <a href="/fines" class="btn btn-xs btn-outline btn-warning">Bayar di Menu Denda</a>
                                    @else
                                        <span class="opacity-40">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-16 opacity-50">
                                    <div class="flex flex-col items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                                        <p>Tidak ada buku yang dilaporkan hilang</p>
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