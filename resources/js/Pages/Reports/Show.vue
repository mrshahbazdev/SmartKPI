<template>
  <AppShell>
    <div class="max-w-4xl mx-auto space-y-6" id="report-content">
      <div class="flex items-center justify-between">
        <div>
          <a href="/reports" class="text-sm text-gray-500 dark:text-gray-400 hover:text-primary-600">← {{ $t('common.back') }}</a>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white mt-2">{{ title }}</h1>
          <p class="text-sm text-gray-500 dark:text-gray-400">{{ formatDate(generatedAt) }}</p>
        </div>
        <button @click="printReport" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm rounded-lg hover:bg-gray-200 print:hidden">
          {{ language === 'de' ? 'Drucken / PDF' : 'Print / PDF' }}
        </button>
      </div>

      <!-- Company sections -->
      <div v-for="(cd, i) in reportData" :key="i" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 print:shadow-none print:border">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">{{ cd.company }}</h2>

        <!-- Summary cards -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
          <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3 text-center">
            <p class="text-2xl font-bold" :class="cd.risk_score >= 70 ? 'text-red-600' : cd.risk_score >= 40 ? 'text-yellow-600' : 'text-green-600'">{{ Math.round(cd.risk_score) }}</p>
            <p class="text-xs text-gray-500">{{ language === 'de' ? 'Risiko-Score' : 'Risk Score' }}</p>
          </div>
          <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3 text-center">
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ cd.kpi_health }}%</p>
            <p class="text-xs text-gray-500">{{ language === 'de' ? 'KPI-Gesundheit' : 'KPI Health' }}</p>
          </div>
          <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3 text-center">
            <p class="text-2xl font-bold text-orange-600">{{ cd.open_problems }}</p>
            <p class="text-xs text-gray-500">{{ language === 'de' ? 'Offene Probleme' : 'Open Problems' }}</p>
          </div>
          <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3 text-center">
            <p class="text-2xl font-bold text-green-600">{{ cd.resolved_problems }}</p>
            <p class="text-xs text-gray-500">{{ language === 'de' ? 'Gelöst' : 'Resolved' }}</p>
          </div>
        </div>

        <!-- KPI table -->
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-gray-200 dark:border-gray-700">
              <th class="py-2 text-left text-gray-500 font-medium">KPI</th>
              <th class="py-2 text-right text-gray-500 font-medium">{{ language === 'de' ? 'Aktuell' : 'Current' }}</th>
              <th class="py-2 text-right text-gray-500 font-medium">{{ language === 'de' ? 'Ziel' : 'Target' }}</th>
              <th class="py-2 text-center text-gray-500 font-medium">Status</th>
              <th class="py-2 text-right text-gray-500 font-medium hidden sm:table-cell">∅</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(k, j) in cd.kpis" :key="j" class="border-b border-gray-100 dark:border-gray-700/50">
              <td class="py-2 text-gray-700 dark:text-gray-300">{{ k.name }}</td>
              <td class="py-2 text-right font-medium text-gray-900 dark:text-white">{{ k.current_value !== null ? formatVal(k.current_value) : '—' }} <span class="text-xs text-gray-400">{{ k.unit }}</span></td>
              <td class="py-2 text-right text-gray-500">{{ k.target !== null ? formatVal(k.target) : '—' }}</td>
              <td class="py-2 text-center">
                <span class="px-2 py-0.5 text-xs rounded-full" :class="statusBadge(k.status)">{{ k.status }}</span>
              </td>
              <td class="py-2 text-right text-gray-500 hidden sm:table-cell">{{ k.trend_avg !== null ? formatVal(k.trend_avg) : '—' }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppShell>
</template>

<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import AppShell from '@/Components/Layout/AppShell.vue';

const props = defineProps({
  reportData: { type: Array, default: () => [] },
  title: { type: String, default: '' },
  language: { type: String, default: 'de' },
  period: { type: String, default: 'month' },
  generatedAt: { type: String, default: '' },
});

const page = usePage();
const locale = computed(() => page.props.locale || 'de');

function formatVal(v) {
  if (v === null || v === undefined) return '—';
  return props.language === 'de' ? parseFloat(v).toLocaleString('de-DE', { maximumFractionDigits: 2 }) : parseFloat(v).toLocaleString('en-US', { maximumFractionDigits: 2 });
}

function formatDate(d) {
  if (!d) return '';
  return new Date(d).toLocaleDateString(props.language === 'de' ? 'de-DE' : 'en-US', { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' });
}

function statusBadge(s) {
  return { on_target: 'bg-green-100 text-green-700', warning: 'bg-yellow-100 text-yellow-700', critical: 'bg-red-100 text-red-700', no_data: 'bg-gray-100 text-gray-500' }[s] || 'bg-gray-100 text-gray-500';
}

function printReport() {
  window.print();
}
</script>
