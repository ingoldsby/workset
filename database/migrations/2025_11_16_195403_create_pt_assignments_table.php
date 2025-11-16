<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pt_assignments', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('pt_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUlid('member_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('assigned_at');
            $table->timestamp('unassigned_at')->nullable();
            $table->timestamps();

            $table->unique(['pt_id', 'member_id', 'assigned_at']);
        });
    }
};
