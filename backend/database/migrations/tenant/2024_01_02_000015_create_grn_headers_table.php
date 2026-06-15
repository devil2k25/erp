<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grn_headers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('grn_number', 50)->unique();
            $table->uuid('vendor_id');
            $table->string('purchase_order_ref', 100)->nullable();
            $table->uuid('received_by');
            $table->uuid('warehouse_id');
            $table->date('received_date');
            $table->enum('status', ['draft', 'received', 'quality_check', 'approved', 'rejected'])->default('draft');
            $table->text('notes')->nullable();
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('vendor_id')->references('id')->on('vendors');
            $table->foreign('received_by')->references('id')->on('users');
            $table->foreign('warehouse_id')->references('id')->on('warehouses');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grn_headers');
    }
};
