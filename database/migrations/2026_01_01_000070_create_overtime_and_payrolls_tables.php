<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Overtime Requests
        Schema::create('overtime_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number', 50)->unique();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->decimal('estimated_hours', 4, 2)->default(0.00);
            $table->decimal('actual_hours', 4, 2)->default(0.00);
            $table->text('reason');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('approved_at')->nullable();
            $table->timestamps();
        });

        // Salary Components (Gaji Pokok, Tunjangan Jabatan, Tunjangan Transport, BPJS, PPh21, Potongan Keterlambatan, dll)
        Schema::create('salary_components', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->enum('type', ['earning', 'deduction']); // earning (+), deduction (-)
            $table->boolean('is_taxable')->default(true);
            $table->boolean('is_fixed')->default(true); // fixed every month or variable based on attendance/performance
            $table->decimal('default_amount', 14, 2)->default(0.00);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Employee Compensation Master
        Schema::create('employee_compensations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('salary_component_id')->constrained('salary_components')->cascadeOnDelete();
            $table->decimal('amount', 14, 2)->default(0.00);
            $table->timestamps();

            $table->unique(['employee_id', 'salary_component_id']);
        });

        // Payroll Periods (e.g., Oktober 2026)
        Schema::create('payroll_periods', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Periode Oktober 2026
            $table->date('start_date');
            $table->date('end_date');
            $table->date('cut_off_date');
            $table->date('payment_date');
            $table->enum('status', ['draft', 'calculated', 'approved', 'paid', 'locked'])->default('draft');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('approved_at')->nullable();
            $table->timestamps();
        });

        // Payroll Items per Employee
        Schema::create('payroll_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_period_id')->constrained('payroll_periods')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->decimal('basic_salary', 14, 2)->default(0.00);
            $table->decimal('total_allowances', 14, 2)->default(0.00);
            $table->decimal('total_overtime', 14, 2)->default(0.00);
            $table->decimal('total_deductions', 14, 2)->default(0.00);
            $table->decimal('net_salary', 14, 2)->default(0.00);
            $table->json('details')->nullable(); // detailed breakdown of each component
            $table->enum('status', ['draft', 'approved', 'paid'])->default('draft');
            $table->timestamps();

            $table->unique(['payroll_period_id', 'employee_id']);
        });

        // Payslips
        Schema::create('payslips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_item_id')->constrained('payroll_items')->cascadeOnDelete();
            $table->string('slip_number', 50)->unique();
            $table->dateTime('generated_at');
            $table->string('pdf_path')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payslips');
        Schema::dropIfExists('payroll_items');
        Schema::dropIfExists('payroll_periods');
        Schema::dropIfExists('employee_compensations');
        Schema::dropIfExists('salary_components');
        Schema::dropIfExists('overtime_requests');
    }
};
