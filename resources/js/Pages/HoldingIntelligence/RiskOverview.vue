<template>
  <AppShell>
    <div class="space-y-6">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $t('holding.risk_overview') }}</h1>

      <!-- Risk cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <div v-for="score in scores" :key="score.company_id"
          class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
          <div class="flex items-center justify-between mb-3">
            <h3 class="font-semibold text-gray-900 dark:text-white">{{ getCompanyName(score.company_id) }}</h3>
            <div class="w-14 h-14 rounded-full flex items-center justify-center text-lg font-bold"
              :class="riskColor(score.risk_score)">
              {{ Math.round(score.risk_score) }}
            </div>
          </div>
          <div class="space-y-2 text-sm">
            <div class="flex justify-between">
              <span class="text-gray-500 dark:text-gray-400">{{ $t('holding.kpi_health') }}</span>
              <span class="font-medium" :class="score.kpi_health >= 70 ? 'text-green-600' : 'text-red-600'">{{ score.kpi_health }}%</span>
            </div>
            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
              <div class="h-2 rounded-full" :class="score.kpi_health >= 70 ? 'bg-green-500' : 'bg-red-500'" :style="{ width: score.kpi_health + '%' }"></div>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-500 dark:text-gray-400">{{ $t('holding.open_problems') }}</span>
              <span class="font-medium text-gray-900 dark:text-white">{{ score.open_problems }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-500 dark:text-gray-400">{{ $t('holding.critical') }}</span>
              <span class="font-medium" :class="score.critical_problems > 0 ? 'text-red-600' : 'text-green-600'">{{ score.critical_problems }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-500 dark:text-gray-400">{{ $t('holding.overdue_actions') }}</span>
              <span class="font-medium text-gray-900 dark:text-white">{{ score.overdue_actions }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Portfolio risk summary -->
      <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ $t('holding.portfolio_risk') }}</h2>
        <div class="flex flex-wrap gap-4">
          <div v-for="score in scores" :key="'bubble-'+score.company_id"
            class="relative flex items-center justify-center rounded-full text-white font-bold text-sm cursor-default"
            :class="riskBgColor(score.risk_score)"
            :style="{ width: bubbleSize(score) + 'px', height: bubbleSize(score) + 'px' }"
            :title="`${getCompanyName(score.company_id)}: ${score.risk_score}`">
            {{ getCompanyName(score.company_id).substring(0, 3) }}
          </div>
        </div>
      </div>
    </div>
  </AppShell>
</template>

<script setup>
import AppShell from '@/Components/Layout/AppShell.vue';

const props = defineProps({
  scores: { type: Array, default: () => [] },
  companies: { type: Array, default: () => [] },
  history: { type: Object, default: () => ({}) },
});

function getCompanyName(id) {
  return props.companies.find(c => c.id === id)?.name || '?';
}

function riskColor(score) {
  if (score >= 70) return 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400';
  if (score >= 40) return 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400';
  return 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400';
}

function riskBgColor(score) {
  if (score >= 70) return 'bg-red-500';
  if (score >= 40) return 'bg-yellow-500';
  return 'bg-green-500';
}

function bubbleSize(score) {
  return Math.max(60, Math.min(120, 60 + score.risk_score * 0.6));
}
</script>
