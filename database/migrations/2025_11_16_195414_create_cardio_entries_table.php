<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cardio_entries', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('training_session_id')->nullable()->constrained()->nullOnDelete();
            $table->string('cardio_type');
            $table->date('entry_date');
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->decimal('distance', 8, 2)->nullable();
            $table->string('distance_unit')->nullable();
            $table->unsignedSmallInteger('avg_heart_rate')->nullable();
            $table->unsignedSmallInteger('max_heart_rate')->nullable();
            $table->unsignedInteger('calories_burned')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'entry_date']);
            $table->index('training_session_id');
        });
    }
};
