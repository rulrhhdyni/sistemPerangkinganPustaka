<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Bebas Pustaka - {{ $nama }}</title>
    <style>
        @page { 
            margin: 2.5cm 3cm; 
        }
        body { 
            font-family: "Times New Roman", Times, serif; 
            font-size: 12pt; 
            line-height: 1.5; 
            color: #000;
        }
        
        /* KOP SURAT */
        .kop-surat { 
            width: 100%; 
            margin-bottom: 5px; 
            border-collapse: collapse;
        }
        .kop-surat td { 
            vertical-align: middle; 
        }
        .logo { 
            width: 90px; 
            height: auto; 
        }
        .instansi { 
            text-align: center; 
        }
        .instansi p.yayasan { 
            font-size: 12pt; 
            font-weight: bold; 
            margin: 0; 
        }
        /* Penyesuaian H1 agar 1 baris lurus */
        .instansi h1 { 
            font-size: 14pt; 
            margin: 2px 0; 
            font-weight: bold; 
            white-space: nowrap; 
        }
        .instansi p.alamat { 
            font-size: 10pt; 
            margin: 0; 
            line-height: 1.3; 
        }
        .garis-kop { 
            border-bottom: 3px solid #000; 
            border-top: 1px solid #000; 
            height: 2px; 
            margin-bottom: 25px; 
            margin-top: 5px;
        }

        /* JUDUL SURAT */
        .judul-surat { 
            text-align: center; 
            margin-bottom: 30px; 
        }
        .judul-surat h3 { 
            font-size: 13pt; 
            margin: 0; 
            text-decoration: underline; 
            font-weight: bold; 
        }
        .judul-surat p { 
            font-size: 11pt; 
            margin: 5px 0 0 0; 
        }

        /* ISI SURAT */
        .isi-surat { 
            text-align: justify; 
            margin-bottom: 20px; 
        }
        .tabel-biodata { 
            margin-left: 0px; 
            margin-top: 10px;
            margin-bottom: 10px; 
            width: 100%; 
        }
        .tabel-biodata td { 
            padding: 3px 0; 
            vertical-align: top; 
        }
        .col-label { width: 120px; }
        .col-titik { width: 15px; text-align: left; }

        /* Paragraf rata kiri, tidak menjorok ke dalam */
        .paragraf-isi {
            text-indent: 0px; 
            margin-top: 10px;
            margin-bottom: 10px;
        }

        /* TANDA TANGAN */
        .tabel-ttd {
            width: 100%;
            margin-top: 30px;
            page-break-inside: avoid;
        }
        .ttd-area {
            position: relative;
            height: 110px; 
        }
        .img-stempel {
            position: absolute;
            left: -40px; 
            top: -10px;
            width: 120px;
            z-index: 1;
            opacity: 0.9;
        }
        .img-ttd {
            position: absolute;
            left: 10px;
            top: 5px;
            width: 110px;
            z-index: 2;
        }
    </style>
</head>
<body>

    <!-- KOP SURAT -->
    <table class="kop-surat">
        <tr>
            <td width="15%" style="text-align: left;">
                <img src="{{ public_path('images/perpus-logo.png') }}" class="logo" alt="Logo">
            </td>
            <td width="70%" class="instansi">
                <p class="yayasan">YAYASAN WAKAF AL-IHSAN RIAU</p>
                <h1>PERPUSTAKAAN AL-IHSAN BOARDING SCHOOL</h1>
                <p class="alamat">
                    JL. PESANTREN RT 03/ RW 04, DUSUN IV, DESA KUBANG JAYA,<br>
                    KEC. SIAK HULU, KAB. KAMPAR, RIAU<br>
                    KODE POS : 28452 TELP. 0857 6305 5661
                </p>
            </td>
            <td width="15%"></td> 
        </tr>
    </table>
    <div class="garis-kop"></div>

    <!-- JUDUL SURAT -->
    <div class="judul-surat">
        <h3>SURAT KETERANGAN BEBAS PUSTAKA</h3>
        <p>No. &nbsp; &nbsp; &nbsp; &nbsp; /PERPUS/IBS/20{{ \Carbon\Carbon::now()->format('y') }}</p>
    </div>

    <!-- ISI SURAT -->
    <div class="isi-surat">
        <p>Perpustakaan Al-Ihsan Riau, dengan ini menyatakan bahwa :</p>

        <table class="tabel-biodata">
            <tr>
                <td class="col-label">Nama</td>
                <td class="col-titik">:</td>
                <td style="text-transform: uppercase; font-weight: bold;">{{ $nama }}</td>
            </tr>
            <tr>
                <td class="col-label">Kelas</td>
                <td class="col-titik">:</td>
                <td style="text-transform: uppercase;">{{ $kelas }}</td>
            </tr>
            <tr>
                <td class="col-label">No Anggota</td>
                <td class="col-titik">:</td>
                <td>{{ $identitas }}</td>
            </tr>
        </table>

        <p class="paragraf-isi">
            Terhitung sejak surat ini dikeluarkan, dinyatakan telah bebas dari pinjaman buku dan koleksi lainnya di Perpustakaan Al-Ihsan Riau.
        </p>
        <p class="paragraf-isi">
            Demikian <b>surat keterangan</b> ini dibuat untuk digunakan sebagaimana mestinya.
        </p>
    </div>

    <!-- TANDA TANGAN & STEMPEL -->
    <table class="tabel-ttd">
        <tr>
            <td width="55%"></td> 
            <td width="45%" style="text-align: left;">
                <p style="margin: 0;">Siak Hulu, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
                <p style="margin: 0; margin-bottom: 5px;">Kepala Perpustakaan,</p>
                
                <div class="ttd-area">
                    <img src="{{ public_path('images/stempel.png') }}" class="img-stempel" alt="Stempel">
                    <img src="{{ public_path('images/sign.png') }}" class="img-ttd" alt="Tanda Tangan">
                </div>
                
                <p style="margin: 0; font-weight: bold; text-decoration: underline;">Fadilah Sari, S.S.I., M.M</p>
            </td>
        </tr>
    </table>

</body>
</html>