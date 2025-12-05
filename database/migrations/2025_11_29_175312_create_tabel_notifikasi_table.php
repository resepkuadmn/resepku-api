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
    Schema::create('tabel_notifikasi', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained('tabel_users')->onDelete('cascade');
        $table->text('pesan');
        $table->enum('status', ['unread', 'read'])->default('unread');
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
        Schema::dropIfExists('tabel_notifikasi');
    }
};
