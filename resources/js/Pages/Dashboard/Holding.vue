<template>
  <AppShell>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ tenant.name }}</h1>
          <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $t('dashboard.holding_overview') }}</p>
        </div>
        <select v-model="selectedDays" @change="changeRange" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
          <option :value="7">7 {{ $t('common.days') }}</option>
          <option :value="30">30 {{ $t('common.days') }}</option>
          <option :value="90">90 {{ $t('common.days') }}</option>
          <option :value="365">{{ $t('common.ytd') }}</option>
        </select>
      </div>

      <!-- Company Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <a
          v-for="c in companies"
          :key="c.id"
          :href="`/dashboard/company/${c.id}`"
          class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5 hover:shadow-md transition-shadow"
        >
          <div class="flex items-start justify-between mb-3">
            <div>
              <h3 class="font-semibold text-gray-900 dark:text-white">{{ c.name }}</h3>
              <p v-if="c.industry" class="text-xs text-gray-500 dark:text-gray-400">{{ c.industry }}</p>
            </div>
            <span class="px-2 py-0.5 rounded-full text-xs font-medium" :class="riskClass(c.risk_score)">{{ c.risk_score }}</span>
          </div>
          <div class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
            <p>{{ c.department_count }} {{ $t('org.departments') }} · {{ c.kpi_count }} KPIs</p>
          </div>
          <!-- Mini traffic light -->
          <div class="mt-3 flex gap-3 text-xs">
            <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-green-500"></span> {{ c.statuses.on_target }}</span>
            <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-yellow-500"></span> {{ c.statuses.warning }}</span>
            <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-red-500"></span> {{ c.statuses.critical }}</span>
          </div>
          <!-- Risk bar -->
          <div class="mt-3 h-1.5 rounded-full bg-gray-100 dark:bg-gray-700 overflow-hidden">
            <div class="h-full rounded-full transition-all" :class="riskBarClass(c.risk_score)" :style="{ width: Math.min(c.risk_score, 100) + '%' }"></div>
          </div>
        </a>
      </div>

      <!-- Comparative Bar Chart (simple SVG) -->
      <div v-if="companies.length > 1" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ $t('dashboard.risk_comparison') }}</h3>
        <div class="space-y-3">
          <div v-for="c in sortedByRisk" :key="c.id" class="flex items-center gap-3">
            <span class="text-sm text-gray-700 dark:text-gray-300 w-32 truncate">{{ c.name }}</span>
            <div class="flex-1 h-6 rounded bg-gray-100 dark:bg-gray-700 overflow-hidden">
              <div class="h-full rounded transition-all flex items-center pl-2 text-xs text-white font-medium"
                :class="riskBarClass(c.risk_score)"
                :style="{ width: Math.max(Math.min(c.risk_score, 100), 5) + '%' }">
                {{ c.risk_score }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppShell>
</template>

<script setup>
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppShell from '@/Components/Layout/AppShell.vue';

const props = defineProps({
  tenant: { type: Object, required: true },
  companies: { type: Array, default: () => [] },
  days: { type: Number, default: 30 },
});

const selectedDays = ref(props.days);
const sortedByRisk = computed(() => [...props.companies].sort((a, b) => b.risk_score - a.risk_score));

function changeRange() {
  router.get(`/dashboard/holding/${props.tenant.id}`, { days: selectedDays.value }, { preserveState: true });
}

function riskClass(score) {
  if (score >= 60) return 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400';
  if (score >= 30) return 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400';
  return 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400';
}

function riskBarClass(score) {
  if (score >= 60) return 'bg-red-500';
  if (score >= 30) return 'bg-yellow-500';
  return 'bg-green-500';
}
</script>
