<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations. (Membuat Tabel)
     */
    public function up(): void
    {
        // 1. Tabel Peminjaman (Loans)
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('members')->onDelete('cascade');
            $table->string('book_title'); 
            $table->string('book_code'); 
            $table->date('borrow_date');
            $table->date('due_date');
            $table->date('return_date')->nullable();
            $table->integer('extension_count')->default(0); 
            $table->enum('status', ['dipinjam', 'kembali', 'hilang'])->default('dipinjam');
            $table->timestamps();
        });

        // 2. Tabel Denda (Fines)
        Schema::create('fines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained('loans')->onDelete('cascade');
            $table->foreignId('member_id')->constrained('members');
            $table->integer('total_fines');
            $table->enum('payment_status', ['belum_bayar', 'lunas'])->default('belum_bayar');
            $table->timestamps();
        });

        // 3. Tabel Surat Bebas Pustaka (Clearance)
        Schema::create('clearance_letters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('members');
            $table->string('letter_number')->unique();
            $table->date('issue_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations. (Menghapus tabel jika terjadi kesalahan)
     */
    public function down(): void
    {
        Schema::dropIfExists('clearance_letters');
        Schema::dropIfExists('fines');
        Schema::dropIfExists('loans');
    }
};