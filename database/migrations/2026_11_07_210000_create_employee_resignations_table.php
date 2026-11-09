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
        Schema::create('employee_resignations', function (Blueprint $table) {
            $table->id();
            $table->string('resignation_number', 50)->unique();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->date('submission_date'); // Tanggal pengajuan dibuat
            $table->date('requested_last_day'); // Permintaan hari kerja terakhir
            $table->date('approved_last_day')->nullable(); // Tanggal efektif disetujui HR/Manajemen
            
            // Kategori dan Alasan
            $table->string('reason_type', 50)->default('career_growth'); // career_growth, relocation, health, personal, education, compensation, family, other
            $table->text('reason_detail'); // Penjelasan rinci pengunduran diri
            $table->text('handover_notes')->nullable(); // Catatan rencana transisi & serah terima tugas
            $table->string('resignation_letter_path')->nullable(); // File upload surat resmi bertanda tangan
            
            // Status Workflow: pending -> approved -> completed (atau rejected / cancelled)
            $table->enum('status', ['pending', 'approved', 'rejected', 'completed', 'cancelled'])->default('pending');
            
            // Otorisasi Persetujuan / Penolakan Manajerial
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('exit_interview_notes')->nullable(); // Catatan wawancara pengakhiran kerja
            
            // Exit Clearance Checklist (Serah Terima & Penyelesaian Hak)
            $table->boolean('clearance_assets_returned')->default(false); // Laptop, ID card, inventaris
            $table->text('clearance_assets_notes')->nullable();
            $table->boolean('clearance_finance_settled')->default(false); // Kasbon/pinjaman lunas & penyesuaian gaji
            $table->text('clearance_finance_notes')->nullable();
            $table->boolean('clearance_handover_completed')->default(false); // Handover task/akses sistem tuntas
            $table->text('clearance_handover_notes')->nullable();
            
            // Finalisasi Offboarding
            $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->text('completion_notes')->nullable();
            
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['employee_id', 'status']);
            $table->index(['branch_id', 'status']);
            $table->index(['submission_date', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_resignations');
    }
};

