<template>
  <AppShell>
    <div class="space-y-6">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $t('action.title') }}</h1>
        <div class="flex gap-2">
          <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
            <input type="checkbox" v-model="showAll" @change="applyFilters" class="rounded border-gray-300 dark:border-gray-600" />
            {{ $t('action.show_all') }}
          </label>
          <a href="/actions/create" class="px-4 py-2 bg-primary-600 text-white text-sm rounded-lg hover:bg-primary-700">
            + {{ $t('action.create') }}
          </a>
        </div>
      </div>

      <!-- Filters -->
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <input v-model="filterForm.search" type="text" :placeholder="$t('common.search')" @input="applyFilters"
          class="col-span-2 sm:col-span-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm" />
        <select v-model="filterForm.status" @change="applyFilters" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
          <option value="">{{ $t('problem.all_statuses') }}</option>
          <option value="open">{{ $t('action.status.open') }}</option>
          <option value="in_progress">{{ $t('action.status.in_progress') }}</option>
          <option value="completed">{{ $t('action.status.completed') }}</option>
          <option value="cancelled">{{ $t('action.status.cancelled') }}</option>
        </select>
        <select v-model="filterForm.priority" @change="applyFilters" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
          <option value="">{{ $t('action.all_priorities') }}</option>
          <option value="low">{{ $t('problem.severity.low') }}</option>
          <option value="medium">{{ $t('problem.severity.medium') }}</option>
          <option value="high">{{ $t('problem.severity.high') }}</option>
          <option value="critical">{{ $t('problem.severity.critical') }}</option>
        </select>
      </div>

      <!-- Desktop: Table -->
      <div class="hidden md:block bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <table class="w-full text-sm">
          <thead class="bg-gray-50 dark:bg-gray-700/50">
            <tr>
              <th class="px-4 py-3 text-left text-gray-500 dark:text-gray-400 font-medium">{{ $t('action.suggestion') }}</th>
              <th class="px-4 py-3 text-center text-gray-500 dark:text-gray-400 font-medium">{{ $t('common.status') }}</th>
              <th class="px-4 py-3 text-center text-gray-500 dark:text-gray-400 font-medium">{{ $t('action.priority_label') }}</th>
              <th class="px-4 py-3 text-left text-gray-500 dark:text-gray-400 font-medium">{{ $t('action.responsible') }}</th>
              <th class="px-4 py-3 text-left text-gray-500 dark:text-gray-400 font-medium">{{ $t('action.deadline') }}</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
            <tr v-for="a in actions.data" :key="a.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/30 cursor-pointer" @click="$inertia.visit(`/actions/${a.id}`)">
              <td class="px-4 py-3">
                <span class="font-medium text-gray-900 dark:text-white">{{ a.title }}</span>
                <p v-if="a.problem" class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                  {{ a.problem.kpi_definition ? (locale === 'de' ? a.problem.kpi_definition.name_de : a.problem.kpi_definition.name_en) : '' }}
                </p>
              </td>
              <td class="px-4 py-3 text-center">
                <span class="px-2 py-0.5 rounded-full text-xs font-medium" :class="statusClass(a.status)">{{ $t(`action.status.${a.status}`) }}</span>
              </td>
              <td class="px-4 py-3 text-center">
                <span class="px-2 py-0.5 rounded-full text-xs font-medium" :class="priorityClass(a.priority)">{{ a.priority }}</span>
              </td>
              <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ a.assignee?.name || '—' }}</td>
              <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ a.deadline ? formatDate(a.deadline) : '—' }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Mobile: Cards -->
      <div class="md:hidden space-y-3">
        <a v-for="a in actions.data" :key="a.id" :href="`/actions/${a.id}`"
          class="block bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
          <div class="flex items-center gap-2 mb-2">
            <span class="px-2 py-0.5 rounded-full text-xs font-medium" :class="priorityClass(a.priority)">{{ a.priority }}</span>
            <span class="px-2 py-0.5 rounded-full text-xs font-medium" :class="statusClass(a.status)">{{ $t(`action.status.${a.status}`) }}</span>
          </div>
          <h3 class="font-medium text-gray-900 dark:text-white">{{ a.title }}</h3>
          <div class="flex items-center justify-between mt-2 text-xs text-gray-500 dark:text-gray-400">
            <span>{{ a.assignee?.name || '—' }}</span>
            <span>{{ a.deadline ? formatDate(a.deadline) : '' }}</span>
          </div>
        </a>
      </div>

      <div v-if="!actions.data?.length" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-12 text-center">
        <p class="text-gray-500 dark:text-gray-400">{{ $t('action.no_actions') }}</p>
      </div>
    </div>
  </AppShell>
</template>

<script setup>
import { reactive, ref, computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import AppShell from '@/Components/Layout/AppShell.vue';

const props = defineProps({
  actions: { type: Object, default: () => ({ data: [] }) },
  users: { type: Array, default: () => [] },
  filters: { type: Object, default: () => ({}) },
});

const page = usePage();
const locale = computed(() => page.props.locale || 'de');
const showAll = ref(!!props.filters.show_all);

const filterForm = reactive({
  search: props.filters.search || '',
  status: props.filters.status || '',
  priority: props.filters.priority || '',
});

let debounce;
function applyFilters() {
  clearTimeout(debounce);
  debounce = setTimeout(() => {
    const params = {};
    Object.entries(filterForm).forEach(([k, v]) => { if (v) params[k] = v; });
    if (showAll.value) params.show_all = 1;
    router.get('/actions', params, { preserveState: true });
  }, 300);
}

function formatDate(d) {
  if (!d) return '';
  return new Date(d).toLocaleDateString(locale.value === 'de' ? 'de-DE' : 'en-US');
}

function statusClass(s) {
  return { open: 'bg-red-50 text-red-600', in_progress: 'bg-yellow-50 text-yellow-600', completed: 'bg-green-50 text-green-600', cancelled: 'bg-gray-100 text-gray-600' }[s] || '';
}
function priorityClass(p) {
  return { low: 'bg-blue-100 text-blue-700', medium: 'bg-yellow-100 text-yellow-700', high: 'bg-orange-100 text-orange-700', critical: 'bg-red-100 text-red-700' }[p] || '';
}
</script>
