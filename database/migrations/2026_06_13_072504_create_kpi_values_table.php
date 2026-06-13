<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kpi_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kpi_definition_id')->constrained()->cascadeOnDelete();
            $table->decimal('value', 20, 4);
            $table->date('recorded_at');
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['on_target', 'warning', 'critical'])->default('on_target');
            $table->timestamps();

            $table->index(['kpi_definition_id', 'recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kpi_values');
    }
};
