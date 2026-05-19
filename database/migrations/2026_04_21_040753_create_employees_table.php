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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('nik')->unique(); // Nomor Identitas Karyawan
            $table->string('nama_lengkap'); // Nama Lengkap
            $table->string('posisi')->nullable(); // Posisi/Jabatan
            $table->string('penempatan')->nullable(); // Lokasi Penempatan
            $table->string('no_rekening')->nullable(); // Nomor Rekening
            $table->string('nama_bank')->nullable(); // Nama Bank
            $table->string('nama_di_rekening')->nullable(); // Nama di Rekening
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
