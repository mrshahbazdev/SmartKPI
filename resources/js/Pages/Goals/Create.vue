<template>
  <AppShell>
    <div class="max-w-2xl mx-auto space-y-6">
      <div>
        <a href="/goals" class="text-sm text-gray-500 dark:text-gray-400 hover:text-primary-600">← {{ $t('common.back') }}</a>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mt-2">{{ $t('goal.create') }}</h1>
      </div>

      <form @submit.prevent="submit" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 space-y-4">
        <div>
          <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">{{ $t('goal.goal_title') }} *</label>
          <input v-model="form.title" type="text" required class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm" />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">{{ $t('org.company') }} *</label>
            <select v-model="form.company_id" required class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
              <option value="">—</option>
              <option v-for="c in companies" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">{{ $t('goal.linked_kpi') }}</label>
            <select v-model="form.kpi_definition_id" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
              <option :value="null">—</option>
              <option v-for="k in filteredKpis" :key="k.id" :value="k.id">{{ locale === 'de' ? k.name_de : k.name_en }}</option>
            </select>
          </div>
        </div>

        <div>
          <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">{{ $t('kpi.description') }}</label>
          <textarea v-model="form.description" rows="2" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm"></textarea>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div>
            <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">{{ $t('goal.target_value') }}</label>
            <input v-model.number="form.target_value" type="number" step="any" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm" />
          </div>
          <div>
            <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">{{ $t('goal.start') }}</label>
            <input v-model="form.start_date" type="date" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm" />
          </div>
          <div>
            <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">{{ $t('goal.end') }}</label>
            <input v-model="form.end_date" type="date" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm" />
          </div>
        </div>

        <div>
          <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">{{ $t('goal.assigned_to') }}</label>
          <select v-model="form.assigned_to" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
            <option :value="null">—</option>
            <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
          </select>
        </div>

        <div class="flex justify-end gap-3 pt-4">
          <a href="/goals" class="px-4 py-2 text-sm text-gray-600">{{ $t('common.cancel') }}</a>
          <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-primary-600 text-white text-sm rounded-lg hover:bg-primary-700 disabled:opacity-50">{{ $t('common.save') }}</button>
        </div>
      </form>
    </div>
  </AppShell>
</template>

<script setup>
import { computed } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import AppShell from '@/Components/Layout/AppShell.vue';

const props = defineProps({
  companies: { type: Array, default: () => [] },
  kpis: { type: Array, default: () => [] },
  users: { type: Array, default: () => [] },
});

const page = usePage();
const locale = computed(() => page.props.locale || 'de');

const form = useForm({
  company_id: '',
  kpi_definition_id: null,
  title: '',
  description: '',
  target_value: null,
  start_date: null,
  end_date: null,
  assigned_to: null,
});

const filteredKpis = computed(() => {
  if (!form.company_id) return props.kpis;
  return props.kpis.filter(k => k.company_id == form.company_id);
});

function submit() {
  form.post('/goals');
}
</script>
