<template>
  <AppShell>
    <div class="max-w-2xl mx-auto space-y-6">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $t('whitelabel.title') }}</h1>

      <form @submit.prevent="submit" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 space-y-5">
        <div>
          <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">{{ $t('whitelabel.app_name') }}</label>
          <input v-model="form.app_name" type="text" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="SmartKPI" />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">{{ $t('whitelabel.primary_color') }}</label>
            <div class="flex items-center gap-3">
              <input v-model="form.primary_color" type="color" class="h-10 w-16 rounded border-0 cursor-pointer" />
              <input v-model="form.primary_color" type="text" class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm" />
            </div>
          </div>
          <div>
            <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">{{ $t('whitelabel.secondary_color') }}</label>
            <div class="flex items-center gap-3">
              <input v-model="form.secondary_color" type="color" class="h-10 w-16 rounded border-0 cursor-pointer" />
              <input v-model="form.secondary_color" type="text" class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm" />
            </div>
          </div>
        </div>

        <!-- Preview -->
        <div class="p-4 rounded-lg border border-gray-200 dark:border-gray-700">
          <p class="text-sm text-gray-500 mb-3">{{ $t('whitelabel.preview') }}</p>
          <div class="flex items-center gap-3 p-3 rounded-lg" :style="{ backgroundColor: form.primary_color + '15' }">
            <div class="w-10 h-10 rounded-lg flex items-center justify-center text-white font-bold text-sm" :style="{ backgroundColor: form.primary_color }">
              {{ (form.app_name || 'SK').substring(0, 2).toUpperCase() }}
            </div>
            <span class="font-semibold" :style="{ color: form.primary_color }">{{ form.app_name || 'SmartKPI' }}</span>
          </div>
        </div>

        <div class="flex justify-end pt-2">
          <button type="submit" :disabled="form.processing" class="px-6 py-2 bg-primary-600 text-white text-sm rounded-lg hover:bg-primary-700 disabled:opacity-50">{{ $t('common.save') }}</button>
        </div>
      </form>
    </div>
  </AppShell>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import AppShell from '@/Components/Layout/AppShell.vue';

const props = defineProps({
  settings: { type: Object, default: () => ({}) },
});

const form = useForm({
  app_name: props.settings.app_name || '',
  primary_color: props.settings.primary_color || '#4F46E5',
  secondary_color: props.settings.secondary_color || '#7C3AED',
});

function submit() {
  form.put('/settings/white-label');
}
</script>
