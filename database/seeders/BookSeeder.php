<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Book;

class BookSeeder extends Seeder
{
    public function run()
    {
        Book::create(['book_code' => 'B001', 'title' => 'Pemrograman Laravel', 'status' => 'tersedia']);
        Book::create(['book_code' => 'B002', 'title' => 'Algoritma Pemrograman', 'status' => 'tersedia']);
        Book::create(['book_code' => 'B003', 'title' => 'Sistem Informasi', 'status' => 'tersedia']);
    }
}