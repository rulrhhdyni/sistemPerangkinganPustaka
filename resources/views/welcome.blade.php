<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sistem Kehadiran Perpustakaan Al-Ihsan</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fluxAppearance
    <style>
        body {
            background-color: #0d2b1a !important;
            color: #e2e8f0 !important;
        }
        header, footer {
            background-color: #1a4731 !important;
            border-color: #22604a !important;
        }
        .bg-base-100 {
            background-color: #1a4731 !important;
        }
        .bg-base-200 {
            background-color: #0d2b1a !important;
        }
        .border-base-300 {
            border-color: #22604a !important;
        }
        .hero {
            background-color: #0d2b1a !important;
        }
        .card {
            background-color: #1a4731 !important;
            border: 1px solid #22604a !important;
        }
        .badge-outline {
            border-color: #22604a !important;
            color: #e2e8f0 !important;
        }
        h1, h2, h3, p {
            color: #e2e8f0 !important;
        }
        .text-base-content\/70 {
            color: #a0b8a8 !important;
        }
        .text-base-content\/60 {
            color: #8aa898 !important;
        }
        /* Marquee */
        .animate-marquee {
            animation: marquee 20s linear infinite;
        }
        @keyframes marquee {
            0% { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }
    </style>
</head>

<body class="min-h-screen flex flex-col">

    <!-- HEADER -->
    <header class="sticky top-0 z-50" style="background-color:#1a4731; border-bottom: 1px solid #22604a;">
        <nav class="max-w-7xl mx-auto px-4">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 py-3">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-green-600 rounded-full flex items-center justify-center text-white font-bold text-lg">
                        AI
                    </div>
                    <div class="leading-tight">
                        <h1 class="text-sm md:text-base font-bold uppercase text-white">Perpustakaan Al-Ihsan</h1>
                        <p class="text-xs md:text-sm font-semibold uppercase" style="color:#a0b8a8;">Pondok Pesantren Al-Ihsan</p>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-2 md:justify-end">
                    <span class="badge badge-outline px-4 py-3 text-xs md:text-sm" style="border-color:#22604a; color:#e2e8f0;">📍 Al-Ihsan</span>
                    <span class="badge badge-outline px-4 py-3 text-xs md:text-sm" style="border-color:#22604a; color:#e2e8f0;">📅 {{ now()->translatedFormat('d M Y') }}</span>
                    <span class="badge badge-outline px-4 py-3 text-xs md:text-sm" style="border-color:#22604a; color:#e2e8f0;">🕐 {{ now()->format('H:i') }}</span>
                </div>
            </div>
        </nav>
    </header>

    <!-- MAIN -->
    <main class="flex-1 min-h-screen" style="background-color:#0d2b1a;">

        <section style="background-color:#0d2b1a;">
            <div class="hero-content px-4 py-8 sm:py-10 md:py-12 text-center mx-auto">
                <div class="max-w-4xl space-y-4">
                    <h1 class="font-bold uppercase leading-tight text-2xl sm:text-3xl md:text-4xl lg:text-5xl text-white">
                        Selamat Datang di <br class="hidden sm:block" />
                        Perpustakaan Pondok Pesantren Al-Ihsan
                    </h1>
                    <p class="text-sm sm:text-base md:text-lg" style="color:#a0b8a8;">
                        Sistem Kehadiran & Layanan Digital Perpustakaan
                    </p>
                </div>
            </div>
        </section>

        <!-- MARQUEE -->
        <div class="overflow-hidden my-4 max-w-7xl mx-auto" style="background-color:#1a4731; border-top:1px solid #22604a; border-bottom:1px solid #22604a;">
            <div class="flex w-max items-center animate-marquee hover:[animation-play-state:paused]">
                <span class="mx-10 py-2 text-sm sm:text-base font-medium whitespace-nowrap text-white">
                    📢 Selamat Datang di Perpustakaan Al-Ihsan, Silakan Tempelkan Kartu RFID Anda. |
                    Ayo Berkunjung ke Perpustakaan — Ilmu Menanti Anda
                </span>
                <span class="mx-10 py-2 text-sm sm:text-base font-medium whitespace-nowrap text-white">
                    📢 Selamat Datang di Perpustakaan Al-Ihsan, Silakan Tempelkan Kartu RFID Anda. |
                    Ayo Berkunjung ke Perpustakaan — Ilmu Menanti Anda
                </span>
            </div>
        </div>

        <!-- RFID SCAN AREA -->
        <div class="max-w-7xl mx-auto px-4 py-6">
            <div class="flex justify-center">
                <div class="w-full max-w-lg rounded-xl shadow-lg p-8 flex flex-col items-center text-center gap-6" style="background-color:#1a4731; border:1px solid #22604a;">

                    <h2 class="text-xl font-semibold text-white">
                        Silakan tempelkan kartu RFID Anda <br>
                        pada reader untuk absensi kehadiran.
                    </h2>

                    <div class="relative flex items-center justify-center w-48 h-48">
                        <div class="absolute w-48 h-48 rounded-full border-4 border-teal-400 opacity-20 animate-ping"></div>
                        <div class="absolute w-36 h-36 rounded-full border-4 border-teal-400 opacity-30 animate-ping" style="animation-delay: 0.3s"></div>
                        <div class="absolute w-24 h-24 rounded-full border-4 border-teal-400 opacity-40 animate-ping" style="animation-delay: 0.6s"></div>
                        <div class="relative z-10 rounded-full p-4" style="background-color:#1a4731;">
                            <svg viewBox="0 0 80 80" class="w-20 h-20" xmlns="http://www.w3.org/2000/svg">
                                <rect x="8" y="25" width="50" height="32" rx="4" fill="#1e293b" stroke="#2ca9bc" stroke-width="1.5"/>
                                <rect x="16" y="33" width="14" height="10" rx="2" fill="#2ca9bc" opacity="0.9"/>
                                <line x1="19" y1="33" x2="19" y2="43" stroke="#1e293b" stroke-width="0.8"/>
                                <line x1="23" y1="33" x2="23" y2="43" stroke="#1e293b" stroke-width="0.8"/>
                                <line x1="27" y1="33" x2="27" y2="43" stroke="#1e293b" stroke-width="0.8"/>
                                <line x1="16" y1="48" x2="45" y2="48" stroke="#2ca9bc" stroke-width="1" opacity="0.5"/>
                                <line x1="16" y1="51" x2="35" y2="51" stroke="#2ca9bc" stroke-width="1" opacity="0.3"/>
                                <path d="M62 32 Q68 40 62 48" fill="none" stroke="#2ca9bc" stroke-width="2" stroke-linecap="round"/>
                                <path d="M66 28 Q75 40 66 52" fill="none" stroke="#2ca9bc" stroke-width="2" stroke-linecap="round" opacity="0.6"/>
                                <path d="M70 24 Q82 40 70 56" fill="none" stroke="#2ca9bc" stroke-width="2" stroke-linecap="round" opacity="0.3"/>
                            </svg>
                        </div>
                    </div>

                    <div id="rfid-status" class="badge badge-outline badge-lg px-8 py-4 text-sm font-medium transition-all duration-300" style="border-color:#22604a; color:#e2e8f0;">
                        ⏳ Menunggu kartu RFID...
                    </div>

                </div>
            </div>
        </div>

    </main>

    <!-- FOOTER -->
    <footer style="background-color:#1a4731; border-top:1px solid #22604a;">
        <div class="max-w-7xl mx-auto px-4 py-4">
            <p class="text-center text-sm" style="color:#a0b8a8;">
                © {{ date('Y') }} Pondok Pesantren Al-Ihsan — Sistem Kehadiran Perpustakaan
            </p>
        </div>
    </footer>

    <!-- MODAL SUKSES -->
    <dialog id="welcome_modal" class="modal">
        <div class="modal-box relative p-6 rounded-xl shadow-xl" style="background-color:#1a4731; border:1px solid #22604a;">
            <form method="dialog">
                <button class="btn btn-sm btn-circle btn-ghost absolute right-4 top-4 text-white">✕</button>
            </form>
            <div class="flex justify-center">
                <div class="bg-green-800 text-green-300 rounded-full w-16 h-16 flex items-center justify-center text-3xl mb-4">✅</div>
            </div>
            <h3 class="text-center text-xl font-semibold text-white mb-2">Ahlan Wa Sahlan! 🌙</h3>
            <p class="text-center mb-4" style="color:#a0b8a8;">Absensi kunjungan perpustakaan berhasil dicatat. Selamat belajar!</p>
            <div class="text-center text-sm" style="color:#8aa898;">
                Waktu kehadiran: <span id="visit_time">{{ now()->format('H:i') }}</span>
            </div>
        </div>
    </dialog>

    <!-- MODAL GAGAL -->
    <dialog id="barcode_gagal" class="modal">
        <div class="modal-box relative p-6 rounded-xl shadow-xl" style="background-color:#1a4731; border:1px solid #22604a;">
            <form method="dialog">
                <button class="btn btn-sm btn-circle btn-ghost absolute right-4 top-4 text-white">✕</button>
            </form>
            <div class="flex justify-center">
                <div class="bg-red-900 text-red-300 rounded-full w-16 h-16 flex items-center justify-center text-3xl mb-4">❌</div>
            </div>
            <h3 class="text-center text-xl font-semibold text-red-300 mb-2">Kartu RFID Tidak Dikenal</h3>
            <p class="text-center mb-4" style="color:#a0b8a8;">Kartu RFID tidak terdaftar. Silakan hubungi petugas perpustakaan.</p>
        </div>
    </dialog>

    @fluxScripts

    <script>
        let rfidBuffer = '';
        let rfidTimeout = null;
        document.addEventListener('keydown', function(e) {
            if (['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName)) return;
            if (e.key === 'Enter') {
                if (rfidBuffer.length > 0) {
                    const rfid = rfidBuffer;
                    rfidBuffer = '';
                    processRFID(rfid);
                }
            } else if (e.key.length === 1) {
                rfidBuffer += e.key;
                clearTimeout(rfidTimeout);
                rfidTimeout = setTimeout(() => { rfidBuffer = ''; }, 100);
            }
        });

        function setStatus(text, type) {
            const el = document.getElementById('rfid-status');
            if (!el) return;
            el.textContent = text;
            el.className = `badge badge-${type} badge-lg px-8 py-4 text-sm font-medium transition-all duration-300`;
        }

        function processRFID(rfid) {
            setStatus('🔄 Memproses kartu...', 'warning');
            fetch('/rfid-absensi', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ rfid: rfid })
            })
            .then(res => res.json())
            .then(data => {
                if (data.type === 'admin') {
                    setStatus('🔐 Admin terdeteksi, masuk...', 'info');
                    window.location.href = data.redirect;
                } else if (data.type === 'member') {
                    setStatus('✅ Absensi Berhasil!', 'success');
                    setTimeout(() => setStatus('⏳ Menunggu kartu RFID...', 'outline'), 4000);
                    const modal = document.getElementById('welcome_modal');
                    if (modal) { modal.showModal(); setTimeout(() => modal.close(), 5000); }
                } else {
                    setStatus('❌ Kartu tidak dikenal!', 'error');
                    setTimeout(() => setStatus('⏳ Menunggu kartu RFID...', 'outline'), 4000);
                    const modal = document.getElementById('barcode_gagal');
                    if (modal) { modal.showModal(); setTimeout(() => modal.close(), 5000); }
                }
            })
            .catch(() => {
                setStatus('❌ Terjadi kesalahan!', 'error');
                setTimeout(() => setStatus('⏳ Menunggu kartu RFID...', 'outline'), 4000);
            });
        }
    </script>

</body>
</html>