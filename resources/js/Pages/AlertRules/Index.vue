<template>
  <AppShell>
    <div class="space-y-6">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $t('alert.rules') }}</h1>
        <button @click="showCreate = true" class="px-4 py-2 bg-primary-600 text-white text-sm rounded-lg hover:bg-primary-700">
          + {{ $t('alert.add_rule') }}
        </button>
      </div>

      <!-- Filter by company -->
      <select v-model="companyFilter" @change="applyFilter" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
        <option value="">{{ $t('org.all_companies') }}</option>
        <option v-for="c in companies" :key="c.id" :value="c.id">{{ c.name }}</option>
      </select>

      <!-- Rules list -->
      <div class="space-y-3">
        <div v-for="rule in rules.data" :key="rule.id"
          class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex-1">
              <div class="flex items-center gap-2 mb-1">
                <span class="px-2 py-0.5 rounded-full text-xs font-medium" :class="severityClass(rule.severity)">{{ rule.severity }}</span>
                <span class="px-2 py-0.5 rounded-full text-xs font-medium" :class="rule.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'">
                  {{ rule.is_active ? $t('common.active') : $t('common.inactive') }}
                </span>
                <span class="text-xs text-gray-500 dark:text-gray-400">{{ rule.type }}</span>
              </div>
              <h3 class="font-medium text-gray-900 dark:text-white">{{ rule.name }}</h3>
              <div class="flex items-center gap-4 mt-1 text-sm text-gray-500 dark:text-gray-400">
                <span>{{ rule.company?.name }}</span>
                <span v-if="rule.kpi_definition">{{ locale === 'de' ? rule.kpi_definition.name_de : rule.kpi_definition.name_en }}</span>
                <span v-if="rule.notify_user">→ {{ rule.notify_user.name }}</span>
                <span v-if="rule.escalation_hours">{{ $t('alert.escalation') }}: {{ rule.escalation_hours }}h</span>
              </div>
            </div>
            <div class="flex gap-2 shrink-0">
              <button @click="toggleActive(rule)" class="text-sm text-gray-500 hover:text-gray-700">
                {{ rule.is_active ? $t('alert.deactivate') : $t('alert.activate') }}
              </button>
              <button @click="deleteRule(rule.id)" class="text-sm text-red-500 hover:text-red-700">{{ $t('common.delete') }}</button>
            </div>
          </div>
        </div>
      </div>

      <div v-if="!rules.data?.length" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-12 text-center">
        <p class="text-gray-500 dark:text-gray-400">{{ $t('alert.no_rules') }}</p>
      </div>

      <!-- Create Modal -->
      <div v-if="showCreate" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" @click.self="showCreate = false">
        <div class="bg-white dark:bg-gray-800 rounded-2xl w-full max-w-lg p-6 space-y-4">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $t('alert.add_rule') }}</h2>
          <form @submit.prevent="createRule" class="space-y-3">
            <div>
              <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">{{ $t('org.company') }} *</label>
              <select v-model="createForm.company_id" required class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                <option value="">—</option>
                <option v-for="c in companies" :key="c.id" :value="c.id">{{ c.name }}</option>
              </select>
            </div>
            <div>
              <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">KPI ({{ $t('common.all') }})</label>
              <select v-model="createForm.kpi_definition_id" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                <option :value="null">{{ $t('alert.all_kpis') }}</option>
                <option v-for="k in filteredKpis" :key="k.id" :value="k.id">{{ locale === 'de' ? k.name_de : k.name_en }}</option>
              </select>
            </div>
            <div>
              <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">{{ $t('org.name') }} *</label>
              <input v-model="createForm.name" type="text" required class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm" />
            </div>
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">{{ $t('alert.type') }}</label>
                <select v-model="createForm.type" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                  <option value="threshold">Threshold</option>
                  <option value="trend">Trend</option>
                  <option value="anomaly">Anomaly</option>
                </select>
              </div>
              <div>
                <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">{{ $t('problem.severity_label') }}</label>
                <select v-model="createForm.severity" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                  <option value="low">{{ $t('problem.severity.low') }}</option>
                  <option value="medium">{{ $t('problem.severity.medium') }}</option>
                  <option value="high">{{ $t('problem.severity.high') }}</option>
                  <option value="critical">{{ $t('problem.severity.critical') }}</option>
                </select>
              </div>
            </div>

            <!-- Threshold conditions -->
            <div v-if="createForm.type === 'threshold'" class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">{{ $t('alert.operator') }}</label>
                <select v-model="conditions.operator" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                  <option value=">">></option>
                  <option value=">=">>=</option>
                  <option value="<"><</option>
                  <option value="<="><=</option>
                </select>
              </div>
              <div>
                <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">{{ $t('kpi.value') }}</label>
                <input v-model.number="conditions.value" type="number" step="any" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm" />
              </div>
            </div>

            <!-- Trend conditions -->
            <div v-if="createForm.type === 'trend'" class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">{{ $t('alert.consecutive') }}</label>
                <input v-model.number="conditions.consecutive" type="number" min="2" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm" />
              </div>
              <div>
                <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">{{ $t('kpi.direction') }}</label>
                <select v-model="conditions.direction" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                  <option value="decline">{{ $t('alert.decline') }}</option>
                  <option value="increase">{{ $t('alert.increase') }}</option>
                </select>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">{{ $t('alert.notify_user') }}</label>
                <select v-model="createForm.notify_user_id" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                  <option :value="null">—</option>
                  <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
                </select>
              </div>
              <div>
                <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">{{ $t('alert.escalation_hours') }}</label>
                <input v-model.number="createForm.escalation_hours" type="number" min="1" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm" />
              </div>
            </div>

            <div class="flex justify-end gap-3 pt-3">
              <button type="button" @click="showCreate = false" class="px-4 py-2 text-sm text-gray-600">{{ $t('common.cancel') }}</button>
              <button type="submit" :disabled="createForm.processing" class="px-4 py-2 bg-primary-600 text-white text-sm rounded-lg hover:bg-primary-700 disabled:opacity-50">
                {{ $t('common.save') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AppShell>
</template>

<script setup>
import { ref, reactive, computed } from 'vue';
import { useForm, router, usePage } from '@inertiajs/vue3';
import AppShell from '@/Components/Layout/AppShell.vue';

const props = defineProps({
  rules: { type: Object, default: () => ({ data: [] }) },
  companies: { type: Array, default: () => [] },
  kpis: { type: Array, default: () => [] },
  users: { type: Array, default: () => [] },
  filters: { type: Object, default: () => ({}) },
});

const page = usePage();
const locale = computed(() => page.props.locale || 'de');
const showCreate = ref(false);
const companyFilter = ref(props.filters.company_id || '');

const conditions = reactive({ operator: '>', value: 0, consecutive: 3, direction: 'decline' });

const createForm = useForm({
  company_id: '',
  kpi_definition_id: null,
  name: '',
  type: 'threshold',
  conditions: {},
  severity: 'medium',
  notify_user_id: null,
  escalation_hours: null,
});

const filteredKpis = computed(() => {
  if (!createForm.company_id) return props.kpis;
  return props.kpis.filter(k => k.company_id == createForm.company_id);
});

function applyFilter() {
  const params = {};
  if (companyFilter.value) params.company_id = companyFilter.value;
  router.get('/alert-rules', params, { preserveState: true });
}

function createRule() {
  createForm.conditions = { ...conditions };
  createForm.post('/alert-rules', { preserveState: true, onSuccess: () => { showCreate.value = false; createForm.reset(); } });
}

function toggleActive(rule) {
  router.put(`/alert-rules/${rule.id}`, { is_active: !rule.is_active }, { preserveState: true });
}

function deleteRule(id) {
  router.delete(`/alert-rules/${id}`, { preserveState: true });
}

function severityClass(s) {
  return { low: 'bg-blue-100 text-blue-700', medium: 'bg-yellow-100 text-yellow-700', high: 'bg-orange-100 text-orange-700', critical: 'bg-red-100 text-red-700' }[s] || '';
}
</script>
