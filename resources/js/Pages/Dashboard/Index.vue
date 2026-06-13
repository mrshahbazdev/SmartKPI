<template>
  <AppShell>
    <div class="space-y-6">
      <!-- Page Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
            {{ $t('dashboard.title') }}
          </h1>
          <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
            {{ $t('dashboard.welcome') }}, {{ auth?.name }}
          </p>
        </div>
        <div class="flex gap-2">
          <select class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
            <option value="7d">7 {{ $t('common.date') }}</option>
            <option value="30d" selected>30 {{ $t('common.date') }}</option>
            <option value="90d">90 {{ $t('common.date') }}</option>
          </select>
        </div>
      </div>

      <!-- KPI Cards Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        <div
          v-for="kpi in demoKpis"
          :key="kpi.id"
          class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5 hover:shadow-md transition-shadow"
        >
          <div class="flex items-start justify-between">
            <div>
              <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                {{ kpi.name }}
              </p>
              <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
                {{ kpi.value }}
              </p>
            </div>
            <span
              class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
              :class="statusClass(kpi.status)"
            >
              {{ $t(`kpi.${kpi.status}`) }}
            </span>
          </div>
          <div class="mt-3 flex items-center gap-1 text-sm">
            <span :class="kpi.trend > 0 ? 'text-green-600' : 'text-red-600'">
              {{ kpi.trend > 0 ? '+' : '' }}{{ kpi.trend }}%
            </span>
            <span class="text-gray-400">{{ $t('dashboard.trend') }}</span>
          </div>
        </div>
      </div>

      <!-- Daily Focus -->
      <div class="bg-gradient-to-r from-primary-600 to-primary-700 rounded-xl p-6 text-white">
        <h2 class="text-lg font-semibold mb-2">{{ $t('dashboard.daily_focus') }}</h2>
        <p class="text-primary-100 text-sm">
          {{ locale === 'de' ? 'Fehlerquote in der Fertigung senken — aktuell bei 6.2%, Ziel: 5%' : 'Reduce error rate in manufacturing — currently at 6.2%, target: 5%' }}
        </p>
      </div>

      <!-- Two Column Layout -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Problems -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
            {{ $t('dashboard.recent_problems') }}
          </h3>
          <div class="space-y-3">
            <div
              v-for="problem in demoProblems"
              :key="problem.id"
              class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-gray-700/50"
            >
              <div class="flex items-center gap-3">
                <span
                  class="w-2 h-2 rounded-full"
                  :class="{
                    'bg-red-500': problem.severity === 'critical',
                    'bg-orange-500': problem.severity === 'high',
                    'bg-yellow-500': problem.severity === 'medium',
                    'bg-blue-500': problem.severity === 'low',
                  }"
                ></span>
                <span class="text-sm text-gray-700 dark:text-gray-300">{{ problem.title }}</span>
              </div>
              <span class="text-xs text-gray-400">{{ problem.time }}</span>
            </div>
          </div>
        </div>

        <!-- Top Risks -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
            {{ $t('dashboard.top_risks') }}
          </h3>
          <div class="space-y-3">
            <div
              v-for="(risk, i) in demoRisks"
              :key="i"
              class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-gray-700/50"
            >
              <span class="text-sm text-gray-700 dark:text-gray-300">{{ risk.name }}</span>
              <div class="flex items-center gap-2">
                <div class="w-24 h-2 bg-gray-200 dark:bg-gray-600 rounded-full overflow-hidden">
                  <div
                    class="h-full rounded-full"
                    :class="risk.level > 75 ? 'bg-red-500' : risk.level > 50 ? 'bg-orange-500' : 'bg-yellow-500'"
                    :style="{ width: risk.level + '%' }"
                  ></div>
                </div>
                <span class="text-xs text-gray-500 w-8 text-right">{{ risk.level }}%</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppShell>
</template>

<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import AppShell from '@/Components/Layout/AppShell.vue';

const page = usePage();
const auth = computed(() => page.props.auth?.user);
const locale = computed(() => page.props.locale || 'de');

const demoKpis = [
  { id: 1, name: locale.value === 'de' ? 'Fehlerquote' : 'Error Rate', value: '6.2%', status: 'critical', trend: -2.1 },
  { id: 2, name: locale.value === 'de' ? 'Umsatz' : 'Revenue', value: '€124.5K', status: 'on_target', trend: 4.5 },
  { id: 3, name: locale.value === 'de' ? 'Durchlaufzeit' : 'Processing Time', value: '3.4h', status: 'warning', trend: -1.2 },
  { id: 4, name: locale.value === 'de' ? 'Kundenzufriedenheit' : 'Customer Satisfaction', value: '87%', status: 'on_target', trend: 2.3 },
];

const demoProblems = [
  { id: 1, title: locale.value === 'de' ? 'Fehlerquote über Schwellwert' : 'Error rate above threshold', severity: 'critical', time: '2h' },
  { id: 2, title: locale.value === 'de' ? 'Durchlaufzeit gestiegen' : 'Processing time increased', severity: 'high', time: '5h' },
  { id: 3, title: locale.value === 'de' ? 'Lieferverzögerung' : 'Delivery delay', severity: 'medium', time: '1d' },
];

const demoRisks = [
  { name: locale.value === 'de' ? 'Fertigung — Qualität' : 'Manufacturing — Quality', level: 82 },
  { name: locale.value === 'de' ? 'Vertrieb — Pipeline' : 'Sales — Pipeline', level: 65 },
  { name: locale.value === 'de' ? 'Personal — Fluktuation' : 'HR — Turnover', level: 45 },
  { name: locale.value === 'de' ? 'Finanzen — Cashflow' : 'Finance — Cashflow', level: 38 },
];

function statusClass(status) {
  return {
    on_target: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
    warning: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
    critical: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
  }[status];
}
</script>
