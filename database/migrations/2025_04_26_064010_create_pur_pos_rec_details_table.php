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
        Schema::create('pur_pos_rec_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pur_pos_rec_id');
            $table->unsignedBigInteger('product_id');
            $table->string('sku');
            $table->unsignedBigInteger('qty');
            $table->decimal('rate', 10, 2)->default(0);
            $table->timestamps();

            $table->foreign('pur_pos_rec_id')->references('id')->on('pur_pos_rec')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pur_pos_rec_details');
    }
};
