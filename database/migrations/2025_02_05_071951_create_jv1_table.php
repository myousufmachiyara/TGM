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
            $table->unsignedBigInteger('debit_acc_id'); // Debit account ID
            $table->unsignedBigInteger('credit_acc_id'); // Credit account ID
            $table->double('amount', 15, 2)->default(0); // Transaction amount
            $table->date('date'); // Transaction date
            $table->string('narration', 800)->nullable(); // Additional remarks
            $table->timestamps(); // created_at and updated_at
            // $table->unsignedBigInteger('created_by')->default(0); // User who created the entry
            // $table->unsignedBigInteger('updated_by')->default(0); // User who last updated the entry
            $table->softDeletes();

            // Foreign keys (optional, if accounts table exists)
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
