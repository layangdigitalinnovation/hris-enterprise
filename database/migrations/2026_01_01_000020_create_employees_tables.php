<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('employee_code', 50)->unique();
            $table->string('nik', 50)->nullable()->unique();
            $table->string('first_name', 100);
            $table->string('last_name', 100)->nullable();
            $table->string('email', 150)->unique();
            $table->string('phone', 30)->nullable();
            $table->enum('gender', ['male', 'female'])->default('male');
            $table->string('birth_place', 100)->nullable();
            $table->date('birth_date')->nullable();
            $table->enum('marital_status', ['single', 'married', 'widowed', 'divorced'])->default('single');
            $table->string('religion', 50)->nullable();
            $table->text('address')->nullable();

            // Job Assignment
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('position_id')->nullable()->constrained('positions')->nullOnDelete();
            $table->unsignedBigInteger('manager_id')->nullable()->index();

            // Employment Details
            $table->date('join_date');
            $table->date('end_date')->nullable();
            $table->enum('employment_status', ['permanent', 'contract', 'probation', 'intern'])->default('permanent');
            $table->enum('status', ['active', 'inactive', 'resigned', 'terminated'])->default('active');

            // Financial & Statutory
            $table->string('bank_name', 50)->nullable();
            $table->string('bank_account_no', 50)->nullable();
            $table->string('bank_account_holder', 150)->nullable();
            $table->string('npwp', 50)->nullable();
            $table->string('bpjs_ketenagakerjaan', 50)->nullable();
            $table->string('bpjs_kesehatan', 50)->nullable();

            $table->string('photo_path')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('employee_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('title');
            $table->string('document_type', 50); // KTP, NPWP, KK, CV, CONTRACT, IJAZAH, CERTIFICATE, OTHER
            $table->string('file_path');
            $table->string('file_name')->nullable();
            $table->unsignedInteger('file_size')->default(0);
            $table->date('expiry_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('employee_employments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('contract_number', 100)->nullable();
            $table->enum('employment_status', ['permanent', 'contract', 'probation', 'intern']);
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_employments');
        Schema::dropIfExists('employee_documents');
        Schema::dropIfExists('employees');
    }
};
