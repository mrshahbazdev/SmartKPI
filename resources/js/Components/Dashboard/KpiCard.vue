<template>
  <a
    :href="kpi.id ? `/kpis/${kpi.id}` : '#'"
    class="block bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5 hover:shadow-md transition-shadow"
  >
    <div class="flex items-start justify-between">
      <div class="min-w-0 flex-1">
        <p class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
          {{ locale === 'de' ? kpi.name_de : kpi.name_en }}
        </p>
        <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
          {{ formatValue(kpi.current_value, kpi.unit) }}
        </p>
      </div>
      <StatusBadge :status="kpi.status" />
    </div>

    <!-- Sparkline -->
    <div v-if="kpi.sparkline?.length" class="mt-3 h-8">
      <svg :viewBox="`0 0 ${kpi.sparkline.length * 8} 32`" class="w-full h-full" preserveAspectRatio="none">
        <polyline
          :points="sparklinePoints"
          fill="none"
          :stroke="sparklineColor"
          stroke-width="1.5"
          stroke-linecap="round"
          stroke-linejoin="round"
        />
      </svg>
    </div>

    <div class="mt-2 flex items-center justify-between text-sm">
      <div class="flex items-center gap-1">
        <template v-if="kpi.trend_percent !== null && kpi.trend_percent !== undefined">
          <span :class="trendColor">
            {{ kpi.trend_percent > 0 ? '+' : '' }}{{ kpi.trend_percent }}%
          </span>
        </template>
      </div>
      <span v-if="kpi.target_value" class="text-gray-400 text-xs">
        {{ $t('kpi.target') }}: {{ formatValue(kpi.target_value, kpi.unit) }}
      </span>
    </div>
  </a>
</template>

<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import StatusBadge from './StatusBadge.vue';

const props = defineProps({
  kpi: { type: Object, required: true },
});

const page = usePage();
const locale = computed(() => page.props.locale || 'de');

const sparklinePoints = computed(() => {
  if (!props.kpi.sparkline?.length) return '';
  const values = props.kpi.sparkline;
  const max = Math.max(...values);
  const min = Math.min(...values);
  const range = max - min || 1;
  return values.map((v, i) => `${i * 8},${32 - ((v - min) / range) * 28}`).join(' ');
});

const sparklineColor = computed(() => {
  const s = props.kpi.status;
  if (s === 'critical') return '#ef4444';
  if (s === 'warning') return '#f59e0b';
  return '#22c55e';
});

const trendColor = computed(() => {
  const t = props.kpi.trend_percent;
  const dir = props.kpi.direction;
  if (t === null || t === undefined) return 'text-gray-400';
  const isGood = dir === 'higher_better' ? t > 0 : t < 0;
  return isGood ? 'text-green-600' : 'text-red-600';
});

function formatValue(val, unit) {
  if (val === null || val === undefined) return '—';
  const num = locale.value === 'de'
    ? val.toLocaleString('de-DE', { maximumFractionDigits: 2 })
    : val.toLocaleString('en-US', { maximumFractionDigits: 2 });
  if (unit === '%') return `${num}%`;
  if (unit === 'EUR' || unit === '€') return `€${num}`;
  if (unit) return `${num} ${unit}`;
  return num;
}
</script>
