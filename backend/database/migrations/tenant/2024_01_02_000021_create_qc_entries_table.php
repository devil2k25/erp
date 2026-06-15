<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qc_entries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('reference_type', 100);
            $table->uuid('reference_id');
            $table->uuid('product_id');
            $table->decimal('inspected_quantity', 12, 4)->default(0);
            $table->decimal('passed_quantity', 12, 4)->default(0);
            $table->decimal('failed_quantity', 12, 4)->default(0);
            $table->string('defect_type')->nullable();
            $table->uuid('inspector_id');
            $table->date('inspection_date');
            $table->enum('status', ['pending', 'passed', 'failed', 'conditional_pass'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['reference_type', 'reference_id']);
            $table->foreign('product_id')->references('id')->on('products');
            $table->foreign('inspector_id')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qc_entries');
    }
};
