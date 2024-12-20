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
            $table->date('delivery_date'); // A date column for the delivery date
            $table->string('payment_term'); // A string for payment term
            $table->unsignedBigInteger('created_by')->default(1); // Use unsignedBigInteger for foreign keys or IDs
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
