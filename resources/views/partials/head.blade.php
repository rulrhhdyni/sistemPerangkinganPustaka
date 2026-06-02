<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>{{ $title ?? config('app.name') }}</title>

<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

<script>
    // Fungsi utama untuk sinkronisasi
    window.syncTheme = function() {
        const saved = localStorage.getItem('theme');
        const systemDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        const theme = saved === 'system' || !saved ? (systemDark ? 'dark' : 'light') : saved;

        // Terapkan ke DOM
        document.documentElement.classList.toggle('dark', theme === 'dark');
        document.documentElement.classList.toggle('light', theme === 'light');
        document.documentElement.setAttribute('data-theme', theme);

        // Paksa Flux agar sinkron
        if (typeof Flux !== 'undefined') {
            localStorage.setItem('flux.appearance', saved || 'system');
        }
    };

    // Jalankan segera
    syncTheme();

    // Fungsi untuk tombol
    window.setTheme = function(theme) {
        localStorage.setItem('theme', theme);
        syncTheme();
        // Force refresh state internal Flux jika perlu
        window.location.reload(); 
    };

    // Dengarkan navigasi Livewire
    document.addEventListener('livewire:navigated', () => {
        syncTheme();
    });
</script>

@fluxAppearance
@vite(['resources/css/app.css', 'resources/js/app.js'])


<style>
    /* ===== THEME VARIABLES ===== */
    :root {
        --bg-body-gradient: linear-gradient(180deg, #f8fafc 0%, #e2e8f0 100%);
        --color-text-primary: #1e293b;
        --color-heading: #0f2b17;
        --color-label: #1e293b;
        --color-description: #475569;

        --bg-header-sidebar: #F0FFF0;
        --border-header-sidebar: 1px solid #d1ebd5;
        --shadow-header: 0 4px 15px -3px rgba(22, 101, 52, 0.08);
        --shadow-sidebar: 4px 0 15px -3px rgba(22, 101, 52, 0.04);

        --color-nav-item: #1a4d2e;
        --color-nav-item-active: #166534;
        --bg-nav-item-active: rgba(22, 101, 52, 0.08);

        --bg-card: #ffffff;
        --border-card: 1px solid #d1ebd5;
        --shadow-card: 0 10px 30px -5px rgba(22, 101, 52, 0.05), 0 4px 12px -2px rgba(22, 101, 52, 0.03);
        --border-base-inner: #e3f5e7;

        --bg-stat: #ffffff;
        --color-stat-title: #64748b;
        --color-stat-value: #166534;
        --color-stat-desc: #475569;
        --shadow-stat: 0 8px 20px -4px rgba(22, 101, 52, 0.04);

        --bg-input: #ffffff;
        --color-input: #1e293b;
        --border-input: 1px solid #cbd5e1;
        --border-input-focus: #166534;
        --shadow-input-focus: 0 0 0 2px rgba(22, 101, 52, 0.15);
        --color-placeholder: #94a3b8;

        --bg-table-th: #eefbee;
        --color-table-th: #166534;
        --color-table-td: #1e293b;

        --bg-dropdown: #ffffff;
        --border-dropdown: 1px solid #d1ebd5;
        --shadow-dropdown: 0 10px 25px -5px rgba(22, 101, 52, 0.1), 0 4px 12px -3px rgba(22, 101, 52, 0.05);
        --bg-dropdown-item-hover: #F0FFF0;
        --color-dropdown-item-hover: #166534;

        --bg-login-gradient: linear-gradient(135deg, #F0FFF0 0%, #e2f3e5 100%);
        --border-card-custom: 1px solid #d1ebd5;
        --shadow-card-custom: 0 20px 40px -15px rgba(22, 101, 52, 0.08), 0 8px 24px -10px rgba(22, 101, 52, 0.04);
        --color-button-primary: #166534;
        --color-button-primary-hover: #114f29;
        --color-button-primary-text: #ffffff;
    }

    .dark {
        --bg-body-gradient: linear-gradient(180deg, #09120c 0%, #030712 100%);
        --color-text-primary: #cbd5e1;
        --color-heading: #10b981;
        --color-label: #cbd5e1;
        --color-description: #94a3b8;

        --bg-header-sidebar: #062e1c;
        --border-header-sidebar: 1px solid #14532d;
        --shadow-header: 0 4px 15px -3px rgba(0, 0, 0, 0.5);
        --shadow-sidebar: 4px 0 15px -3px rgba(0, 0, 0, 0.3);

        --color-nav-item: #a7f3d0;
        --color-nav-item-active: #10b981;
        --bg-nav-item-active: rgba(16, 185, 129, 0.15);

        --bg-card: #0f172a;
        --border-card: 1px solid #1e293b;
        --shadow-card: 0 10px 30px -5px rgba(0, 0, 0, 0.3), 0 4px 12px -2px rgba(0, 0, 0, 0.2);
        --border-base-inner: #1e293b;

        --bg-stat: #0f172a;
        --color-stat-title: #94a3b8;
        --color-stat-value: #10b981;
        --color-stat-desc: #64748b;
        --shadow-stat: 0 8px 20px -4px rgba(0, 0, 0, 0.3);

        --bg-input: #1e293b;
        --color-input: #f1f5f9;
        --border-input: 1px solid #334155;
        --border-input-focus: #10b981;
        --shadow-input-focus: 0 0 0 2px rgba(16, 185, 129, 0.25);
        --color-placeholder: #64748b;

        --bg-table-th: #064e3b;
        --color-table-th: #10b981;
        --color-table-td: #cbd5e1;

        --bg-dropdown: #0f172a;
        --border-dropdown: 1px solid #1e293b;
        --shadow-dropdown: 0 10px 25px -5px rgba(0, 0, 0, 0.4), 0 4px 12px -3px rgba(0, 0, 0, 0.2);
        --bg-dropdown-item-hover: #064e3b;
        --color-dropdown-item-hover: #10b981;

        --bg-login-gradient: linear-gradient(135deg, #051610 0%, #030712 100%);
        --border-card-custom: 1px solid #1e293b;
        --shadow-card-custom: 0 20px 40px -15px rgba(0, 0, 0, 0.4), 0 8px 24px -10px rgba(0, 0, 0, 0.2);
        --color-button-primary: #10b981;
        --color-button-primary-hover: #059669;
        --color-button-primary-text: #061f14;
    }

    /* ===== BACKGROUND & CORE THEME ===== */
    body {
        background: var(--bg-body-gradient) !important;
        color: var(--color-text-primary) !important;
        min-height: 100vh;
        font-family: var(--font-sans), system-ui, -apple-system, sans-serif !important;
    }
    [data-flux-main], flux-main, main {
        background-color: transparent !important;
    }

    /* ===== HEADINGS & TEXT ===== */
    h1, h2, h3, h4, h5, h6 {
        color: var(--color-heading) !important;
        font-weight: 700 !important;
    }
    [data-flux-label], label {
        color: var(--color-label) !important;
        font-weight: 600 !important;
    }
    [data-flux-description] {
        color: var(--color-description) !important;
    }

    /* ===== NAVBAR / HEADER ===== */
    header, [data-flux-header], flux-header {
        background-color: var(--bg-header-sidebar) !important;
        border-bottom: var(--border-header-sidebar) !important;
        box-shadow: var(--shadow-header) !important;
    }
    flux-navbar flux-navbar-item,
    [data-flux-navbar] a,
    [data-flux-navbar] button {
        color: var(--color-nav-item) !important;
        font-weight: 600 !important;
    }
    [data-flux-navbar] a[data-current],
    [data-flux-navbar] button[data-current],
    flux-navbar flux-navbar-item[current] {
        color: var(--color-nav-item-active) !important;
        background-color: var(--bg-nav-item-active) !important;
        border-radius: 8px !important;
    }

    /* ===== SIDEBAR ===== */
    flux-sidebar, [data-flux-sidebar] {
        background-color: var(--bg-header-sidebar) !important;
        border-right: var(--border-header-sidebar) !important;
        box-shadow: var(--shadow-sidebar) !important;
    }

    /* ===== CARD & PANELS ===== */
    .bg-base-100, .card {
        background-color: var(--bg-card) !important;
        border: var(--border-card) !important;
        box-shadow: var(--shadow-card) !important;
        border-radius: 1rem !important;
    }
    .bg-base-200 {
        background-color: var(--bg-table-th) !important;
    }
    .border-base-300, .border-base-200 {
        border-color: var(--border-base-inner) !important;
    }
    [class*="dark:bg-slate"],
    [class*="dark:bg-zinc"],
    [class*="bg-slate-900"],
    [class*="bg-zinc-800"] {
        background-color: var(--bg-card) !important;
        border: var(--border-card) !important;
    }

    /* ===== STATS ===== */
    .stats {
        background-color: var(--bg-stat) !important;
        border: var(--border-card) !important;
        box-shadow: var(--shadow-stat) !important;
        display: flex !important;
    }
    .stat {
        background-color: var(--bg-stat) !important;
        color: var(--color-text-primary) !important;
    }
    .stat-title {
        color: var(--color-stat-title) !important;
        font-weight: 600 !important;
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
    }
    .stat-value {
        color: var(--color-stat-value) !important;
        font-weight: 800 !important;
    }
    .stat-desc {
        color: var(--color-stat-desc) !important;
        opacity: 0.9 !important;
    }

    /* ===== FIX TEXT BASE-CONTENT ===== */
    .text-base-content {
        color: var(--color-text-primary) !important;
    }
    [class*="text-base-content"] {
        color: var(--color-text-primary) !important;
        opacity: 1 !important;
    }
    .card p {
        color: var(--color-text-primary) !important;
    }

    /* ===== TABLES ===== */
    table th {
        background-color: var(--bg-table-th) !important;
        color: var(--color-table-th) !important;
        font-weight: 700 !important;
    }
    table td {
        color: var(--color-table-td) !important;
    }
    .table-zebra tbody tr:nth-child(even) {
        background-color: var(--bg-card) !important;
    }
    .table-zebra tbody tr:nth-child(odd) {
        background-color: var(--bg-stat) !important;
    }

    /* ===== INPUTS ===== */
    input.input, select.select, textarea.textarea,
    [data-flux-input] input, [data-flux-select] select {
        background-color: var(--bg-input) !important;
        color: var(--color-input) !important;
        border: var(--border-input) !important;
    }
    input.input:focus, select.select:focus, textarea.textarea:focus,
    [data-flux-input] input:focus, [data-flux-select] select:focus {
        border-color: var(--border-input-focus) !important;
        box-shadow: var(--shadow-input-focus) !important;
    }
    input::placeholder {
        color: var(--color-placeholder) !important;
    }

    /* ===== DROPDOWN PROFILE & LOGOUT ===== */
    [data-flux-dropdown] [data-flux-menu],
    flux-dropdown flux-menu,
    [role="menu"] {
        background-color: var(--bg-dropdown) !important;
        border: var(--border-dropdown) !important;
        box-shadow: var(--shadow-dropdown) !important;
        border-radius: 0.75rem !important;
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
        color: var(--color-text-primary) !important;
        font-weight: 500 !important;
    }
    [data-flux-menu-item]:hover,
    [role="menuitem"]:hover,
    [data-flux-menu] a:hover,
    [data-flux-menu] button:hover {
        background-color: var(--bg-dropdown-item-hover) !important;
        color: var(--color-dropdown-item-hover) !important;
    }
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

    /* ===== FIX DARK MODE TOGGLE ===== */
    header button,
    header a {
        pointer-events: auto !important;
        cursor: pointer !important;
        position: relative !important;
        z-index: 9999 !important;
    }
    flux-navbar, [data-flux-navbar] {
        pointer-events: auto !important;
    }
</style>