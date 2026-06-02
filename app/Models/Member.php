<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    protected $table = 'members';

    // Hanya menyimpan rfid_code dan type sesuai arsitektur baru
    protected $fillable = [
        'rfid_code',
        'type',
    ];

    public function loans()
    {
        return $this->hasMany(\App\Models\Loan::class);
    }

    public function fines()
    {
        return $this->hasMany(\App\Models\Fine::class);
    }

    /**
     * Fallback nama jika dipanggil langsung tanpa mapping API
     */
    public function getDisplayNameAttribute(): string
    {
        return 'RFID: ' . ($this->rfid_code ?? '-');
    }

    public function getNameAttribute(): string
    {
        return $this->getDisplayNameAttribute();
    }

    /**
     * Fallback URL foto
     */
    public function getDisplayFotoAttribute(): string
    {
        return 'https://ui-avatars.com/api/?name=Member&background=random';
    }

    public function getFotoAttribute(): string
    {
        return $this->getDisplayFotoAttribute();
    }

    /**
     * Fallback kontak
     */
    public function getDisplayPhoneAttribute(): string
    {
        return '-';
    }

    public function getPhoneAttribute(): string
    {
        return $this->getDisplayPhoneAttribute();
    }
}