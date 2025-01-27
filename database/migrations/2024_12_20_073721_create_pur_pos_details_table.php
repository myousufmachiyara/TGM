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
        Schema::create('pur_pos_details', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->unsignedBigInteger('pur_pos_id'); // Foreign key to 'pur_pos' table
            $table->string('item_name'); // item name
            $table->decimal('item_rate', 10, 2); // Decimal column for rate
            $table->decimal('item_qty', 10, 2); // Decimal column for quantity
            $table->timestamps(); // Adds created_at and updated_at columns
        
            // Foreign key constraints
            $table->foreign('pur_pos_id')->references('id')->on('pur_pos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pur_pos_details');
    }
};
