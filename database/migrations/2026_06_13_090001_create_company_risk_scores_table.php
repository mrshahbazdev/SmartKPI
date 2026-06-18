<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_risk_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->date('score_date');
            $table->decimal('risk_score', 5, 2); // 0-100
            $table->decimal('kpi_health', 5, 2)->default(0); // % of KPIs on target
            $table->integer('open_problems')->default(0);
            $table->integer('critical_problems')->default(0);
            $table->integer('overdue_actions')->default(0);
            $table->json('breakdown')->nullable(); // detailed risk factors
            $table->timestamps();
            $table->unique(['company_id', 'score_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_risk_scores');
    }
};
