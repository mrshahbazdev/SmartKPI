<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cross_company_effects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_problem_id')->constrained('problems')->cascadeOnDelete();
            $table->foreignId('source_company_id')->constrained('companies');
            $table->foreignId('affected_company_id')->constrained('companies');
            $table->foreignId('affected_kpi_id')->nullable()->constrained('kpi_definitions')->nullOnDelete();
            $table->string('impact_type'); // supply_chain, customer_base, shared_resource, financial
            $table->decimal('impact_score', 5, 2)->default(0);
            $table->text('description')->nullable();
            $table->string('status')->default('detected'); // detected, confirmed, mitigated, resolved
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cross_company_effects');
    }
};
