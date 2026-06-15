<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bom_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('parent_product_id');
            $table->uuid('component_product_id');
            $table->decimal('quantity', 12, 4);
            $table->uuid('unit_id');
            $table->decimal('scrap_percentage', 5, 2)->default(0);
            $table->unsignedInteger('sequence')->default(0);
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['parent_product_id', 'component_product_id']);
            $table->foreign('parent_product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('component_product_id')->references('id')->on('products');
            $table->foreign('unit_id')->references('id')->on('units');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bom_items');
    }
};
