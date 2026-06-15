<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('machines', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('code', 50)->unique();
            $table->uuid('production_line_id')->nullable();
            $table->string('machine_type', 100)->nullable();
            $table->string('manufacturer')->nullable();
            $table->string('model_number', 100)->nullable();
            $table->string('serial_number', 100)->nullable();
            $table->date('purchase_date')->nullable();
            $table->date('warranty_expiry')->nullable();
            $table->enum('status', ['running', 'idle', 'maintenance', 'breakdown', 'retired'])->default('idle');
            $table->timestamp('last_maintenance_at')->nullable();
            $table->timestamp('next_maintenance_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('production_line_id')->references('id')->on('production_lines')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('machines');
    }
};
