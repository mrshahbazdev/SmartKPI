<template>
  <AppShell>
    <div class="space-y-6">
      <!-- Page Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
            {{ $t('analysis.title') }}
          </h1>
          <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
            {{ $t('analysis.subtitle') }}
          </p>
        </div>
        <div class="flex gap-2">
          <select
            v-model="selectedDepartment"
            class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm"
          >
            <option value="all">{{ $t('common.all') }} {{ $t('nav.departments') }}</option>
            <option v-for="dept in demoDepartments" :key="dept.id" :value="dept.id">
              {{ dept.name }}
            </option>
          </select>
        </div>
      </div>

      <!-- Summary Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div
          v-for="card in summaryCards"
          :key="card.label"
          class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5"
        >
          <div class="flex items-center gap-3">
            <div
              class="flex-shrink-0 w-10 h-10 rounded-lg flex items-center justify-center"
              :class="card.bgClass"
            >
              <span class="w-5 h-5" :class="card.iconClass" v-html="card.icon"></span>
            </div>
            <div>
              <p class="text-sm text-gray-500 dark:text-gray-400">{{ card.label }}</p>
              <p class="text-xl font-bold text-gray-900 dark:text-white">{{ card.value }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Active Problems Table -->
      <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
        <div class="p-5 border-b border-gray-200 dark:border-gray-700">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
            {{ $t('analysis.active_problems') }}
          </h2>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="border-b border-gray-200 dark:border-gray-700 text-left">
                <th class="px-5 py-3 text-gray-500 dark:text-gray-400 font-medium">{{ $t('analysis.problem') }}</th>
                <th class="px-5 py-3 text-gray-500 dark:text-gray-400 font-medium">{{ $t('analysis.kpi') }}</th>
                <th class="px-5 py-3 text-gray-500 dark:text-gray-400 font-medium">{{ $t('problem.severity.high').split(' ')[0] ? $t('analysis.severity') : $t('analysis.severity') }}</th>
                <th class="px-5 py-3 text-gray-500 dark:text-gray-400 font-medium">{{ $t('analysis.root_causes') }}</th>
                <th class="px-5 py-3 text-gray-500 dark:text-gray-400 font-medium">{{ $t('common.status') }}</th>
                <th class="px-5 py-3 text-gray-500 dark:text-gray-400 font-medium">{{ $t('analysis.detected') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="problem in demoProblems"
                :key="problem.id"
                class="border-b border-gray-100 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors"
              >
                <td class="px-5 py-3 text-gray-900 dark:text-white font-medium">{{ problem.title }}</td>
                <td class="px-5 py-3 text-gray-600 dark:text-gray-400">{{ problem.kpi }}</td>
                <td class="px-5 py-3">
                  <span
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                    :class="severityClass(problem.severity)"
                  >
                    {{ $t(`problem.severity.${problem.severity}`) }}
                  </span>
                </td>
                <td class="px-5 py-3 text-gray-600 dark:text-gray-400">{{ problem.rootCauses }}</td>
                <td class="px-5 py-3">
                  <span
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                    :class="problemStatusClass(problem.status)"
                  >
                    {{ $t(`analysis.status_${problem.status}`) }}
                  </span>
                </td>
                <td class="px-5 py-3 text-gray-500 dark:text-gray-400">{{ problem.detected }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Two Column: Root Cause Breakdown + Cause-Effect -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Root Cause Breakdown -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
            {{ $t('analysis.root_cause_breakdown') }}
          </h3>
          <div class="space-y-4">
            <div v-for="cause in demoCauses" :key="cause.label">
              <div class="flex items-center justify-between mb-1">
                <span class="text-sm text-gray-700 dark:text-gray-300">{{ cause.label }}</span>
                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ cause.count }}</span>
              </div>
              <div class="w-full h-2 bg-gray-200 dark:bg-gray-600 rounded-full overflow-hidden">
                <div
                  class="h-full rounded-full transition-all"
                  :class="cause.barClass"
                  :style="{ width: cause.pct + '%' }"
                ></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Cause-Effect Relationships -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
            {{ $t('analysis.cause_effect') }}
          </h3>
          <div class="space-y-3">
            <div
              v-for="(rel, i) in demoRelationships"
              :key="i"
              class="flex items-center gap-3 p-3 rounded-lg bg-gray-50 dark:bg-gray-700/50"
            >
              <div class="flex-1 text-right">
                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ rel.cause }}</span>
              </div>
              <div class="flex-shrink-0">
                <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
              </div>
              <div class="flex-1">
                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ rel.effect }}</span>
              </div>
              <span
                class="flex-shrink-0 text-xs px-2 py-0.5 rounded-full"
                :class="rel.confidence >= 0.8 ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : rel.confidence >= 0.5 ? 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400' : 'bg-gray-100 text-gray-600 dark:bg-gray-600 dark:text-gray-300'"
              >
                {{ Math.round(rel.confidence * 100) }}%
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppShell>
</template>

<script setup>
import { ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import AppShell from '@/Components/Layout/AppShell.vue';

const page = usePage();
const locale = computed(() => page.props.locale || 'de');

const selectedDepartment = ref('all');

const demoDepartments = [
  { id: 1, name: locale.value === 'de' ? 'Fertigung' : 'Manufacturing' },
  { id: 2, name: locale.value === 'de' ? 'Vertrieb' : 'Sales' },
  { id: 3, name: locale.value === 'de' ? 'Personal' : 'HR' },
  { id: 4, name: locale.value === 'de' ? 'Finanzen' : 'Finance' },
];

const summaryCards = computed(() => [
  {
    label: locale.value === 'de' ? 'Offene Probleme' : 'Open Problems',
    value: '7',
    bgClass: 'bg-red-100 dark:bg-red-900/30',
    iconClass: 'text-red-600 dark:text-red-400',
    icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>',
  },
  {
    label: locale.value === 'de' ? 'Ursachen erkannt' : 'Causes Identified',
    value: '12',
    bgClass: 'bg-orange-100 dark:bg-orange-900/30',
    iconClass: 'text-orange-600 dark:text-orange-400',
    icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>',
  },
  {
    label: locale.value === 'de' ? 'Laufende Maßnahmen' : 'Actions In Progress',
    value: '5',
    bgClass: 'bg-blue-100 dark:bg-blue-900/30',
    iconClass: 'text-blue-600 dark:text-blue-400',
    icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>',
  },
  {
    label: locale.value === 'de' ? 'Gelöst (30 Tage)' : 'Resolved (30 days)',
    value: '14',
    bgClass: 'bg-green-100 dark:bg-green-900/30',
    iconClass: 'text-green-600 dark:text-green-400',
    icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
  },
]);

const demoProblems = computed(() => [
  {
    id: 1,
    title: locale.value === 'de' ? 'Fehlerquote über Schwellwert' : 'Error rate above threshold',
    kpi: locale.value === 'de' ? 'Fehlerquote' : 'Error Rate',
    severity: 'critical',
    rootCauses: 3,
    status: 'investigating',
    detected: locale.value === 'de' ? 'Vor 2 Std.' : '2h ago',
  },
  {
    id: 2,
    title: locale.value === 'de' ? 'Durchlaufzeit gestiegen' : 'Processing time increased',
    kpi: locale.value === 'de' ? 'Durchlaufzeit' : 'Processing Time',
    severity: 'high',
    rootCauses: 2,
    status: 'action_planned',
    detected: locale.value === 'de' ? 'Vor 5 Std.' : '5h ago',
  },
  {
    id: 3,
    title: locale.value === 'de' ? 'Lieferverzögerung' : 'Delivery delay',
    kpi: locale.value === 'de' ? 'Liefertreue' : 'Delivery Reliability',
    severity: 'medium',
    rootCauses: 1,
    status: 'investigating',
    detected: locale.value === 'de' ? 'Vor 1 Tag' : '1d ago',
  },
  {
    id: 4,
    title: locale.value === 'de' ? 'Kundenzufriedenheit gesunken' : 'Customer satisfaction dropped',
    kpi: locale.value === 'de' ? 'Zufriedenheit' : 'Satisfaction',
    severity: 'high',
    rootCauses: 2,
    status: 'resolved',
    detected: locale.value === 'de' ? 'Vor 3 Tagen' : '3d ago',
  },
  {
    id: 5,
    title: locale.value === 'de' ? 'Gewinnmarge unter Ziel' : 'Profit margin below target',
    kpi: locale.value === 'de' ? 'Gewinnmarge' : 'Profit Margin',
    severity: 'low',
    rootCauses: 1,
    status: 'action_planned',
    detected: locale.value === 'de' ? 'Vor 5 Tagen' : '5d ago',
  },
]);

const demoCauses = computed(() => [
  { label: locale.value === 'de' ? 'Maschinenstillstand' : 'Machine Downtime', count: 5, pct: 85, barClass: 'bg-red-500' },
  { label: locale.value === 'de' ? 'Personalmangel' : 'Staff Shortage', count: 4, pct: 68, barClass: 'bg-orange-500' },
  { label: locale.value === 'de' ? 'Lieferantenprobleme' : 'Supplier Issues', count: 3, pct: 50, barClass: 'bg-yellow-500' },
  { label: locale.value === 'de' ? 'Prozessineffizienz' : 'Process Inefficiency', count: 2, pct: 34, barClass: 'bg-blue-500' },
  { label: locale.value === 'de' ? 'Schulungsbedarf' : 'Training Gaps', count: 1, pct: 17, barClass: 'bg-purple-500' },
]);

const demoRelationships = computed(() => [
  { cause: locale.value === 'de' ? 'Maschinenstillstand' : 'Machine Downtime', effect: locale.value === 'de' ? 'Fehlerquote' : 'Error Rate', confidence: 0.92 },
  { cause: locale.value === 'de' ? 'Personalmangel' : 'Staff Shortage', effect: locale.value === 'de' ? 'Durchlaufzeit' : 'Processing Time', confidence: 0.78 },
  { cause: locale.value === 'de' ? 'Lieferantenprobleme' : 'Supplier Issues', effect: locale.value === 'de' ? 'Liefertreue' : 'Delivery Reliability', confidence: 0.85 },
  { cause: locale.value === 'de' ? 'Fehlerquote' : 'Error Rate', effect: locale.value === 'de' ? 'Zufriedenheit' : 'Satisfaction', confidence: 0.65 },
]);

function severityClass(severity) {
  return {
    critical: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
    high: 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400',
    medium: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
    low: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
  }[severity];
}

function problemStatusClass(status) {
  return {
    investigating: 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400',
    action_planned: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
    resolved: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
  }[status];
}
</script>
