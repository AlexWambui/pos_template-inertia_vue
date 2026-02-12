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

            $table->string('sku')->unique()->nullable();
            $table->string('name');
            $table->decimal('buying_price', 12, 2)->nullable(); // For profit calculation
            $table->decimal('selling_price', 12, 2);
            $table->string('barcode')->unique()->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('current_stock')->default(0);
            $table->string('unit_of_measurement')->nullable(); // For receipt display

            $table->timestamps();
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
