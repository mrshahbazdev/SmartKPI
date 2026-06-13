<template>
  <AppShell>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-1">
            <a href="/dashboard" class="hover:text-primary-600">{{ $t('nav.dashboard') }}</a>
            <span>/</span>
            <span>{{ company.name }}</span>
          </div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ company.name }}</h1>
          <p v-if="company.industry" class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ company.industry }}</p>
        </div>
        <select v-model="selectedDays" @change="changeRange" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
          <option :value="7">7 {{ $t('common.days') }}</option>
          <option :value="30">30 {{ $t('common.days') }}</option>
          <option :value="90">90 {{ $t('common.days') }}</option>
          <option :value="365">{{ $t('common.ytd') }}</option>
        </select>
      </div>

      <!-- Company-level KPIs -->
      <div v-if="companyKpis.length" class="space-y-3">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200">{{ $t('dashboard.company_kpis') }}</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <div v-for="kpi in companyKpis" :key="kpi.id" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ locale === 'de' ? kpi.name_de : kpi.name_en }}</p>
            <div class="flex items-end gap-2 mt-1">
              <span class="text-2xl font-bold text-gray-900 dark:text-white">{{ formatVal(kpi.current_value, kpi.unit) }}</span>
              <StatusBadge :status="kpi.status" />
            </div>
          </div>
        </div>
      </div>

      <!-- Department Summaries -->
      <div class="space-y-3">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200">{{ $t('org.departments') }}</h2>

        <!-- Desktop: Table view -->
        <div class="hidden md:block bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
          <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-700/50">
              <tr>
                <th class="px-4 py-3 text-left text-gray-500 dark:text-gray-400 font-medium">{{ $t('org.department') }}</th>
                <th class="px-4 py-3 text-center text-gray-500 dark:text-gray-400 font-medium">KPIs</th>
                <th class="px-4 py-3 text-center text-gray-500 dark:text-gray-400 font-medium">{{ $t('kpi.on_target') }}</th>
                <th class="px-4 py-3 text-center text-gray-500 dark:text-gray-400 font-medium">{{ $t('kpi.warning') }}</th>
                <th class="px-4 py-3 text-center text-gray-500 dark:text-gray-400 font-medium">{{ $t('kpi.critical') }}</th>
                <th class="px-4 py-3 text-center text-gray-500 dark:text-gray-400 font-medium">{{ $t('dashboard.risk_score') }}</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
              <tr v-for="dept in departments" :key="dept.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/30 cursor-pointer" @click="drillDown(dept.id)">
                <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ dept.name }}</td>
                <td class="px-4 py-3 text-center text-gray-600 dark:text-gray-400">{{ dept.kpi_count }}</td>
                <td class="px-4 py-3 text-center"><span class="text-green-600">{{ dept.statuses.on_target }}</span></td>
                <td class="px-4 py-3 text-center"><span class="text-yellow-600">{{ dept.statuses.warning }}</span></td>
                <td class="px-4 py-3 text-center"><span class="text-red-600">{{ dept.statuses.critical }}</span></td>
                <td class="px-4 py-3 text-center">
                  <span class="px-2 py-0.5 rounded-full text-xs font-medium" :class="riskClass(dept.risk_score)">{{ dept.risk_score }}</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Mobile: Card view -->
        <div class="md:hidden space-y-3">
          <a v-for="dept in departments" :key="dept.id" :href="`/dashboard/department/${dept.id}`"
            class="block bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4"
          >
            <div class="flex items-center justify-between mb-2">
              <h3 class="font-medium text-gray-900 dark:text-white">{{ dept.name }}</h3>
              <span class="px-2 py-0.5 rounded-full text-xs font-medium" :class="riskClass(dept.risk_score)">{{ dept.risk_score }}</span>
            </div>
            <div class="flex gap-3 text-xs">
              <span class="text-green-600">{{ dept.statuses.on_target }} {{ $t('kpi.on_target') }}</span>
              <span class="text-yellow-600">{{ dept.statuses.warning }} {{ $t('kpi.warning') }}</span>
              <span class="text-red-600">{{ dept.statuses.critical }} {{ $t('kpi.critical') }}</span>
            </div>
          </a>
        </div>
      </div>

      <!-- Top Risks & Wins -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
          <h3 class="font-semibold text-red-600 mb-3">{{ $t('dashboard.top_risks') }}</h3>
          <div class="space-y-2">
            <div v-for="r in topRisks" :key="r.id" class="flex items-center justify-between text-sm">
              <span class="text-gray-700 dark:text-gray-300">{{ r.name }}</span>
              <span class="font-mono text-red-600">{{ r.risk_score }}</span>
            </div>
            <p v-if="!topRisks.length" class="text-sm text-gray-400">—</p>
          </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
          <h3 class="font-semibold text-green-600 mb-3">{{ $t('dashboard.top_wins') }}</h3>
          <div class="space-y-2">
            <div v-for="w in topWins" :key="w.id" class="flex items-center justify-between text-sm">
              <span class="text-gray-700 dark:text-gray-300">{{ w.name }}</span>
              <span class="font-mono text-green-600">{{ w.risk_score }}</span>
            </div>
            <p v-if="!topWins.length" class="text-sm text-gray-400">—</p>
          </div>
        </div>
      </div>
    </div>
  </AppShell>
</template>

<script setup>
import { computed, ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import AppShell from '@/Components/Layout/AppShell.vue';
import StatusBadge from '@/Components/Dashboard/StatusBadge.vue';

const props = defineProps({
  company: { type: Object, required: true },
  departments: { type: Array, default: () => [] },
  topRisks: { type: Array, default: () => [] },
  topWins: { type: Array, default: () => [] },
  companyKpis: { type: Array, default: () => [] },
  days: { type: Number, default: 30 },
});

const page = usePage();
const locale = computed(() => page.props.locale || 'de');
const selectedDays = ref(props.days);

function changeRange() {
  router.get(`/dashboard/company/${props.company.id}`, { days: selectedDays.value }, { preserveState: true });
}

function drillDown(deptId) {
  router.visit(`/dashboard/department/${deptId}`);
}

function formatVal(v, unit) {
  if (v === null || v === undefined) return '—';
  const num = locale.value === 'de' ? v.toLocaleString('de-DE', { maximumFractionDigits: 2 }) : v.toLocaleString('en-US', { maximumFractionDigits: 2 });
  if (unit === '%') return `${num}%`;
  if (unit === 'EUR') return `€${num}`;
  if (unit) return `${num} ${unit}`;
  return num;
}

function riskClass(score) {
  if (score >= 60) return 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400';
  if (score >= 30) return 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400';
  return 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400';
}
</script>
