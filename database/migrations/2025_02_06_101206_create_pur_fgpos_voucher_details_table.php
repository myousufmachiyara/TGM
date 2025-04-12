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
        Schema::create('pur_fgpos_voucher_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fgpo_id');
            $table->unsignedBigInteger('voucher_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('po_id');
            $table->string('product_sku')->nullable();
            $table->string('description')->nullable();
            $table->double('width', 15, 2);
            $table->double('qty', 15, 2);
            $table->double('rate', 15, 2);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('fgpo_id')->references('id')->on('pur_fgpos')->onDelete('cascade');
            $table->foreign('voucher_id')->references('id')->on('jv1')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('po_id')->references('id')->on('pur_pos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pur_fgpos_voucher_details');
    }
};
