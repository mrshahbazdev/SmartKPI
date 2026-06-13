<template>
  <AppShell>
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $t('scenario.title') }}</h1>
        <a href="/scenarios/create" class="px-4 py-2 bg-primary-600 text-white text-sm rounded-lg hover:bg-primary-700">+ {{ $t('scenario.create') }}</a>
      </div>

      <div class="space-y-3">
        <a v-for="s in scenarios.data" :key="s.id" :href="`/scenarios/${s.id}`"
          class="block bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 hover:shadow-md transition-shadow">
          <div class="flex items-center justify-between">
            <div>
              <h3 class="font-medium text-gray-900 dark:text-white">{{ s.title }}</h3>
              <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ s.company?.name }} · {{ s.creator?.name }}</p>
            </div>
            <span class="text-xs text-gray-400">{{ formatDate(s.created_at) }}</span>
          </div>
        </a>
      </div>

      <div v-if="!scenarios.data?.length" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-12 text-center">
        <p class="text-gray-500 dark:text-gray-400">{{ $t('scenario.no_scenarios') }}</p>
      </div>
    </div>
  </AppShell>
</template>

<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import AppShell from '@/Components/Layout/AppShell.vue';

const props = defineProps({ scenarios: { type: Object, default: () => ({ data: [] }) } });
const page = usePage();
const locale = computed(() => page.props.locale || 'de');

function formatDate(d) {
  if (!d) return '';
  return new Date(d).toLocaleDateString(locale.value === 'de' ? 'de-DE' : 'en-US');
}
</script>
