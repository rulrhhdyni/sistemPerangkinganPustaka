<div class="py-6 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">

        {{-- Header & Pencarian --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-base-content">Cetak Surat Bebas Pustaka</h1>
                <p class="text-sm text-base-content/60 mt-1">Daftar siswa/anggota dan status kelayakan administrasi perpustakaan.</p>
            </div>

            <div class="w-full sm:w-80 relative">
                <input type="search" wire:model.live.debounce.400ms="search" 
                    class="input input-bordered w-full pl-10 focus:ring-blue-500 focus:border-blue-500 rounded-xl bg-base-100 text-base-content" 
                    placeholder="Cari Nama atau NIS/RFID...">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none opacity-50">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-base-content" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" /></svg>
                </div>
            </div>
        </div>

        {{-- Tabel Data --}}
        <div class="card bg-base-100 shadow-sm border border-base-300 rounded-2xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table table-zebra w-full">
                    <thead class="bg-base-200 text-base-content/80">
                        <tr>
                            <th class="w-12 text-center border-b border-base-300">#</th>
                            <th class="border-b border-base-300">Identitas Siswa / Member</th>
                            <th class="border-b border-base-300">Kelas</th>
                            <th class="border-b border-base-300">Status Kelayakan</th>
                            <th class="text-center w-40 border-b border-base-300">Cetak Dokumen</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($members as $member)
                            <tr class="hover:bg-base-200/50 transition-colors">
                                <td class="text-center text-base-content/80">{{ $members->firstItem() + $loop->index }}</td>
                                
                                <td>
                                    <div class="font-bold text-base-content">{{ $member->api_nama }}</div>
                                    <div class="text-xs text-base-content/50 font-mono mt-0.5">NIS/ID: {{ $member->api_identitas }}</div>
                                </td>
                                
                                <td>
                                    <div class="text-sm font-medium text-base-content/80">{{ $member->api_kelas }}</div>
                                </td>

                                <td>
                                    {{-- INDIKATOR KELAYAKAN (Warna Adaptif Menggunakan Success/Error bawaan Tema) --}}
                                    @if($member->active_loans == 0 && ($member->unpaid_fines == 0 || is_null($member->unpaid_fines)))
                                        <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-success/10 text-success border border-success/20 font-bold text-xs shadow-sm">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                                            Bebas Pustaka
                                        </div>
                                    @else
                                        <div class="flex flex-col gap-1 w-max">
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-error/10 text-error border border-error/20 font-bold text-xs">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" /></svg>
                                                Terkendala Tanggungan
                                            </span>
                                            <div class="text-[11px] text-error font-medium bg-error/5 px-2 py-1 rounded border border-error/10">
                                                @if($member->active_loans > 0)
                                                    • {{ $member->active_loans }} Buku sedang dipinjam<br>
                                                @endif
                                                @if($member->unpaid_fines > 0)
                                                    • Denda: Rp {{ number_format($member->unpaid_fines, 0, ',', '.') }}
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                </td>

                                <td class="text-center">
                                    {{-- TOMBOL DOWNLOAD PDF --}}
                                    @if($member->active_loans == 0 && ($member->unpaid_fines == 0 || is_null($member->unpaid_fines)))
                                        <button wire:click="downloadPdf({{ $member->id }})" 
                                            class="btn btn-sm bg-blue-600 hover:bg-blue-700 text-white border-none shadow-sm flex items-center gap-2 mx-auto rounded-lg">
                                            
                                            {{-- Animasi Loading saat memproses PDF --}}
                                            <span wire:loading wire:target="downloadPdf({{ $member->id }})" class="loading loading-spinner loading-xs"></span>
                                            
                                            <span wire:loading.remove wire:target="downloadPdf({{ $member->id }})">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                            </span>
                                            Cetak PDF
                                        </button>
                                    @else
                                        <button disabled class="btn btn-sm btn-disabled opacity-50 mx-auto rounded-lg cursor-not-allowed">
                                            Akses Ditutup
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-16">
                                    <div class="flex flex-col items-center gap-2 text-base-content/50">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                                        <p class="text-sm font-medium">Tidak ada data anggota yang ditemukan.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="p-4 border-t border-base-300">
                {{ $members->links() }}
            </div>
        </div>
    </div>
</div>