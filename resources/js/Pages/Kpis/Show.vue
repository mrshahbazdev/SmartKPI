<template>
  <AppShell>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-1">
            <a href="/kpis" class="hover:text-primary-600">{{ $t('nav.kpis') }}</a>
            <span>/</span>
            <span>{{ locale === 'de' ? kpi.name_de : kpi.name_en }}</span>
          </div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
            {{ locale === 'de' ? kpi.name_de : kpi.name_en }}
          </h1>
          <p v-if="kpiDesc" class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ kpiDesc }}</p>
        </div>
        <div class="flex gap-2">
          <a :href="`/kpis/${kpi.id}/edit`" class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-sm rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
            {{ $t('common.edit') }}
          </a>
        </div>
      </div>

      <!-- Stats Row -->
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
          <p class="text-xs text-gray-500 dark:text-gray-400">{{ $t('kpi.current_value') }}</p>
          <p class="text-xl font-bold text-gray-900 dark:text-white mt-1">
            {{ kpi.latest_value ? formatVal(kpi.latest_value.value) : '—' }}
          </p>
          <StatusBadge v-if="kpi.latest_value" :status="kpi.latest_value.status" class="mt-1" />
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
          <p class="text-xs text-gray-500 dark:text-gray-400">{{ $t('kpi.target') }}</p>
          <p class="text-xl font-bold text-gray-900 dark:text-white mt-1">{{ kpi.target_value ? formatVal(kpi.target_value) : '—' }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
          <p class="text-xs text-gray-500 dark:text-gray-400">{{ $t('kpi.frequency') }}</p>
          <p class="text-xl font-bold text-gray-900 dark:text-white mt-1 capitalize">{{ $t(`kpi.${kpi.frequency}`) }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
          <p class="text-xs text-gray-500 dark:text-gray-400">{{ $t('kpi.direction') }}</p>
          <p class="text-sm font-bold text-gray-900 dark:text-white mt-1">{{ $t(`kpi.${kpi.direction}`) }}</p>
        </div>
      </div>

      <!-- Chart -->
      <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ $t('kpi.trend') }}</h3>
        <div class="h-64 sm:h-80">
          <svg v-if="values.length > 1" class="w-full h-full" :viewBox="`0 0 ${chartW} ${chartH}`">
            <!-- Grid lines -->
            <line v-for="i in 4" :key="'g'+i" :x1="chartPad" :x2="chartW - chartPad" :y1="chartPad + (i-1) * ((chartH - 2*chartPad) / 3)" :y2="chartPad + (i-1) * ((chartH - 2*chartPad) / 3)" stroke="#e5e7eb" stroke-width="0.5" />
            <!-- Target line -->
            <line v-if="kpi.target_value" :x1="chartPad" :x2="chartW - chartPad" :y1="targetY" :y2="targetY" stroke="#3b82f6" stroke-width="1" stroke-dasharray="4,4" />
            <!-- Area fill -->
            <polygon :points="areaPoints" fill="url(#gradient)" opacity="0.3" />
            <!-- Line -->
            <polyline :points="linePoints" fill="none" stroke="#3b82f6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            <!-- Dots -->
            <circle v-for="(pt, i) in chartPoints" :key="i" :cx="pt.x" :cy="pt.y" r="3" :fill="dotColor(values[i]?.status)" />
            <defs>
              <linearGradient id="gradient" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0%" stop-color="#3b82f6" stop-opacity="0.4" />
                <stop offset="100%" stop-color="#3b82f6" stop-opacity="0" />
              </linearGradient>
            </defs>
          </svg>
          <p v-else class="flex items-center justify-center h-full text-gray-400 text-sm">{{ $t('kpi.no_data') }}</p>
        </div>
      </div>

      <!-- Data Entry Form -->
      <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ $t('kpi.add_value') }}</h3>
        <form @submit.prevent="addValue" class="grid grid-cols-1 sm:grid-cols-4 gap-4 items-end">
          <div>
            <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">{{ $t('common.date') }}</label>
            <input v-model="valueForm.recorded_at" type="date" required class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm" />
          </div>
          <div>
            <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">{{ $t('kpi.value') }} ({{ kpi.unit || '—' }})</label>
            <input v-model.number="valueForm.value" type="number" step="any" required class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm" />
          </div>
          <div>
            <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">{{ $t('common.notes') }}</label>
            <input v-model="valueForm.notes" type="text" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm" />
          </div>
          <button type="submit" :disabled="valueForm.processing" class="px-4 py-2 bg-primary-600 text-white text-sm rounded-lg hover:bg-primary-700 disabled:opacity-50">
            {{ $t('common.save') }}
          </button>
        </form>
      </div>

      <!-- Value History -->
      <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="p-5 border-b border-gray-200 dark:border-gray-700">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $t('kpi.history') }}</h3>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-700/50">
              <tr>
                <th class="px-4 py-3 text-left text-gray-500 dark:text-gray-400 font-medium">{{ $t('common.date') }}</th>
                <th class="px-4 py-3 text-right text-gray-500 dark:text-gray-400 font-medium">{{ $t('kpi.value') }}</th>
                <th class="px-4 py-3 text-center text-gray-500 dark:text-gray-400 font-medium">{{ $t('common.status') }}</th>
                <th class="px-4 py-3 text-left text-gray-500 dark:text-gray-400 font-medium">{{ $t('common.notes') }}</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
              <tr v-for="val in values" :key="val.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ val.recorded_at }}</td>
                <td class="px-4 py-3 text-right font-medium text-gray-900 dark:text-white">{{ formatVal(val.value) }}</td>
                <td class="px-4 py-3 text-center">
                  <StatusBadge :status="val.status" />
                </td>
                <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ val.notes || '—' }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AppShell>
</template>

<script setup>
import { computed } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import AppShell from '@/Components/Layout/AppShell.vue';
import StatusBadge from '@/Components/Dashboard/StatusBadge.vue';

const props = defineProps({
  kpi: { type: Object, required: true },
  values: { type: Array, default: () => [] },
});

const page = usePage();
const locale = computed(() => page.props.locale || 'de');
const kpiDesc = computed(() => locale.value === 'de' ? props.kpi.description_de : props.kpi.description_en);

const valueForm = useForm({
  value: null,
  recorded_at: new Date().toISOString().split('T')[0],
  notes: '',
});

function addValue() {
  valueForm.post(`/kpis/${props.kpi.id}/values`, {
    onSuccess: () => valueForm.reset(),
    preserveScroll: true,
  });
}

function formatVal(v) {
  if (v === null || v === undefined) return '—';
  const num = locale.value === 'de'
    ? Number(v).toLocaleString('de-DE', { maximumFractionDigits: 2 })
    : Number(v).toLocaleString('en-US', { maximumFractionDigits: 2 });
  if (props.kpi.unit === '%') return `${num}%`;
  if (props.kpi.unit === 'EUR') return `€${num}`;
  if (props.kpi.unit) return `${num} ${props.kpi.unit}`;
  return num;
}

// Chart calculations
const chartW = 800;
const chartH = 300;
const chartPad = 30;

const chartPoints = computed(() => {
  if (props.values.length < 2) return [];
  const vals = [...props.values].reverse();
  const vMin = Math.min(...vals.map(v => v.value));
  const vMax = Math.max(...vals.map(v => v.value));
  const range = vMax - vMin || 1;
  const w = chartW - 2 * chartPad;
  const h = chartH - 2 * chartPad;
  return vals.map((v, i) => ({
    x: chartPad + (i / (vals.length - 1)) * w,
    y: chartPad + h - ((v.value - vMin) / range) * h,
  }));
});

const linePoints = computed(() => chartPoints.value.map(p => `${p.x},${p.y}`).join(' '));
const areaPoints = computed(() => {
  if (chartPoints.value.length < 2) return '';
  const pts = chartPoints.value;
  const bottom = chartH - chartPad;
  return `${pts[0].x},${bottom} ` + pts.map(p => `${p.x},${p.y}`).join(' ') + ` ${pts[pts.length-1].x},${bottom}`;
});

const targetY = computed(() => {
  if (!props.kpi.target_value || props.values.length < 2) return 0;
  const vals = props.values.map(v => v.value);
  const vMin = Math.min(...vals);
  const vMax = Math.max(...vals);
  const range = vMax - vMin || 1;
  return chartPad + (chartH - 2 * chartPad) - ((props.kpi.target_value - vMin) / range) * (chartH - 2 * chartPad);
});

function dotColor(status) {
  return { on_target: '#22c55e', warning: '#f59e0b', critical: '#ef4444' }[status] || '#6b7280';
}
</script>
