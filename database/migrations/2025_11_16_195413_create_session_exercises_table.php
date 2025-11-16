<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('session_exercises', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('training_session_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('exercise_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUlid('member_exercise_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedSmallInteger('order')->default(0);
            $table->string('superset_group')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['training_session_id', 'order']);
            $table->index('exercise_id');
            $table->index('member_exercise_id');
        });
    }
};
