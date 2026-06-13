<template>
  <AppShell>
    <div class="space-y-6">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $t('holding.cross_effects') }}</h1>

      <div class="space-y-3">
        <div v-for="effect in effects.data" :key="effect.id"
          class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
          <div class="flex flex-col sm:flex-row sm:items-center gap-3">
            <div class="flex items-center gap-2 text-sm">
              <span class="px-2 py-1 rounded-lg bg-red-50 dark:bg-red-900/20 text-red-600 font-medium">{{ effect.source_company?.name }}</span>
              <span class="text-gray-400">→</span>
              <span class="px-2 py-1 rounded-lg bg-orange-50 dark:bg-orange-900/20 text-orange-600 font-medium">{{ effect.affected_company?.name }}</span>
            </div>
            <div class="flex-1">
              <p class="text-sm text-gray-700 dark:text-gray-300">{{ effect.description }}</p>
              <div class="flex items-center gap-3 mt-1 text-xs text-gray-500 dark:text-gray-400">
                <span class="px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700">{{ effect.impact_type }}</span>
                <span>{{ $t('holding.impact_score') }}: {{ effect.impact_score }}%</span>
                <span class="px-2 py-0.5 rounded-full" :class="effectStatusClass(effect.status)">{{ effect.status }}</span>
              </div>
            </div>
            <div v-if="effect.affected_kpi" class="text-sm text-gray-500 dark:text-gray-400 shrink-0">
              {{ locale === 'de' ? effect.affected_kpi.name_de : effect.affected_kpi.name_en }}
            </div>
          </div>
        </div>
      </div>

      <div v-if="!effects.data?.length" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-12 text-center">
        <p class="text-gray-500 dark:text-gray-400">{{ $t('holding.no_cross_effects') }}</p>
      </div>
    </div>
  </AppShell>
</template>

<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import AppShell from '@/Components/Layout/AppShell.vue';

const props = defineProps({
  effects: { type: Object, default: () => ({ data: [] }) },
  companies: { type: Array, default: () => [] },
});

const page = usePage();
const locale = computed(() => page.props.locale || 'de');

function effectStatusClass(s) {
  return {
    detected: 'bg-red-50 text-red-600',
    confirmed: 'bg-yellow-50 text-yellow-600',
    mitigated: 'bg-blue-50 text-blue-600',
    resolved: 'bg-green-50 text-green-600',
  }[s] || '';
}
</script>
