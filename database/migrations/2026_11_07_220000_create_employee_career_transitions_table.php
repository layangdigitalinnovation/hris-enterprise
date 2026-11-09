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
        Schema::create('employee_career_transitions', function (Blueprint $table) {
            $table->id();
            $table->string('transition_number', 50)->unique(); // E.g. TRN-202611-0001
            $table->string('letter_number', 100)->nullable(); // Nomor SK Resmi (opsional/manual input)
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            
            // Jenis Pergerakan Karir: promotion, transfer, rotation, demotion
            $table->enum('type', ['promotion', 'transfer', 'rotation', 'demotion'])->default('promotion');

            // Snapshot Keadaan Semula (From)
            $table->foreignId('from_branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('from_department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('from_position_id')->nullable()->constrained('positions')->nullOnDelete();
            $table->foreignId('from_manager_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->foreignId('from_payroll_template_id')->nullable()->constrained('payroll_templates')->nullOnDelete();
            $table->decimal('from_salary', 15, 2)->nullable();
            $table->string('from_currency', 10)->default('IDR');

            // Keadaan Baru yang Dituju (To)
            $table->foreignId('to_branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('to_department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('to_position_id')->nullable()->constrained('positions')->nullOnDelete();
            $table->foreignId('to_manager_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->foreignId('to_payroll_template_id')->nullable()->constrained('payroll_templates')->nullOnDelete();
            $table->decimal('to_salary', 15, 2)->nullable();
            $table->string('to_currency', 10)->default('IDR');

            // Tanggal & Informasi Pengajuan
            $table->date('request_date');
            $table->date('effective_date');
            $table->text('reason'); // Alasan / latar belakang promosi/mutasi
            $table->text('notes')->nullable(); // Uraian tugas, pertimbangan, atau tanggung jawab baru
            $table->string('attachment_path')->nullable(); // Lampiran SK atau dokumen pendukung

            // Alur Approval & Pelaksanaan
            // pending: Menunggu Review
            // approved: Disetujui (Menunggu Tanggal Efektif / Siap Diterapkan)
            // applied: Telah Diterapkan ke Master Pegawai (Completed)
            // rejected: Ditolak
            // cancelled: Dibatalkan
            $table->enum('status', ['pending', 'approved', 'applied', 'rejected', 'cancelled'])->default('pending');

            // Approval data
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();
            $table->text('rejection_reason')->nullable();

            // Execution / Applied data
            $table->foreignId('applied_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('applied_at')->nullable();
            $table->text('execution_notes')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_career_transitions');
    }
};

