<template>
  <AppShell>
    <div class="space-y-6">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $t('audit.title') }}</h1>

      <!-- Filters -->
      <div class="flex flex-col sm:flex-row gap-3">
        <select v-model="filterType" @change="applyFilters" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
          <option value="">{{ $t('audit.all_types') }}</option>
          <option v-for="t in subjectTypes" :key="t" :value="t">{{ t }}</option>
        </select>
        <select v-model="filterEvent" @change="applyFilters" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
          <option value="">{{ $t('audit.all_events') }}</option>
          <option value="created">Created</option>
          <option value="updated">Updated</option>
          <option value="deleted">Deleted</option>
        </select>
      </div>

      <!-- Activity log -->
      <div class="space-y-2">
        <div v-for="a in activities.data" :key="a.id"
          class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
          <div class="flex flex-col sm:flex-row sm:items-center gap-2">
            <div class="flex-1">
              <div class="flex items-center gap-2 mb-1">
                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ a.causer_name }}</span>
                <span class="px-2 py-0.5 text-xs rounded-full" :class="eventClass(a.event)">{{ a.event }}</span>
                <span class="text-sm text-gray-500">{{ a.subject_type }} #{{ a.subject_id }}</span>
              </div>
              <p class="text-sm text-gray-600 dark:text-gray-400">{{ a.description }}</p>
              <!-- Changed attributes -->
              <div v-if="a.properties?.old || a.properties?.attributes" class="mt-2 text-xs font-mono">
                <div v-if="a.properties?.old" class="text-red-500">
                  <span v-for="(val, key) in a.properties.old" :key="key" class="mr-3">{{ key }}: {{ val }}</span>
                </div>
                <div v-if="a.properties?.attributes" class="text-green-600">
                  <span v-for="(val, key) in a.properties.attributes" :key="key" class="mr-3">{{ key }}: {{ val }}</span>
                </div>
              </div>
            </div>
            <span class="text-xs text-gray-400 whitespace-nowrap shrink-0">{{ formatDate(a.created_at) }}</span>
          </div>
        </div>
      </div>

      <div v-if="!activities.data?.length" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-12 text-center">
        <p class="text-gray-500 dark:text-gray-400">{{ $t('audit.no_activity') }}</p>
      </div>
    </div>
  </AppShell>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import AppShell from '@/Components/Layout/AppShell.vue';

const props = defineProps({
  activities: { type: Object, default: () => ({ data: [] }) },
  subjectTypes: { type: Array, default: () => [] },
  filters: { type: Object, default: () => ({}) },
});

const page = usePage();
const locale = computed(() => page.props.locale || 'de');
const filterType = ref(props.filters.subject_type || '');
const filterEvent = ref(props.filters.event || '');

function applyFilters() {
  const params = {};
  if (filterType.value) params.subject_type = filterType.value;
  if (filterEvent.value) params.event = filterEvent.value;
  router.get('/settings/audit-trail', params, { preserveState: true });
}

function eventClass(e) {
  return { created: 'bg-green-100 text-green-700', updated: 'bg-blue-100 text-blue-700', deleted: 'bg-red-100 text-red-700' }[e] || 'bg-gray-100 text-gray-600';
}

function formatDate(d) {
  if (!d) return '';
  return new Date(d).toLocaleDateString(locale.value === 'de' ? 'de-DE' : 'en-US', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
}
</script>
