<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            // Hapus kolom id_server jika ada
            if (Schema::hasColumn('members', 'id_server')) {
                $table->dropColumn('id_server');
            }
            
            // Daftar kolom profil lama yang akan dihapus
            $columns = [
                'nama', 'foto', 'no_telp', 'kelas', 'nis_lokal', 
                'alamat', 'no_hp', 'no_rekening', 'saldo_efektif', 'synced_at'
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('members', $column)) {
                    $table->dropColumn($column);
                }
            }

            // Ubah rfid_code menjadi unique identifier utama
            $table->string('rfid_code')->unique()->change();
        });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->string('id_server')->nullable();
            $table->string('nama')->nullable();
            $table->string('foto')->nullable();
            $table->string('no_telp')->nullable();
            $table->string('kelas')->nullable();
            $table->string('nis_lokal')->nullable();
            $table->text('alamat')->nullable();
            $table->string('no_hp')->nullable();
            $table->string('no_rekening')->nullable();
            $table->bigInteger('saldo_efektif')->default(0);
            $table->timestamp('synced_at')->nullable();
            
            $table->dropUnique(['rfid_code']);
        });
    }
};