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
        Schema::create('tb_barang', function (Blueprint $table) {
            $table->id('id_barang');
            $table->string('image')->nullable();
            $table->unsignedBigInteger('id_kategori')->index();
            $table->unsignedBigInteger('id_supplier')->index();
            $table->string('nama_barang', 100);
            $table->string('deskripsi');
            $table->integer('harga');
            $table->timestamp('tanggal_dibuat')->nullable();
	        $table->timestamp('tanggal_update')->nullable();

             // relasi
            $table->foreign('id_kategori')->references('id_kategori')->on('tb_kategori');
            $table->foreign('id_supplier')->references('id_supplier')->on('tb_supplier');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_barang');
    }
};
