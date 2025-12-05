<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
{
    Schema::create('tabel_about', function (Blueprint $table) {
        $table->id();
        $table->string('judul');
        $table->text('deskripsi'); // Menggunakan 'text' agar bisa menampung tulisan panjang
        $table->string('gambar')->nullable(); // Nullable: jaga-jaga jika gambar belum diupload
        $table->string('layout'); // Untuk menyimpan opsi 'kiri' atau 'kanan'
        $table->timestamps(); // created_at dan updated_at
    });
}

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tabel_about');
    }
};
