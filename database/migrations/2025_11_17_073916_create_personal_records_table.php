<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personal_records', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('exercise_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUlid('member_exercise_id')->nullable()->constrained()->nullOnDelete();
            $table->string('record_type'); // max_weight, max_volume, max_reps_at_weight
            $table->decimal('weight', 10, 2)->nullable();
            $table->integer('reps')->nullable();
            $table->decimal('volume', 12, 2)->nullable();
            $table->foreignUlid('session_set_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('achieved_at');
            $table->timestamps();

            $table->index(['user_id', 'exercise_id', 'record_type']);
            $table->index(['user_id', 'member_exercise_id', 'record_type']);
            $table->index('achieved_at');
        });
    }
};
