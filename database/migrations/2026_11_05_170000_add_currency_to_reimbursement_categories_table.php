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
        Schema::table('reimbursement_categories', function (Blueprint $table) {
            if (!Schema::hasColumn('reimbursement_categories', 'currency')) {
                $table->string('currency', 10)->default('ALL')->after('description'); // ALL, IDR, SAR
            }
            if (!Schema::hasColumn('reimbursement_categories', 'max_amount_sar')) {
                $table->decimal('max_amount_sar', 15, 2)->nullable()->after('max_amount');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reimbursement_categories', function (Blueprint $table) {
            if (Schema::hasColumn('reimbursement_categories', 'max_amount_sar')) {
                $table->dropColumn('max_amount_sar');
            }
            if (Schema::hasColumn('reimbursement_categories', 'currency')) {
                $table->dropColumn('currency');
            }
        });
    }
};

