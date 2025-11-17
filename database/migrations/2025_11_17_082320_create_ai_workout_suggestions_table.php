<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_workout_suggestions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('generated_by')->constrained('users')->cascadeOnDelete();
            $table->string('suggestion_type');
            $table->json('prompt_context');
            $table->json('suggestion_data');
            $table->json('analysis_data')->nullable();
            $table->foreignUlid('applied_to_session_id')->nullable()->constrained('training_sessions')->nullOnDelete();
            $table->foreignUlid('applied_to_program_id')->nullable()->constrained('programs')->nullOnDelete();
            $table->timestamp('applied_at')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('generated_by');
            $table->index(['user_id', 'created_at']);
        });
    }
};
