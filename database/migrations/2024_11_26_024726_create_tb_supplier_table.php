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
        Schema::create('tb_supplier', function (Blueprint $table) {
            $table->id('id_supplier');
            $table->string('nama_supplier', 100);
            $table->string('kontak');
            $table->string('alamat');
            $table->timestamp('tanggal_dibuat')->nullable();
	        $table->timestamp('tanggal_update')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_supplier');
    }
};
