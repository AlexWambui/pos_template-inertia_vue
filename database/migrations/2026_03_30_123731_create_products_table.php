<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name')->unique();
            $table->string('sku')->unique()->nullable();
            $table->decimal('buying_price', 12, 2)->nullable(); // For profit calculation
            $table->decimal('selling_price', 12, 2);
            $table->string('barcode')->unique()->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('current_stock')->default(0);
            $table->decimal('weight_value', 10, 2)->nullable();
            $table->string('weight_unit')->nullable();
            $table->integer('sort_order')->default(0);
            $table->string('image')->nullable();
            $table->foreignId('product_category_id')->nullable()->constrained('product_categories')->nullOnDelete();
            $table->timestamps();

            $table->index('name');
            $table->index('is_active');
            $table->index('current_stock');
            $table->index('sort_order');
            $table->index('product_category_id');
            $table->index(['is_active', 'sort_order']); // For active products sorted by order
            $table->index(['product_category_id', 'is_active']); // For filtering by category and active status
            $table->index(['is_active', 'current_stock']); // For low stock queries on active products
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
