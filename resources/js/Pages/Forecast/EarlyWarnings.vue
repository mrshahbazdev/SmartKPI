<template>
  <AppShell>
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $t('forecast.early_warnings') }}</h1>
        <a href="/forecast" class="text-sm text-primary-600 hover:text-primary-700">{{ $t('forecast.title') }}</a>
      </div>

      <div class="space-y-3">
        <div v-for="(w, i) in warnings" :key="i"
          class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4"
          :class="w.days_until_breach <= 7 ? 'border-red-300 dark:border-red-700' : w.days_until_breach <= 30 ? 'border-yellow-300 dark:border-yellow-700' : ''">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
              <div class="flex items-center gap-2 mb-1">
                <span class="px-2 py-0.5 rounded-full text-xs font-medium"
                  :class="w.days_until_breach <= 7 ? 'bg-red-100 text-red-700' : w.days_until_breach <= 30 ? 'bg-yellow-100 text-yellow-700' : 'bg-blue-100 text-blue-700'">
                  {{ w.days_until_breach }} {{ $t('forecast.days') }}
                </span>
                <span class="text-xs text-gray-500 dark:text-gray-400">{{ w.breach_date }}</span>
              </div>
              <h3 class="font-medium text-gray-900 dark:text-white">{{ locale === 'de' ? w.kpi_name_de : w.kpi_name_en }}</h3>
              <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                {{ locale === 'de'
                  ? `Bei aktuellem Trend wird der Schwellenwert (${w.threshold}) in ${w.days_until_breach} Tagen erreicht`
                  : `At current trend, threshold (${w.threshold}) will be reached in ${w.days_until_breach} days` }}
              </p>
            </div>
            <div class="text-right shrink-0">
              <p class="text-sm text-gray-500 dark:text-gray-400">{{ w.department }} · {{ w.company }}</p>
              <p class="text-lg font-bold text-red-600">→ {{ formatVal(w.predicted_value) }}</p>
            </div>
          </div>
        </div>
      </div>

      <div v-if="!warnings.length" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-12 text-center">
        <p class="text-green-600 dark:text-green-400 font-medium">{{ $t('forecast.no_warnings') }}</p>
      </div>
    </div>
  </AppShell>
</template>

<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import AppShell from '@/Components/Layout/AppShell.vue';

const props = defineProps({ warnings: { type: Array, default: () => [] } });
const page = usePage();
const locale = computed(() => page.props.locale || 'de');

function formatVal(v) {
  return locale.value === 'de' ? v.toLocaleString('de-DE', { maximumFractionDigits: 2 }) : v.toLocaleString('en-US', { maximumFractionDigits: 2 });
}
</script>
