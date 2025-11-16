<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workout_preferences', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('user_id')->constrained()->cascadeOnDelete();
            $table->json('weekly_schedule')->nullable();
            $table->json('focus_areas')->nullable();
            $table->integer('analysis_window_days')->default(14);
            $table->json('preferences')->nullable();
            $table->timestamps();

            $table->index('user_id');
        });
    }
};
