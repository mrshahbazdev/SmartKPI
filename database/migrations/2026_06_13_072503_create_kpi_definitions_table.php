<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kpi_definitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('name_de');
            $table->string('name_en');
            $table->text('description_de')->nullable();
            $table->text('description_en')->nullable();
            $table->string('formula')->nullable();
            $table->string('unit')->nullable();
            $table->decimal('target_value', 20, 4)->nullable();
            $table->decimal('warning_threshold', 20, 4)->nullable();
            $table->decimal('critical_threshold', 20, 4)->nullable();
            $table->enum('frequency', ['daily', 'weekly', 'monthly', 'quarterly', 'yearly'])->default('monthly');
            $table->enum('direction', ['higher_better', 'lower_better'])->default('higher_better');
            $table->string('category')->nullable();
            $table->boolean('is_template')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kpi_definitions');
    }
};
