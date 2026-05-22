<div class="mt-10" x-data="barcodeScanner()" x-init="init()" @keydown.window="capture($event)">
    <form wire:submit.prevent="save" class="flex flex-col gap-4">
        <flux:radio.group label="" variant="segmented" wire:model.change="form.visit_type">
            @foreach ($typeList as $key => $value)
                <flux:radio label="{{ $value }}" value="{{ $key }}" />
            @endforeach
        </flux:radio.group>
        @if ($form['visit_type'] === 'member')
            <flux:input label="Member ID" wire:model="form.member_id" />
        @endif
        @if ($form['visit_type'] === 'guest')
            <flux:input label="Nama Lengkap" wire:model="form.guest_name"  />
            <flux:input label="No. Telepon" wire:model="form.guest_phone" type="number"/>
            <flux:input label="No. Identitas (KTP/SIM)" wire:model="form.guest_identity" type="number" />
            <flux:textarea label="Tujuan Kunjungan" wire:model="form.purpose" rows="3" />
        @endif
        <flux:button variant="primary" type="submit">Save</flux:button>
    </form>

    <script>
        function barcodeScanner() {
            return {
                buffer: '',
                timeout: null,

                init() {
                    Livewire.on('barcodeNotFound', () => {
                        // lakukan sesuatu dengan data.barcode
                    });
                },

                capture(e) {
                    // biasanya scanner mengirimkan karakter lalu Enter
                    if (e.key === 'Enter') {
                        if (this.buffer.length > 0) {
                            // kirim ke Livewire
                            // @this.set('barcode', this.buffer);
                            // this.buffer = '';
                            console.log('Scanned barcode:', this.buffer);
                            Livewire.dispatch('barcodeScanned', { barcode: this.buffer });
                            this.buffer = '';
                        }
                    } else {
                        this.buffer += e.key;
                        // optional: reset buffer jika terlalu lama
                        clearTimeout(this.timeout);
                        this.timeout = setTimeout(() => {
                            this.buffer = '';
                        }, 100); // reset jika delay >100ms
                    }
                }
            }
        }
    </script>
</div>
