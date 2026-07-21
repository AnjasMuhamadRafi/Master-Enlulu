<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('candidate_registrations', function (Blueprint $table) {
            $table->id();
            $table->string('token', 64)->unique();
            $table->string('candidate_name');
            $table->char('employee_nik', 16)->nullable()->index();
            $table->string('status', 20)->default('pending')->index();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('applied_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->string('full_name')->nullable();
            $table->char('nik_ktp', 16)->nullable()->index();
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('birth_place')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('mother_name')->nullable();
            $table->string('work_location')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_account_number', 50)->nullable();
            $table->string('bank_account_holder')->nullable();

            $table->string('ktp_path')->nullable();
            $table->string('kk_path')->nullable();
            $table->string('diploma_path')->nullable();
            $table->string('cv_path')->nullable();
            $table->string('application_letter_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidate_registrations');
    }
};
