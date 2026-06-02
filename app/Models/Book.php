<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    // Mengizinkan kolom-kolom ini untuk diisi melalui sistem
    protected $fillable = [
        'book_code', 
        'title', 
        'status'
    ];

    // Opsional: Jika Anda ingin membuat relasi ke Loan
    public function loans()
    {
        return $this->hasMany(Loan::class);
    }
}