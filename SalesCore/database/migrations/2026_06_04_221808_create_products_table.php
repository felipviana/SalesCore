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

            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();

            $table->string('name', 150);
            $table->string('sku', 50)->nullable()->unique();
            $table->string('barcode', 50)->nullable()->unique();

            $table->string('unit', 10)->default('UN');

            $table->decimal('cost_price', 10, 2)->default(0);
            $table->decimal('sale_price', 10, 2);

            $table->decimal('stock_quantity', 10, 3)->default(0);
            $table->decimal('minimum_stock', 10, 3)->default(0);

            $table->boolean('active')->default(true);

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
