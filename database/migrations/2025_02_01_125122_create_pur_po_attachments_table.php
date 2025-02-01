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
        Schema::create('pur_po_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pur_po_id')->constrained('pur_pos')->cascadeOnDelete(); // Foreign key to products
            $table->string('att_path'); // Path to the attachements
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pur_po_attachments');
    }
};
