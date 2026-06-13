<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forecasts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kpi_definition_id')->constrained('kpi_definitions')->cascadeOnDelete();
            $table->date('forecast_date');
            $table->decimal('predicted_value', 20, 4);
            $table->decimal('lower_bound', 20, 4)->nullable();
            $table->decimal('upper_bound', 20, 4)->nullable();
            $table->decimal('confidence', 5, 2)->default(0.8);
            $table->string('horizon'); // 7d, 30d, 90d
            $table->string('method')->default('linear'); // linear, moving_avg, exponential
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forecasts');
    }
};
