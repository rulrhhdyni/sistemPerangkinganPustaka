<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$db = DB::connection('ibs');
$tables = ['nasabahs', 'nasabah_santris', 'nasabah_pegawais', 'master_simpanans'];
foreach ($tables as $table) {
    echo "=== $table ===\n";
    try {
        $cols = $db->select("DESCRIBE $table");
        foreach ($cols as $col) {
            echo "{$col->Field} ({$col->Type})\n";
        }
    } catch (\Exception $e) {
        echo $e->getMessage() . "\n";
    }
}
