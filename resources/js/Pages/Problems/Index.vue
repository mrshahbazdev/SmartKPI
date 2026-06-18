<template>
  <AppShell>
    <div class="space-y-6">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $t('problem.title') }}</h1>
        <div class="flex gap-2">
          <a href="/problems/timeline" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm rounded-lg hover:bg-gray-200">
            {{ $t('problem.timeline_view') }}
          </a>
        </div>
      </div>

      <!-- Filters -->
      <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <input v-model="filterForm.search" type="text" :placeholder="$t('common.search')" @input="applyFilters"
          class="col-span-2 sm:col-span-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm" />
        <select v-model="filterForm.company_id" @change="applyFilters" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
          <option value="">{{ $t('org.all_companies') }}</option>
          <option v-for="c in companies" :key="c.id" :value="c.id">{{ c.name }}</option>
        </select>
        <select v-model="filterForm.severity" @change="applyFilters" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
          <option value="">{{ $t('problem.all_severities') }}</option>
          <option value="low">{{ $t('problem.severity.low') }}</option>
          <option value="medium">{{ $t('problem.severity.medium') }}</option>
          <option value="high">{{ $t('problem.severity.high') }}</option>
          <option value="critical">{{ $t('problem.severity.critical') }}</option>
        </select>
        <select v-model="filterForm.status" @change="applyFilters" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
          <option value="">{{ $t('problem.all_statuses') }}</option>
          <option value="open">{{ $t('problem.status_open') }}</option>
          <option value="investigating">{{ $t('problem.status_investigating') }}</option>
          <option value="resolved">{{ $t('problem.status_resolved') }}</option>
          <option value="closed">{{ $t('problem.status_closed') }}</option>
        </select>
        <select v-model="filterForm.assigned_to" @change="applyFilters" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
          <option value="">{{ $t('problem.all_assignees') }}</option>
          <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
        </select>
      </div>

      <!-- Problems List -->
      <div class="space-y-3">
        <a v-for="p in problems.data" :key="p.id" :href="`/problems/${p.id}`"
          class="block bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 hover:shadow-md transition-shadow">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-2 mb-1">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium" :class="severityClass(p.severity)">
                  {{ $t(`problem.severity.${p.severity}`) }}
                </span>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium" :class="statusClass(p.status)">
                  {{ $t(`problem.status_${p.status}`) }}
                </span>
              </div>
              <h3 class="font-medium text-gray-900 dark:text-white truncate">{{ p.title }}</h3>
              <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 line-clamp-1">{{ p.description }}</p>
            </div>
            <div class="flex items-center gap-4 text-sm text-gray-500 dark:text-gray-400 shrink-0">
              <span v-if="p.kpi_definition" class="hidden sm:block">{{ locale === 'de' ? p.kpi_definition.name_de : p.kpi_definition.name_en }}</span>
              <span v-if="p.department">{{ p.department.name }}</span>
              <span v-if="p.assignee" class="text-primary-600 dark:text-primary-400">{{ p.assignee.name }}</span>
              <span>{{ formatDate(p.detected_at) }}</span>
            </div>
          </div>
        </a>
      </div>

      <div v-if="!problems.data?.length" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-12 text-center">
        <p class="text-gray-500 dark:text-gray-400">{{ $t('problem.no_problems') }}</p>
      </div>

      <!-- Pagination -->
      <div v-if="problems.links?.length > 3" class="flex justify-center gap-1">
        <a v-for="link in problems.links" :key="link.label" :href="link.url || '#'"
          class="px-3 py-1 text-sm rounded" :class="link.active ? 'bg-primary-600 text-white' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700'"
          v-html="link.label"></a>
      </div>
    </div>
  </AppShell>
</template>

<script setup>
import { reactive, computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import AppShell from '@/Components/Layout/AppShell.vue';

const props = defineProps({
  problems: { type: Object, default: () => ({ data: [] }) },
  companies: { type: Array, default: () => [] },
  departments: { type: Array, default: () => [] },
  users: { type: Array, default: () => [] },
  filters: { type: Object, default: () => ({}) },
});

const page = usePage();
const locale = computed(() => page.props.locale || 'de');

const filterForm = reactive({
  search: props.filters.search || '',
  company_id: props.filters.company_id || '',
  severity: props.filters.severity || '',
  status: props.filters.status || '',
  assigned_to: props.filters.assigned_to || '',
});

let debounce;
function applyFilters() {
  clearTimeout(debounce);
  debounce = setTimeout(() => {
    const params = {};
    Object.entries(filterForm).forEach(([k, v]) => { if (v) params[k] = v; });
    router.get('/problems', params, { preserveState: true });
  }, 300);
}

function formatDate(d) {
  if (!d) return '';
  return new Date(d).toLocaleDateString(locale.value === 'de' ? 'de-DE' : 'en-US');
}

function severityClass(s) {
  return {
    low: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
    medium: 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
    high: 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
    critical: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
  }[s] || '';
}

function statusClass(s) {
  return {
    open: 'bg-red-50 text-red-600 dark:bg-red-900/20 dark:text-red-400',
    investigating: 'bg-yellow-50 text-yellow-600 dark:bg-yellow-900/20 dark:text-yellow-400',
    resolved: 'bg-green-50 text-green-600 dark:bg-green-900/20 dark:text-green-400',
    closed: 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400',
  }[s] || '';
}
</script>
