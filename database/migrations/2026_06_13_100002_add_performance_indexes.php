<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // KPI values — most queried table
        Schema::table('kpi_values', function (Blueprint $table) {
            $table->index(['kpi_definition_id', 'recorded_at'], 'kpi_values_kpi_date_idx');
            $table->index('status', 'kpi_values_status_idx');
        });

        // Problems — filtered by status, severity
        Schema::table('problems', function (Blueprint $table) {
            $table->index(['status', 'severity'], 'problems_status_severity_idx');
            $table->index('department_id', 'problems_department_idx');
        });

        // Actions — filtered by status, assigned_to
        Schema::table('actions', function (Blueprint $table) {
            $table->index(['status', 'assigned_to'], 'actions_status_user_idx');
            $table->index('deadline', 'actions_deadline_idx');
        });

        // KPI definitions — filtered by company, category
        Schema::table('kpi_definitions', function (Blueprint $table) {
            $table->index(['company_id', 'category'], 'kpi_defs_company_category_idx');
            $table->index('is_active', 'kpi_defs_active_idx');
        });

        // Company risk scores — queried by date
        Schema::table('company_risk_scores', function (Blueprint $table) {
            $table->index('score_date', 'risk_scores_date_idx');
        });

        // Activity log — queried by subject and event
        Schema::table('activity_log', function (Blueprint $table) {
            $table->index(['subject_type', 'subject_id'], 'activity_subject_idx');
            $table->index('event', 'activity_event_idx');
        });
    }

    public function down(): void
    {
        Schema::table('kpi_values', function (Blueprint $table) {
            $table->dropIndex('kpi_values_kpi_date_idx');
            $table->dropIndex('kpi_values_status_idx');
        });
        Schema::table('problems', function (Blueprint $table) {
            $table->dropIndex('problems_status_severity_idx');
            $table->dropIndex('problems_department_idx');
        });
        Schema::table('actions', function (Blueprint $table) {
            $table->dropIndex('actions_status_user_idx');
            $table->dropIndex('actions_deadline_idx');
        });
        Schema::table('kpi_definitions', function (Blueprint $table) {
            $table->dropIndex('kpi_defs_company_category_idx');
            $table->dropIndex('kpi_defs_active_idx');
        });
        Schema::table('company_risk_scores', function (Blueprint $table) {
            $table->dropIndex('risk_scores_date_idx');
        });
        Schema::table('activity_log', function (Blueprint $table) {
            $table->dropIndex('activity_subject_idx');
            $table->dropIndex('activity_event_idx');
        });
    }
};
