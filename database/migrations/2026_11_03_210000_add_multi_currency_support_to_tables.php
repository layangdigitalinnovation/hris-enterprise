<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add currency and country_code to branches table
        if (Schema::hasTable('branches')) {
            Schema::table('branches', function (Blueprint $table) {
                if (!Schema::hasColumn('branches', 'currency')) {
                    $table->string('currency', 3)->default('IDR')->after('timezone');
                }
                if (!Schema::hasColumn('branches', 'country_code')) {
                    $table->string('country_code', 2)->default('ID')->after('currency');
                }
            });
        }

        // 2. Add currency to employees table
        if (Schema::hasTable('employees')) {
            Schema::table('employees', function (Blueprint $table) {
                if (!Schema::hasColumn('employees', 'currency')) {
                    $table->string('currency', 3)->nullable()->after('branch_id');
                }
            });
        }

        // 3. Add currency to payroll_items table
        if (Schema::hasTable('payroll_items')) {
            Schema::table('payroll_items', function (Blueprint $table) {
                if (!Schema::hasColumn('payroll_items', 'currency')) {
                    $table->string('currency', 3)->default('IDR')->after('employee_id');
                }
            });
        }

        // 4. Add currency to overtime_rates table
        if (Schema::hasTable('overtime_rates')) {
            Schema::table('overtime_rates', function (Blueprint $table) {
                if (!Schema::hasColumn('overtime_rates', 'currency')) {
                    $table->string('currency', 3)->default('IDR')->after('calculation_type');
                }
            });
        }

        // 5. Update existing branches
        DB::table('branches')
            ->where('timezone', 'Asia/Riyadh')
            ->orWhere('name', 'like', '%Riyadh%')
            ->orWhere('name', 'like', '%Saudi%')
            ->update([
                'currency' => 'SAR',
                'country_code' => 'SA',
            ]);

        DB::table('branches')
            ->where(function ($q) {
                $q->whereNull('timezone')
                  ->orWhere('timezone', '!=', 'Asia/Riyadh');
            })
            ->where('name', 'not like', '%Riyadh%')
            ->where('name', 'not like', '%Saudi%')
            ->update([
                'currency' => 'IDR',
                'country_code' => 'ID',
            ]);

        // 6. Update OvertimeRate OT_SAUDI_107 to currency SAR
        if (Schema::hasTable('overtime_rates')) {
            DB::table('overtime_rates')
                ->where('calculation_type', 'saudi_article_107')
                ->orWhere('code', 'OT_SAUDI_107')
                ->update(['currency' => 'SAR']);
        }

        // 7. Seed Currency Settings if not exist
        if (Schema::hasTable('system_settings')) {
            DB::table('system_settings')->updateOrInsert(
                ['key' => 'exchange_rate_sar_to_idr'],
                [
                    'value' => '4250',
                    'group' => 'currency',
                    'label' => 'Kurs SAR ke IDR (1 SAR = Rp)',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            DB::table('system_settings')->updateOrInsert(
                ['key' => 'base_currency'],
                [
                    'value' => 'IDR',
                    'group' => 'currency',
                    'label' => 'Mata Uang Dasar Sistem',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('overtime_rates') && Schema::hasColumn('overtime_rates', 'currency')) {
            Schema::table('overtime_rates', function (Blueprint $table) {
                $table->dropColumn('currency');
            });
        }

        if (Schema::hasTable('payroll_items') && Schema::hasColumn('payroll_items', 'currency')) {
            Schema::table('payroll_items', function (Blueprint $table) {
                $table->dropColumn('currency');
            });
        }

        if (Schema::hasTable('employees') && Schema::hasColumn('employees', 'currency')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->dropColumn('currency');
            });
        }

        if (Schema::hasTable('branches')) {
            Schema::table('branches', function (Blueprint $table) {
                if (Schema::hasColumn('branches', 'country_code')) {
                    $table->dropColumn('country_code');
                }
                if (Schema::hasColumn('branches', 'currency')) {
                    $table->dropColumn('currency');
                }
            });
        }
    }
};

