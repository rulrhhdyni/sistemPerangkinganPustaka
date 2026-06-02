<?php
// Bootstrap Laravel
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== DATABASE: cashless_ibs ===\n";
echo "=== ALL TABLES ===\n";
$tables = DB::connection('ibs')->select("SHOW TABLES");
foreach ($tables as $t) {
    $vals = (array)$t;
    echo " - " . array_values($vals)[0] . "\n";
}

echo "\n=== SAMPLE nasabahs ===\n";
try {
    $rows = DB::connection('ibs')->table('nasabahs')->limit(3)->get();
    foreach ($rows as $r) {
        echo json_encode($r) . "\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== SAMPLE nasabah_santris ===\n";
try {
    $rows = DB::connection('ibs')->table('nasabah_santris')->limit(2)->get();
    foreach ($rows as $r) {
        echo json_encode($r) . "\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== SAMPLE nasabah_pegawais ===\n";
try {
    $rows = DB::connection('ibs')->table('nasabah_pegawais')->limit(2)->get();
    foreach ($rows as $r) {
        echo json_encode($r) . "\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== SAMPLE master_simpanans (first 3) ===\n";
try {
    $rows = DB::connection('ibs')->table('master_simpanans')->limit(3)->get();
    foreach ($rows as $r) {
        echo json_encode($r) . "\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
