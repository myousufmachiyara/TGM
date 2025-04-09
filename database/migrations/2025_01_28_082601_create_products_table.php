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
            $table->string('measurement_unit')->nullable();
            $table->string('item_type')->nullable();
            $table->decimal('width')->default(0);
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('sale_price', 10, 2)->default(0);
            $table->text('purchase_note')->nullable();
            $table->decimal('opening_stock')->default(0);
            $table->boolean('has_variations')->default(0);
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
