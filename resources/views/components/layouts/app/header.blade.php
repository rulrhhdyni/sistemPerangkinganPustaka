<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-green-950">
    <x-ui.toaster />
    <flux:header container class="border-b border-green-800 bg-green-900">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

        <a href="{{ route('dashboard') }}" class="ms-2 me-5 flex items-center space-x-2 rtl:space-x-reverse lg:ms-0"
            wire:navigate>
            <x-app-logo />
        </a>

        {{-- Semua menu dalam 1 navbar --}}
        <flux:navbar class="-mb-px max-lg:hidden">
            <flux:navbar.item icon="layout-grid" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                {{ __('Dashboard') }}
            </flux:navbar.item>
            <flux:navbar.item icon="arrows-right-left" :href="route('loan.index')" :current="request()->routeIs('loan.*')" wire:navigate>
                {{ __('Sirkulasi') }}
            </flux:navbar.item>
            <flux:navbar.item icon="banknotes" :href="route('fine.index')" :current="request()->routeIs('fine.*')" wire:navigate>
                {{ __('Denda') }}
            </flux:navbar.item>
            <flux:navbar.item icon="book-open" :href="route('lost.index')" :current="request()->routeIs('lost.*')" wire:navigate>
                {{ __('Buku Hilang') }}
            </flux:navbar.item>
            <flux:navbar.item icon="document-check" :href="route('clearance.index')" :current="request()->routeIs('clearance.*')" wire:navigate>
                {{ __('Bebas Pustaka') }}
            </flux:navbar.item>
            <flux:navbar.item icon="users" :href="route('members.index')" :current="request()->routeIs('members.*')" wire:navigate>
                {{ __('Data Members') }}
            </flux:navbar.item>
            <flux:navbar.item icon="clock" :href="route('visitors.index')" :current="request()->routeIs('visitors.*')" wire:navigate>
                {{ __('Data Kunjungan') }}
            </flux:navbar.item>
            @if(auth()->user()->is_admin)
            <flux:navbar.item icon="shield-check" :href="route('users.index')" :current="request()->routeIs('users.*')" wire:navigate>
                {{ __('Users') }}
            </flux:navbar.item>
            @endif
        </flux:navbar>

        <flux:spacer />

        <flux:dropdown position="bottom" align="end">
            <flux:profile
                name="{{ auth()->user()->name }}"
                avatar="{{ asset('images/logo.png') }}"
            />
            <flux:menu>
                <flux:menu.radio.group>
                    <div class="p-0 text-sm font-normal">
                        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                            <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                <img src="{{ asset('images/logo.png') }}" alt="Avatar User"
                                    class="h-full w-full rounded-lg object-cover" />
                            </span>
                            <div class="grid flex-1 text-start text-sm leading-tight">
                                <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                            </div>
                        </div>
                    </div>
                </flux:menu.radio.group>
                <flux:menu.separator />
                <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                    {{ __('Settings') }}
                </flux:menu.item>
                <flux:menu.separator />
                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                        {{ __('Log Out') }}
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>

    </flux:header>

    <flux:sidebar stashable sticky class="lg:hidden border-e border-green-800 bg-green-900">
        <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />
        <a href="{{ route('dashboard') }}" class="ms-1 flex items-center space-x-2 rtl:space-x-reverse" wire:navigate>
            <x-app-logo />
        </a>
        <flux:navlist variant="outline">
            <flux:navlist.group :heading="__('Platform')">
                <flux:navlist.item icon="layout-grid" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>{{ __('Dashboard') }}</flux:navlist.item>
                <flux:navlist.item icon="arrows-right-left" :href="route('loan.index')" wire:navigate>{{ __('Sirkulasi') }}</flux:navlist.item>
                <flux:navlist.item icon="banknotes" :href="route('fine.index')" wire:navigate>{{ __('Denda') }}</flux:navlist.item>
                <flux:navlist.item icon="book-open" :href="route('lost.index')" wire:navigate>{{ __('Buku Hilang') }}</flux:navlist.item>
                <flux:navlist.item icon="document-check" :href="route('clearance.index')" wire:navigate>{{ __('Bebas Pustaka') }}</flux:navlist.item>
                <flux:navlist.item icon="users" :href="route('members.index')" wire:navigate>{{ __('Members') }}</flux:navlist.item>
                <flux:navlist.item icon="clock" :href="route('visitors.index')" wire:navigate>{{ __('Kunjungan') }}</flux:navlist.item>
                @if(auth()->user()->is_admin)
                <flux:navlist.item icon="shield-check" :href="route('users.index')" wire:navigate>{{ __('Users') }}</flux:navlist.item>
                @endif
            </flux:navlist.group>
        </flux:navlist>
        <flux:spacer />
    </flux:sidebar>

    {{ $slot }}

    @fluxScripts
</body>
</html>