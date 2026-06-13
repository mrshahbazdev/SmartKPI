<template>
  <AppShell>
    <div class="max-w-2xl mx-auto space-y-6">
      <div>
        <a href="/scenarios" class="text-sm text-gray-500 dark:text-gray-400 hover:text-primary-600">← {{ $t('common.back') }}</a>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mt-2">{{ $t('scenario.create') }}</h1>
        <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">{{ $t('scenario.what_if_desc') }}</p>
      </div>

      <form @submit.prevent="submit" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">{{ $t('org.company') }} *</label>
            <select v-model="form.company_id" required @change="filterKpis" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
              <option value="">—</option>
              <option v-for="c in companies" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">{{ $t('scenario.scenario_name') }} *</label>
            <input v-model="form.title" type="text" required class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm" />
          </div>
        </div>

        <div>
          <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">{{ $t('kpi.description') }}</label>
          <textarea v-model="form.description" rows="2" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm"></textarea>
        </div>

        <!-- Assumptions -->
        <div>
          <label class="block text-sm text-gray-600 dark:text-gray-400 mb-2">{{ $t('scenario.assumptions') }}</label>
          <div v-for="(a, i) in form.assumptions" :key="i" class="flex items-center gap-2 mb-2">
            <select v-model="a.kpi_id" required class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
              <option value="">{{ $t('forecast.select_kpi') }}</option>
              <option v-for="k in companyKpis" :key="k.id" :value="k.id">{{ locale === 'de' ? k.name_de : k.name_en }}</option>
            </select>
            <div class="flex items-center gap-1 shrink-0">
              <input v-model.number="a.change_pct" type="number" step="0.1" required class="w-20 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm text-right" />
              <span class="text-sm text-gray-500">%</span>
            </div>
            <button v-if="form.assumptions.length > 1" type="button" @click="form.assumptions.splice(i, 1)" class="text-red-500 text-sm">×</button>
          </div>
          <button type="button" @click="form.assumptions.push({ kpi_id: '', change_pct: 0 })" class="text-sm text-primary-600 hover:text-primary-700">+ {{ $t('scenario.add_assumption') }}</button>
        </div>

        <div class="flex justify-end gap-3 pt-4">
          <a href="/scenarios" class="px-4 py-2 text-sm text-gray-600">{{ $t('common.cancel') }}</a>
          <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-primary-600 text-white text-sm rounded-lg hover:bg-primary-700 disabled:opacity-50">{{ $t('scenario.simulate') }}</button>
        </div>
      </form>
    </div>
  </AppShell>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import AppShell from '@/Components/Layout/AppShell.vue';

const props = defineProps({
  companies: { type: Array, default: () => [] },
  kpis: { type: Array, default: () => [] },
});

const page = usePage();
const locale = computed(() => page.props.locale || 'de');

const form = useForm({
  company_id: '',
  title: '',
  description: '',
  assumptions: [{ kpi_id: '', change_pct: 0 }],
});

const companyKpis = computed(() => {
  if (!form.company_id) return props.kpis;
  return props.kpis.filter(k => k.company_id == form.company_id);
});

function filterKpis() {
  // Reset KPI selections when company changes
}

function submit() {
  form.post('/scenarios');
}
</script>
