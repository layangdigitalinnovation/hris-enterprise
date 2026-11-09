<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('visit_number', 50)->unique();
            $table->string('client_name', 150);
            $table->text('purpose');
            $table->dateTime('check_in_at');
            $table->decimal('check_in_lat', 10, 7);
            $table->decimal('check_in_lon', 10, 7);
            $table->text('check_in_address')->nullable();
            $table->dateTime('check_out_at')->nullable();
            $table->decimal('check_out_lat', 10, 7)->nullable();
            $table->decimal('check_out_lon', 10, 7)->nullable();
            $table->text('check_out_address')->nullable();
            $table->text('outcome')->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->enum('status', ['ongoing', 'completed', 'cancelled'])->default('ongoing');
            $table->string('photo_path')->nullable();
            $table->string('timezone', 50)->default('Asia/Jakarta');
            $table->timestamps();

            $table->index(['employee_id', 'status']);
            $table->index(['employee_id', 'check_in_at']);
            $table->index(['status', 'check_in_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_visits');
    }
};

