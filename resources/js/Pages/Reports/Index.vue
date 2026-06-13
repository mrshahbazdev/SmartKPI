<template>
  <AppShell>
    <div class="max-w-2xl mx-auto space-y-6">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $t('report.title') }}</h1>

      <form @submit.prevent="generate" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 space-y-4">
        <div>
          <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">{{ $t('org.company') }}</label>
          <select v-model="form.company_id" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
            <option value="">{{ $t('report.all_companies') }}</option>
            <option v-for="c in companies" :key="c.id" :value="c.id">{{ c.name }}</option>
          </select>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">{{ $t('report.language') }} *</label>
            <select v-model="form.language" required class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
              <option value="de">Deutsch</option>
              <option value="en">English</option>
            </select>
          </div>
          <div>
            <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">{{ $t('report.period') }} *</label>
            <select v-model="form.period" required class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
              <option value="week">{{ $t('report.week') }}</option>
              <option value="month">{{ $t('report.month') }}</option>
              <option value="quarter">{{ $t('report.quarter') }}</option>
            </select>
          </div>
        </div>
        <div class="flex justify-end">
          <button type="submit" :disabled="form.processing" class="px-6 py-2 bg-primary-600 text-white text-sm rounded-lg hover:bg-primary-700 disabled:opacity-50">
            {{ $t('report.generate') }}
          </button>
        </div>
      </form>
    </div>
  </AppShell>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import AppShell from '@/Components/Layout/AppShell.vue';

defineProps({
  companies: { type: Array, default: () => [] },
});

const form = useForm({
  company_id: '',
  language: 'de',
  period: 'month',
});

function generate() {
  form.post('/reports/generate');
}
</script>
