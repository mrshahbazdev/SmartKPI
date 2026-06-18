<template>
  <AppShell>
    <div class="space-y-6">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $t('holding.benchmark') }}</h1>

      <!-- Category selector -->
      <div class="flex flex-wrap gap-2">
        <button v-for="cat in categories" :key="cat"
          @click="selectCategory(cat)"
          class="px-4 py-2 text-sm rounded-lg transition-colors"
          :class="selectedCategory === cat ? 'bg-primary-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200'">
          {{ cat }}
        </button>
      </div>

      <!-- Benchmark table -->
      <div v-if="benchmarks.length" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <table class="w-full text-sm">
          <thead class="bg-gray-50 dark:bg-gray-700/50">
            <tr>
              <th class="px-4 py-3 text-left text-gray-500 dark:text-gray-400 font-medium">KPI</th>
              <th v-for="c in companies" :key="c.id" class="px-4 py-3 text-center text-gray-500 dark:text-gray-400 font-medium">{{ c.name }}</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
            <tr v-for="kpiName in uniqueKpiNames" :key="kpiName">
              <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ kpiName }}</td>
              <td v-for="c in companies" :key="c.id" class="px-4 py-3 text-center">
                <template v-if="getBenchmarkValue(kpiName, c.id)">
                  <span class="font-semibold" :class="statusColor(getBenchmarkValue(kpiName, c.id).status)">
                    {{ formatVal(getBenchmarkValue(kpiName, c.id).value) }}
                  </span>
                  <span class="text-xs text-gray-400 ml-1">{{ getBenchmarkValue(kpiName, c.id).unit }}</span>
                </template>
                <span v-else class="text-gray-300">—</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-else class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-12 text-center">
        <p class="text-gray-500 dark:text-gray-400">{{ $t('holding.no_benchmarks') }}</p>
      </div>
    </div>
  </AppShell>
</template>

<script setup>
import { computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import AppShell from '@/Components/Layout/AppShell.vue';

const props = defineProps({
  categories: { type: Array, default: () => [] },
  benchmarks: { type: Array, default: () => [] },
  companies: { type: Array, default: () => [] },
  selectedCategory: { type: String, default: '' },
});

const page = usePage();
const locale = computed(() => page.props.locale || 'de');

const uniqueKpiNames = computed(() => {
  const names = new Set();
  props.benchmarks.forEach(b => names.add(locale.value === 'de' ? b.kpi_name_de : b.kpi_name_en));
  return [...names];
});

function selectCategory(cat) {
  router.get('/holding/benchmark', { category: cat }, { preserveState: true });
}

function getBenchmarkValue(kpiName, companyId) {
  return props.benchmarks.find(b => (locale.value === 'de' ? b.kpi_name_de : b.kpi_name_en) === kpiName && b.company_id === companyId);
}

function formatVal(v) {
  return locale.value === 'de' ? v.toLocaleString('de-DE', { maximumFractionDigits: 2 }) : v.toLocaleString('en-US', { maximumFractionDigits: 2 });
}

function statusColor(s) {
  return { on_target: 'text-green-600', warning: 'text-yellow-600', critical: 'text-red-600' }[s] || 'text-gray-600';
}
</script>
