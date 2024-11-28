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
            $table->id('id');
            $table->string('image')->nullable();
            $table->unsignedBigInteger('id_kategori')->index();
            $table->unsignedBigInteger('id_supplier')->index();
            $table->string('nama_barang', 100);
            $table->string('deskripsi');
            $table->integer('harga');
            $table->timestamp('tanggal_dibuat')->nullable();
	        $table->timestamp('tanggal_update')->nullable();

             // relasi
            $table->foreign('id_kategori')->references('id')->on('tb_kategori');
            $table->foreign('id_supplier')->references('id')->on('tb_supplier');
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
