<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_insights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kpi_definition_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('problem_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('type'); // anomaly, recommendation, insight, root_cause, action_suggestion, chat_response
            $table->text('title_de');
            $table->text('title_en');
            $table->longText('content_de');
            $table->longText('content_en');
            $table->json('metadata')->nullable(); // confidence, model, tokens, context
            $table->string('severity')->nullable(); // info, warning, critical
            $table->string('status')->default('active'); // active, dismissed, applied
            $table->timestamps();

            $table->index(['type', 'status']);
            $table->index(['kpi_definition_id', 'type']);
            $table->index(['company_id', 'type']);
        });

        Schema::create('ai_chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('session_id');
            $table->enum('role', ['user', 'assistant']);
            $table->longText('content');
            $table->json('context')->nullable(); // KPI data, charts, references
            $table->timestamps();

            $table->index(['user_id', 'session_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_chat_messages');
        Schema::dropIfExists('ai_insights');
    }
};
