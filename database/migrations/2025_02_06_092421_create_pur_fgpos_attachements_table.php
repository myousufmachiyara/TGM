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
        Schema::create('pur_fgpos_attachements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fgpo_id')->constrained('pur_fgpos')->cascadeOnDelete();
            $table->string('att_path');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pur_fgpos_attachements');
    }
};
