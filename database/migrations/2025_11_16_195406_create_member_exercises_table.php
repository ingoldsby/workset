<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('member_exercises', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('category');
            $table->string('primary_muscle');
            $table->json('secondary_muscles')->nullable();
            $table->string('equipment');
            $table->string('mechanics')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'name']);
        });
    }
};
