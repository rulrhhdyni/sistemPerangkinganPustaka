<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Step 1: Drop unique key lama dan rename
        Schema::table('members', function (Blueprint $table) {
            $table->dropUnique('members_slims_member_id_unique');
            $table->renameColumn('slims_member_id', 'id_server');
        });

        // Step 2: Modifikasi kolom dan hapus kolom yang tidak diperlukan
        Schema::table('members', function (Blueprint $table) {
            $table->string('id_server')->nullable()->change(); // Nullable untuk registrasi manual
            $table->string('rfid_code')->nullable()->after('id_server');
            
            // Menghapus kolom lama
            $table->dropColumn(['register_date', 'expire_date', 'image']);

            // Membuat index unik baru
            $table->unique('id_server');
        });
    }

    public function down()
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropUnique(['id_server']);
            $table->renameColumn('id_server', 'slims_member_id');
        });

        Schema::table('members', function (Blueprint $table) {
            $table->string('slims_member_id')->nullable(false)->change();
            $table->dropColumn('rfid_code');
            $table->date('register_date')->nullable();
            $table->date('expire_date')->nullable();
            $table->string('image')->nullable();
            $table->unique('slims_member_id');
        });
    }
};