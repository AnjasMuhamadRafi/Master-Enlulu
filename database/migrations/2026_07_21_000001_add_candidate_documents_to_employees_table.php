<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('nama_ibu_kandung')->nullable()->after('nama_ktp');
            $table->string('dokumen_ktp')->nullable()->after('foto');
            $table->string('dokumen_kk')->nullable()->after('dokumen_ktp');
            $table->string('dokumen_ijazah')->nullable()->after('dokumen_kk');
            $table->string('dokumen_cv')->nullable()->after('dokumen_ijazah');
            $table->string('dokumen_surat_lamaran')->nullable()->after('dokumen_cv');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'nama_ibu_kandung',
                'dokumen_ktp',
                'dokumen_kk',
                'dokumen_ijazah',
                'dokumen_cv',
                'dokumen_surat_lamaran',
            ]);
        });
    }
};
