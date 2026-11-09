<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Change calculation_type from enum to varchar(50) to support saudi_article_107 and extensible formulas
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `overtime_rates` MODIFY COLUMN `calculation_type` VARCHAR(50) NOT NULL DEFAULT 'flat'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `overtime_rates` MODIFY COLUMN `calculation_type` ENUM('flat', 'depnaker_formula', 'percentage') NOT NULL DEFAULT 'flat'");
        }
    }
};

