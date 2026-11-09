<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Payroll Templates Master
        Schema::create('payroll_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('code', 50)->unique();
            $table->text('description')->nullable();
            $table->string('currency', 3)->default('IDR'); // IDR, SAR
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->decimal('base_salary', 14, 2)->default(0.00);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        // 2. Payroll Template Items (Reusable Component Structure)
        Schema::create('payroll_template_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_template_id')->constrained('payroll_templates')->cascadeOnDelete();
            $table->foreignId('salary_component_id')->constrained('salary_components')->cascadeOnDelete();
            $table->enum('type', ['earning', 'deduction'])->default('earning');
            $table->string('calculation_mode', 50)->default('fixed'); // fixed, percentage, per_attendance, per_late_minute
            $table->decimal('rate_value', 14, 2)->default(0.00);
            $table->decimal('amount', 14, 2)->default(0.00);
            $table->boolean('is_fixed')->default(true); // For fixed allowance classification (Saudi Article 107 & Depnaker)
            $table->boolean('is_taxable')->default(true);
            $table->integer('order_index')->default(0);
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->unique(['payroll_template_id', 'salary_component_id'], 'tpl_item_unique');
        });

        // 3. Link Employee to Payroll Template
        if (Schema::hasTable('employees') && !Schema::hasColumn('employees', 'payroll_template_id')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->foreignId('payroll_template_id')->nullable()->after('branch_id')->constrained('payroll_templates')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('employees') && Schema::hasColumn('employees', 'payroll_template_id')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->dropForeign(['payroll_template_id']);
                $table->dropColumn('payroll_template_id');
            });
        }

        Schema::dropIfExists('payroll_template_items');
        Schema::dropIfExists('payroll_templates');
    }
};

