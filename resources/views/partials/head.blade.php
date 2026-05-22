<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>{{ $title ?? config('app.name') }}</title>

<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance

<style>
    /* ===== BACKGROUND ===== */
    body {
        background-color: #0d2b1a !important;
    }
    [data-flux-main], flux-main, main {
        background-color: #0d2b1a !important;
    }

    /* ===== NAVBAR ===== */
    header, [data-flux-header], flux-header {
        background-color: #1a4731 !important;
        border-bottom: 1px solid #22604a !important;
    }

    /* ===== CARD ===== */
    .bg-base-100 {
        background-color: #1a4731 !important;
    }
    .bg-base-200 {
        background-color: #163d29 !important;
    }
    .border-base-300, .border-base-200 {
        border-color: #22604a !important;
    }
    .stats {
        background-color: #1a4731 !important;
        border-color: #22604a !important;
    }
    [class*="dark:bg-slate"],
    [class*="dark:bg-zinc"],
    [class*="bg-slate-900"],
    [class*="bg-zinc-800"] {
        background-color: #1a4731 !important;
    }

    /* ===== FIX DROPDOWN PROFILE & LOGOUT ===== */
    [data-flux-dropdown] [data-flux-menu],
    flux-dropdown flux-menu,
    [role="menu"] {
        background-color: #1a4731 !important;
        border: 1px solid #22604a !important;
        box-shadow: 0 8px 16px rgba(0,0,0,0.3) !important;
        z-index: 99999 !important;
        pointer-events: auto !important;
        position: relative !important;
    }

    [data-flux-menu-item],
    [role="menuitem"],
    [data-flux-menu] a,
    [data-flux-menu] button {
        pointer-events: auto !important;
        cursor: pointer !important;
        z-index: 99999 !important;
        position: relative !important;
        color: #ffffff !important;
    }

    [data-flux-menu-item]:hover,
    [role="menuitem"]:hover,
    [data-flux-menu] a:hover,
    [data-flux-menu] button:hover {
        background-color: #22604a !important;
    }

    /* Pastikan form logout bisa diklik */
    form[action*="logout"] {
        pointer-events: auto !important;
        z-index: 99999 !important;
        position: relative !important;
    }
    form[action*="logout"] button {
        pointer-events: auto !important;
        cursor: pointer !important;
        z-index: 99999 !important;
        position: relative !important;
        width: 100% !important;
    }
</style>