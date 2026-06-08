<?php

namespace App\Service;

use App\Models\Member;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ApiSyncService
{
    protected string $baseUrl;
    protected string $token;

    public function __construct()
    {
        $this->baseUrl = config('services.ibs_api.url');
        $this->token   = config('services.ibs_api.token');
    }

    /**
     * Sinkronisasi Cerdas (Differential Sync)
     */
    public function sync(): array
    {
        try {
            // 1. Tarik data dari API
            $response = Http::withToken($this->token)
                            ->timeout(15)
                            ->get($this->baseUrl . 'master-simpanans', [
                                'per_page' => 5000,
                                'limit'    => 5000
                            ]);

            if (!$response->successful()) {
                Log::error('[ApiSyncService] Gagal fetch data RFID dari API IBS');
                return ['synced' => 0, 'failed' => true, 'message' => 'Gagal menghubungi server pusat.'];
            }

            $apiData = $response->json('data.data') ?? [];
            if (empty($apiData) && !empty($response->json('data'))) {
                 $apiData = $response->json('data'); 
                 if (isset($apiData['data'])) $apiData = $apiData['data'];
            }

            if (empty($apiData)) {
                return ['synced' => 0, 'failed' => false, 'message' => 'Data dari server pusat kosong.'];
            }

            // 2. Ambil data lokal (HANYA Santri dan Pegawai agar Admin/Guest manual tidak ikut terhapus)
            $localMembers = Member::whereIn('type', ['Santri', 'Pegawai'])
                                  ->select('id', 'rfid_code', 'type')
                                  ->get()
                                  ->keyBy('rfid_code');

            $toInsert = [];
            $toUpdate = [];
            $rfidDariApi = [];

            // 3. Pilah mana data BARU dan mana data BERUBAH
            foreach ($apiData as $item) {
                // Pastikan RFID berupa string dan tidak ada spasi tersembunyi
                $rfid = trim((string) ($item['rfid_code'] ?? ''));
                if ($rfid === '') continue;

                $nasabah = $item['nasabah'] ?? [];
                $jenisNasabah = $nasabah['jenis_nasabah'] ?? [];
                
                // FILTER: Hanya ambil kode 01 dan 02
                $kodeJenis = $jenisNasabah['kode'] ?? '';
                if (!in_array($kodeJenis, ['01', '02'])) {
                    continue;
                }

                $rfidDariApi[] = $rfid; // Catat RFID yang valid dari server

                // Normalisasi penamaan tipe (Mencegah false-update karena huruf besar/kecil)
                $namaKomponen = strtolower($jenisNasabah['nama_komponen'] ?? '');
                $type = str_contains($namaKomponen, 'santri') ? 'Santri' : 'Pegawai';

                if (!$localMembers->has($rfid)) {
                    // Jika RFID tidak ada di lokal -> TAMBAH DATA BARU
                    $toInsert[] = [
                        'rfid_code'  => $rfid,
                        'type'       => $type,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                } else {
                    // Jika RFID ada di lokal, periksa apakah tipe-nya berubah
                    $local = $localMembers->get($rfid);
                    if ($local->type !== $type) {
                        $toUpdate[] = [
                            'rfid_code'  => $rfid,
                            'type'       => $type,
                            'updated_at' => now(),
                        ];
                    }
                }
            }

            // Hapus duplikasi RFID di memori jika API mengembalikan data ganda
            $rfidDariApi = array_unique($rfidDariApi);

            // 4. Cari data yang DIHAPUS (Ada di lokal, tapi tidak ada di API)
            $rfidToDelete = $localMembers->keys()->diff($rfidDariApi)->toArray();

            $totalPerubahan = count($toInsert) + count($toUpdate) + count($rfidToDelete);

            // Jika tidak ada perubahan sama sekali, berhentikan proses
            if ($totalPerubahan === 0) {
                return ['synced' => 0, 'failed' => false, 'message' => 'Data sudah up-to-date. Tidak ada perubahan.'];
            }

            // 5. Eksekusi ke Database Lokal
            DB::beginTransaction();
            try {
                // Proses Tambah
                if (!empty($toInsert)) {
                    foreach (array_chunk($toInsert, 500) as $chunk) {
                        Member::insert($chunk);
                    }
                }

                // Proses Ubah
                if (!empty($toUpdate)) {
                    foreach (array_chunk($toUpdate, 500) as $chunk) {
                        Member::upsert($chunk, ['rfid_code'], ['type', 'updated_at']);
                    }
                }

                // Proses Hapus
                if (!empty($rfidToDelete)) {
                    Member::whereIn('rfid_code', $rfidToDelete)->delete();
                }

                DB::commit();

                $pesan = sprintf("%d ditambah, %d diubah, %d dihapus.", count($toInsert), count($toUpdate), count($rfidToDelete));
                Log::info('[ApiSyncService] Sync berhasil: ' . $pesan);

                return ['synced' => $totalPerubahan, 'failed' => false, 'message' => $pesan];

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('[ApiSyncService] Gagal memproses database lokal: ' . $e->getMessage());
                return ['synced' => 0, 'failed' => true, 'message' => 'Terjadi kesalahan sistem saat menyimpan data.'];
            }

        } catch (\Exception $e) {
            Log::error('[ApiSyncService] Exception saat tarik API: ' . $e->getMessage());
            return ['synced' => 0, 'failed' => true, 'message' => 'Gagal terhubung ke API.'];
        }
    }
}