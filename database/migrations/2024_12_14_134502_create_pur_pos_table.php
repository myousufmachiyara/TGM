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
        Schema::create('pur_pos', function (Blueprint $table) {
            $table->id();
            $table->string('fabric');               // A string for fabric name or type
            $table->decimal('rate', 10, 2);         // A decimal for rate, with 10 digits and 2 decimal places
            $table->decimal('quantity', 10, 2);     // A decimal for quantity, with 10 digits and 2 decimal places
            $table->string('payment_term');         // A string for payment term, it can also be text depending on length
            $table->date('delivery_date');          // A date column for the delivery date
            $table->string('vendor_name');          // A string for vendor name
            $table->timestamps();
        });
    }   

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pur_pos');
    }
};
