<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('transaction_number', 50)->unique();
            $table->uuid('product_id');
            $table->uuid('warehouse_id');
            $table->enum('transaction_type', [
                'grn', 'production_consumption', 'production_output',
                'transfer_in', 'transfer_out', 'adjustment',
                'return', 'scrap', 'sale_dispatch'
            ]);
            $table->string('reference_type', 100)->nullable();
            $table->uuid('reference_id')->nullable();
            $table->decimal('quantity', 12, 4);
            $table->uuid('unit_id');
            $table->decimal('unit_cost', 12, 4)->nullable();
            $table->string('batch_number', 100)->nullable();
            $table->uuid('performed_by');
            $table->timestamp('transaction_date');
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['product_id', 'warehouse_id']);
            $table->index(['reference_type', 'reference_id']);
            $table->foreign('product_id')->references('id')->on('products');
            $table->foreign('warehouse_id')->references('id')->on('warehouses');
            $table->foreign('unit_id')->references('id')->on('units');
            $table->foreign('performed_by')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_transactions');
    }
};
