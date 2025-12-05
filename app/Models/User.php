<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

// [PENTING 1] Panggil Library Sanctum di sini
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    // [PENTING 2] Masukkan fitur token ke dalam class User
    // Pastikan 'HasApiTokens' tertulis di baris ini
    use HasApiTokens, HasFactory, Notifiable;

    // Nama tabel di database Anda
    protected $table = 'tabel_users';

    protected $fillable = [
        'username',
        'email',
        'password',
        'role',
        'reset_token',
        'token_expiry',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
}