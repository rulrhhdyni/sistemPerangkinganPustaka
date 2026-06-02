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
            /* Background abu-abu tipis dicampur gradasi putih */
            background: linear-gradient(180deg, #f8fafc 0%, #e2e8f0 100%) !important; 
            color: #1e293b !important; 
            min-height: 100vh;
        }

        /* CARD RFID: Berbasis #F0FFF0 menyusut halus ke putih */
        .card-custom {
            background: linear-gradient(180deg, #F0FFF0 0%, #ffffff 100%) !important;
            border: 1px solid #cbd5e1 !important;
            box-shadow: 0 15px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.03) !important;
            position: relative;
        }

        /* MARQUEE */
        .marquee-container {
            background: linear-gradient(90deg, #ffffff 0%, #F0FFF0 50%, #ffffff 100%) !important;
            border-top: 1px solid #e2e8f0 !important;
            border-bottom: 1px solid #e2e8f0 !important;
        }

        .animate-marquee {
            animation: marquee 25s linear infinite;
        }
        @keyframes marquee {
            0% { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }

        /* NAVBAR MENYERUPAI GAMBAR REFERENSI */
        .nav-pill {
            background-color: #F0FFF0 !important;
            border-radius: 9999px; /* Bentuk melingkar (pill) */
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.03);
            border: 1px solid #e2e8f0;
        }
        
        .nav-link {
            color: #14532d; /* Hijau gelap persis seperti menu di gambar */
            font-weight: 700;
            font-size: 0.95rem;
            transition: all 0.2s;
        }
    </style>
</head>

<body class="flex flex-col">

    <header class="w-full mt-4 z-50">
        <div class="max-w-7xl mx-auto px-4 md:px-6">
            <nav class="nav-pill px-6 py-3 md:py-4 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 flex items-center justify-center">
                        <a href="/">
                            <img src="{{ asset('images/logo.png') }}" alt="Logo Al-Ihsan" class="w-full h-full object-cover">
                        </a>
                    </div>
                    <div class="flex flex-col justify-center">
                        <div class="flex items-baseline gap-2">
                            <h1 class="text-lg md:text-xl font-black text-green-900 tracking-tight leading-none" style="color: #064e3b;">Al-Ihsan <span class="font-bold text-green-700">Boarding School</span></h1>
                        </div>
                        <p class="text-[10px] md:text-xs font-bold text-green-800 tracking-[0.2em] mt-1" style="color: #166534;">SHOLEH | PEDULI | MEMIMPIN</p>
                    </div>
                </div>
                
                <div class="flex flex-wrap items-center gap-6 md:gap-8 justify-center">
                    <span class="nav-link flex items-center gap-2">
                        <span>📍</span> Perpustakaan
                    </span>
                    <span class="nav-link flex items-center gap-2">
                        <span>📅</span> {{ now()->translatedFormat('d M Y') }}
                    </span>
                    <span class="nav-link flex items-center gap-2">
                        <span>🕐</span> {{ now()->format('H:i') }}
                    </span>
                </div>
            </nav>
        </div>
    </header>

    <main class="flex-1 pb-16">

        <section class="py-12 md:py-16">
            <div class="max-w-4xl mx-auto px-6 text-center space-y-4">
                <h1 class="font-black uppercase tracking-tight text-3xl sm:text-4xl md:text-5xl text-slate-800 leading-tight">
                    Selamat Datang di <br />
                    Perpustakaan Pondok Pesantren <br />
                    <span style="color: #166534;">Al-Ihsan</span>
                </h1>
                <p class="text-sm sm:text-base md:text-lg font-semibold text-slate-500 tracking-wide">
                    Sistem Kehadiran & Layanan Digital Perpustakaan
                </p>
            </div>
        </section>

        <div class="overflow-hidden mb-12 max-w-7xl mx-auto marquee-container">
            <div class="flex w-max items-center animate-marquee hover:[animation-play-state:paused]">
                <span class="mx-8 py-3 text-xs sm:text-sm font-bold text-green-800 whitespace-nowrap tracking-wide">
                    📢 Selamat Datang di Perpustakaan Al-Ihsan, Silakan Tempelkan Kartu RFID Anda untuk melakukan Absensi Kehadiran. 🌟 Ayo Berkunjung ke Perpustakaan — Ilmu Menanti Anda! 📚
                </span>
                <span class="mx-8 py-3 text-xs sm:text-sm font-bold text-green-800 whitespace-nowrap tracking-wide">
                    📢 Selamat Datang di Perpustakaan Al-Ihsan, Silakan Tempelkan Kartu RFID Anda untuk melakukan Absensi Kehadiran. 🌟 Ayo Berkunjung ke Perpustakaan — Ilmu Menanti Anda! 📚
                </span>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-6">
            <div class="flex justify-center">
                <div class="card-custom w-full max-w-lg rounded-3xl p-8 sm:p-10 flex flex-col items-center text-center gap-8 overflow-hidden border border-green-100">
                    
                    <div class="absolute top-0 right-0 -mr-12 -mt-12 w-40 h-40 rounded-full bg-green-100 opacity-40 blur-3xl"></div>

                    <div class="space-y-2 relative z-10">
                        <h2 class="text-xl sm:text-2xl font-extrabold text-slate-800 tracking-tight">
                            Silakan Tempelkan Kartu RFID Anda
                        </h2>
                        <p class="text-xs sm:text-sm font-semibold text-slate-500">
                            pada reader untuk absensi kehadiran.
                        </p>
                    </div>

                    <div class="relative flex items-center justify-center w-44 h-44 z-10 my-2">
                        <div class="absolute w-44 h-44 rounded-full border-4 border-green-200 opacity-40 animate-ping"></div>
                        <div class="absolute w-32 h-32 rounded-full border-4 border-green-300 opacity-30 animate-ping" style="animation-delay: 0.3s"></div>
                        
                        <div class="relative z-10 rounded-full p-6 shadow-sm border border-slate-100 bg-white">
                            <svg viewBox="0 0 80 80" class="w-20 h-20" xmlns="http://www.w3.org/2000/svg">
                                <rect x="8" y="25" width="50" height="32" rx="5" fill="#F0FFF0" stroke="#166534" stroke-width="2"/>
                                <rect x="16" y="33" width="14" height="10" rx="2" fill="#166534" opacity="0.9"/>
                                <line x1="19" y1="33" x2="19" y2="43" stroke="#F0FFF0" stroke-width="1"/>
                                <line x1="23" y1="33" x2="23" y2="43" stroke="#F0FFF0" stroke-width="1"/>
                                <line x1="27" y1="33" x2="27" y2="43" stroke="#F0FFF0" stroke-width="1"/>
                                <line x1="16" y1="49" x2="45" y2="49" stroke="#166534" stroke-width="1.5" opacity="0.5"/>
                                <line x1="16" y1="53" x2="35" y2="53" stroke="#166534" stroke-width="1.5" opacity="0.3"/>
                                <path d="M62 32 Q68 40 62 48" fill="none" stroke="#166534" stroke-width="2" stroke-linecap="round"/>
                                <path d="M67 27 Q77 40 67 53" fill="none" stroke="#166534" stroke-width="2" stroke-linecap="round" opacity="0.5"/>
                            </svg>
                        </div>
                    </div>

                    <div id="rfid-status" class="w-full max-w-xs py-3 text-xs sm:text-sm font-bold transition-all duration-300 rounded-xl shadow-sm border bg-white border-green-700 text-green-700">
                        ⏳ Menunggu kartu RFID...
                    </div>

                </div>
            </div>
        </div>

    </main>

    <footer class="mt-auto bg-slate-100 border-t border-slate-200 text-slate-500">
        <div class="max-w-7xl mx-auto px-6 py-4">
            <p class="text-center text-xs sm:text-sm font-medium tracking-wide">
                © {{ date('Y') }} <b>Pondok Pesantren Al-Ihsan</b> — Sistem Kehadiran Perpustakaan
            </p>
        </div>
    </footer>

    <dialog id="welcome_modal" class="modal">
        <div class="modal-box relative p-6 rounded-2xl shadow-2xl bg-white border border-green-100">
            <form method="dialog"><button class="btn btn-sm btn-circle btn-ghost absolute right-4 top-4 text-slate-400">✕</button></form>
            <div class="flex justify-center mb-4">
                <div class="bg-green-100 text-green-600 rounded-full w-20 h-20 flex items-center justify-center text-4xl shadow-inner">✅</div>
            </div>
            <h3 class="text-center text-2xl font-black text-slate-800 mb-1">Ahlan Wa Sahlan! 🌙</h3>
            <p class="text-center mb-5 text-slate-500 font-medium text-sm">Absensi kunjungan perpustakaan berhasil dicatat. Selamat belajar!</p>
            <div class="text-center text-sm font-bold p-3.5 rounded-xl bg-green-50 text-green-800 border border-green-200">
                Waktu Kehadiran: <span id="visit_time" class="font-mono text-base">{{ now()->format('H:i') }}</span>
            </div>
        </div>
    </dialog>

    <dialog id="barcode_gagal" class="modal">
        <div class="modal-box relative p-6 rounded-2xl shadow-2xl bg-white border border-red-100">
            <form method="dialog"><button class="btn btn-sm btn-circle btn-ghost absolute right-4 top-4 text-slate-400">✕</button></form>
            <div class="flex justify-center mb-4">
                <div class="bg-red-100 text-red-600 rounded-full w-20 h-20 flex items-center justify-center text-4xl shadow-inner">❌</div>
            </div>
            <h3 class="text-center text-2xl font-black text-red-700 mb-1">Kartu Tidak Dikenal</h3>
            <p class="text-center text-sm text-slate-500 font-medium">Kartu RFID belum terdaftar dalam sistem. Silakan laporkan ke petugas perpustakaan.</p>
        </div>
    </dialog>

    @fluxScripts

    <script>
        // Logika JS tidak diubah sama sekali
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

        function setStatus(text, bgType) {
            const el = document.getElementById('rfid-status');
            if (!el) return;
            el.textContent = text;
            
            if(bgType === 'success') {
                el.style.backgroundColor = '#dcfce7'; 
                el.style.borderColor = '#166534';
                el.style.color = '#166534';
            } else if (bgType === 'error') {
                el.style.backgroundColor = '#fee2e2'; 
                el.style.borderColor = '#b91c1c';
                el.style.color = '#b91c1c';
            } else if (bgType === 'warning') {
                el.style.backgroundColor = '#fef3c7'; 
                el.style.borderColor = '#d97706';
                el.style.color = '#d97706';
            } else {
                el.style.backgroundColor = '#ffffff';
                el.style.borderColor = '#166534';
                el.style.color = '#166534';
            }
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
            .then(async res => {
                // Cek jika server mengembalikan error (misal 500 Internal Server Error)
                if (!res.ok) {
                    const errorText = await res.text();
                    throw new Error(`Server Error (${res.status}): ${errorText}`);
                }
                return res.json();
            })
            .then(data => {
                if (data.type === 'admin') {
                    setStatus('🔐 Admin terdeteksi, masuk...', 'success');
                    window.location.href = data.redirect;
                } else if (data.type === 'member') {
                    setStatus('✅ Absensi Berhasil!', 'success');
                    setTimeout(() => setStatus('⏳ Menunggu kartu RFID...', 'default'), 4000);
                    const modal = document.getElementById('welcome_modal');
                    if (modal) { modal.showModal(); setTimeout(() => modal.close(), 5000); }
                } else {
                    setStatus('❌ Kartu tidak dikenal!', 'error');
                    setTimeout(() => setStatus('⏳ Menunggu kartu RFID...', 'default'), 4000);
                    const modal = document.getElementById('barcode_gagal');
                    if (modal) { modal.showModal(); setTimeout(() => modal.close(), 5000); }
                }
            })
            .catch(err => {
                // TAMPILKAN ERROR DI CONSOLE
                console.error('RFID Process Error:', err);
                setStatus('❌ Kesalahan: ' + err.message.substring(0, 20) + '...', 'error');
                setTimeout(() => setStatus('⏳ Menunggu kartu RFID...', 'default'), 4000);
                
                // Opsional: Tampilkan modal error jika ingin lebih terlihat
                const modal = document.getElementById('barcode_gagal');
                if (modal) { modal.showModal(); }
            });
        }
    </script>

</body>
</html>