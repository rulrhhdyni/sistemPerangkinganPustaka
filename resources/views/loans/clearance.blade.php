<x-layouts.app :title="__('Surat Bebas Pustaka')">
    <div class="py-6 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">

            {{-- Header --}}
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-semibold text-base-content">Surat Bebas Pustaka</h1>
                    <p class="text-sm text-base-content/60 mt-1">Cek kelayakan bebas pustaka santri</p>
                </div>
            </div>

            {{-- Alert Messages --}}
            @if(session('success'))
                <div class="alert alert-success mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    {{ session('success') }}
                </div>
            @endif

            {{-- Stats --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                <div class="card bg-base-100 border border-base-300 shadow-sm p-4">
                    <p class="text-xs text-base-content/60 mb-1">Total Anggota</p>
                    <p class="text-2xl font-semibold">{{ $members->count() }}</p>
                    <p class="text-xs text-base-content/50 mt-1">terdaftar</p>
                </div>
                <div class="card bg-base-100 border border-base-300 shadow-sm p-4">
                    <p class="text-xs text-base-content/60 mb-1">Bebas Pustaka</p>
                    <p class="text-2xl font-semibold text-success">
                        {{ $members->filter(function($m) {
                            return $m->loans->where('status', 'dipinjam')->count() == 0
                                && $m->fines->where('payment_status', 'belum_bayar')->count() == 0;
                        })->count() }}
                    </p>
                    <p class="text-xs text-base-content/50 mt-1">santri</p>
                </div>
                <div class="card bg-base-100 border border-base-300 shadow-sm p-4">
                    <p class="text-xs text-base-content/60 mb-1">Belum Bebas</p>
                    <p class="text-2xl font-semibold text-error">
                        {{ $members->filter(function($m) {
                            return $m->loans->where('status', 'dipinjam')->count() > 0
                                || $m->fines->where('payment_status', 'belum_bayar')->count() > 0;
                        })->count() }}
                    </p>
                    <p class="text-xs text-base-content/50 mt-1">santri</p>
                </div>
            </div>

            {{-- Search --}}
            <div class="card bg-base-100 shadow border border-base-300 mb-4">
                <div class="p-4">
                    <input type="text" id="searchInput" placeholder="Cari nama atau ID santri..."
                        class="input input-sm input-bordered w-full sm:w-96"
                        onkeyup="filterTable()" />
                </div>
            </div>

            {{-- Table --}}
            <div class="card bg-base-100 shadow border border-base-300">
                <div class="overflow-x-auto">
                    <table class="table table-zebra text-sm" id="clearanceTable">
                        <thead class="bg-base-200">
                            <tr>
                                <th>#</th>
                                <th>Santri</th>
                                <th>Tipe</th>
                                <th>Pinjaman Aktif</th>
                                <th>Denda Belum Bayar</th>
                                <th>Status</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($members as $member)
                                @php
                                    $activeLoans = $member->loans->where('status', 'dipinjam')->count();
                                    $unpaidFines = $member->fines->where('payment_status', 'belum_bayar')->count();
                                    $isClear = ($activeLoans == 0 && $unpaidFines == 0);
                                @endphp
                                <tr class="member-row">
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        <div class="flex items-center gap-2">
                                            <div class="w-8 h-8 rounded-full flex items-center justify-center font-semibold text-xs
                                                {{ $isClear ? 'bg-success/10 text-success' : 'bg-error/10 text-error' }}">
                                                {{ strtoupper(substr($member->name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="font-medium member-name">{{ $member->name }}</div>
                                                <div class="text-xs text-base-content/50">{{ $member->slims_member_id }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-outline badge-sm">{{ $member->type ?? '-' }}</span>
                                    </td>
                                    <td>
                                        @if($activeLoans > 0)
                                            <span class="badge badge-error badge-sm">{{ $activeLoans }} buku</span>
                                        @else
                                            <span class="badge badge-success badge-sm">Tidak ada</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($unpaidFines > 0)
                                            <span class="badge badge-warning badge-sm">{{ $unpaidFines }} denda</span>
                                        @else
                                            <span class="badge badge-success badge-sm">Lunas</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($isClear)
                                            <span class="badge badge-success">✓ Bebas Pustaka</span>
                                        @else
                                            <span class="badge badge-error">✗ Belum Bebas</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('clearance.check', $member->id) }}"
                                            class="btn btn-xs {{ $isClear ? 'btn-success' : 'btn-warning' }}">
                                            Cetak Surat
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-16 opacity-50">
                                        Tidak ada data anggota
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <script>
        function filterTable() {
            const input = document.getElementById('searchInput').value.toLowerCase();
            const rows = document.querySelectorAll('.member-row');
            rows.forEach(row => {
                const name = row.querySelector('.member-name').textContent.toLowerCase();
                row.style.display = name.includes(input) ? '' : 'none';
            });
        }
    </script>
</x-layouts.app>