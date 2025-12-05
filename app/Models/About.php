<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class About extends Model
{
    use HasFactory;

    // Beritahu Laravel nama tabelnya (karena pakai prefix 'tabel_')
    protected $table = 'tabel_about';

    protected $fillable = [
        'judul', 'deskripsi', 'gambar', 'layout'
    ];
}