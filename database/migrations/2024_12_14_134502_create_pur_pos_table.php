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
        Schema::create('pur_pos', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->string('vendor_name'); // A string for vendor name
            $table->date('order_date'); // A date column for the order date
            $table->date('delivery_date')->nullable(); // A date column for the delivery date
            $table->decimal('other_exp', 10, 2)->nullable()->default(0);
            $table->decimal('bill_discount', 10, 2)->nullable()->default(0);
            $table->unsignedBigInteger('created_by')->default(1); // Use unsignedBigInteger for foreign keys or IDs
            $table->softDeletes();
            $table->timestamps(); // Adds created_at and updated_at columns
        });
    }   

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pur_pos');
    }
};
