<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('production_plans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('plan_number', 50)->unique();
            $table->uuid('product_id');
            $table->uuid('production_line_id');
            $table->decimal('planned_quantity', 12, 4);
            $table->decimal('actual_quantity', 12, 4)->default(0);
            $table->dateTime('planned_start_date');
            $table->dateTime('planned_end_date');
            $table->dateTime('actual_start_date')->nullable();
            $table->dateTime('actual_end_date')->nullable();
            $table->enum('status', ['draft', 'approved', 'in_progress', 'completed', 'cancelled', 'on_hold'])->default('draft');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->string('batch_number', 100)->nullable();
            $table->text('notes')->nullable();
            $table->uuid('created_by');
            $table->uuid('approved_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('product_id')->references('id')->on('products');
            $table->foreign('production_line_id')->references('id')->on('production_lines');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_plans');
    }
};
