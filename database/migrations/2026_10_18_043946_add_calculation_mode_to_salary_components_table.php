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
        Schema::table('salary_components', function (Blueprint $table) {
            $table->string('calculation_mode', 50)->default('fixed')->after('type'); // fixed, percentage, per_attendance, per_late_minute
            $table->decimal('rate_value', 14, 2)->default(0.00)->after('calculation_mode');
            $table->text('description')->nullable()->after('default_amount');
        });

        // Initialize existing components with their default calculation policies
        DB::table('salary_components')->where('code', 'BASIC')->update([
            'calculation_mode' => 'fixed',
            'rate_value' => 0.00,
            'description' => 'Gaji Pokok dasar karyawan yang disepakati',
        ]);

        DB::table('salary_components')->where('code', 'ALW_POS')->update([
            'calculation_mode' => 'percentage',
            'rate_value' => 15.00,
            'description' => '15% dari Gaji Pokok untuk tunjangan jabatan',
        ]);

        DB::table('salary_components')->where('code', 'ALW_MEAL')->update([
            'calculation_mode' => 'per_attendance',
            'rate_value' => 50000.00,
            'description' => 'Rp 50.000 per hari kerja kehadiran fisik karyawan',
        ]);

        DB::table('salary_components')->where('code', 'DED_BPJS_KES')->update([
            'calculation_mode' => 'percentage',
            'rate_value' => 1.00,
            'description' => 'Potongan 1% dari Gaji Pokok untuk iuran BPJS Kesehatan pekerja',
        ]);

        DB::table('salary_components')->where('code', 'DED_BPJS_TK')->update([
            'calculation_mode' => 'percentage',
            'rate_value' => 2.00,
            'description' => 'Potongan 2% dari Gaji Pokok untuk iuran JHT BPJS Ketenagakerjaan',
        ]);

        DB::table('salary_components')->where('code', 'DED_LATE')->update([
            'calculation_mode' => 'per_late_minute',
            'rate_value' => 1000.00,
            'description' => 'Potongan keterlambatan Rp 1.000 per menit dari mesin presensi',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salary_components', function (Blueprint $table) {
            $table->dropColumn(['calculation_mode', 'rate_value', 'description']);
        });
    }
};
