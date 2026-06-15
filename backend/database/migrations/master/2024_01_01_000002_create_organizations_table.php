<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'master';

    public function up(): void
    {
        Schema::connection('master')->create('organizations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug', 100)->unique();
            $table->string('owner_name');
            $table->string('owner_email')->unique();
            $table->string('owner_phone', 20)->nullable();
            $table->string('logo_path', 500)->nullable();
            $table->string('industry_type', 100)->nullable();
            $table->text('address')->nullable();
            $table->string('country', 100)->nullable();
            $table->string('timezone', 100)->default('UTC');
            $table->enum('status', ['active', 'suspended', 'trial', 'cancelled'])->default('trial');
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::connection('master')->dropIfExists('organizations');
    }
};
