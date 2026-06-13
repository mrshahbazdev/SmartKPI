<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name_de');
            $table->string('name_en');
            $table->text('description_de')->nullable();
            $table->text('description_en')->nullable();
            $table->decimal('price_monthly', 10, 2);
            $table->decimal('price_yearly', 10, 2)->nullable();
            $table->string('currency')->default('EUR');
            $table->integer('max_companies')->default(1);
            $table->integer('max_departments')->default(3);
            $table->integer('max_kpis')->default(20);
            $table->integer('max_users')->default(5);
            $table->boolean('has_api_access')->default(false);
            $table->boolean('has_forecasting')->default(false);
            $table->boolean('has_cross_company')->default(false);
            $table->boolean('has_white_label')->default(false);
            $table->json('features')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('tenant_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->foreignId('plan_id')->constrained('subscription_plans');
            $table->string('status')->default('active'); // active, cancelled, past_due, trialing
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('current_period_start')->nullable();
            $table->timestamp('current_period_end')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('stripe_subscription_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_subscriptions');
        Schema::dropIfExists('subscription_plans');
    }
};
