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
            // Change status from enum to varchar to allow flexible values
            $table->string('status')->default('Aktif')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // Revert back to enum
            $table->enum('status', ['Aktif', 'Cuti', 'Non Aktif', 'Resign'])->default('Aktif')->change();
        });
    }
};
