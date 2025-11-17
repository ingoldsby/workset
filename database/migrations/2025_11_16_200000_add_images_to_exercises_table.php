<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exercises', function (Blueprint $table) {
            if (! Schema::hasColumn('exercises', 'image_url')) {
                $table->string('image_url')->nullable()->after('language');
            }
            if (! Schema::hasColumn('exercises', 'thumbnail_url')) {
                $table->string('thumbnail_url')->nullable()->after('image_url');
            }
        });
    }
};
