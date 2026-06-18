<template>
  <AppShell>
    <div class="space-y-6">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $t('relationship.title') }}</h1>
      </div>

      <!-- Add Relationship Form -->
      <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">{{ $t('relationship.add') }}</h2>
        <form @submit.prevent="addRelationship" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 items-end">
          <div>
            <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">{{ $t('relationship.cause') }}</label>
            <select v-model="form.cause_kpi_id" required class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
              <option value="">—</option>
              <option v-for="k in kpis" :key="k.id" :value="k.id">{{ locale === 'de' ? k.name_de : k.name_en }} ({{ k.department }})</option>
            </select>
          </div>
          <div>
            <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">{{ $t('relationship.effect') }}</label>
            <select v-model="form.effect_kpi_id" required class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
              <option value="">—</option>
              <option v-for="k in kpis" :key="k.id" :value="k.id">{{ locale === 'de' ? k.name_de : k.name_en }} ({{ k.department }})</option>
            </select>
          </div>
          <div>
            <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">{{ $t('relationship.weight') }}</label>
            <input v-model.number="form.weight" type="number" min="0" max="1" step="0.1" required class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm" />
          </div>
          <div>
            <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">{{ $t('relationship.confidence') }}</label>
            <input v-model.number="form.confidence" type="number" min="0" max="1" step="0.1" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm" />
          </div>
          <div>
            <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">{{ $t('relationship.lag_days') }}</label>
            <input v-model.number="form.lag_days" type="number" min="0" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm" />
          </div>
          <div>
            <button type="submit" :disabled="form.processing" class="w-full px-4 py-2 bg-primary-600 text-white text-sm rounded-lg hover:bg-primary-700 disabled:opacity-50">
              {{ $t('common.save') }}
            </button>
          </div>
        </form>
      </div>

      <!-- Graph Visualization (simple) -->
      <div v-if="graph.edges.length" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ $t('relationship.graph') }}</h2>
        <!-- Simple cause chain display -->
        <div class="space-y-3">
          <div v-for="edge in graph.edges" :key="edge.id" class="flex items-center gap-3 p-3 rounded-lg bg-gray-50 dark:bg-gray-700/50">
            <div class="flex-1 text-right">
              <span class="font-medium text-gray-900 dark:text-white">{{ getNodeLabel(edge.source) }}</span>
              <p class="text-xs text-gray-500 dark:text-gray-400">{{ getNodeDept(edge.source) }}</p>
            </div>
            <div class="flex items-center gap-1 text-primary-600 dark:text-primary-400 shrink-0">
              <span class="text-xs">{{ $t('relationship.weight') }}: {{ edge.weight }}</span>
              <span>→</span>
            </div>
            <div class="flex-1">
              <span class="font-medium text-gray-900 dark:text-white">{{ getNodeLabel(edge.target) }}</span>
              <p class="text-xs text-gray-500 dark:text-gray-400">{{ getNodeDept(edge.target) }}</p>
            </div>
            <div class="flex gap-1 shrink-0">
              <a :href="`/relationships/trace/${edge.target}`" class="text-xs text-primary-600 hover:text-primary-700">{{ $t('relationship.trace') }}</a>
              <button @click="deleteRel(edge.id)" class="text-xs text-red-500 hover:text-red-700 ml-2">×</button>
            </div>
          </div>
        </div>
      </div>

      <div v-else class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-12 text-center">
        <p class="text-gray-500 dark:text-gray-400">{{ $t('relationship.no_relationships') }}</p>
      </div>
    </div>
  </AppShell>
</template>

<script setup>
import { computed } from 'vue';
import { useForm, router, usePage } from '@inertiajs/vue3';
import AppShell from '@/Components/Layout/AppShell.vue';

const props = defineProps({
  relationships: { type: Array, default: () => [] },
  kpis: { type: Array, default: () => [] },
  graph: { type: Object, default: () => ({ nodes: [], edges: [] }) },
});

const page = usePage();
const locale = computed(() => page.props.locale || 'de');

const form = useForm({
  cause_kpi_id: '',
  effect_kpi_id: '',
  weight: 0.5,
  confidence: 0.5,
  lag_days: 0,
});

function addRelationship() {
  form.post('/relationships', { preserveState: true, onSuccess: () => form.reset() });
}

function deleteRel(id) {
  router.delete(`/relationships/${id}`, { preserveState: true });
}

function getNodeLabel(id) {
  const node = props.graph.nodes.find(n => n.id === id);
  return node ? (locale.value === 'de' ? node.label_de : node.label_en) : '?';
}

function getNodeDept(id) {
  const node = props.graph.nodes.find(n => n.id === id);
  return node?.department || '';
}
</script>
