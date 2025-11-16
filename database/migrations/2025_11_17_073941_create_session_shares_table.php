<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('session_shares', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('training_session_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('shared_by_id')->constrained('users')->cascadeOnDelete();
            $table->string('share_token', 64)->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamp('expires_at')->nullable();
            $table->integer('view_count')->default(0);
            $table->timestamps();

            $table->index('share_token');
            $table->index(['training_session_id', 'is_active']);
        });
    }
};
