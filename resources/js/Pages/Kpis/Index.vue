<template>
  <AppShell>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $t('nav.kpis') }}</h1>
          <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
            {{ filteredCount }} {{ $t('kpi.definitions') }}
          </p>
        </div>
        <div class="flex gap-2">
          <a href="/kpis/templates" class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
            {{ $t('kpi.template_library') }}
          </a>
          <a href="/kpis/create" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
            {{ $t('kpi.create') }}
          </a>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
          <input
            v-model="filterForm.search"
            type="text"
            :placeholder="$t('common.search') + '...'"
            class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm"
            @input="debouncedFilter"
          />
          <select
            v-model="filterForm.company_id"
            class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm"
            @change="applyFilters"
          >
            <option value="">{{ $t('org.all_companies') }}</option>
            <option v-for="c in companies" :key="c.id" :value="c.id">{{ c.name }}</option>
          </select>
          <select
            v-model="filterForm.department_id"
            class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm"
            @change="applyFilters"
          >
            <option value="">{{ $t('org.all_departments') }}</option>
            <option v-for="d in filteredDepartments" :key="d.id" :value="d.id">{{ d.name }}</option>
          </select>
          <select
            v-model="filterForm.category"
            class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm"
            @change="applyFilters"
          >
            <option value="">{{ $t('kpi.all_categories') }}</option>
            <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
          </select>
          <select
            v-model="filterForm.status"
            class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm"
            @change="applyFilters"
          >
            <option value="">{{ $t('kpi.all_statuses') }}</option>
            <option value="on_target">{{ $t('kpi.on_target') }}</option>
            <option value="warning">{{ $t('kpi.warning') }}</option>
            <option value="critical">{{ $t('kpi.critical') }}</option>
          </select>
        </div>
      </div>

      <!-- KPI Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        <KpiCard v-for="kpi in kpis.data" :key="kpi.id" :kpi="kpi" />
      </div>

      <!-- Pagination -->
      <div v-if="kpis.links?.length > 3" class="flex justify-center">
        <nav class="flex gap-1">
          <template v-for="link in kpis.links" :key="link.label">
            <a
              v-if="link.url"
              :href="link.url"
              class="px-3 py-1.5 text-sm rounded-md"
              :class="link.active ? 'bg-primary-600 text-white' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700'"
              v-html="link.label"
            ></a>
            <span v-else class="px-3 py-1.5 text-sm text-gray-400" v-html="link.label"></span>
          </template>
        </nav>
      </div>

      <!-- Empty state -->
      <div v-if="!kpis.data?.length" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-12 text-center">
        <p class="text-gray-500 dark:text-gray-400 mb-4">{{ $t('kpi.no_kpis') }}</p>
        <a href="/kpis/templates" class="text-primary-600 hover:text-primary-500 text-sm font-medium">
          {{ $t('kpi.start_from_template') }}
        </a>
      </div>
    </div>
  </AppShell>
</template>

<script setup>
import { computed, reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import AppShell from '@/Components/Layout/AppShell.vue';
import KpiCard from '@/Components/Dashboard/KpiCard.vue';

const props = defineProps({
  kpis: { type: Object, default: () => ({ data: [] }) },
  companies: { type: Array, default: () => [] },
  departments: { type: Array, default: () => [] },
  filters: { type: Object, default: () => ({}) },
  categories: { type: Array, default: () => [] },
});

const filterForm = reactive({
  search: props.filters.search || '',
  company_id: props.filters.company_id || '',
  department_id: props.filters.department_id || '',
  category: props.filters.category || '',
  status: props.filters.status || '',
});

const filteredDepartments = computed(() => {
  if (!filterForm.company_id) return props.departments;
  return props.departments.filter(d => d.company_id == filterForm.company_id);
});

const filteredCount = computed(() => props.kpis.total || props.kpis.data?.length || 0);

let debounceTimer = null;
function debouncedFilter() {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(applyFilters, 300);
}

function applyFilters() {
  const params = {};
  Object.entries(filterForm).forEach(([k, v]) => { if (v) params[k] = v; });
  router.get('/kpis', params, { preserveState: true, replace: true });
}
</script>
