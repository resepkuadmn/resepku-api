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
    Schema::create('tabel_tips', function (Blueprint $table) {
        $table->id();
        $table->string('artikel_id_string')->unique(); // Unique agar URL artikel tidak kembar
        $table->string('judul');
        $table->string('gambar')->nullable();
        $table->text('konten'); // Menggunakan 'text' karena isinya HTML (<ul><li>..)
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
        Schema::dropIfExists('tabel_tips');
    }
};
