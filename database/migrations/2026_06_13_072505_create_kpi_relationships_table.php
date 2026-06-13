<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kpi_relationships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cause_kpi_id')->constrained('kpi_definitions')->cascadeOnDelete();
            $table->foreignId('effect_kpi_id')->constrained('kpi_definitions')->cascadeOnDelete();
            $table->decimal('weight', 5, 2)->default(1.00);
            $table->decimal('confidence', 5, 2)->default(0.50);
            $table->integer('lag_days')->default(0);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kpi_relationships');
    }
};
