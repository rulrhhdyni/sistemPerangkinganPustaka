<div class="stats stats-vertical lg:stats-horizontal shadow border border-base-300 w-full dark:bg-slate-900" wire:poll.3000ms>

    {{-- TOTAL MEMBER --}}
    <div class="stat ">
        <div class="stat-title text-lg">Total Member</div>
        <div class="stat-value text-primary text-7xl">
            {{ number_format($totalMembers) }}
        </div>
        <div class="stat-desc text-xs opacity-70">
            Terdaftar di sistem
        </div>
    </div>

    {{-- HARI INI --}}
    <div class="stat">
        <div class="stat-title text-lg">Pengunjung Hari Ini</div>
        <div class="stat-value text-7xl">
            {{ $todayTotal }}
        </div>
        <div class="stat-desc text-xs flex gap-2">
            <div class="badge badge-success badge-outline flex items-center gap-1">
                <span>Member :</span>
                <strong>{{ $todayMember }}</strong>
            </div>
            <div class="badge badge-warning badge-outline flex items-center gap-1">
                <span>Guest :</span>
                <strong>{{ $todayGuest }}</strong>
            </div>
        </div>
    </div>

    {{-- BULAN INI --}}
    <div class="stat">
        <div class="stat-title text-lg">Pengunjung Bulan Ini</div>
        <div class="stat-value text-7xl">
            {{ $monthTotal }}
        </div>
        <div class="stat-desc text-xs flex gap-2">
            <div class="badge badge-success badge-outline flex items-center gap-1">
                <span>Member :</span>
                <strong>{{ $monthMember }}</strong>
            </div>
            <div class="badge badge-warning badge-outline flex items-center gap-1">
                <span>Guest :</span>
                <strong>{{ $monthGuest }}</strong>
            </div>
        </div>
    </div>

    {{-- TAHUN INI --}}
    <div class="stat">
        <div class="stat-title text-lg">Pengunjung Tahun Ini</div>
        <div class="stat-value text-7xl">
            {{ $yearTotal }}
        </div>
        <div class="stat-desc text-xs flex gap-2">
            <div class="badge badge-success badge-outline flex items-center gap-1">
                <span>Member :</span>
                <strong>{{ $yearMember }}</strong>
            </div>
            <div class="badge badge-warning badge-outline flex items-center gap-1">
                <span>Guest :</span>
                <strong>{{ $yearGuest }}</strong>
            </div>
        </div>
    </div>


</div>
