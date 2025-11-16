<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('session_sets', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('session_exercise_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('set_number');
            $table->string('set_type')->default('normal');

            // Prescribed values
            $table->unsignedSmallInteger('prescribed_reps')->nullable();
            $table->decimal('prescribed_weight', 8, 2)->nullable();
            $table->unsignedSmallInteger('prescribed_rpe')->nullable();

            // Performed values
            $table->unsignedSmallInteger('performed_reps')->nullable();
            $table->decimal('performed_weight', 8, 2)->nullable();
            $table->unsignedSmallInteger('performed_rpe')->nullable();
            $table->unsignedInteger('time_seconds')->nullable();
            $table->string('tempo')->nullable();

            // Completion tracking
            $table->boolean('completed')->default(false);
            $table->boolean('completed_as_prescribed')->default(false);
            $table->boolean('skipped')->default(false);
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            $table->index(['session_exercise_id', 'set_number']);
        });
    }
};
