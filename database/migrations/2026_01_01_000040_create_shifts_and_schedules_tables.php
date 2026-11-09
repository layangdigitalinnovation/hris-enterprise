<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('late_tolerance_minutes')->default(15);
            $table->integer('early_leave_tolerance_minutes')->default(0);
            $table->boolean('is_overnight')->default(false);
            $table->string('color', 20)->default('#3B82F6');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('work_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->enum('type', ['fixed', 'flexible', 'rotating'])->default('fixed');
            $table->date('effective_date')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('employee_shift_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('shift_id')->nullable()->constrained('shifts')->nullOnDelete();
            $table->date('date');
            $table->boolean('is_day_off')->default(false);
            $table->timestamps();

            $table->unique(['employee_id', 'date']);
        });

        Schema::create('holidays', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->date('date');
            $table->boolean('is_national')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('holidays');
        Schema::dropIfExists('employee_shift_assignments');
        Schema::dropIfExists('work_schedules');
        Schema::dropIfExists('shifts');
    }
};
