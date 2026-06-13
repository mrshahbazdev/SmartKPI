<template>
  <AppShell>
    <div class="space-y-6">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $t('kpi.template_library') }}</h1>
          <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $t('kpi.template_desc') }}</p>
        </div>
        <a href="/kpis" class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-sm rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
          {{ $t('common.back') }}
        </a>
      </div>

      <!-- Templates by category -->
      <div v-for="(group, category) in groupedTemplates" :key="category" class="space-y-3">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200">{{ category }}</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
          <div
            v-for="tpl in group"
            :key="tpl.id"
            class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5"
          >
            <div class="flex items-start justify-between mb-3">
              <div>
                <h3 class="font-medium text-gray-900 dark:text-white">
                  {{ locale === 'de' ? tpl.name_de : tpl.name_en }}
                </h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                  {{ locale === 'de' ? tpl.description_de : tpl.description_en }}
                </p>
              </div>
              <span v-if="tpl.unit" class="text-xs bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded text-gray-600 dark:text-gray-400">
                {{ tpl.unit }}
              </span>
            </div>

            <div class="text-xs text-gray-500 dark:text-gray-400 space-y-1 mb-4">
              <p v-if="tpl.formula"><span class="font-medium">{{ $t('kpi.formula') }}:</span> {{ tpl.formula }}</p>
              <p><span class="font-medium">{{ $t('kpi.direction') }}:</span> {{ $t(`kpi.${tpl.direction}`) }}</p>
              <p><span class="font-medium">{{ $t('kpi.frequency') }}:</span> {{ $t(`kpi.${tpl.frequency}`) }}</p>
            </div>

            <button @click="openUseModal(tpl)" class="w-full px-3 py-2 bg-primary-600 text-white text-sm rounded-lg hover:bg-primary-700 transition-colors">
              {{ $t('kpi.use_template') }}
            </button>
          </div>
        </div>
      </div>

      <!-- Use Template Modal -->
      <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" @click.self="showModal = false">
        <div class="bg-white dark:bg-gray-800 rounded-xl w-full max-w-md p-6 space-y-4">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
            {{ $t('kpi.use_template') }}: {{ locale === 'de' ? selectedTemplate?.name_de : selectedTemplate?.name_en }}
          </h3>
          <form @submit.prevent="submitUse">
            <div class="space-y-3">
              <div>
                <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">{{ $t('org.company') }}</label>
                <select v-model="templateForm.company_id" required class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                  <option value="">—</option>
                  <option v-for="c in companies" :key="c.id" :value="c.id">{{ c.name }}</option>
                </select>
              </div>
              <div>
                <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">{{ $t('org.department') }}</label>
                <select v-model="templateForm.department_id" required class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                  <option value="">—</option>
                  <option v-for="d in filteredDepts" :key="d.id" :value="d.id">{{ d.name }}</option>
                </select>
              </div>
              <div>
                <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">{{ $t('kpi.target') }}</label>
                <input v-model.number="templateForm.target_value" type="number" step="any" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm" />
              </div>
            </div>
            <div class="flex justify-end gap-3 mt-6">
              <button type="button" @click="showModal = false" class="px-4 py-2 text-sm text-gray-600">{{ $t('common.cancel') }}</button>
              <button type="submit" :disabled="templateForm.processing" class="px-4 py-2 bg-primary-600 text-white text-sm rounded-lg hover:bg-primary-700 disabled:opacity-50">
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
import { computed, ref } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import AppShell from '@/Components/Layout/AppShell.vue';

const props = defineProps({
  templates: { type: Object, default: () => ({ data: [] }) },
  companies: { type: Array, default: () => [] },
  departments: { type: Array, default: () => [] },
});

const page = usePage();
const locale = computed(() => page.props.locale || 'de');

const groupedTemplates = computed(() => {
  const groups = {};
  const data = props.templates.data || props.templates;
  (Array.isArray(data) ? data : []).forEach(t => {
    const cat = t.category || 'Other';
    if (!groups[cat]) groups[cat] = [];
    groups[cat].push(t);
  });
  return groups;
});

const showModal = ref(false);
const selectedTemplate = ref(null);

const templateForm = useForm({
  company_id: '',
  department_id: '',
  target_value: null,
  warning_threshold: null,
  critical_threshold: null,
});

const filteredDepts = computed(() => {
  if (!templateForm.company_id) return props.departments;
  return props.departments.filter(d => d.company_id == templateForm.company_id);
});

function openUseModal(tpl) {
  selectedTemplate.value = tpl;
  templateForm.target_value = tpl.target_value;
  templateForm.warning_threshold = tpl.warning_threshold;
  templateForm.critical_threshold = tpl.critical_threshold;
  showModal.value = true;
}

function submitUse() {
  templateForm.post(`/kpis/templates/${selectedTemplate.value.id}/use`, {
    onSuccess: () => { showModal.value = false; },
  });
}
</script>
