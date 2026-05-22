<x-layouts.app :title="__('Buku Hilang')">
    <div class="py-6 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-semibold">Buku Hilang</h1>
                    <p class="text-sm text-base-content/60 mt-1">Daftar buku yang dilaporkan hilang</p>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success mb-4">{{ session('success') }}</div>
            @endif

            {{-- Stats --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                <div class="card bg-base-100 border border-base-300 shadow-sm p-4">
                    <p class="text-xs text-base-content/60 mb-1">Total Buku Hilang</p>
                    <p class="text-2xl font-semibold text-error">{{ $lostLoans->count() }}</p>
                </div>
                <div class="card bg-base-100 border border-base-300 shadow-sm p-4">
                    <p class="text-xs text-base-content/60 mb-1">Denda Belum Bayar</p>
                    <p class="text-2xl font-semibold text-warning">
                        Rp {{ number_format($lostLoans->sum(fn($l) => $l->fines->where('fine_type','hilang')->where('payment_status','belum_bayar')->sum('total_fines')), 0, ',', '.') }}
                    </p>
                </div>
                <div class="card bg-base-100 border border-base-300 shadow-sm p-4">
                    <p class="text-xs text-base-content/60 mb-1">Denda Sudah Bayar</p>
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
                                <th>Santri</th>
                                <th>Buku</th>
                                <th>Tgl Pinjam</th>
                                <th>Harga Buku</th>
                                <th>Status Denda</th>
                                <th class="text-center">Aksi</th>
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
                                                {{ strtoupper(substr($loan->member->name ?? 'X', 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="font-medium">{{ $loan->member->name ?? '-' }}</div>
                                                <div class="text-xs text-base-content/50">{{ $loan->member->slims_member_id ?? '-' }}</div>
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
                                            <span style="background:#16a34a;color:white;padding:2px 8px;border-radius:9999px;font-size:11px;font-weight:500;">✓ Lunas</span>
                                        @else
                                            <span style="background:#ca8a04;color:white;padding:2px 8px;border-radius:9999px;font-size:11px;font-weight:500;">Belum Bayar</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($fine && $fine->payment_status === 'lunas')
                                            <span style="background:#16a34a;color:white;padding:2px 8px;border-radius:9999px;font-size:11px;font-weight:500;">✓ Selesai</span>
                                        @elseif($fine)
                                            <form action="{{ route('fine.pay', $fine->id) }}" method="POST"
                                                onsubmit="return confirm('Tandai denda buku hilang ini sebagai LUNAS?')">
                                                @csrf
                                                <button type="submit" style="background:#16a34a;color:white;padding:3px 10px;border-radius:6px;font-size:12px;border:none;cursor:pointer;">
                                                    Bayar Lunas
                                                </button>
                                            </form>
                                        @else
                                            <span style="opacity:0.4;font-size:12px;">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-16 opacity-50">
                                        <div class="flex flex-col items-center gap-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                                            <p>Tidak ada buku yang hilang</p>
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
</x-layouts.app>