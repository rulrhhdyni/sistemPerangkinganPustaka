<div class="py-6 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">

        {{-- Header --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h1 class="text-2xl font-semibold text-base-content">
                Data Members
            </h1>
            <div class="flex gap-2">
                <button wire:click="syncApiData" class="btn btn-sm btn-info text-white" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="syncApiData">Sync Data API</span>
                    <span wire:loading wire:target="syncApiData" class="loading loading-spinner loading-sm"></span>
                </button>
            </div>
        </div>

        {{-- Card --}}
        <div class="mt-6 card bg-base-100 shadow border border-base-300">

            {{-- Filter --}}
            <div class="p-4 flex flex-col sm:flex-row gap-3 justify-between">
                <select wire:model.live="perPage" class="select select-sm select-bordered w-28">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
                <select wire:model.live="filterType" class="select select-sm select-bordered w-32">
                    <option value="">Semua Tipe</option>
                    @foreach ($types as $tp)
                        <option value="{{ $tp }}">{{ $tp }}</option>
                    @endforeach
                </select>
                <input type="search" wire:model.live.debounce.400ms="search" placeholder="Cari RFID..."
                    class="input input-sm input-bordered w-full sm:w-96" />
            </div>

            {{-- Table --}}
            <div class="overflow-x-auto">
                <table class="table table-zebra w-full">
                    <thead class="bg-base-200">
                        <tr>
                            <th>#</th>
                            <th>Foto</th>
                            <th>Nama</th>
                            <th>Kelas</th>
                            <th>Tipe</th>
                            <th>Kontak</th>
                            <th>RFID</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($members as $member)
                            <tr>
                                <td>{{ $loop->iteration + ($members->firstItem() - 1) }}</td>
                                <td>
                                    <div class="avatar">
                                        <div class="w-10 rounded-full border">
                                            <img src="{{ $member->api_foto }}" 
                                                 onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($member->api_nama) }}'" />
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $member->api_nama }}</td>
                                <td>{{ $member->api_kelas }}</td>
                                <td><span class="badge badge-sm badge-info text-white">{{ $member->type }}</span></td>
                                <td>{{ $member->api_phone }}</td>
                                <td>{{ $member->rfid_code ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center py-4">Tidak ada data nasabah / RFID ditemukan</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Footer --}}
            <div class="p-4 flex flex-col sm:flex-row gap-2 items-center justify-between text-sm">
                <div>
                    Showing <strong>{{ $members->firstItem() ?? 0 }}</strong> to <strong>{{ $members->lastItem() ?? 0 }}</strong> of <strong>{{ $members->total() }}</strong>
                </div>

                @if ($members instanceof \Illuminate\Pagination\LengthAwarePaginator && $members->hasPages())
                    <div class="join grid grid-cols-2">
                        <button class="join-item btn btn-outline btn-sm" wire:click="previousPage" @if ($members->onFirstPage()) disabled @endif>Previous</button>
                        <button class="join-item btn btn-outline btn-sm" wire:click="nextPage" @if (!$members->hasMorePages()) disabled @endif>Next</button>
                    </div>
                @endif
            </div>

        </div>
    </div>
</div>