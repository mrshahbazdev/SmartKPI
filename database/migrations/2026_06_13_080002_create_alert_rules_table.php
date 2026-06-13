<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alert_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('kpi_definition_id')->nullable()->constrained('kpi_definitions')->nullOnDelete();
            $table->string('name');
            $table->string('type'); // threshold, trend, anomaly
            $table->json('conditions'); // e.g. {"operator": ">", "value": 5, "consecutive": 3}
            $table->string('severity')->default('medium'); // low, medium, high, critical
            $table->boolean('is_active')->default(true);
            $table->boolean('auto_create_problem')->default(true);
            $table->foreignId('notify_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->integer('escalation_hours')->nullable(); // escalate if unresolved after X hours
            $table->foreignId('escalate_to_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alert_rules');
    }
};
