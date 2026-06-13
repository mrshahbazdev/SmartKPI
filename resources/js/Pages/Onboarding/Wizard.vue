<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <!-- Desktop: sidebar progress + right content -->
    <div class="flex min-h-screen">
      <!-- Sidebar (desktop only) -->
      <div class="hidden lg:flex lg:w-80 lg:flex-col bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 p-8">
        <div class="mb-8">
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">SmartKPI</h1>
          <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ steps[step]?.subtitle }}</p>
        </div>
        <div class="space-y-4 flex-1">
          <div v-for="(s, i) in steps" :key="i" class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium shrink-0"
              :class="i < step ? 'bg-green-100 text-green-700' : i === step ? 'bg-primary-100 text-primary-700 ring-2 ring-primary-500' : 'bg-gray-100 text-gray-400'">
              <span v-if="i < step">&#10003;</span>
              <span v-else>{{ i + 1 }}</span>
            </div>
            <span class="text-sm" :class="i === step ? 'text-gray-900 dark:text-white font-medium' : 'text-gray-500'">{{ s.title }}</span>
          </div>
        </div>
      </div>

      <!-- Mobile progress bar -->
      <div class="lg:hidden fixed top-0 inset-x-0 z-50 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-4 py-3">
        <div class="flex items-center justify-between mb-2">
          <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $t('onboarding.step') }} {{ step + 1 }}/{{ steps.length }}</span>
          <span class="text-xs text-gray-500">{{ steps[step]?.title }}</span>
        </div>
        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5">
          <div class="bg-primary-500 h-1.5 rounded-full transition-all" :style="{ width: ((step + 1) / steps.length * 100) + '%' }"></div>
        </div>
      </div>

      <!-- Content -->
      <div class="flex-1 flex items-center justify-center p-6 lg:p-12 pt-20 lg:pt-12">
        <div class="w-full max-w-lg">
          <!-- Step 0: Language -->
          <div v-if="step === 0" class="space-y-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Sprache wählen / Choose Language</h2>
            <div class="grid grid-cols-2 gap-4">
              <button @click="selectLanguage('de')"
                class="p-6 rounded-xl border-2 text-center transition-all"
                :class="langForm.locale === 'de' ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300'">
                <span class="text-3xl block mb-2">🇩🇪</span>
                <span class="font-medium text-gray-900 dark:text-white">Deutsch</span>
              </button>
              <button @click="selectLanguage('en')"
                class="p-6 rounded-xl border-2 text-center transition-all"
                :class="langForm.locale === 'en' ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300'">
                <span class="text-3xl block mb-2">🇬🇧</span>
                <span class="font-medium text-gray-900 dark:text-white">English</span>
              </button>
            </div>
            <button @click="submitLanguage" :disabled="!langForm.locale" class="w-full px-6 py-3 bg-primary-600 text-white rounded-lg hover:bg-primary-700 disabled:opacity-50 font-medium">
              {{ $t('common.next') }}
            </button>
          </div>

          <!-- Step 1: Create Structure -->
          <div v-if="step === 1" class="space-y-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $t('onboarding.create_structure') }}</h2>
            <div>
              <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">{{ $t('org.company') }} *</label>
              <input v-model="structForm.company_name" type="text" required class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
            </div>
            <div>
              <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">{{ $t('org.industry') }}</label>
              <input v-model="structForm.industry" type="text" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
            </div>
            <div>
              <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">{{ $t('org.departments') }} *</label>
              <div v-for="(dept, i) in structForm.departments" :key="i" class="flex gap-2 mb-2">
                <input v-model="structForm.departments[i]" type="text" class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm" />
                <button v-if="structForm.departments.length > 1" @click="structForm.departments.splice(i, 1)" class="px-2 text-red-500 hover:text-red-700">&times;</button>
              </div>
              <button @click="structForm.departments.push('')" class="text-sm text-primary-600 hover:text-primary-700">+ {{ $t('onboarding.add_department') }}</button>
            </div>
            <div class="flex gap-3">
              <button @click="step = 0" class="px-4 py-2 text-gray-600">{{ $t('common.back') }}</button>
              <button @click="submitStructure" :disabled="!structForm.company_name || structForm.departments.filter(d=>d).length === 0" class="flex-1 px-6 py-3 bg-primary-600 text-white rounded-lg hover:bg-primary-700 disabled:opacity-50 font-medium">{{ $t('common.next') }}</button>
            </div>
          </div>

          <!-- Step 2: KPI Suggestions -->
          <div v-if="step === 2" class="space-y-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $t('onboarding.select_kpis') }}</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $t('onboarding.kpi_suggestion_desc') }}</p>
            <div class="space-y-2 max-h-80 overflow-y-auto">
              <label v-for="t in templates" :key="t.id" class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 dark:border-gray-700 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50">
                <input type="checkbox" v-model="selectedTemplates" :value="t.id" class="rounded text-primary-600" />
                <div>
                  <p class="text-sm font-medium text-gray-900 dark:text-white">{{ locale === 'de' ? t.name_de : t.name_en }}</p>
                  <p class="text-xs text-gray-500">{{ t.category }} · {{ t.unit }}</p>
                </div>
              </label>
            </div>
            <div class="flex gap-3">
              <button @click="step = 1" class="px-4 py-2 text-gray-600">{{ $t('common.back') }}</button>
              <button @click="submitKpis" class="flex-1 px-6 py-3 bg-primary-600 text-white rounded-lg hover:bg-primary-700 font-medium">{{ $t('common.next') }}</button>
            </div>
          </div>

          <!-- Step 3: Invite Team -->
          <div v-if="step === 3" class="space-y-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $t('onboarding.invite_team') }}</h2>
            <div v-for="(inv, i) in invitations" :key="i" class="flex flex-col sm:flex-row gap-2 p-3 bg-gray-50 dark:bg-gray-700/30 rounded-lg">
              <input v-model="inv.name" :placeholder="$t('auth.name')" class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm" />
              <input v-model="inv.email" type="email" :placeholder="$t('auth.email')" class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm" />
              <button @click="invitations.splice(i, 1)" class="px-2 text-red-500 hover:text-red-700 shrink-0">&times;</button>
            </div>
            <button @click="invitations.push({ name: '', email: '', department_id: '' })" class="text-sm text-primary-600 hover:text-primary-700">+ {{ $t('onboarding.add_member') }}</button>
            <div class="flex gap-3">
              <button @click="step = 2" class="px-4 py-2 text-gray-600">{{ $t('common.back') }}</button>
              <button @click="submitInvitations" class="flex-1 px-6 py-3 bg-primary-600 text-white rounded-lg hover:bg-primary-700 font-medium">{{ $t('common.next') }}</button>
            </div>
          </div>

          <!-- Step 4: First Data / Complete -->
          <div v-if="step === 4" class="space-y-6 text-center">
            <div class="text-6xl">🎉</div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $t('onboarding.ready') }}</h2>
            <p class="text-gray-500 dark:text-gray-400">{{ $t('onboarding.ready_desc') }}</p>
            <button @click="completeOnboarding" class="w-full px-6 py-3 bg-primary-600 text-white rounded-lg hover:bg-primary-700 font-medium">
              {{ $t('onboarding.go_to_dashboard') }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router, useForm, usePage } from '@inertiajs/vue3';

const props = defineProps({
  currentStep: { type: Number, default: 0 },
  companies: { type: Array, default: () => [] },
  templates: { type: Array, default: () => [] },
});

const page = usePage();
const locale = computed(() => page.props.locale || 'de');
const step = ref(props.currentStep);

const steps = [
  { title: 'Sprache / Language', subtitle: 'Choose your preferred language' },
  { title: 'Struktur / Structure', subtitle: 'Create your organization' },
  { title: 'KPIs', subtitle: 'Select key performance indicators' },
  { title: 'Team', subtitle: 'Invite your team members' },
  { title: 'Fertig / Ready', subtitle: 'Start using SmartKPI' },
];

const langForm = useForm({ locale: locale.value });
const structForm = ref({ company_name: '', industry: '', departments: [''] });
const selectedTemplates = ref([]);
const invitations = ref([{ name: '', email: '', department_id: '' }]);

function selectLanguage(lang) { langForm.locale = lang; }

function submitLanguage() {
  langForm.post('/onboarding/language', {
    preserveState: true,
    onSuccess: () => { step.value = 1; },
  });
}

function submitStructure() {
  router.post('/onboarding/structure', structForm.value, {
    preserveState: true,
    onSuccess: () => { step.value = 2; },
  });
}

function submitKpis() {
  const company = props.companies[0];
  const dept = company?.departments?.[0];
  router.post('/onboarding/kpis', {
    template_ids: selectedTemplates.value,
    company_id: company?.id,
    department_id: dept?.id,
  }, {
    preserveState: true,
    onSuccess: () => { step.value = 3; },
  });
}

function submitInvitations() {
  const valid = invitations.value.filter(i => i.name && i.email);
  if (valid.length === 0) {
    step.value = 4;
    return;
  }
  const dept = props.companies[0]?.departments?.[0];
  const mapped = valid.map(i => ({ ...i, department_id: i.department_id || dept?.id }));
  router.post('/onboarding/invitations', { invitations: mapped }, {
    preserveState: true,
    onSuccess: () => { step.value = 4; },
  });
}

function completeOnboarding() {
  router.post('/onboarding/complete');
}
</script>
