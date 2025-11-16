<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invites', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('email');
            $table->string('token')->unique();
            $table->foreignUlid('invited_by')->constrained('users')->cascadeOnDelete();
            $table->foreignUlid('pt_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('role')->default('member');
            $table->timestamp('expires_at');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();

            $table->index(['email', 'accepted_at']);
        });
    }
};
