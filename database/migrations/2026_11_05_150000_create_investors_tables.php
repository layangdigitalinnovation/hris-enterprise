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
        Schema::create('investors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('investor_code')->unique();
            $table->string('name');
            $table->string('nik_or_passport')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_account_no')->nullable();
            $table->string('bank_account_holder')->nullable();
            $table->string('currency', 10)->default('IDR');
            $table->decimal('initial_investment', 15, 2)->default(0.00);
            $table->date('investment_date');
            $table->date('contract_end_date')->nullable();
            $table->decimal('profit_share_percentage', 5, 2)->default(0.00);
            $table->string('profit_share_scheme', 30)->default('net_profit'); // net_profit, gross_revenue, fixed_monthly
            $table->boolean('has_fixed_stipend')->default(false);
            $table->decimal('fixed_stipend_amount', 15, 2)->default(0.00);
            $table->unsignedTinyInteger('stipend_payment_day')->default(25);
            $table->boolean('include_stipend_in_bep')->default(false);
            $table->string('status', 20)->default('active'); // active, completed, paused
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('investor_distributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('investor_id')->constrained('investors')->cascadeOnDelete();
            $table->string('distribution_number')->unique();
            $table->string('type', 30)->default('profit_share'); // profit_share, stipend, capital_return
            $table->string('period_label', 60); // e.g. "Januari 2026", "Q1 2026"
            $table->date('distribution_date');
            $table->string('currency', 10)->default('IDR');
            $table->decimal('basis_amount', 15, 2)->nullable(); // e.g. Net profit base
            $table->decimal('percentage_applied', 5, 2)->nullable(); // e.g. 15.00%
            $table->decimal('amount', 15, 2);
            $table->string('status', 20)->default('pending'); // pending, approved, paid, cancelled
            $table->date('payment_date')->nullable();
            $table->string('payment_method', 30)->nullable(); // bank_transfer, cash, cheque
            $table->string('payment_reference')->nullable();
            $table->string('attachment_path')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investor_distributions');
        Schema::dropIfExists('investors');
    }
};

