<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Indexes for training_sessions table
        Schema::table('training_sessions', function (Blueprint $table) {
            // Query sessions by completion status
            $table->index('completed_at');
            $table->index('started_at');
            // Combined index for user's sessions by status
            $table->index(['user_id', 'completed_at']);
            $table->index(['user_id', 'started_at']);
        });

        // Indexes for program_versions table
        Schema::table('program_versions', function (Blueprint $table) {
            // Index on is_active for filtering (program_id + is_active already exists)
            $table->index('is_active');
        });

        // Indexes for session_sets table
        Schema::table('session_sets', function (Blueprint $table) {
            // Filter sets by completion status
            $table->index(['session_exercise_id', 'completed']);
            $table->index('completed');
        });

        // Indexes for invites table
        Schema::table('invites', function (Blueprint $table) {
            // Lookup invite by email
            $table->index('email');
            // Find pending invites
            $table->index('accepted_at');
            $table->index('expires_at');
            // Combined index for active invites
            $table->index(['email', 'accepted_at', 'expires_at']);
        });

        // Indexes for exercises table
        Schema::table('exercises', function (Blueprint $table) {
            // Additional filtering indexes (name, category, primary_muscle, equipment already exist)
            $table->index('level');
            // Combined index for common filter combinations
            $table->index(['category', 'equipment']);
            $table->index(['primary_muscle', 'level']);
        });

        // Indexes for pt_assignments table
        Schema::table('pt_assignments', function (Blueprint $table) {
            // Find active assignments quickly
            $table->index('unassigned_at');
            $table->index(['member_id', 'unassigned_at']);
        });

        // Indexes for program_days table
        Schema::table('program_days', function (Blueprint $table) {
            // Order days within a version
            $table->index(['program_version_id', 'day_number']);
        });

        // Indexes for program_day_exercises table
        // Note: index on ['program_day_id', 'order'] already exists from create table migration
        // No additional indexes needed

        // Indexes for session_plans table
        Schema::table('session_plans', function (Blueprint $table) {
            // Find user's session plans by creation date (program_day_id already exists)
            $table->index(['user_id', 'created_at']);
        });

        // Indexes for member_exercises table
        Schema::table('member_exercises', function (Blueprint $table) {
            // Filter member's custom exercises
            $table->index(['user_id', 'is_active']);
            $table->index(['user_id', 'exercise_id']);
        });

        // Indexes for cardio_entries table
        Schema::table('cardio_entries', function (Blueprint $table) {
            // Fetch cardio entries for a session
            $table->index('session_id');
            $table->index(['user_id', 'created_at']);
        });

        // Indexes for analytics_snapshots table
        Schema::table('analytics_snapshots', function (Blueprint $table) {
            // Query snapshots by type and date (user_id + snapshot_type + snapshot_date already exists)
            $table->index(['snapshot_type', 'snapshot_date']);
        });

        // Indexes for audit_logs table
        Schema::table('audit_logs', function (Blueprint $table) {
            // Query audit logs (auditable_type + auditable_id and user_id already exist)
            $table->index(['user_id', 'created_at']);
            $table->index('action');
        });

        // Indexes for recycle_bin table
        Schema::table('recycle_bin', function (Blueprint $table) {
            // Query deleted items
            $table->index(['original_table', 'original_id']);
            $table->index('deleted_by');
            $table->index('deleted_at');
        });
    }
};
