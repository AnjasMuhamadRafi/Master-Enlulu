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
        Schema::table('employees', function (Blueprint $table) {
            $table->string('nik_ktp')->nullable()->after('nik')->comment('NIK KTP');
            $table->string('nama_ktp')->nullable()->after('nik_ktp')->comment('Nama KTP');
            $table->string('kode_vendor')->nullable()->after('nama_lengkap')->comment('Kode Vendor');
            $table->string('type_lokasi')->nullable()->after('penempatan')->comment('Type Lokasi');
            $table->string('area_kerja')->nullable()->after('type_lokasi')->comment('Area Kerja');
            $table->text('note1')->nullable()->after('status')->comment('Catatan Tambahan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['nik_ktp', 'nama_ktp', 'kode_vendor', 'type_lokasi', 'area_kerja', 'note1']);
        });
    }
};
