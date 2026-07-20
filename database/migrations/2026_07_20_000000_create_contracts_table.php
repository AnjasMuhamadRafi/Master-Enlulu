<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->string('employee_nik', 16);
            $table->string('contract_number', 100);
            $table->date('contract_date');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('template_name')->default('Perjanjian Mitra 2025');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['employee_nik', 'contract_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
