<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scenarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users');
            $table->string('title');
            $table->text('description')->nullable();
            $table->json('assumptions'); // e.g. [{"kpi_id":1,"change":"+10%"}, {"kpi_id":2,"change":"-5%"}]
            $table->json('projected_outcomes')->nullable();
            $table->timestamps();
        });

        Schema::create('goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('kpi_definition_id')->nullable()->constrained('kpi_definitions')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('target_value', 20, 4)->nullable();
            $table->decimal('current_value', 20, 4)->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('status')->default('active'); // active, achieved, missed, cancelled
            $table->decimal('progress', 5, 2)->default(0); // 0-100%
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('webhooks', function (Blueprint $table) {
            $table->id();
            $table->string('url');
            $table->string('event'); // problem.created, action.completed, kpi.threshold_breach, etc.
            $table->string('secret')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_triggered_at')->nullable();
            $table->integer('failure_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhooks');
        Schema::dropIfExists('goals');
        Schema::dropIfExists('scenarios');
    }
};
