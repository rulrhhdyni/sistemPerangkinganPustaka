<div class="py-6 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">

        {{-- Header --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h1 class="text-2xl font-semibold text-base-content">
                Data Kunjungan
            </h1>
        </div>

        {{-- Card --}}
        <div class="mt-6 card bg-base-100 shadow border border-base-300">

            {{-- Filter --}}
            <div class="p-4 flex flex-col lg:flex-row gap-3 lg:items-center lg:justify-between">

                {{-- LEFT --}}
                <div class="flex flex-wrap items-center gap-2">

                    {{-- Per Page --}}
                    <select wire:model.live="perPage" class="select select-sm select-bordered w-24">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>

                    {{-- Filter Type --}}
                    <flux:dropdown>
                        <flux:button icon:trailing="chevron-down" size="sm" variant="outline">
                            {{ $type ? 'Type: ' . ucfirst($type) : 'Filter Type' }}
                        </flux:button>

                        <flux:menu>
                            <flux:menu.radio.group wire:model.change="type">
                                @foreach ($typeList as $key => $label)
                                    <flux:menu.radio value="{{ $key }}">
                                        {{ $label }}
                                    </flux:menu.radio>
                                @endforeach
                            </flux:menu.radio.group>
                        </flux:menu>
                    </flux:dropdown>

                    {{-- Rekap --}}
                    <flux:dropdown>
                        <flux:button icon:trailing="chevron-down" size="sm" variant="outline">
                            {{ $periode ? 'Rekap: ' . $periodeList[$periode] : 'Rekap by' }}
                        </flux:button>

                        <flux:menu>
                            <flux:menu.radio.group wire:model.change="periode">
                                @foreach ($periodeList as $key => $label)
                                    <flux:menu.radio value="{{ $key }}">
                                        {{ $label }}
                                    </flux:menu.radio>
                                @endforeach
                            </flux:menu.radio.group>

                            <flux:menu.separator />

                            <flux:menu.item wire:click="clearRekap" class="text-error">
                                Clear Rekap
                            </flux:menu.item>
                        </flux:menu>
                    </flux:dropdown>

                    {{-- Badge --}}
                    @if ($periode)
                        <span class="badge badge-info badge-outline text-xs">
                            Rekap Aktif
                        </span>
                    @endif
                </div>

                {{-- RIGHT --}}
                <div class="w-full lg:w-72">
                    <input type="search" wire:model.live.debounce.400ms="search"
                        placeholder="Search name / identity..." class="input input-sm input-bordered w-full" />
                </div>

            </div>

            {{-- Table --}}
            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead class="bg-base-200">
                        <tr>
                            <th>#</th>
                            <th>Member ID / Identity</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Type</th>

                            @if (!$periode)
                                <th>Visit Time</th>
                                <th>Visit Date</th>
                            @else
                                <th class="text-center">Jumlah</th>
                            @endif
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($visits as $visit)
                            <tr>
                                {{-- PERBAIKAN PAGINATION NUMBERING --}}
                                <td>{{ $visits->firstItem() + $loop->index }}</td>

                                <td class="font-medium">
                                    {{-- PERBAIKAN IDENTITAS --}}
                                    {{ $visit->api_identitas ?? $visit->guest_identity ?? '-' }}
                                </td>

                                <td class="font-medium">
                                    {{-- PERBAIKAN NAMA --}}
                                    {{ $visit->api_nama ?? $visit->guest_name ?? '-' }}
                                </td>

                                <td>
                                    {{ $visit->email ?? '-' }}
                                </td>

                                <td>
                                    {{ $visit->guest_phone ?? '-' }}
                                </td>

                                <td>
                                    <span
                                        class="badge badge-outline
                                        {{ ($visit->type ?? $visit->visit_type) === 'member' ? 'badge-success' : 'badge-warning' }}">
                                        {{ ucfirst($visit->type ?? ($visit->visit_type ?? 'guest')) }}
                                    </span>
                                </td>

                                {{-- MODE LIST --}}
                                @if (!$periode)
                                    <td>{{ $visit->visit_time }}</td>
                                    <td>{{ $visit->visit_date }}</td>
                                @else
                                    {{-- MODE REKAP --}}
                                    <td class="text-center font-bold">
                                        {{ $visit->jml }}
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $periode ? 7 : 9 }}" class="text-center py-10 opacity-60">
                                    No data found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>

            {{-- Footer --}}
            <div class="p-4 flex flex-col sm:flex-row gap-2 items-center justify-between text-sm">
                <div>
                    Showing
                    <strong>{{ $visits->firstItem() ?? 0 }}</strong>
                    to
                    <strong>{{ $visits->lastItem() ?? 0 }}</strong>
                    of
                    <strong>{{ $visits->total() }}</strong>
                </div>

                @if ($visits instanceof \Illuminate\Pagination\LengthAwarePaginator && $visits->hasPages())
                    <div class="join grid grid-cols-2">
                        @if ($visits && $visits->onFirstPage())
                            <button class="join-item btn btn-outline" disabled>Previous page</button>
                        @else
                            <button class="join-item btn btn-outline" wire:click="previousPage">Previous page</button>
                        @endif
                        @if ($visits && $visits->hasMorePages())
                            <button class="join-item btn btn-outline" wire:click="nextPage">Next</button>
                        @else
                            <button class="join-item btn btn-outline" disabled>Next</button>
                        @endif
                    </div>
                @endif
            </div>

        </div>
        <flux:modal name="range-rekap-modal" class="md:w-96">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">Rekap Kunjungan</flux:heading>
                </div>
                <flux:input label="Start Date" type="date" wire:model.defer="rangeDate.start" />
                <flux:input label="End Date" type="date" wire:model.defer="rangeDate.end" />
                <div class="flex">
                    <flux:spacer />
                    <flux:button type="submit" wire:click="setRangeDate" variant="primary">Lihat Data</flux:button>
                </div>
            </div>
        </flux:modal>
    </div>
</div>