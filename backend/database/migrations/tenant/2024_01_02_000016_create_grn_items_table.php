<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grn_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('grn_header_id');
            $table->uuid('product_id');
            $table->decimal('quantity_ordered', 12, 4);
            $table->decimal('quantity_received', 12, 4)->default(0);
            $table->uuid('unit_id');
            $table->decimal('unit_price', 12, 4)->default(0);
            $table->string('batch_number', 100)->nullable();
            $table->date('expiry_date')->nullable();
            $table->enum('qc_status', ['pending', 'passed', 'failed'])->default('pending');
            $table->text('qc_notes')->nullable();
            $table->timestamps();

            $table->foreign('grn_header_id')->references('id')->on('grn_headers')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products');
            $table->foreign('unit_id')->references('id')->on('units');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grn_items');
    }
};
