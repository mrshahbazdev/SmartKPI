<template>
  <AppShell>
    <div class="space-y-6">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $t('problem.timeline_view') }}</h1>
        <a href="/problems" class="text-sm text-primary-600 hover:text-primary-700">{{ $t('problem.list_view') }}</a>
      </div>

      <!-- Filters -->
      <div class="flex gap-3">
        <select v-model="filterForm.company_id" @change="applyFilters" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
          <option value="">{{ $t('org.all_companies') }}</option>
          <option v-for="c in companies" :key="c.id" :value="c.id">{{ c.name }}</option>
        </select>
      </div>

      <!-- Timeline -->
      <div class="relative">
        <!-- Vertical line -->
        <div class="absolute left-4 sm:left-8 top-0 bottom-0 w-0.5 bg-gray-200 dark:bg-gray-700"></div>

        <div class="space-y-6">
          <div v-for="p in problems" :key="p.id" class="relative pl-10 sm:pl-16">
            <!-- Dot -->
            <div class="absolute left-2.5 sm:left-6.5 w-3 h-3 rounded-full border-2 border-white dark:border-gray-900"
              :class="dotColor(p.severity)"></div>

            <a :href="`/problems/${p.id}`" class="block bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 hover:shadow-md transition-shadow">
              <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div>
                  <div class="flex items-center gap-2 mb-1">
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium" :class="severityClass(p.severity)">
                      {{ $t(`problem.severity.${p.severity}`) }}
                    </span>
                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ formatDate(p.detected_at) }}</span>
                  </div>
                  <h3 class="font-medium text-gray-900 dark:text-white">{{ p.title }}</h3>
                  <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 line-clamp-2">{{ p.description }}</p>
                </div>
                <div class="flex items-center gap-2 text-sm shrink-0">
                  <span v-if="p.department" class="text-gray-500 dark:text-gray-400">{{ p.department.name }}</span>
                  <span v-if="p.assignee" class="text-primary-600 dark:text-primary-400">{{ p.assignee.name }}</span>
                </div>
              </div>
            </a>
          </div>
        </div>
      </div>

      <div v-if="!problems.length" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-12 text-center">
        <p class="text-gray-500 dark:text-gray-400">{{ $t('problem.no_problems') }}</p>
      </div>
    </div>
  </AppShell>
</template>

<script setup>
import { reactive, computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import AppShell from '@/Components/Layout/AppShell.vue';

const props = defineProps({
  problems: { type: Array, default: () => [] },
  companies: { type: Array, default: () => [] },
  departments: { type: Array, default: () => [] },
  filters: { type: Object, default: () => ({}) },
});

const page = usePage();
const locale = computed(() => page.props.locale || 'de');

const filterForm = reactive({ company_id: props.filters.company_id || '' });

function applyFilters() {
  const params = {};
  if (filterForm.company_id) params.company_id = filterForm.company_id;
  router.get('/problems/timeline', params, { preserveState: true });
}

function formatDate(d) {
  if (!d) return '';
  return new Date(d).toLocaleDateString(locale.value === 'de' ? 'de-DE' : 'en-US', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}

function dotColor(s) { return { low: 'bg-blue-500', medium: 'bg-yellow-500', high: 'bg-orange-500', critical: 'bg-red-500' }[s] || 'bg-gray-500'; }
function severityClass(s) { return { low: 'bg-blue-100 text-blue-700', medium: 'bg-yellow-100 text-yellow-700', high: 'bg-orange-100 text-orange-700', critical: 'bg-red-100 text-red-700' }[s] || ''; }
</script>
