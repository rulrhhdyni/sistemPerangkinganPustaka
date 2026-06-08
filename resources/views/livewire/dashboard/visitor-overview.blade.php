<div class="card bg-base-100 shadow border border-base-300" >
    <div class="p-4 flex items-center justify-between">
        <h2 class="font-semibold text-sm">
            Current Visitors (Hari Ini) {{ session('success') }}
        </h2>

        <span class="badge badge-info badge-outline text-xs">
            {{ now()->format('d M Y') }}
        </span>
    </div>

    <div class="overflow-x-auto" wire:poll.3000ms>
        <table class="table table-sm">
            <thead class="bg-base-200">
                <tr>
                    <th>#</th>
                    <th>Nama</th>
                    <th>Identity</th>
                    <th>Type</th>
                    <th>Jam</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($this->visitors as $i => $visit)
                    <tr>
                        <td>{{ $i + 1 }}</td>

                        <td class="font-medium">
                            {{-- Panggil Nama dari API --}}
                            {{ $visit->api_nama ?? $visit->guest_name ?? '-' }}
                        </td>

                        <td class="text-xs">
                            {{-- Panggil Identitas dari API --}}
                            {{ $visit->api_identitas ?? $visit->guest_identity ?? '-' }}
                        </td>

                        <td>
                            <span
                                class="badge badge-outline
                                {{ $visit->visit_type === 'member' ? 'badge-success' : 'badge-warning' }}">
                                {{ ucfirst($visit->visit_type) }}
                            </span>
                        </td>

                        <td class="text-xs">
                            {{ $visit->visit_time }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-6 opacity-60">
                            Belum ada pengunjung hari ini
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="p-3 text-xs text-right opacity-60">
        Menampilkan {{ $this->visitors->count() }} pengunjung terbaru
    </div>
</div>