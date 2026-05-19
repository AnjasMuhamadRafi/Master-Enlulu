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
        Schema::table('users', function (Blueprint $table) {
            // Field untuk menyimpan posisi yang di-handle oleh ADMIN/PIC
            // Null untuk Super Admin (akses semua), atau nama position untuk ADMIN/PIC terbatas
            $table->string('handled_position')->nullable()->after('role')
                ->comment('Posisi yang di-handle (SPRINTER, TRANSPORTER, WH, etc) - NULL untuk Super Admin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('handled_position');
        });
    }
};
