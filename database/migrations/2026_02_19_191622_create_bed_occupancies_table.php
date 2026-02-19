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
        Schema::create('bed_occupancies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bed_id')->constrained()->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->timestamp('admitted_at');
            $table->timestamp('discharged_at')->nullable();
            $table->timestamps();

            $table->index(['bed_id', 'discharged_at']);
            $table->index(['patient_id', 'discharged_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bed_occupancies');
    }
};
