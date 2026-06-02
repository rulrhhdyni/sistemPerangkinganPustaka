<div class="py-6 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">

        {{-- Header --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-base-content">Data Denda</h1>
                <p class="text-sm text-base-content/60 mt-1">Daftar denda yang belum dibayar</p>
            </div>
        </div>

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
                <p class="text-xs text-base-content/60 mb-1">Peminjam Terdenda</p>
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
                            <th>Peminjam</th>
                            <th>Buku Dipinjam</th>
                            <th>Tgl Batas Kembali</th>
                            <th>Keterlambatan</th>
                            <th>Tipe Denda</th>
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
                                    @if($fine->loan && $fine->loan->due_date)
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
                                        <span class="badge badge-error badge-sm">Buku Hilang</span>
                                    @else
                                        <span class="badge badge-warning badge-sm">Keterlambatan</span>
                                    @endif
                                </td>
                                <td class="font-semibold text-error">
                                    Rp {{ number_format($fine->total_fines, 0, ',', '.') }}
                                </td>
                                <td>
                                    <span class="badge badge-warning badge-sm">Belum Bayar</span>
                                </td>
                                <td class="text-center">
                                    <button 
                                        wire:click="openPaymentModal({{ $fine->id }})" 
                                        class="btn btn-xs btn-success text-white">
                                        Bayar Denda
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-16">
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


    {{-- Modal Pembayaran Denda --}}
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

                {{-- Tampilan Informasi Lengkap Peminjam (Tetap muncul di Cash & Cashless) --}}
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