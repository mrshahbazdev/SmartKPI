<template>
  <AppShell>
    <div class="max-w-3xl mx-auto space-y-6">
      <!-- Header -->
      <div>
        <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-2">
          <a href="/actions" class="hover:text-primary-600">{{ $t('action.title') }}</a>
          <span>/</span>
          <span>#{{ action.id }}</span>
        </div>
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
          <div>
            <div class="flex items-center gap-2 mb-1">
              <span class="px-2 py-0.5 rounded-full text-xs font-medium" :class="priorityClass(action.priority)">{{ action.priority }}</span>
              <span class="px-2 py-0.5 rounded-full text-xs font-medium" :class="statusClass(action.status)">{{ $t(`action.status.${action.status}`) }}</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ action.title }}</h1>
            <p v-if="action.description" class="text-gray-600 dark:text-gray-400 mt-1">{{ action.description }}</p>
          </div>
          <!-- Quick status update -->
          <div class="flex gap-2 shrink-0">
            <select v-model="statusVal" @change="updateStatus" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
              <option value="open">{{ $t('action.status.open') }}</option>
              <option value="in_progress">{{ $t('action.status.in_progress') }}</option>
              <option value="completed">{{ $t('action.status.completed') }}</option>
              <option value="cancelled">{{ $t('action.status.cancelled') }}</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Info cards -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
          <p class="text-sm text-gray-500 dark:text-gray-400">{{ $t('action.responsible') }}</p>
          <select v-model="assignedTo" @change="updateAssignee" class="w-full mt-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
            <option value="">—</option>
            <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
          </select>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
          <p class="text-sm text-gray-500 dark:text-gray-400">{{ $t('action.deadline') }}</p>
          <p class="text-lg font-semibold text-gray-900 dark:text-white mt-1">{{ action.deadline ? formatDate(action.deadline) : '—' }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
          <p class="text-sm text-gray-500 dark:text-gray-400">{{ $t('action.source_label') }}</p>
          <p class="text-lg font-semibold text-gray-900 dark:text-white mt-1">{{ action.source || 'manual' }}</p>
        </div>
      </div>

      <!-- Linked Problem -->
      <div v-if="action.problem" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">{{ $t('problem.linked') }}</h2>
        <a :href="`/problems/${action.problem.id}`" class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-gray-700/50 hover:bg-gray-100 dark:hover:bg-gray-600/50">
          <div>
            <span class="px-2 py-0.5 rounded-full text-xs font-medium" :class="severityClass(action.problem.severity)">{{ action.problem.severity }}</span>
            <span class="ml-2 text-gray-700 dark:text-gray-300">{{ action.problem.title }}</span>
          </div>
          <span class="text-gray-400">→</span>
        </a>
      </div>

      <!-- Effectiveness -->
      <div v-if="effectiveness" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">{{ $t('action.effectiveness') }}</h2>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div class="text-center p-3 rounded-lg bg-gray-50 dark:bg-gray-700/50">
            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $t('action.before') }}</p>
            <p class="text-xl font-bold text-gray-900 dark:text-white">{{ formatVal(effectiveness.before) }}</p>
          </div>
          <div class="text-center p-3 rounded-lg bg-gray-50 dark:bg-gray-700/50">
            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $t('action.after') }}</p>
            <p class="text-xl font-bold text-gray-900 dark:text-white">{{ formatVal(effectiveness.after) }}</p>
          </div>
          <div class="text-center p-3 rounded-lg" :class="isImprovement ? 'bg-green-50 dark:bg-green-900/20' : 'bg-red-50 dark:bg-red-900/20'">
            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $t('action.change') }}</p>
            <p class="text-xl font-bold" :class="isImprovement ? 'text-green-600' : 'text-red-600'">
              {{ effectiveness.change_pct !== null ? (effectiveness.change_pct > 0 ? '+' : '') + effectiveness.change_pct + '%' : '—' }}
            </p>
          </div>
        </div>
      </div>
    </div>
  </AppShell>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import AppShell from '@/Components/Layout/AppShell.vue';

const props = defineProps({
  action: { type: Object, required: true },
  effectiveness: { type: Object, default: null },
  users: { type: Array, default: () => [] },
});

const page = usePage();
const locale = computed(() => page.props.locale || 'de');
const statusVal = ref(props.action.status);
const assignedTo = ref(props.action.assigned_to || '');

const isImprovement = computed(() => {
  if (!props.effectiveness) return false;
  return props.effectiveness.direction === 'higher_better'
    ? props.effectiveness.change > 0
    : props.effectiveness.change < 0;
});

function updateStatus() {
  router.put(`/actions/${props.action.id}`, { status: statusVal.value }, { preserveState: true });
}

function updateAssignee() {
  router.put(`/actions/${props.action.id}`, { assigned_to: assignedTo.value || null }, { preserveState: true });
}

function formatDate(d) {
  if (!d) return '—';
  return new Date(d).toLocaleDateString(locale.value === 'de' ? 'de-DE' : 'en-US');
}

function formatVal(v) {
  if (v === null || v === undefined) return '—';
  return locale.value === 'de' ? v.toLocaleString('de-DE', { maximumFractionDigits: 2 }) : v.toLocaleString('en-US', { maximumFractionDigits: 2 });
}

function priorityClass(p) { return { low: 'bg-blue-100 text-blue-700', medium: 'bg-yellow-100 text-yellow-700', high: 'bg-orange-100 text-orange-700', critical: 'bg-red-100 text-red-700' }[p] || ''; }
function statusClass(s) { return { open: 'bg-red-50 text-red-600', in_progress: 'bg-yellow-50 text-yellow-600', completed: 'bg-green-50 text-green-600', cancelled: 'bg-gray-100 text-gray-600' }[s] || ''; }
function severityClass(s) { return { low: 'bg-blue-100 text-blue-700', medium: 'bg-yellow-100 text-yellow-700', high: 'bg-orange-100 text-orange-700', critical: 'bg-red-100 text-red-700' }[s] || ''; }
</script>
