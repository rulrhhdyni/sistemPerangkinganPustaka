<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    protected $fillable = [
        'member_id',
        'book_code',
        'book_title',
        'borrow_date',
        'due_date',
        'return_date',
        'extension_count',
        'status'
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function fines()
    {
        return $this->hasMany(Fine::class);
    }
}