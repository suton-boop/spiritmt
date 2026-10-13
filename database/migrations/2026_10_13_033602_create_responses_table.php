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
        Schema::create('responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('presentation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('slide_id')->constrained()->cascadeOnDelete();
            $table->string('participant_session');
            $table->string('participant_name')->nullable();
            $table->json('response_value');
            $table->integer('score')->default(0);
            $table->integer('response_time_ms')->nullable();
            $table->timestamps();

            $table->index(['slide_id', 'participant_session']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('responses');
    }
};
