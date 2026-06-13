<template>
  <AppShell>
    <div class="space-y-6">
      <!-- Header -->
      <div>
        <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-2">
          <a href="/problems" class="hover:text-primary-600">{{ $t('problem.title') }}</a>
          <span>/</span>
          <span>#{{ problem.id }}</span>
        </div>
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
          <div>
            <div class="flex items-center gap-2 mb-1">
              <span class="px-2 py-0.5 rounded-full text-xs font-medium" :class="severityClass(problem.severity)">
                {{ $t(`problem.severity.${problem.severity}`) }}
              </span>
              <span class="px-2 py-0.5 rounded-full text-xs font-medium" :class="statusClass(problem.status)">
                {{ $t(`problem.status_${problem.status}`) }}
              </span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ problem.title }}</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">{{ problem.description }}</p>
          </div>
          <!-- Status update -->
          <div class="flex gap-2 shrink-0">
            <select v-model="statusForm.status" @change="updateStatus"
              class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
              <option value="open">{{ $t('problem.status_open') }}</option>
              <option value="investigating">{{ $t('problem.status_investigating') }}</option>
              <option value="resolved">{{ $t('problem.status_resolved') }}</option>
              <option value="closed">{{ $t('problem.status_closed') }}</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Info cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
          <p class="text-sm text-gray-500 dark:text-gray-400">{{ $t('problem.detected') }}</p>
          <p class="text-lg font-semibold text-gray-900 dark:text-white mt-1">{{ formatDate(problem.detected_at) }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
          <p class="text-sm text-gray-500 dark:text-gray-400">{{ $t('org.department') }}</p>
          <p class="text-lg font-semibold text-gray-900 dark:text-white mt-1">{{ problem.department?.name || '—' }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
          <p class="text-sm text-gray-500 dark:text-gray-400">KPI</p>
          <p class="text-lg font-semibold text-gray-900 dark:text-white mt-1">
            {{ problem.kpi_definition ? (locale === 'de' ? problem.kpi_definition.name_de : problem.kpi_definition.name_en) : '—' }}
          </p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
          <p class="text-sm text-gray-500 dark:text-gray-400">{{ $t('action.responsible') }}</p>
          <div class="flex items-center gap-2 mt-1">
            <select v-model="assignForm.assigned_to" @change="assignUser"
              class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
              <option value="">—</option>
              <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Root Causes -->
      <div v-if="problem.root_causes?.length" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">{{ $t('cause.root_cause') }}</h2>
        <div class="space-y-2">
          <div v-for="rc in problem.root_causes" :key="rc.id" class="flex items-center justify-between text-sm p-2 rounded-lg bg-gray-50 dark:bg-gray-700/50">
            <span class="text-gray-700 dark:text-gray-300">{{ rc.description }}</span>
            <span class="text-gray-500 dark:text-gray-400">{{ $t('cause.confidence') }}: {{ Math.round(rc.confidence * 100) }}%</span>
          </div>
        </div>
      </div>

      <!-- Actions linked to this problem -->
      <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
        <div class="flex items-center justify-between mb-3">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $t('nav.actions') }}</h2>
          <a :href="`/actions/create?problem_id=${problem.id}`" class="text-sm text-primary-600 hover:text-primary-700">+ {{ $t('action.create') }}</a>
        </div>
        <div v-if="problem.actions?.length" class="space-y-2">
          <a v-for="a in problem.actions" :key="a.id" :href="`/actions/${a.id}`"
            class="flex items-center justify-between text-sm p-3 rounded-lg bg-gray-50 dark:bg-gray-700/50 hover:bg-gray-100 dark:hover:bg-gray-600/50">
            <div class="flex items-center gap-2">
              <span class="px-2 py-0.5 rounded-full text-xs font-medium" :class="priorityClass(a.priority)">{{ a.priority }}</span>
              <span class="text-gray-700 dark:text-gray-300">{{ a.title }}</span>
            </div>
            <div class="flex items-center gap-2">
              <span v-if="a.assignee" class="text-gray-500 dark:text-gray-400">{{ a.assignee.name }}</span>
              <span class="px-2 py-0.5 rounded-full text-xs" :class="actionStatusClass(a.status)">{{ a.status }}</span>
            </div>
          </a>
        </div>
        <p v-else class="text-sm text-gray-400">{{ $t('action.no_actions') }}</p>
      </div>
    </div>
  </AppShell>
</template>

<script setup>
import { reactive, computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import AppShell from '@/Components/Layout/AppShell.vue';

const props = defineProps({
  problem: { type: Object, required: true },
  users: { type: Array, default: () => [] },
});

const page = usePage();
const locale = computed(() => page.props.locale || 'de');

const statusForm = reactive({ status: props.problem.status });
const assignForm = reactive({ assigned_to: props.problem.assigned_to || '' });

function updateStatus() {
  router.put(`/problems/${props.problem.id}`, { status: statusForm.status }, { preserveState: true });
}

function assignUser() {
  if (assignForm.assigned_to) {
    router.post(`/problems/${props.problem.id}/assign`, { assigned_to: assignForm.assigned_to }, { preserveState: true });
  }
}

function formatDate(d) {
  if (!d) return '—';
  return new Date(d).toLocaleDateString(locale.value === 'de' ? 'de-DE' : 'en-US', { day: '2-digit', month: '2-digit', year: 'numeric' });
}

function severityClass(s) {
  return { low: 'bg-blue-100 text-blue-700', medium: 'bg-yellow-100 text-yellow-700', high: 'bg-orange-100 text-orange-700', critical: 'bg-red-100 text-red-700' }[s] || '';
}
function statusClass(s) {
  return { open: 'bg-red-50 text-red-600', investigating: 'bg-yellow-50 text-yellow-600', resolved: 'bg-green-50 text-green-600', closed: 'bg-gray-100 text-gray-600' }[s] || '';
}
function priorityClass(p) {
  return { low: 'bg-blue-100 text-blue-700', medium: 'bg-yellow-100 text-yellow-700', high: 'bg-orange-100 text-orange-700', critical: 'bg-red-100 text-red-700' }[p] || '';
}
function actionStatusClass(s) {
  return { open: 'bg-red-50 text-red-600', in_progress: 'bg-yellow-50 text-yellow-600', completed: 'bg-green-50 text-green-600', cancelled: 'bg-gray-100 text-gray-600' }[s] || '';
}
</script>
