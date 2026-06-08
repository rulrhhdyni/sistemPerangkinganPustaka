<div>
    <x-ui.slide-over name="update-users" title="Update Users" description="Update existing User" button-text="Save"
        action="saveUser">
        <flux:input type="text" label="Nama Pengguna" wire:model="form.name" />
        <flux:input type="email" label="Email Pengguna" wire:model="form.email" />
        
        <flux:input type="text" label="Tempelkan Kartu (RFID)" wire:model="form.rfid_id" />

        {{-- Menggunakan Checkbox biasa agar lebih jelas --}}
        <div class="py-2">
            <flux:checkbox wire:model="form.is_admin" label="Jadikan sebagai Admin (Ceklis jika Ya)" />
        </div>
        
        <flux:checkbox label="Update Password" wire:model.change="updatePassword" align="left" />
        @if ($updatePassword)
            <flux:input type="password" label="Password" wire:model="form.password" />
            <flux:input type="password" label="Confirm Password" wire:model="form.password_confirmation" />
        @endif
    </x-ui.slide-over>
</div>