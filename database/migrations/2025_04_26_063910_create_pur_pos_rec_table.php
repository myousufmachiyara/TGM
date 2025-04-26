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
        Schema::create('pur_pos_rec', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('po_id');
            $table->date('rec_date');
            $table->timestamps();
            $table->softDeletes();

            $table->unsignedBigInteger('created_by')->default(0);
            $table->unsignedBigInteger('updated_by')->default(0);

            $table->foreign('po_id')->references('id')->on('pur_pos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pur_pos_rec');
    }
};
