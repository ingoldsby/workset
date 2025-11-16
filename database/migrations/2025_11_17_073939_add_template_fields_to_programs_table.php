<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->boolean('is_template')->default(false)->after('visibility');
            $table->string('category')->nullable()->after('is_template');
            $table->text('tags')->nullable()->after('category');
            $table->integer('install_count')->default(0)->after('tags');
            $table->foreignUlid('cloned_from_id')->nullable()->after('install_count')->constrained('programs')->nullOnDelete();

            $table->index('is_template');
            $table->index('category');
        });
    }
};
