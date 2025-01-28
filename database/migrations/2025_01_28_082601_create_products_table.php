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
            $table->string('name');
            $table->string('sku');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('category_id');
            $table->string('measurement_unit');
            $table->decimal('price', 10, 2);
            $table->decimal('sale_price', 10, 2);
            $table->enum('status', ['out_of_stock', 'backorder', 'in_stock'])->default('in_stock');
            $table->text('purchase_note')->nullable();
            $table->softDeletes();
            $table->timestamps();
            
            $table->foreign('category_id')->references('id')->on('product_categories')->onDelete('cascade');
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
