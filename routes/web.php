<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;

Route::get('/test-email', function () {
    try {
        // GANTI dengan email penerima asli Anda
        $penerima = 'email_anda@gmail.com'; 
        
        Mail::raw('Ini adalah email tes dari Laravel Resepku. Jika Anda membaca ini, berarti settingan SMTP sudah BENAR 100%!', function ($message) use ($penerima) {
            $message->to($penerima)
                    ->subject('Tes Koneksi Email Resepku');
        });

        return "<h1>SUKSES! ✅</h1> <p>Email berhasil dikirim. Silakan cek inbox/spam Anda.</p>";
    } catch (\Exception $e) {
        return "<h1>GAGAL! ❌</h1> <p>Penyebab error:</p> <pre style='color:red; background:#eee; padding:10px;'>" . $e->getMessage() . "</pre>";
    }
});

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
