<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Service\ApiSyncService;

class SyncController extends Controller
{
    /**
     * Trigger sync manual via HTTP request (bisa dipanggil dari tombol/cron eksternal).
     */
    public function triggerSync(ApiSyncService $syncService)
    {
        $result = $syncService->sync();

        if ($result['failed']) {
            return response()->json([
                'message' => 'Gagal sinkronisasi dari API IBS. Cek log server.',
            ], 500);
        }

        return response()->json([
            'message' => "Sync berhasil! {$result['synced']} data member diperbarui.",
            'synced'  => $result['synced'],
        ]);
    }
}