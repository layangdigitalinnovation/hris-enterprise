<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Raw Attendance Logs from devices / files
        Schema::create('attendance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->nullable()->constrained('devices')->nullOnDelete();
            $table->foreignId('employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->string('device_pin', 50);
            $table->dateTime('log_time');
            $table->string('verify_type', 30)->default('fingerprint'); // fingerprint, face, rfid, pin, manual
            $table->string('log_type', 30)->default('check_in'); // check_in, check_out, break_out, break_in, overtime_in, overtime_out
            $table->json('raw_payload')->nullable();
            $table->string('hash_signature', 64)->unique(); // sha256(device_id + device_pin + log_time) to avoid duplicate logs
            $table->enum('status', ['pending', 'processed', 'ignored'])->default('pending');
            $table->timestamps();

            $table->index(['employee_id', 'log_time']);
            $table->index(['device_pin', 'log_time']);
        });

        // History of sync execution
        Schema::create('attendance_sync_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->nullable()->constrained('devices')->nullOnDelete();
            $table->dateTime('run_at');
            $table->string('trigger_type', 30)->default('scheduler'); // scheduler, manual, webhook
            $table->integer('records_fetched')->default(0);
            $table->integer('records_inserted')->default(0);
            $table->enum('status', ['success', 'partial', 'failed'])->default('success');
            $table->text('error_message')->nullable();
            $table->timestamps();
        });

        // Processed Daily Attendance Summaries
        Schema::create('attendance_daily_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->date('date');
            $table->foreignId('shift_id')->nullable()->constrained('shifts')->nullOnDelete();
            $table->time('clock_in')->nullable();
            $table->time('clock_out')->nullable();
            $table->integer('late_minutes')->default(0);
            $table->integer('early_leave_minutes')->default(0);
            $table->decimal('work_hours', 5, 2)->default(0.00);
            $table->decimal('overtime_hours', 5, 2)->default(0.00);
            $table->enum('status', [
                'present',
                'late',
                'early_leave',
                'absent',
                'on_leave',
                'sick',
                'permission',
                'holiday',
                'day_off'
            ])->default('absent');
            $table->boolean('is_locked')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'date']);
            $table->index(['date', 'status']);
        });

        // Manual Correction / Adjustments
        Schema::create('attendance_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->date('date');
            $table->time('requested_clock_in')->nullable();
            $table->time('requested_clock_out')->nullable();
            $table->text('reason');
            $table->string('document_path')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('approved_at')->nullable();
            $table->text('approval_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_adjustments');
        Schema::dropIfExists('attendance_daily_summaries');
        Schema::dropIfExists('attendance_sync_runs');
        Schema::dropIfExists('attendance_logs');
    }
};
