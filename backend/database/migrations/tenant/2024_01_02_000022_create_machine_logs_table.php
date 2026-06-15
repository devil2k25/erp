<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('machine_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('machine_id');
            $table->enum('log_type', ['status_change', 'error', 'maintenance', 'production_start', 'production_end']);
            $table->string('status_from', 50)->nullable();
            $table->string('status_to', 50)->nullable();
            $table->text('message')->nullable();
            $table->uuid('logged_by')->nullable();
            $table->timestamp('logged_at');
            $table->uuid('production_entry_id')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['machine_id', 'logged_at']);
            $table->foreign('machine_id')->references('id')->on('machines')->onDelete('cascade');
            $table->foreign('logged_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('production_entry_id')->references('id')->on('production_entries')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('machine_logs');
    }
};
