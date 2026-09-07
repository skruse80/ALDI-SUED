<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('code')->unique();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('sku')->unique();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('price_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('store_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('amount_cents');
            $table->char('currency', 3);
            $table->timestamp('recorded_at');
            $table->uuid('trace_id')->index();
            $table->timestamps();

            $table->index(['store_id', 'product_id', 'recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('price_histories');
        Schema::dropIfExists('products');
        Schema::dropIfExists('stores');
    }
};
