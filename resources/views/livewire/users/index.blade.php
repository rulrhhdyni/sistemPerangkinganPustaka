<div class="py-6 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">

        {{-- Header --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h1 class="text-2xl font-semibold text-base-content">
                Data Users
            </h1>
            <div class="flex gap-2">
                <button x-on:click="$dispatch('open-slideover', 'create-users')" class="btn btn-sm btn-primary">
                    Add User
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
                <input type="search" wire:model.live.debounce.400ms="search" placeholder="Search name or email..."
                    class="input input-sm input-bordered w-full sm:w-90" />
            </div>

            {{-- Table --}}
            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead class="bg-base-200">
                        <tr>
                            <th>#</th>
                            <th>Nama Pengguna</th>
                            <th>Email Pengguna</th>
                            <th>RFID</th> {{-- Tambahan Header RFID --}}
                            <th>Is Admin</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($users as $user)
                            <tr wire:key="member-{{ $user->id }}">
                                <td>{{ $loop->iteration }}</td>
                                <td class="font-medium">{{ $user->name }}</td>
                                <td class="font-medium">{{ $user->email }}</td>
                                <td>{{ $user->rfid_id }}</td> {{-- Tambahan Data RFID --}}
                                <td>{{ $user->is_admin ? 'Yes' : 'No' }}</td>
                                <td class="text-center space-x-1">
                                    <button wire:click="edit({{ $user->id }})" class="btn btn-xs btn-warning">
                                        Edit
                                    </button>
                                    <button wire:click="openDelete({{ $user->id }})"
                                        class="btn btn-xs btn-error">
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="full" class="text-center py-10 opacity-60">
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
                    <strong>{{ $users->firstItem() }}</strong>
                    to
                    <strong>{{ $users->lastItem() }}</strong>
                    of
                    <strong>{{ $users->total() }}</strong>
                </div>

                {{-- {{ $users->links() }} --}}
                @if ($users instanceof \Illuminate\Pagination\LengthAwarePaginator && $users->hasPages())
                    <div class="join grid grid-cols-2">
                        @if ($users && $users->onFirstPage())
                            <button class="join-item btn btn-outline" disabled>Previous page</button>
                        @else
                            <button class="join-item btn btn-outline" wire:click="previousPage">Previous page</button>
                        @endif
                        @if ($users && $users->hasMorePages())
                            <button class="join-item btn btn-outline" wire:click="nextPage">Next</button>
                        @else
                            <button class="join-item btn btn-outline" disabled>Next</button>
                        @endif
                    </div>
                @endif
            </div>

        </div>

        <livewire:users.create wire:key="create-users" />
        <livewire:users.update :user="$selectedId" wire:key="update-users-{{ $selectedId }}" />
        <flux:modal name="confirm" class="min-w-[22rem]">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">Delete User?</flux:heading>
                    <flux:text class="mt-2">
                        You're about to delete this users.<br>
                        This action cannot be reversed.
                    </flux:text>
                </div>
                <div class="flex gap-2">
                    <flux:spacer />
                    <flux:modal.close>
                        <flux:button variant="ghost">Cancel</flux:button>
                    </flux:modal.close>
                    <flux:button type="submit" variant="danger" wire:click="deleteUser()">Delete user
                    </flux:button>
                </div>
            </div>
        </flux:modal>

    </div>
</div>