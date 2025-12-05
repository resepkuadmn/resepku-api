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
    Schema::create('tabel_request', function (Blueprint $table) {
        $table->id();
        // Relasi ke tabel_users
        $table->foreignId('user_id')->constrained('tabel_users')->onDelete('cascade');
        
        $table->string('username')->nullable(); // Opsional, krn sudah ada relasi user_id
        $table->string('email')->nullable();    // Opsional
        $table->string('resep_diminta');
        $table->string('jenis');
        $table->enum('status', ['pending', 'disetujui', 'ditolak'])->default('pending');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tabel_request');
    }
};
