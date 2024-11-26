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
        Schema::create('tb_rak', function (Blueprint $table) {
            $table->id('id_rak');
            $table->string('kode_rak', 20);
            $table->string('nama_rak', 100);
            $table->string('lokasi_rak');
            $table->timestamp('tanggal_dibuat')->nullable();
	        $table->timestamp('tanggal_update')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_rak');
    }
};
