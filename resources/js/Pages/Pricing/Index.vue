<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <div class="max-w-6xl mx-auto px-4 py-16 sm:py-24">
      <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-gray-900 dark:text-white sm:text-5xl">{{ $t('pricing.title') }}</h1>
        <p class="mt-4 text-lg text-gray-500 dark:text-gray-400 max-w-2xl mx-auto">{{ $t('pricing.subtitle') }}</p>
        <!-- Billing toggle -->
        <div class="mt-8 flex items-center justify-center gap-3">
          <span :class="!yearly ? 'text-gray-900 dark:text-white font-medium' : 'text-gray-500'">{{ $t('pricing.monthly') }}</span>
          <button @click="yearly = !yearly" class="relative w-14 h-7 rounded-full transition-colors" :class="yearly ? 'bg-primary-600' : 'bg-gray-300'">
            <span class="absolute w-5 h-5 top-1 rounded-full bg-white transition-transform" :class="yearly ? 'translate-x-8' : 'translate-x-1'"></span>
          </button>
          <span :class="yearly ? 'text-gray-900 dark:text-white font-medium' : 'text-gray-500'">{{ $t('pricing.yearly') }}
            <span class="text-xs text-green-600 font-medium ml-1">-20%</span>
          </span>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <div v-for="plan in plans" :key="plan.id"
          class="relative bg-white dark:bg-gray-800 rounded-2xl border-2 p-8 flex flex-col"
          :class="plan.slug === 'professional' ? 'border-primary-500 shadow-xl scale-105' : 'border-gray-200 dark:border-gray-700'">
          <div v-if="plan.slug === 'professional'" class="absolute -top-4 left-1/2 -translate-x-1/2 px-4 py-1 bg-primary-600 text-white text-xs font-medium rounded-full">
            {{ $t('pricing.popular') }}
          </div>
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ locale === 'de' ? plan.name_de : plan.name_en }}</h3>
          <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 min-h-[3rem]">{{ locale === 'de' ? plan.description_de : plan.description_en }}</p>
          <div class="mt-6 mb-6">
            <span class="text-4xl font-bold text-gray-900 dark:text-white">€{{ yearly && plan.price_yearly ? Math.round(plan.price_yearly / 12) : plan.price_monthly }}</span>
            <span class="text-gray-500">/{{ $t('pricing.per_month') }}</span>
          </div>

          <!-- Features -->
          <ul class="space-y-3 flex-1 mb-8">
            <li class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
              <span class="text-green-500">&#10003;</span>
              {{ plan.max_companies }} {{ $t('pricing.companies') }}
            </li>
            <li class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
              <span class="text-green-500">&#10003;</span>
              {{ plan.max_kpis }} KPIs
            </li>
            <li class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
              <span class="text-green-500">&#10003;</span>
              {{ plan.max_users }} {{ $t('pricing.users') }}
            </li>
            <li v-if="plan.has_api_access" class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
              <span class="text-green-500">&#10003;</span>
              REST API
            </li>
            <li v-if="plan.has_forecasting" class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
              <span class="text-green-500">&#10003;</span>
              {{ $t('pricing.forecasting') }}
            </li>
            <li v-if="plan.has_cross_company" class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
              <span class="text-green-500">&#10003;</span>
              {{ $t('pricing.cross_company') }}
            </li>
            <li v-if="plan.has_white_label" class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
              <span class="text-green-500">&#10003;</span>
              {{ $t('pricing.white_label') }}
            </li>
          </ul>

          <button class="w-full px-6 py-3 rounded-lg font-medium transition-colors"
            :class="plan.slug === 'professional' ? 'bg-primary-600 text-white hover:bg-primary-700' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200'">
            {{ $t('pricing.select_plan') }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

defineProps({
  plans: { type: Array, default: () => [] },
});

const page = usePage();
const locale = computed(() => page.props.locale || 'de');
const yearly = ref(false);
</script>
