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
    Schema::create('tabel_pengunjung', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->nullable()->constrained('tabel_users')->onDelete('set null');
        $table->string('ip_address');
        $table->date('tanggal');
        $table->string('user_type')->default('guest'); // guest, member, admin
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
        Schema::dropIfExists('tabel_pengunjung');
    }
};
