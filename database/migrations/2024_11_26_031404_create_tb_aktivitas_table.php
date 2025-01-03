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
            $table->id('id');
            $table->unsignedBigInteger('id_barang')->index();
            $table->string('username');
            $table->unsignedBigInteger('id_rak')->index();
            $table->date('exp_barang')->nullable();
            $table->integer('jumlah_barang');
            $table->integer('harga_barang');
            $table->integer('total_harga');
            $table->enum('status', ['masuk', 'keluar'])->default('masuk');
            $table->enum('alasan', ['diterima','diambil', 'return', 'dibuang'])->default('diterima');
            $table->timestamp('tanggal_dibuat')->nullable();
	        $table->timestamp('tanggal_update')->nullable();

            // relasi
            $table->foreign('id_barang')->references('id')->on('tb_barang');
            $table->foreign('id_rak')->references('id')->on('tb_rak');
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
