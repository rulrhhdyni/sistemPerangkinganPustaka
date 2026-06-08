<div class="py-6 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">

        {{-- Header --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-base-content">Pembayaran & Rekapitulasi Denda</h1>
                <p class="text-sm text-base-content/60 mt-1">Riwayat tagihan hilang dan keterlambatan</p>
            </div>
        </div>

       {{-- Stats / Dashboard (REVISI KOTAK PERSEGI BERJAJAR HORIZONTAL) --}}
        <div class="mb-8" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.5rem;">
            
            {{-- Card 1: Pemasukan Keterlambatan --}}
            <div class="card bg-base-100 border border-base-300 shadow-sm p-5 rounded-xl aspect-square flex flex-col justify-between">
                <div>
                    <p class="text-[11px] font-bold text-base-content/50 uppercase tracking-wider">Total Denda terlambat</p>
                    <p class="text-2xl font-bold text-success mt-2">
                        Rp {{ number_format($totalLunasTelat, 0, ',', '.') }}
                    </p>
                </div>
                <span class="text-[10px] font-bold text-success bg-success/10 px-2 py-1 rounded w-fit uppercase">Lunas</span>
            </div>
            
            {{-- Card 2: Pemasukan Buku Hilang --}}
            <div class="card bg-base-100 border border-base-300 shadow-sm p-5 rounded-xl aspect-square flex flex-col justify-between">
                <div>
                    <p class="text-[11px] font-bold text-base-content/50 uppercase tracking-wider">Total Denda Buku Hilang</p>
                    <p class="text-2xl font-bold text-success mt-2">
                        Rp {{ number_format($totalLunasHilang, 0, ',', '.') }}
                    </p>
                </div>
                <span class="text-[10px] font-bold text-success bg-success/10 px-2 py-1 rounded w-fit uppercase">Lunas</span>
            </div>
            
            {{-- Card 3: Total Uang Masuk (Premium Blue Frosted Glass) --}}
            <div class="card border border-blue-500/20 shadow-md p-5 rounded-xl aspect-square flex flex-col justify-between relative overflow-hidden" style="background: rgba(59, 130, 246, 0.08); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px);">
                <div class="relative z-10 flex flex-col justify-between h-full w-full">
                    <div>
                        <p class="text-[11px] font-bold text-blue-500 uppercase tracking-wider">Total Denda Keseluruhan</p>
                        <p class="text-2xl font-bold text-blue-600 dark:text-blue-400 mt-2">
                            Rp {{ number_format($totalLunasKeseluruhan, 0, ',', '.') }}
                        </p>
                    </div>
                    <span class="text-[10px] font-bold text-blue-600 bg-blue-500/20 px-2 py-1 rounded w-fit uppercase tracking-wide">Total Net</span>
                </div>
            </div>
            
            {{-- Card 4: Peminjam Menunggak --}}
            <div class="card bg-base-100 border border-base-300 shadow-sm p-5 rounded-xl aspect-square flex flex-col justify-between">
                <div>
                    <p class="text-[11px] font-bold text-base-content/50 uppercase tracking-wider">Belum Lunas</p>
                    <p class="text-3xl font-bold text-error mt-2">
                        {{ $orangBelumLunas }} <span class="text-sm font-normal text-base-content/50">Orang</span>
                    </p>
                </div>
                <span class="text-[10px] font-bold text-error bg-error/10 px-2 py-1 rounded w-fit uppercase">Menunggak</span>
            </div>
            
        </div>

        {{-- Table Riwayat Denda (Lunas & Belum Lunas) --}}
        <div class="card bg-base-100 shadow border border-base-300">
            <div class="overflow-x-auto">
                <table class="table table-zebra text-sm">
                    <thead class="bg-base-200">
                        <tr>
                            <th>#</th>
                            <th>Peminjam</th>
                            <th>Buku Dipinjam</th>
                            <th>Tgl Batas Kembali</th>
                            <th>Keterlambatan</th>
                            <th>Tipe Denda</th>
                            <th>Denda</th>
                            <th>Jumlah Dibayar</th>
                            <th class="text-center">Aksi</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($fines as $fine)
                            <tr>
                                <td><td>{{ $fines->firstItem() + $loop->index }}</td></td>
                                <td>
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center text-primary font-semibold text-xs">
                                            {{ strtoupper(substr($fine->api_nama ?? 'X', 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="font-medium">{{ $fine->api_nama ?? '-' }}</div>
                                            <div class="text-xs text-base-content/50">RFID: {{ $fine->member->rfid_code ?? '-' }}</div>
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
                                    {{-- REVISI BUG: Kosongkan selisih hari jika buku hilang --}}
                                    @if($fine->fine_type === 'hilang')
                                        <span class="text-base-content/30 italic text-xs">Hilang</span>
                                    @elseif($fine->loan && $fine->loan->due_date)
                                        @php
                                            $returnDate = $fine->loan->return_date ? \Carbon\Carbon::parse($fine->loan->return_date)->startOfDay() : \Carbon\Carbon::today();
                                            $dueDate = \Carbon\Carbon::parse($fine->loan->due_date)->startOfDay();
                                            $lateDays = (int) $dueDate->diffInDays($returnDate);
                                        @endphp
                                        <span class="badge badge-error badge-sm">{{ $lateDays }} hari</span>
                                    @else
                                        <span class="text-base-content/30">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($fine->fine_type === 'hilang')
                                        <span class="badge badge-error badge-sm text-white">Buku Hilang</span>
                                    @else
                                        <span class="badge badge-warning badge-sm text-black">Keterlambatan</span>
                                    @endif
                                </td>
                                <td class="font-semibold text-error">
                                    Rp {{ number_format($fine->total_fines, 0, ',', '.') }}
                                </td>
                                <td>
                                    {{-- REVISI: Tampilkan Lunas atau Belum Bayar --}}
                                    @if($fine->payment_status === 'lunas')
                                        <span class="badge badge-success badge-sm text-white">Lunas</span>
                                    @else
                                        <span class="badge badge-warning badge-sm text-black">Belum Bayar</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($fine->payment_status === 'lunas')
                                        <button class="btn btn-xs btn-ghost text-success" disabled>Selesai</button>
                                    @else
                                        <button 
                                            wire:click="openPaymentModal({{ $fine->id }})" 
                                            class="btn btn-xs bg-emerald-600 hover:bg-emerald-700 border-none text-white">
                                            Bayar Denda
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-16">
                                    <div class="flex flex-col items-center gap-2 opacity-50">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                                        <p class="text-sm">Tidak ada riwayat denda</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{-- Table Riwayat Denda --}}
            <div class="card bg-base-100 shadow border border-base-300">
                <div class="overflow-x-auto">
                    <table class="table table-zebra text-sm">
                    </table>
                </div>
                
                {{-- Tambahkan Pagination Controls Di Sini --}}
                <div class="p-4 border-t border-base-300">
                    {{ $fines->links() }}
                </div>
            </div>
        </div>

    </div>

    {{-- Modal Pembayaran Denda (KODE LAMA, TIDAK DIUBAH) --}}
    <flux:modal name="payment-modal" class="min-w-[28rem]">

    
        @if($errorMessage)
    <div class="p-3 mb-4 text-sm text-red-800 bg-red-100 rounded-lg">
        {{ $errorMessage }}
    </div>
@endif
        <div class="space-y-4">
            <div>
                <flux:heading size="lg">💳 Pembayaran Denda Perpustakaan</flux:heading>
                <flux:text class="mt-1">Pilih metode pembayaran untuk tagihan ini.</flux:text>
            </div>

            @if($selectedFine)
                {{-- Detail Tagihan Buku --}}
                <div class="p-3 bg-base-200 rounded-lg text-sm border border-base-300">
                    <div class="flex justify-between">
                        <span>Buku:</span>
                        <span class="text-base-content/80 font-medium">{{ $selectedFine->loan->book_title ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between mt-1">
                        <span>Total Denda:</span>
                        <strong class="text-error text-base">Rp {{ number_format($selectedFine->total_fines, 0, ',', '.') }}</strong>
                    </div>
                </div>

                {{-- Tampilan Informasi Lengkap Peminjam --}}
                @if($member_api_data)
                    <div class="p-4 border border-primary/20 rounded-xl bg-primary/5 flex items-center gap-4">
                        @if(!empty($member_api_data['foto']))
                            <img src="{{ $member_api_data['foto'] }}" class="w-16 h-16 rounded-full object-cover border bg-white shadow-sm">
                        @endif
                        <div class="flex-1 text-sm">
                            <p class="font-semibold text-base text-base-content mb-1">{{ $member_api_data['nama'] }}</p>
                            <p class="text-xs text-base-content/70"><strong>Kelas:</strong> {{ $member_api_data['kelas'] }}</p>
                            <p class="text-xs text-base-content/70"><strong>Alamat:</strong> {{ $member_api_data['alamat'] }}</p>
                            <p class="text-xs text-base-content/70"><strong>Tipe:</strong> {{ $member_api_data['type'] }}</p>
                        </div>
                    </div>
                @endif

                {{-- Opsi Metode Pembayaran --}}
                <div>
                    <label class="label text-xs font-semibold uppercase text-base-content/70">Metode Pembayaran</label>
                    <div class="flex gap-4 mt-1">
                        <label class="flex items-center gap-2 cursor-pointer bg-base-200 p-3 rounded-lg border border-base-300 flex-1 hover:bg-base-300">
                            <input type="radio" wire:model.live="paymentMethod" value="rfid" class="radio radio-primary radio-sm">
                            <span class="text-sm font-medium">Cashless</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer bg-base-200 p-3 rounded-lg border border-base-300 flex-1 hover:bg-base-300">
                            <input type="radio" wire:model.live="paymentMethod" value="cash" class="radio radio-primary radio-sm">
                            <span class="text-sm font-medium">Uang Tunai (Cash)</span>
                        </label>
                    </div>
                </div>

                {{-- Opsi Tampilan Form Berdasarkan Pilihan --}}
                @if($paymentMethod === 'rfid' && $member_api_data)
                    <div class="p-4 bg-base-200 border border-base-300 rounded-lg space-y-2 text-sm">
                        <div class="font-bold text-xs uppercase text-base-content/70 border-b border-base-300 pb-1 mb-2">
                            Detail Rekening Cashless
                        </div>
                        <div class="grid grid-cols-3 gap-1">
                            <span class="text-base-content/60">No Rek:</span>
                            <span class="col-span-2 font-mono text-xs">{{ $member_api_data['no_rekening'] }}</span>
                            
                            <span class="text-base-content/60">Saldo:</span>
                            <span class="col-span-2 text-success font-semibold">Rp {{ number_format($member_api_data['saldo'], 0, ',', '.') }}</span>
                            
                            <span class="text-base-content/60">Sisa Saldo:</span>
                            <span class="col-span-2 {{ $member_api_data['sisa'] >= 0 ? 'text-info' : 'text-error' }} font-bold">
                                Rp {{ number_format($member_api_data['sisa'], 0, ',', '.') }}
                            </span>
                        </div>

                        {{-- Input Keterangan Transaksi (Bisa diedit oleh petugas) --}}
                        <div class="mt-4 pt-4 border-t border-base-300">
                            <label class="label text-xs font-semibold uppercase text-base-content/70 pb-1">Keterangan Transaksi</label>
                            <textarea 
                                wire:model="keterangan" 
                                class="textarea textarea-bordered w-full text-sm leading-relaxed" 
                                rows="2" 
                                placeholder="Tambahkan catatan khusus jika diperlukan..."></textarea>
                        </div>
                        
                        <div class="mt-3 pt-2 border-t border-base-300">
                            <flux:input wire:model="pin" type="password" label="PIN Kartu (Optional)" placeholder="Masukkan PIN..." />
                        </div>
                    </div>
                @endif
            @endif

            <div class="flex gap-2 justify-end pt-3 border-t border-base-300">

                <button type="button" wire:click="resetPaymentForm" class="btn btn-ghost">Batal</button>
                
                <button 
                    type="button"
                    wire:click.prevent="processPayment" 
                    wire:loading.attr="disabled"
                    class="btn bg-emerald-600 hover:bg-emerald-700 text-white border-none"
                >
                    <span wire:loading wire:target="processPayment" class="loading loading-spinner loading-xs"></span>
                    <span wire:loading.remove wire:target="processPayment">Proses Pembayaran</span>
                </button>
            </div>
        </div>
    </flux:modal>
</div>

<script>
    window.addEventListener('console-log', event => {
        console.log(event.detail[0].message);
    });
</script>