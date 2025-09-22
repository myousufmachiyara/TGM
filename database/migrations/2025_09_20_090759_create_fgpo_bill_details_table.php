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
        Schema::create('fgpo_bill_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bill_id'); // FK from fgpo_bills
            $table->unsignedBigInteger('production_id'); // FK from fgpo_production
            $table->unsignedBigInteger('product_id'); // FK from product
            $table->decimal('rate', 15, 2)->default(0); // Rate
            $table->decimal('received_qty', 15, 2)->nullable();
            $table->decimal('adjusted_amount', 15, 2)->default(0); // Adjusted Amount
            $table->timestamps();

            // Foreign Keys
            $table->foreign('bill_id')->references('id')->on('fgpo_bills')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('production_id')->references('id')->on('pur_fgpos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fgpo_bill_details');
    }
};
