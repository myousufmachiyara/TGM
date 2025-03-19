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
        Schema::create('pur_fgpos', function (Blueprint $table) {
            $table->id();
            $table->string('doc_code')->default('FGPO');
            $table->unsignedBigInteger('vendor_id');
            $table->date('order_date');
            $table->timestamps();
            $table->softDeletes();
            $table->unsignedBigInteger('created_by')->default(0);
            $table->unsignedBigInteger('updated_by')->default(0);
            
            $table->foreign('vendor_id')->references('id')->on('chart_of_accounts')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pur_fgpos');
    }
};
