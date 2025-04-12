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
            $table->unsignedBigInteger('pur_pos_id');
            $table->text('description')->nullable();
            $table->decimal('width');
            $table->unsignedBigInteger('item_id');
            $table->double('item_rate', 10, 2);
            $table->decimal('item_qty', 10, 2);
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('item_id')->references('id')->on('products')->onDelete('cascade');
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
