<template>
  <AppShell>
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $t('goal.title') }}</h1>
        <a href="/goals/create" class="px-4 py-2 bg-primary-600 text-white text-sm rounded-lg hover:bg-primary-700">+ {{ $t('goal.create') }}</a>
      </div>

      <!-- Filters -->
      <div class="flex flex-col sm:flex-row gap-3">
        <select v-model="filterCompany" @change="applyFilters" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
          <option value="">{{ $t('problem.all_severities') }}</option>
          <option v-for="c in companies" :key="c.id" :value="c.id">{{ c.name }}</option>
        </select>
        <select v-model="filterStatus" @change="applyFilters" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
          <option value="">{{ $t('problem.all_statuses') }}</option>
          <option value="active">{{ $t('goal.active') }}</option>
          <option value="achieved">{{ $t('goal.achieved') }}</option>
          <option value="missed">{{ $t('goal.missed') }}</option>
          <option value="cancelled">{{ $t('goal.cancelled') }}</option>
        </select>
      </div>

      <!-- Goals list -->
      <div class="space-y-3">
        <div v-for="g in goals.data" :key="g.id"
          class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex-1">
              <div class="flex items-center gap-2 mb-1">
                <h3 class="font-medium text-gray-900 dark:text-white">{{ g.title }}</h3>
                <span class="px-2 py-0.5 text-xs rounded-full" :class="goalStatusClass(g.status)">{{ $t(`goal.${g.status}`) }}</span>
              </div>
              <p class="text-sm text-gray-500 dark:text-gray-400">{{ g.company?.name }} · {{ g.assignee?.name || '—' }}</p>
              <p v-if="g.kpi_definition" class="text-xs text-gray-400 mt-1">{{ locale === 'de' ? g.kpi_definition.name_de : g.kpi_definition.name_en }}</p>
            </div>
            <div class="sm:text-right shrink-0 sm:w-48">
              <div class="flex items-center gap-2 mb-1">
                <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                  <div class="h-2 rounded-full bg-primary-500" :style="{ width: Math.min(g.progress, 100) + '%' }"></div>
                </div>
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ Math.round(g.progress) }}%</span>
              </div>
              <p v-if="g.end_date" class="text-xs text-gray-400">{{ $t('goal.deadline') }}: {{ formatDate(g.end_date) }}</p>
            </div>
          </div>
        </div>
      </div>

      <div v-if="!goals.data?.length" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-12 text-center">
        <p class="text-gray-500 dark:text-gray-400">{{ $t('goal.no_goals') }}</p>
      </div>
    </div>
  </AppShell>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import AppShell from '@/Components/Layout/AppShell.vue';

const props = defineProps({
  goals: { type: Object, default: () => ({ data: [] }) },
  companies: { type: Array, default: () => [] },
  filters: { type: Object, default: () => ({}) },
});

const page = usePage();
const locale = computed(() => page.props.locale || 'de');
const filterCompany = ref(props.filters.company_id || '');
const filterStatus = ref(props.filters.status || '');

function applyFilters() {
  const params = {};
  if (filterCompany.value) params.company_id = filterCompany.value;
  if (filterStatus.value) params.status = filterStatus.value;
  router.get('/goals', params, { preserveState: true });
}

function goalStatusClass(s) {
  return {
    active: 'bg-blue-100 text-blue-700',
    achieved: 'bg-green-100 text-green-700',
    missed: 'bg-red-100 text-red-700',
    cancelled: 'bg-gray-100 text-gray-600',
  }[s] || '';
}

function formatDate(d) {
  if (!d) return '';
  return new Date(d).toLocaleDateString(locale.value === 'de' ? 'de-DE' : 'en-US');
}
</script>
