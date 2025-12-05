<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tips extends Model
{
    use HasFactory;

    protected $table = 'tabel_tips';

    protected $fillable = [
        'artikel_id_string', 'judul', 'gambar', 'konten'
    ];
}