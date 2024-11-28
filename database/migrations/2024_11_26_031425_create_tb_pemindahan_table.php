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
        Schema::create('tb_pemindahan', function (Blueprint $table) {
            $table->id('id');
            $table->unsignedBigInteger('id_barang')->index();
            $table->unsignedBigInteger('id_rak_asal')->index();
            $table->unsignedBigInteger('id_rak_tujuan')->index();
            $table->integer('jumlah_pindah');
            $table->timestamp('tanggal_dibuat')->nullable();
	        $table->timestamp('tanggal_update')->nullable();

            // Relasi
            $table->foreign('id_barang')->references('id')->on('tb_barang');
            $table->foreign('id_rak_asal')->references('id')->on('tb_rak');
            $table->foreign('id_rak_tujuan')->references('id')->on('tb_rak'); // Ubah ke id_rak_tujuan

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_pemindahan');
    }
};
