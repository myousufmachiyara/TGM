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
        Schema::create('fgpo_bills', function (Blueprint $table) {
            $table->id(); // Bill No (Primary Key)
            $table->date('bill_date'); // Bill Date
            $table->unsignedBigInteger('vendor_id'); // Vendor (FK from chart_of_accounts)
            $table->string('ref_bill_no')->nullable(); // Reference Bill #            
            $table->timestamps();

            $table->foreign('vendor_id')->references('id')->on('chart_of_accounts')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fgpo_bills');
    }
};
