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
        Schema::create('employee_loans', function (Blueprint $table) {
            $table->id();
            $table->string('loan_number', 50)->unique();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('currency', 10)->default('IDR'); // IDR atau SAR
            $table->decimal('amount', 15, 2); // Nominal diajukan
            $table->decimal('approved_amount', 15, 2)->nullable(); // Nominal disetujui (dapat beda jika partial)
            $table->unsignedInteger('tenor_months')->default(1); // Jangka waktu angsuran bulan (1 - 60)
            $table->decimal('interest_rate_percent', 5, 2)->default(0.00); // Suku bunga/margin (%)
            $table->decimal('monthly_installment', 15, 2); // Nilai cicilan per bulan
            $table->date('repayment_start_date'); // Tanggal mulai cicilan
            $table->text('reason'); // Alasan / keperluan pinjaman
            $table->boolean('payroll_deduction')->default(true); // Opsi potong slip gaji
            $table->enum('status', ['pending', 'approved', 'active', 'disbursed', 'completed', 'rejected', 'cancelled'])->default('pending');

            // Persetujuan (Approval)
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();
            $table->text('rejection_reason')->nullable();

            // Pencairan Dana (Disbursement)
            $table->foreignId('disbursed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('disbursed_at')->nullable();
            $table->string('payment_method', 50)->nullable(); // bank_transfer, cash, payroll
            $table->string('disbursement_reference', 100)->nullable();
            $table->string('disbursement_proof')->nullable();
            $table->text('disbursement_notes')->nullable();

            // Berkas Dokumen Pendukung & Otor
            $table->string('attachment_file')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['employee_id', 'status']);
            $table->index(['branch_id', 'status']);
            $table->index(['currency', 'status']);
        });

        Schema::create('employee_loan_installments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_loan_id')->constrained('employee_loans')->cascadeOnDelete();
            $table->unsignedInteger('installment_number'); // Ke-1, Ke-2, dst.
            $table->date('due_date');
            $table->decimal('amount', 15, 2); // Tagihan angsuran bulan ini
            $table->decimal('paid_amount', 15, 2)->default(0.00); // Nominal yang telah dibayar
            $table->timestamp('paid_at')->nullable();
            $table->enum('status', ['unpaid', 'paid', 'partially_paid', 'waived'])->default('unpaid');
            $table->string('payment_method', 50)->nullable(); // bank_transfer, cash, payroll
            $table->string('payment_reference', 100)->nullable();
            $table->foreignId('payroll_period_id')->nullable()->constrained('payroll_periods')->nullOnDelete();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('proof_file')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['employee_loan_id', 'status']);
            $table->index(['due_date', 'status']);
            $table->index('payroll_period_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_loan_installments');
        Schema::dropIfExists('employee_loans');
    }
};
