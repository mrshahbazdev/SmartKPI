<template>
  <AppShell>
    <div class="max-w-3xl mx-auto space-y-6">
      <div class="flex items-center justify-between">
        <div>
          <a href="/scenarios" class="text-sm text-gray-500 dark:text-gray-400 hover:text-primary-600">← {{ $t('common.back') }}</a>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white mt-2">{{ scenario.title }}</h1>
          <p class="text-sm text-gray-500 dark:text-gray-400">{{ scenario.company?.name }} · {{ scenario.creator?.name }}</p>
        </div>
        <button @click="deleteScenario" class="text-sm text-red-500 hover:text-red-700">{{ $t('common.delete') }}</button>
      </div>

      <p v-if="scenario.description" class="text-gray-600 dark:text-gray-300">{{ scenario.description }}</p>

      <!-- Assumptions -->
      <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ $t('scenario.assumptions') }}</h2>
        <div class="space-y-2">
          <div v-for="(a, i) in enrichedAssumptions" :key="i"
            class="flex items-center justify-between py-2 border-b border-gray-100 dark:border-gray-700 last:border-0">
            <div>
              <span class="font-medium text-gray-900 dark:text-white">{{ locale === 'de' ? a.kpi_name_de : a.kpi_name_en }}</span>
              <span class="text-xs text-gray-400 ml-2">({{ a.unit }})</span>
            </div>
            <div class="text-right">
              <span class="text-sm text-gray-500">{{ formatVal(a.current_value) }} →</span>
              <span class="font-bold ml-1" :class="a.change_pct >= 0 ? 'text-green-600' : 'text-red-600'">
                {{ a.change_pct >= 0 ? '+' : '' }}{{ a.change_pct }}%
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Projected Outcomes -->
      <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ $t('scenario.outcomes') }}</h2>
        <div v-if="scenario.projected_outcomes?.length" class="space-y-2">
          <div v-for="(o, i) in scenario.projected_outcomes" :key="i"
            class="flex flex-col sm:flex-row sm:items-center sm:justify-between py-2 border-b border-gray-100 dark:border-gray-700 last:border-0">
            <div>
              <span class="font-medium text-gray-900 dark:text-white">{{ locale === 'de' ? o.kpi_name_de : o.kpi_name_en }}</span>
              <span class="text-xs text-gray-400 ml-2">({{ o.unit }})</span>
            </div>
            <div class="text-right mt-1 sm:mt-0">
              <span class="text-sm text-gray-500">{{ formatVal(o.current_value) }}</span>
              <span class="mx-2 text-gray-400">→</span>
              <span class="font-bold" :class="o.change_pct >= 0 ? 'text-green-600' : 'text-red-600'">{{ formatVal(o.projected_value) }}</span>
              <span class="text-xs ml-1" :class="o.change_pct >= 0 ? 'text-green-500' : 'text-red-500'">
                ({{ o.change_pct >= 0 ? '+' : '' }}{{ o.change_pct }}%)
              </span>
            </div>
          </div>
        </div>
        <p v-else class="text-gray-500 dark:text-gray-400">{{ $t('scenario.no_outcomes') }}</p>
      </div>
    </div>
  </AppShell>
</template>

<script setup>
import { computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import AppShell from '@/Components/Layout/AppShell.vue';

const props = defineProps({
  scenario: { type: Object, required: true },
  enrichedAssumptions: { type: Array, default: () => [] },
});

const page = usePage();
const locale = computed(() => page.props.locale || 'de');

function formatVal(v) {
  if (v === null || v === undefined) return '—';
  const num = parseFloat(v);
  return locale.value === 'de' ? num.toLocaleString('de-DE', { maximumFractionDigits: 2 }) : num.toLocaleString('en-US', { maximumFractionDigits: 2 });
}

function deleteScenario() {
  if (confirm(locale.value === 'de' ? 'Szenario löschen?' : 'Delete scenario?')) {
    router.delete(`/scenarios/${props.scenario.id}`);
  }
}
</script>
