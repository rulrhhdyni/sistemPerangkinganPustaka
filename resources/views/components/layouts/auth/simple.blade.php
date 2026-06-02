<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
        <style>
            /* Background halaman login */
            body {
                background: var(--bg-login-gradient) !important;
                background-image: none !important;
            }
            .bg-background {
                background-color: var(--bg-header-sidebar) !important;
            }

            /* Card form login */
            [data-flux-card],
            flux-card,
            .bg-white {
                background-color: var(--bg-card) !important;
                border: var(--border-card-custom) !important;
                box-shadow: var(--shadow-card-custom) !important;
                border-radius: 1.25rem !important;
            }

            .border-card-login {
                border: var(--border-card-custom) !important;
            }

            /* Input field */
            input[type="email"],
            input[type="password"],
            input[type="text"],
            [data-flux-input] input {
                background-color: var(--bg-input) !important;
                color: var(--color-input) !important;
                border: var(--border-input) !important;
                border-radius: 0.5rem !important;
            }

            input[type="email"]:focus,
            input[type="password"]:focus,
            input[type="text"]:focus,
            [data-flux-input] input:focus {
                border-color: var(--border-input-focus) !important;
                box-shadow: var(--shadow-input-focus) !important;
            }

            input::placeholder {
                color: var(--color-placeholder) !important;
            }

            /* Teks judul & label */
            h1, h2, h3 {
                color: var(--color-heading) !important;
                font-weight: 800 !important;
            }
            label, span, p {
                color: var(--color-text-primary) !important;
            }

            /* Tombol Log in */
            [data-flux-button][data-variant="primary"],
            flux-button[variant="primary"] {
                background-color: var(--color-button-primary) !important;
                color: var(--color-button-primary-text) !important;
                border-radius: 0.5rem !important;
                font-weight: 600 !important;
            }
            [data-flux-button][data-variant="primary"]:hover {
                background-color: var(--color-button-primary-hover) !important;
            }
        </style>
    </head>
    <body class="min-h-screen antialiased">
        <div class="flex min-h-svh flex-col items-center justify-center gap-6 p-6 md:p-10">
            <div class="flex w-full max-w-lg flex-col gap-2">
                <div class="flex flex-col gap-6 rounded-2xl p-8 shadow-lg bg-white border-card-login">
                    {{ $slot }}
                </div>
            </div>
        </div>
        @fluxScripts
    </body>
</html>