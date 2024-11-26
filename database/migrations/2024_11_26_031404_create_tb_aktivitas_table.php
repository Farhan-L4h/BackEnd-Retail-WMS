<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tb_aktivitas', function (Blueprint $table) {
            $table->id('id_aktivitas');
            $table->unsignedBigInteger('id_barang')->index();
            $table->unsignedBigInteger('id_user')->index();
            $table->unsignedBigInteger('id_rak')->index();
            $table->date('exp_barang');
            $table->integer('jumlah_barang');
            $table->integer('total_harga');
            $table->enum('status', ['masuk', 'keluar'])->default('masuk');
            $table->timestamp('tanggal_dibuat')->nullable();
	        $table->timestamp('tanggal_update')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_aktivitas');
    }
};
