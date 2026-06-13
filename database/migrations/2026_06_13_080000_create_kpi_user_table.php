<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kpi_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kpi_definition_id')->constrained('kpi_definitions')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role')->default('responsible'); // responsible, viewer, contributor
            $table->timestamps();
            $table->unique(['kpi_definition_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kpi_user');
    }
};
