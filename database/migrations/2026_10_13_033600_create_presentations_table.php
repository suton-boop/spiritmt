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
        Schema::create('presentations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('folder_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('access_code', 12)->unique();
            $table->unsignedBigInteger('current_slide_id')->nullable();
            $table->string('theme')->default('default'); // default, dark, vibrant, emerald, sunset, minimal
            $table->string('voting_status')->default('open'); // open, closed
            $table->boolean('show_results')->default(true);
            $table->boolean('is_presenting')->default(false);
            $table->boolean('allow_questions')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presentations');
    }
};
