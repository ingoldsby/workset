<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_snapshots', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('user_id');
            $table->string('snapshot_type');
            $table->date('snapshot_date');
            $table->json('data');
            $table->timestamps();

            $table->index(['user_id', 'snapshot_type', 'snapshot_date']);
            $table->index('snapshot_date');
        });
    }
};
