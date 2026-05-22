<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Member extends Model
{
    protected $table = 'members';
    
    protected $fillable = [
        'slims_member_id',
        'name',
        'email',
        'phone',
        'address',
        'type',
        'register_date',
        'expire_date',
        'image',
    ];

    // TAMBAHAN RELASI ↓
    public function loans()
    {
        return $this->hasMany(\App\Models\Loan::class);
    }

    public function fines()
    {
        return $this->hasMany(\App\Models\Fine::class);
    }
}