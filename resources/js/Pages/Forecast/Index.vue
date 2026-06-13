<template>
  <AppShell>
    <div class="space-y-6">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $t('forecast.title') }}</h1>

      <!-- KPI + Horizon selector -->
      <div class="flex flex-col sm:flex-row gap-3">
        <select v-model="selectedKpi" @change="loadForecast" class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
          <option value="">{{ $t('forecast.select_kpi') }}</option>
          <option v-for="k in kpis" :key="k.id" :value="k.id">{{ locale === 'de' ? k.name_de : k.name_en }} ({{ k.company }})</option>
        </select>
        <select v-model="selectedHorizon" @change="loadForecast" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
          <option value="7d">7 {{ $t('forecast.days') }}</option>
          <option value="30d">30 {{ $t('forecast.days') }}</option>
          <option value="90d">90 {{ $t('forecast.days') }}</option>
        </select>
        <button @click="generateForecast" :disabled="!selectedKpi || generating" class="px-4 py-2 bg-primary-600 text-white text-sm rounded-lg hover:bg-primary-700 disabled:opacity-50 shrink-0">
          {{ generating ? '...' : $t('forecast.generate') }}
        </button>
      </div>

      <!-- Chart area -->
      <div v-if="historicalValues.length || forecasts.length" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ $t('forecast.chart') }}</h2>
        <!-- Simple line visualization -->
        <div class="relative h-64 overflow-x-auto">
          <div class="flex items-end gap-px h-full min-w-max">
            <div v-for="(v, i) in chartData" :key="i"
              class="flex-shrink-0 w-3 rounded-t-sm transition-all relative group"
              :class="v.type === 'historical' ? 'bg-primary-500' : 'bg-primary-300 border border-dashed border-primary-400'"
              :style="{ height: barHeight(v.value) + '%' }">
              <div class="absolute bottom-full left-1/2 -translate-x-1/2 bg-gray-800 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 whitespace-nowrap z-10">
                {{ v.date }}: {{ formatVal(v.value) }}
              </div>
            </div>
          </div>
        </div>
        <div class="flex items-center gap-4 mt-3 text-xs text-gray-500 dark:text-gray-400">
          <span class="flex items-center gap-1"><span class="w-3 h-3 bg-primary-500 rounded-sm"></span> {{ $t('forecast.historical') }}</span>
          <span class="flex items-center gap-1"><span class="w-3 h-3 bg-primary-300 border border-dashed border-primary-400 rounded-sm"></span> {{ $t('forecast.predicted') }}</span>
        </div>
      </div>

      <!-- Forecast table -->
      <div v-if="forecasts.length" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <table class="w-full text-sm">
          <thead class="bg-gray-50 dark:bg-gray-700/50">
            <tr>
              <th class="px-4 py-3 text-left text-gray-500 dark:text-gray-400 font-medium">{{ $t('forecast.date') }}</th>
              <th class="px-4 py-3 text-right text-gray-500 dark:text-gray-400 font-medium">{{ $t('forecast.predicted_val') }}</th>
              <th class="px-4 py-3 text-right text-gray-500 dark:text-gray-400 font-medium hidden sm:table-cell">{{ $t('forecast.lower') }}</th>
              <th class="px-4 py-3 text-right text-gray-500 dark:text-gray-400 font-medium hidden sm:table-cell">{{ $t('forecast.upper') }}</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
            <tr v-for="f in forecasts" :key="f.id">
              <td class="px-4 py-2 text-gray-700 dark:text-gray-300">{{ f.forecast_date }}</td>
              <td class="px-4 py-2 text-right font-medium text-gray-900 dark:text-white">{{ formatVal(f.predicted_value) }}</td>
              <td class="px-4 py-2 text-right text-gray-500 hidden sm:table-cell">{{ formatVal(f.lower_bound) }}</td>
              <td class="px-4 py-2 text-right text-gray-500 hidden sm:table-cell">{{ formatVal(f.upper_bound) }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-if="!selectedKpi" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-12 text-center">
        <p class="text-gray-500 dark:text-gray-400">{{ $t('forecast.select_kpi_prompt') }}</p>
      </div>
    </div>
  </AppShell>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import AppShell from '@/Components/Layout/AppShell.vue';

const props = defineProps({
  kpis: { type: Array, default: () => [] },
  selectedKpiId: { type: [Number, String], default: null },
  horizon: { type: String, default: '30d' },
  forecasts: { type: Array, default: () => [] },
  historicalValues: { type: Array, default: () => [] },
});

const page = usePage();
const locale = computed(() => page.props.locale || 'de');
const selectedKpi = ref(props.selectedKpiId || '');
const selectedHorizon = ref(props.horizon);
const generating = ref(false);

const chartData = computed(() => {
  const hist = props.historicalValues.map(v => ({ date: v.date, value: v.value, type: 'historical' }));
  const pred = props.forecasts.map(f => ({ date: f.forecast_date, value: parseFloat(f.predicted_value), type: 'forecast' }));
  return [...hist.slice(-30), ...pred.slice(0, 30)];
});

function loadForecast() {
  if (!selectedKpi.value) return;
  router.get('/forecast', { kpi_id: selectedKpi.value, horizon: selectedHorizon.value }, { preserveState: true });
}

function generateForecast() {
  generating.value = true;
  router.post('/forecast/generate', { kpi_id: selectedKpi.value, horizon: selectedHorizon.value }, {
    preserveState: true,
    onFinish: () => { generating.value = false; loadForecast(); },
  });
}

function barHeight(val) {
  const allVals = chartData.value.map(d => d.value);
  const max = Math.max(...allVals, 1);
  return Math.max(5, (val / max) * 100);
}

function formatVal(v) {
  if (v === null || v === undefined) return '—';
  const num = parseFloat(v);
  return locale.value === 'de' ? num.toLocaleString('de-DE', { maximumFractionDigits: 2 }) : num.toLocaleString('en-US', { maximumFractionDigits: 2 });
}
</script>
