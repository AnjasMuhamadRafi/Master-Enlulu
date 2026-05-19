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
        // Drop the old employees table
        Schema::dropIfExists('employees');
        
        // Create new employees table with NIK as primary key
        Schema::create('employees', function (Blueprint $table) {
            $table->char('nik', 16)->primary(); // NIK as primary key, exactly 16 characters
            $table->string('nama_lengkap'); // Nama Lengkap (required)
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
        
        // Recreate old table structure
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('nik')->unique();
            $table->string('nama_lengkap');
            $table->string('posisi')->nullable();
            $table->string('penempatan')->nullable();
            $table->string('no_rekening')->nullable();
            $table->string('nama_bank')->nullable();
            $table->string('nama_di_rekening')->nullable();
            $table->timestamps();
        });
    }
};
