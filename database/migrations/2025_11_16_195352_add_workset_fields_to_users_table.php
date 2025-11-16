<?php

use App\Enums\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default(Role::Member->value)->after('email');
            $table->string('timezone')->default('Australia/Brisbane')->after('role');
            $table->string('locale')->default('en-AU')->after('timezone');

            // Units & preferences
            $table->string('weight_unit')->default('kg')->after('locale');
            $table->string('distance_unit')->default('km')->after('weight_unit');
            $table->decimal('weight_rounding', 4, 2)->default(0.5)->after('distance_unit');
            $table->decimal('barbell_weight', 5, 2)->default(15)->after('weight_rounding');
            $table->boolean('show_pace_speed')->default(false)->after('barbell_weight');
            $table->boolean('dumbbell_pair_mode')->default(true)->after('show_pace_speed');

            // First run & onboarding
            $table->boolean('completed_onboarding')->default(false)->after('dumbbell_pair_mode');
            $table->timestamp('onboarded_at')->nullable()->after('completed_onboarding');

            // Soft deletes
            $table->softDeletes()->after('updated_at');
        });
    }
};
