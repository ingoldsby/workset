<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recycle_bin', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('user_id');
            $table->string('recyclable_type');
            $table->ulid('recyclable_id');
            $table->json('data');
            $table->timestamp('deleted_at');
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->index(['recyclable_type', 'recyclable_id']);
            $table->index('user_id');
            $table->index('expires_at');
        });
    }
};
