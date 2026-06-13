<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('root_causes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('problem_id')->constrained()->cascadeOnDelete();
            $table->foreignId('kpi_relationship_id')->nullable()->constrained()->nullOnDelete();
            $table->text('description');
            $table->decimal('confidence', 5, 2)->default(0.50);
            $table->enum('source', ['manual', 'rule_based', 'ai'])->default('manual');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('root_causes');
    }
};
