<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // NIK ENLULU: NIK sementara yang diterbitkan oleh PT Enlulu
            $table->string('nik_enlulu')->nullable()->after('nik_ktp')->comment('NIK sementara dari PT Enlulu');
            // NIK OS: NIK yang diberikan oleh client/outsourcing
            $table->string('nik_os')->nullable()->after('nik_enlulu')->comment('NIK dari client/outsourcing');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['nik_enlulu', 'nik_os']);
        });
    }
};
