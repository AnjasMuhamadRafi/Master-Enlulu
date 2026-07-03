<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bpu_tk', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_identitas', 16)->unique()->comment('NIK KTP 16 digit');
            $table->string('nama_lengkap')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('handphone', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('jenis_pekerjaan_1')->nullable();
            $table->string('jenis_pekerjaan_2')->nullable();
            $table->string('lokasi_pekerjaan')->nullable();
            $table->unsignedBigInteger('upah')->nullable();
            $table->string('kode_paket', 10)->nullable()->default('T');
            $table->unsignedSmallInteger('bulan_iuran')->nullable()->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bpu_tk');
    }
};
