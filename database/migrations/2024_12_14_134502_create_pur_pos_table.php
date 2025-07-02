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
            $table->unsignedBigInteger('vendor_id');
            $table->unsignedBigInteger('category_id');  // Ensure this is unsignedBigInteger
            $table->string('po_code');
            $table->string('order_by');
            $table->string('remarks')->nullable();
            $table->date('order_date'); // A date column for the order date
            $table->unsignedBigInteger('created_by'); // Use unsignedBigInteger for foreign keys or IDs
            $table->softDeletes();
            $table->timestamps(); // Adds created_at and updated_at columns 
            
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
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
