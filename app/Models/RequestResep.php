<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestResep extends Model
{
    use HasFactory;

    protected $table = 'tabel_request';

    protected $fillable = [
        'user_id', 'username', 'email', 'resep_diminta', 'jenis', 'status'
    ];

    // Relasi: Request ini milik siapa?
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}