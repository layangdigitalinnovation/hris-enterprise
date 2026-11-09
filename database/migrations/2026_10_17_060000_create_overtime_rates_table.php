<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('overtime_rates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 50)->unique();
            $table->enum('day_type', ['weekday', 'weekend', 'holiday', 'all'])->default('all');
            $table->enum('calculation_type', ['flat', 'depnaker_formula', 'percentage'])->default('flat');
            $table->decimal('rate_per_hour', 12, 2)->default(35000.00);
            $table->decimal('multiplier', 4, 2)->default(1.50);
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('overtime_rates');
    }
};
