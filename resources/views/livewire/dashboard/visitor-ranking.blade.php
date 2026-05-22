<div class="card bg-base-100 shadow border border-base-300">

    {{-- HEADER --}}
    <div class="p-4 flex justify-between items-center border-b border-base-200" wire:poll.3000ms>
        <h2 class="font-semibold text-sm">
            🏆 Top 3 Pengunjung Terbanyak
        </h2>

        <select wire:model.live="periode" class="select select-sm select-bordered">
            <option value="this_month">Bulan Ini</option>
            <option value="this_year">Tahun Ini</option>
        </select>
    </div>

    {{-- CONTENT --}}
    <div class="p-4 grid grid-cols-1 sm:grid-cols-3 gap-4">

        @forelse ($this->topThree as $i => $row)
            <div
                class="card bg-base-100 border
                {{ $i === 0 ? 'border-success bg-success/5' : '' }}
                {{ $i === 1 ? 'border-warning bg-warning/5' : '' }}
                {{ $i === 2 ? 'border-info bg-info/5' : '' }}
            ">
                <div class="card-body p-4 gap-2">

                    {{-- RANK --}}
                    <div class="flex justify-between items-center">
                        <span class="font-bold text-lg">
                            @if ($i === 0)
                                🏆 Rank #1
                            @elseif ($i === 1)
                                🥈 Rank #2
                            @else
                                🥉 Rank #3
                            @endif
                        </span>

                        <span
                            class="badge badge-outline
                            {{ $row->type === 'member' ? 'badge-success' : 'badge-warning' }}">
                            {{ ucfirst($row->type) }}
                        </span>
                    </div>

                    {{-- NAME --}}
                    <div>
                        <p class="font-semibold text-md truncate">
                            {{ $row->guest_name ?? 'Guest Tidak Dikenal' }}
                        </p>
                        <p class="text-xs opacity-60">
                            {{ $row->guest_identity ?? '-' }}
                        </p>
                    </div>

                    {{-- TOTAL --}}
                    <div class="flex justify-between items-end mt-2">
                        <div>
                            <p class="text-xs opacity-60">
                                Total Kunjungan
                            </p>
                            <p
                                class="text-2xl font-bold
                                {{ $i === 0 ? 'text-warning' : '' }}
                                {{ $i === 2 ? 'text-accent' : '' }}
                            ">
                                {{ $row->total }}
                            </p>
                        </div>

                        <div class="text-3xl opacity-30">
                            {{ $row->type === 'member' ? '👤' : '👥' }}
                        </div>
                    </div>

                </div>
            </div>
        @empty
            <div class="col-span-3 text-center py-8 opacity-60">
                Tidak ada data
            </div>
        @endforelse

    </div>
</div>
