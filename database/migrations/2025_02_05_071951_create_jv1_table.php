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
        Schema::create('jv1', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->unsignedBigInteger('debit_acc_id'); 
            $table->unsignedBigInteger('credit_acc_id');
            $table->double('amount', 15, 2);
            $table->date('date');
            $table->string('narration', 800)->nullable(); 
            $table->string('ref_doc', 800);
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->default(0); 
            $table->unsignedBigInteger('updated_by')->default(0);
            $table->softDeletes();

            $table->foreign('debit_acc_id')->references('id')->on('chart_of_accounts')->onDelete('cascade');
            $table->foreign('credit_acc_id')->references('id')->on('chart_of_accounts')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jv1');
    }
};
