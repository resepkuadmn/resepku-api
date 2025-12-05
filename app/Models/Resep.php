<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Resep extends Model
{
    use HasFactory;

    protected $table = 'tabel_resep';

    protected $fillable = [
        'resep_id_string', 'judul', 'gambar', 'waktu', 
        'porsi', 'bahan', 'cara_membuat', 'jenis'
    ];
}