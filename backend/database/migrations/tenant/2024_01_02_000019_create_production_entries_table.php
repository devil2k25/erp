<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('production_entries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('production_plan_id');
            $table->uuid('shift_id');
            $table->uuid('machine_id')->nullable();
            $table->uuid('operator_id');
            $table->date('entry_date');
            $table->dateTime('start_time');
            $table->dateTime('end_time')->nullable();
            $table->decimal('planned_quantity', 12, 4)->default(0);
            $table->decimal('produced_quantity', 12, 4)->default(0);
            $table->decimal('rejected_quantity', 12, 4)->default(0);
            $table->decimal('rework_quantity', 12, 4)->default(0);
            $table->unsignedInteger('downtime_minutes')->default(0);
            $table->string('downtime_reason')->nullable();
            $table->decimal('oee_availability', 5, 2)->nullable();
            $table->decimal('oee_performance', 5, 2)->nullable();
            $table->decimal('oee_quality', 5, 2)->nullable();
            $table->decimal('oee_score', 5, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['production_plan_id', 'entry_date']);
            $table->foreign('production_plan_id')->references('id')->on('production_plans')->onDelete('cascade');
            $table->foreign('shift_id')->references('id')->on('shifts');
            $table->foreign('machine_id')->references('id')->on('machines')->nullOnDelete();
            $table->foreign('operator_id')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_entries');
    }
};
