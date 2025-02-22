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
            $table->string('product_sku')->nullable();
            $table->string('description');
            $table->double('qty', 15, 2)->default(0);
            $table->double('rate', 15, 2)->default(0);
            $table->string('unit');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('fgpo_id')->references('id')->on('pur_fgpos')->onDelete('cascade');
            $table->foreign('voucher_id')->references('id')->on('jv1')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
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
