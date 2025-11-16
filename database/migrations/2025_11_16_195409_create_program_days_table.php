<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('program_days', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('program_version_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('day_number');
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('rest_days_after')->default(0);
            $table->timestamps();

            $table->unique(['program_version_id', 'day_number']);
            $table->index('program_version_id');
        });
    }
};
