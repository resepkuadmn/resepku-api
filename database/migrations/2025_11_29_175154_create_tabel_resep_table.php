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
    Schema::create('tabel_resep', function (Blueprint $table) {
        $table->id();
        $table->string('resep_id_string')->unique(); // Slug untuk URL (misal: nasi-goreng-spesial)
        $table->string('judul');
        $table->string('gambar')->nullable();
        $table->string('waktu'); // misal: "30 Menit"
        $table->string('porsi'); // misal: "2 Porsi"
        $table->text('bahan');   // Kita simpan format HTML/JSON di sini
        $table->text('cara_membuat');
        $table->string('jenis'); // 'makanan', 'minuman', 'dessert'
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
        Schema::dropIfExists('tabel_resep');
    }
};
