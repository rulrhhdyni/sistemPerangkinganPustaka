<x-layouts.app :title="__('Data Denda')">
    <div class="py-6 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">

            {{-- Header --}}
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-semibold text-base-content">Data Denda</h1>
                    <p class="text-sm text-base-content/60 mt-1">Daftar denda yang belum dibayar</p>
                </div>
            </div>

            {{-- Alert Messages --}}
            @if(session('success'))
                <div class="alert alert-success mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-error mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    {{ session('error') }}
                </div>
            @endif

            {{-- Stats --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                <div class="card bg-base-100 border border-base-300 shadow-sm p-4">
                    <p class="text-xs text-base-content/60 mb-1">Total Denda Belum Bayar</p>
                    <p class="text-2xl font-semibold text-error">{{ $fines->count() }}</p>
                    <p class="text-xs text-base-content/50 mt-1">transaksi</p>
                </div>
                <div class="card bg-base-100 border border-base-300 shadow-sm p-4">
                    <p class="text-xs text-base-content/60 mb-1">Total Nominal Denda</p>
                    <p class="text-2xl font-semibold text-warning">
                        Rp {{ number_format($fines->sum('total_fines'), 0, ',', '.') }}
                    </p>
                    <p class="text-xs text-base-content/50 mt-1">belum dibayar</p>
                </div>
                <div class="card bg-base-100 border border-base-300 shadow-sm p-4">
                    <p class="text-xs text-base-content/60 mb-1">Santri Terdenda</p>
                    <p class="text-2xl font-semibold text-info">
                        {{ $fines->unique('member_id')->count() }}
                    </p>
                    <p class="text-xs text-base-content/50 mt-1">orang</p>
                </div>
            </div>

            {{-- Table --}}
            <div class="card bg-base-100 shadow border border-base-300">
                <div class="overflow-x-auto">
                    <table class="table table-zebra text-sm">
                        <thead class="bg-base-200">
                            <tr>
                                <th>#</th>
                                <th>Santri</th>
                                <th>Buku Dipinjam</th>
                                <th>Tgl Batas Kembali</th>
                                <th>Keterlambatan</th>
                                <th>Denda</th>
                                <th>Status</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($fines as $fine)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        <div class="flex items-center gap-2">
                                            <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center text-primary font-semibold text-xs">
                                                {{ strtoupper(substr($fine->member->name ?? 'X', 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="font-medium">{{ $fine->member->name ?? '-' }}</div>
                                                <div class="text-xs text-base-content/50">{{ $fine->member->slims_member_id ?? '-' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="font-medium">{{ $fine->loan->book_title ?? '-' }}</div>
                                        <div class="text-xs text-base-content/50">{{ $fine->loan->book_code ?? '-' }}</div>
                                    </td>
                                    <td class="text-xs">
                                        {{ $fine->loan->due_date ? \Carbon\Carbon::parse($fine->loan->due_date)->format('d M Y') : '-' }}
                                    </td>
                                    <td>
                                        @if($fine->loan && $fine->loan->due_date)
                                            @php
                                                $returnDate = $fine->loan->return_date ?? now();
                                                $lateDays = \Carbon\Carbon::parse($fine->loan->due_date)->diffInDays(\Carbon\Carbon::parse($returnDate));
                                            @endphp
                                            <span class="badge badge-error badge-sm">{{ $lateDays }} hari</span>
                                        @else
                                            <span class="text-base-content/30">-</span>
                                        @endif
                                    </td>
                                    <td class="font-semibold text-error">
                                        Rp {{ number_format($fine->total_fines, 0, ',', '.') }}
                                    </td>
                                    <td>
                                        <span class="badge badge-warning badge-sm">Belum Bayar</span>
                                    </td>
                                    <td class="text-center">
                                       <form action="{{ route('fine.pay', $fine->id) }}" method="POST"
                                           onsubmit="return confirm('Tandai denda ini sebagai LUNAS?')"
                                           data-turbo="false">
                                           @csrf
                                           <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                           <button type="submit" class="btn btn-xs btn-success">
                                               Bayar Lunas
                                           </button>
                                       </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-16">
                                        <div class="flex flex-col items-center gap-2 opacity-50">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                                            <p class="text-sm">Tidak ada denda yang belum dibayar</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($fines->count() > 0)
                    <div class="p-4 border-t border-base-300 flex justify-end">
                        <div class="text-sm text-base-content/60">
                            Total Denda: <span class="font-semibold text-error text-base">
                                Rp {{ number_format($fines->sum('total_fines'), 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-layouts.app>