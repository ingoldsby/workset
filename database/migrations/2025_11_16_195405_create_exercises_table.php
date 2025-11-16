<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exercises', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('category');
            $table->string('primary_muscle');
            $table->json('secondary_muscles')->nullable();
            $table->string('equipment')->nullable();
            $table->json('equipment_variants')->nullable();
            $table->string('mechanics')->nullable();
            $table->string('level')->nullable();
            $table->json('aliases')->nullable();
            $table->unsignedInteger('wger_id')->nullable()->unique();
            $table->string('language', 10)->default('en-AU');
            $table->timestamps();

            $table->index('name');
            $table->index('category');
            $table->index('primary_muscle');
            $table->index('equipment');
        });
    }
};
