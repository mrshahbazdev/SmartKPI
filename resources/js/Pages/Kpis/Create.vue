<template>
  <AppShell>
    <div class="max-w-3xl mx-auto space-y-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $t('kpi.create') }}</h1>
      </div>

      <form @submit.prevent="submit" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 space-y-6">
        <!-- Bilingual Names -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('kpi.name') }} (DE)</label>
            <input v-model="form.name_de" type="text" required class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
            <p v-if="form.errors.name_de" class="mt-1 text-sm text-red-600">{{ form.errors.name_de }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('kpi.name') }} (EN)</label>
            <input v-model="form.name_en" type="text" required class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
          </div>
        </div>

        <!-- Bilingual Descriptions -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('kpi.description') }} (DE)</label>
            <textarea v-model="form.description_de" rows="2" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"></textarea>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('kpi.description') }} (EN)</label>
            <textarea v-model="form.description_en" rows="2" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"></textarea>
          </div>
        </div>

        <!-- Assignment -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('org.company') }}</label>
            <select v-model="form.company_id" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
              <option value="">—</option>
              <option v-for="c in companies" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('org.department') }}</label>
            <select v-model="form.department_id" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
              <option value="">—</option>
              <option v-for="d in filteredDepts" :key="d.id" :value="d.id">{{ d.name }}</option>
            </select>
          </div>
        </div>

        <!-- Formula & Unit -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('kpi.formula') }}</label>
            <input v-model="form.formula" type="text" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('kpi.unit') }}</label>
            <input v-model="form.unit" type="text" placeholder="%, EUR, h, ..." class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('kpi.category') }}</label>
            <input v-model="form.category" type="text" placeholder="Manufacturing, Sales, ..." class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
          </div>
        </div>

        <!-- Targets & Thresholds -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('kpi.target') }}</label>
            <input v-model.number="form.target_value" type="number" step="any" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('kpi.warning_threshold') }}</label>
            <input v-model.number="form.warning_threshold" type="number" step="any" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('kpi.critical_threshold') }}</label>
            <input v-model.number="form.critical_threshold" type="number" step="any" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
          </div>
        </div>

        <!-- Frequency & Direction -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('kpi.frequency') }}</label>
            <select v-model="form.frequency" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
              <option value="daily">{{ $t('kpi.daily') }}</option>
              <option value="weekly">{{ $t('kpi.weekly') }}</option>
              <option value="monthly">{{ $t('kpi.monthly') }}</option>
              <option value="quarterly">{{ $t('kpi.quarterly') }}</option>
              <option value="yearly">{{ $t('kpi.yearly') }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('kpi.direction') }}</label>
            <select v-model="form.direction" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
              <option value="higher_better">{{ $t('kpi.higher_better') }}</option>
              <option value="lower_better">{{ $t('kpi.lower_better') }}</option>
            </select>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
          <a href="/kpis" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">{{ $t('common.cancel') }}</a>
          <button type="submit" :disabled="form.processing" class="px-6 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 disabled:opacity-50">
            {{ $t('common.save') }}
          </button>
        </div>
      </form>
    </div>
  </AppShell>
</template>

<script setup>
import { computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AppShell from '@/Components/Layout/AppShell.vue';

const props = defineProps({
  templates: { type: Object, default: () => ({ data: [] }) },
  companies: { type: Array, default: () => [] },
  departments: { type: Array, default: () => [] },
});

const form = useForm({
  name_de: '',
  name_en: '',
  description_de: '',
  description_en: '',
  formula: '',
  unit: '',
  target_value: null,
  warning_threshold: null,
  critical_threshold: null,
  frequency: 'monthly',
  direction: 'higher_better',
  category: '',
  company_id: '',
  department_id: '',
});

const filteredDepts = computed(() => {
  if (!form.company_id) return props.departments;
  return props.departments.filter(d => d.company_id == form.company_id);
});

function submit() {
  form.post('/kpis');
}
</script>
