<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('program_day_exercises', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('program_day_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('exercise_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUlid('member_exercise_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedSmallInteger('order')->default(0);
            $table->string('superset_group')->nullable();
            $table->unsignedSmallInteger('sets');
            $table->unsignedSmallInteger('reps_min')->nullable();
            $table->unsignedSmallInteger('reps_max')->nullable();
            $table->unsignedSmallInteger('rpe')->nullable();
            $table->unsignedInteger('rest_seconds')->nullable();
            $table->string('tempo')->nullable();
            $table->text('notes')->nullable();
            $table->json('progression_rules')->nullable();
            $table->timestamps();

            $table->index(['program_day_id', 'order']);
            $table->index('exercise_id');
            $table->index('member_exercise_id');
        });
    }
};
