<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Bebas Pustaka - {{ $member->name }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Times New Roman', serif; padding: 40px; color: #000; }
        .header { text-align: center; border-bottom: 3px double #000; padding-bottom: 10px; margin-bottom: 20px; }
        .header h2 { font-size: 16px; text-transform: uppercase; }
        .header h1 { font-size: 22px; font-weight: bold; text-transform: uppercase; }
        .header p { font-size: 12px; }
        .title { text-align: center; margin: 30px 0 20px; }
        .title h3 { font-size: 18px; font-weight: bold; text-decoration: underline; text-transform: uppercase; }
        .status-box { text-align: center; margin: 20px 0; padding: 15px; border: 2px solid #000; display: inline-block; width: 100%; }
        .status-clear { background: #d4edda; color: #155724; font-size: 20px; font-weight: bold; }
        .status-not-clear { background: #f8d7da; color: #721c24; font-size: 20px; font-weight: bold; }
        .info-table { width: 100%; margin: 20px 0; }
        .info-table td { padding: 5px 10px; font-size: 14px; }
        .info-table td:first-child { width: 180px; font-weight: bold; }
        .warning-list { margin: 15px 0; padding: 15px; border: 1px solid #ccc; background: #fff3cd; }
        .warning-list p { font-weight: bold; margin-bottom: 8px; }
        .warning-list ul { padding-left: 20px; font-size: 14px; }
        .signature { margin-top: 60px; display: flex; justify-content: space-between; }
        .signature-box { text-align: center; width: 200px; }
        .signature-box .line { border-top: 1px solid #000; margin-top: 60px; padding-top: 5px; font-size: 13px; }
        .print-btn { text-align: center; margin: 30px 0; }
        .print-btn button { padding: 10px 30px; background: #007bff; color: white; border: none; border-radius: 5px; font-size: 16px; cursor: pointer; margin: 0 5px; }
        .print-btn .back-btn { background: #6c757d; }
        @media print {
            .print-btn { display: none; }
            body { padding: 20px; }
        }
    </style>
</head>
<body>

    {{-- Tombol Aksi --}}
    <div class="print-btn">
        <button onclick="window.print()">🖨️ Cetak Surat</button>
        <button class="back-btn" onclick="window.history.back()">← Kembali</button>
    </div>

    {{-- Header Surat --}}
    <div class="header">
        <h2>Perpustakaan</h2>
        <h1>Pesantren Al-Ihsan Boarding School</h1>
        <p>Jl. Pesantren RT.03/RW.04, Dusun IV, Desa Kubang Jaya Kec. Siak Hulu Kab. Kampar – Riau | Telp: 0811-7685-185</p>
    </div>

    {{-- Judul --}}
    <div class="title">
        <h3>Surat Keterangan Bebas Pustaka</h3>
    </div>

    {{-- Status --}}
    <div class="status-box {{ $isClear ? 'status-clear' : 'status-not-clear' }}">
        @if($isClear)
            ✅ BEBAS PUSTAKA
        @else
            ❌ BELUM BEBAS PUSTAKA
        @endif
    </div>

    {{-- Info Santri --}}
    <table class="info-table">
        <tr>
            <td>Nama</td>
            <td>: {{ $member->name }}</td>
        </tr>
        <tr>
            <td>No. Anggota</td>
            <td>: {{ $member->slims_member_id ?? '-' }}</td>
        </tr>
        <tr>
            <td>Tanggal Cek</td>
            <td>: {{ \Carbon\Carbon::now()->isoFormat('D MMMM Y') }}</td>
        </tr>
        <tr>
            <td>Status</td>
            <td>: {{ $isClear ? 'Bebas Pustaka' : 'Belum Bebas Pustaka' }}</td>
        </tr>
    </table>

    {{-- Peringatan jika belum bebas --}}
    @if(!$isClear)
        <div class="warning-list">
            <p>⚠️ Keterangan Tanggungan:</p>
            <ul>
                @if($activeLoans > 0)
                    <li>Masih memiliki <strong>{{ $activeLoans }} buku</strong> yang belum dikembalikan</li>
                @endif
                @if($unpaidFines > 0)
                    <li>Masih memiliki <strong>{{ $unpaidFines }} denda</strong> yang belum dibayar</li>
                @endif
            </ul>
        </div>
    @else
        <p style="margin: 15px 0; font-size: 14px;">
            Yang bersangkutan dinyatakan telah mengembalikan seluruh buku pinjaman
            dan telah melunasi seluruh denda perpustakaan.
        </p>
    @endif

    {{-- Tanda Tangan --}}
    <div class="signature">
        <div class="signature-box">
            <p style="font-size: 13px;">Mengetahui,</p>
            <div class="line">Kepala Perpustakaan</div>
        </div>
        <div class="signature-box">
            <p style="font-size: 13px;">{{ \Carbon\Carbon::now()->isoFormat('D MMMM Y') }}</p>
            <div class="line">Petugas Perpustakaan</div>
        </div>
    </div>

</body>
</html>