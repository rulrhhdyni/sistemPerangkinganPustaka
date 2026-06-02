<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            // Data dari nasabahs
            $table->string('nama')->nullable()->after('type');
            $table->string('foto')->nullable()->after('nama');
            $table->string('no_telp')->nullable()->after('foto');

            // Data dari nasabah_santris (jika type = Santri)
            $table->string('kelas')->nullable()->after('no_telp');
            $table->string('nis_lokal')->nullable()->after('kelas');
            $table->text('alamat')->nullable()->after('nis_lokal');

            // Data dari nasabah_pegawais (jika type = Pegawai)
            $table->string('no_hp')->nullable()->after('alamat');

            // Data dari master_simpanans
            $table->string('no_rekening')->nullable()->after('no_hp');
            $table->bigInteger('saldo_efektif')->default(0)->after('no_rekening');

            // Timestamp sinkronisasi terakhir
            $table->timestamp('synced_at')->nullable()->after('saldo_efektif');
        });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn([
                'nama', 'foto', 'no_telp',
                'kelas', 'nis_lokal', 'alamat',
                'no_hp', 'no_rekening', 'saldo_efektif',
                'synced_at',
            ]);
        });
    }
};
