<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_focus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->foreignId('action_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('problem_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('focus_date');
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('high');
            $table->timestamps();

            $table->unique(['department_id', 'focus_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_focus');
    }
};
