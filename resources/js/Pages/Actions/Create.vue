<template>
  <AppShell>
    <div class="max-w-2xl mx-auto space-y-6">
      <div>
        <a href="/actions" class="text-sm text-gray-500 dark:text-gray-400 hover:text-primary-600">← {{ $t('common.back') }}</a>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mt-2">{{ $t('action.create') }}</h1>
      </div>

      <form @submit.prevent="submit" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 space-y-4">
        <div>
          <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">{{ $t('action.suggestion') }} *</label>
          <input v-model="form.title" type="text" required class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm" />
          <p v-if="form.errors.title" class="text-red-500 text-xs mt-1">{{ form.errors.title }}</p>
        </div>

        <div>
          <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">{{ $t('kpi.description') }}</label>
          <textarea v-model="form.description" rows="3" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm"></textarea>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">{{ $t('problem.linked') }}</label>
            <select v-model="form.problem_id" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
              <option :value="null">—</option>
              <option v-for="p in problems" :key="p.id" :value="p.id">{{ p.title }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">{{ $t('action.responsible') }} *</label>
            <select v-model="form.assigned_to" required class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
              <option value="">—</option>
              <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">{{ $t('action.priority_label') }} *</label>
            <select v-model="form.priority" required class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
              <option value="low">{{ $t('problem.severity.low') }}</option>
              <option value="medium">{{ $t('problem.severity.medium') }}</option>
              <option value="high">{{ $t('problem.severity.high') }}</option>
              <option value="critical">{{ $t('problem.severity.critical') }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">{{ $t('action.deadline') }}</label>
            <input v-model="form.deadline" type="date" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm" />
          </div>
        </div>

        <div class="flex justify-end gap-3 pt-4">
          <a href="/actions" class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400">{{ $t('common.cancel') }}</a>
          <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-primary-600 text-white text-sm rounded-lg hover:bg-primary-700 disabled:opacity-50">
            {{ $t('common.save') }}
          </button>
        </div>
      </form>
    </div>
  </AppShell>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import AppShell from '@/Components/Layout/AppShell.vue';

const props = defineProps({
  problems: { type: Array, default: () => [] },
  users: { type: Array, default: () => [] },
  problem_id: { type: [Number, String], default: null },
});

const form = useForm({
  title: '',
  description: '',
  problem_id: props.problem_id ? Number(props.problem_id) : null,
  assigned_to: '',
  priority: 'medium',
  deadline: null,
});

function submit() {
  form.post('/actions');
}
</script>
