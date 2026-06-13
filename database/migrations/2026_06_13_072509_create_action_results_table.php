<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('action_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('action_id')->constrained()->cascadeOnDelete();
            $table->decimal('kpi_value_before', 20, 4)->nullable();
            $table->decimal('kpi_value_after', 20, 4)->nullable();
            $table->text('notes')->nullable();
            $table->decimal('effectiveness_score', 5, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('action_results');
    }
};
