<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('device_model', 100)->default('Solution Fingerprint / Face'); // Solution X302, X100-C, etc.
            $table->string('ip_address', 45)->nullable();
            $table->integer('port')->default(4370);
            $table->string('comm_key', 50)->default('0'); // Communication password (default 0 for ZKTeco/Solution)
            $table->string('serial_number', 100)->nullable()->unique();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('location')->nullable();
            $table->enum('status', ['online', 'offline', 'disabled'])->default('offline');
            $table->enum('sync_method', ['lan_tcp', 'cloud_adms', 'file_import', 'mock'])->default('lan_tcp');
            $table->boolean('auto_sync')->default(true);
            $table->integer('sync_interval_minutes')->default(15);
            $table->timestamp('last_sync_at')->nullable();
            $table->text('last_error_message')->nullable();
            $table->timestamps();
        });

        Schema::create('device_employee_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained('devices')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('device_pin', 50); // PIN or User ID on the Solution Device
            $table->string('privilege', 20)->default('user'); // user, admin
            $table->string('card_number', 50)->nullable();
            $table->timestamp('enrolled_at')->nullable();
            $table->timestamps();

            $table->unique(['device_id', 'device_pin']);
            $table->unique(['device_id', 'employee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_employee_mappings');
        Schema::dropIfExists('devices');
    }
};
