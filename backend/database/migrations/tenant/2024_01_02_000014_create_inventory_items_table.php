<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('product_id');
            $table->uuid('warehouse_id');
            $table->string('batch_number', 100)->nullable();
            $table->string('lot_number', 100)->nullable();
            $table->decimal('quantity_on_hand', 12, 4)->default(0);
            $table->decimal('quantity_reserved', 12, 4)->default(0);
            $table->date('manufactured_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->decimal('unit_cost', 12, 4)->nullable();
            $table->string('location_code', 100)->nullable();
            $table->string('barcode', 100)->nullable();
            $table->timestamps();

            $table->index(['product_id', 'warehouse_id', 'batch_number']);
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};
