<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('sku', 100)->unique();
            $table->string('barcode', 100)->unique()->nullable();
            $table->uuid('category_id')->nullable();
            $table->uuid('unit_id');
            $table->enum('type', ['raw_material', 'semi_finished', 'finished_good', 'consumable', 'service'])->default('raw_material');
            $table->text('description')->nullable();
            $table->string('hsn_code', 50)->nullable();
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('cost_price', 12, 2)->default(0);
            $table->decimal('selling_price', 12, 2)->default(0);
            $table->decimal('min_stock_level', 12, 2)->default(0);
            $table->decimal('max_stock_level', 12, 2)->nullable();
            $table->decimal('reorder_point', 12, 2)->default(0);
            $table->unsignedInteger('lead_time_days')->default(0);
            $table->string('image_path', 500)->nullable();
            $table->boolean('is_trackable')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('category_id')->references('id')->on('categories')->nullOnDelete();
            $table->foreign('unit_id')->references('id')->on('units');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
