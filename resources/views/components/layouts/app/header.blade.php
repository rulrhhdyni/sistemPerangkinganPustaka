<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen">
    <x-ui.toaster />
    <flux:header container class="border-b border-zinc-200/80 bg-white/80">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

        <a href="{{ route('dashboard') }}" class="ms-2 me-5 flex items-center space-x-2 rtl:space-x-reverse lg:ms-0"
            wire:navigate>
            <x-app-logo />
        </a>

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

        {{-- ✅ Theme Switcher --}}
        <div class="flex items-center" style="position:relative; z-index:9999;">
            <button
                id="theme-toggle-btn"
                onclick="toggleTheme()"
                class="p-2 rounded-lg transition-colors cursor-pointer"
                title="Ganti Mode Terang/Gelap"
                style="pointer-events:auto; z-index:9999; position:relative;">
                <svg id="icon-moon" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z" />
                </svg>
                <svg id="icon-sun" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M17.657 17.657l-.707-.707M6.343 6.343l-.707-.707M12 8a4 4 0 100 8 4 4 0 000-8z" />
                </svg>
            </button>
        </div>

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

    <flux:sidebar stashable sticky class="lg:hidden border-e border-zinc-200 bg-white">
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

    <script>
        function toggleTheme() {
            const isDark = document.documentElement.classList.contains('dark');
            if (isDark) {
                setTheme('light');
            } else {
                setTheme('dark');
            }
            updateThemeIcon();
        }

        function updateThemeIcon() {
            const isDark = document.documentElement.classList.contains('dark');
            const moon = document.getElementById('icon-moon');
            const sun = document.getElementById('icon-sun');
            if (moon && sun) {
                if (isDark) {
                    moon.classList.add('hidden');
                    sun.classList.remove('hidden');
                } else {
                    moon.classList.remove('hidden');
                    sun.classList.add('hidden');
                }
            }
        }

        document.addEventListener('DOMContentLoaded', updateThemeIcon);
        window.addEventListener('theme-changed', updateThemeIcon);
    </script>

    @fluxScripts
</body>
</html>