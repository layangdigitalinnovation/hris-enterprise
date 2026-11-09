<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('overtime_requests', function (Blueprint $table) {
            $table->foreignId('overtime_rate_id')
                ->nullable()
                ->after('employee_id')
                ->constrained('overtime_rates')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('overtime_requests', function (Blueprint $table) {
            $table->dropForeign(['overtime_rate_id']);
            $table->dropColumn('overtime_rate_id');
        });
    }
};

