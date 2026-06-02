<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransaksiLokalPerpus extends Model
{
    protected $table = 'transaksi_lokal_perpus';
    protected $guarded = []; // Ganti $fillable dengan ini untuk sementara

    // Relasi ke Denda
    public function fine()
    {
        return $this->belongsTo(Fine::class, 'fine_id');
    }

    // Relasi ke Member
    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id');
    }
}