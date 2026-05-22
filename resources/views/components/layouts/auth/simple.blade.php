<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
        <style>
            /* Background halaman login */
            body {
                background-color: #0d2b1a !important;
                background-image: none !important;
            }
            .bg-background {
                background-color: #0d2b1a !important;
            }

            /* Card form login */
            [data-flux-card],
            flux-card,
            .bg-white {
                background-color: #163d29 !important;
                border-color: #166534 !important;
            }

            /* Input field */
            input[type="email"],
            input[type="password"],
            input[type="text"],
            [data-flux-input] input {
                background-color: #0d2b1a !important;
                color: #ffffff !important;
                border-color: #166534 !important;
            }

            input::placeholder {
                color: #6b8f7a !important;
            }

            /* Teks judul & label */
            h1, h2, h3, label, span, p {
                color: #ffffff !important;
            }

            /* Tombol Log in */
            [data-flux-button][data-variant="primary"],
            flux-button[variant="primary"] {
                background-color: #166534 !important;
                color: #ffffff !important;
            }
            [data-flux-button][data-variant="primary"]:hover {
                background-color: #15803d !important;
            }
        </style>
    </head>
    <body class="min-h-screen antialiased" style="background-color:#0d2b1a;">
        <div class="flex min-h-svh flex-col items-center justify-center gap-6 p-6 md:p-10" style="background-color:#0d2b1a;">
            <div class="flex w-full max-w-lg flex-col gap-2">
                <div class="flex flex-col gap-6 rounded-xl p-8" style="background-color:#163d29; border:1px solid #166534;">
                    {{ $slot }}
                </div>
            </div>
        </div>
        @fluxScripts
    </body>
</html>