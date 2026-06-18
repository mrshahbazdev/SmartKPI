<template>
  <AppShell>
    <div class="max-w-3xl mx-auto space-y-6">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $t('gdpr.title') }}</h1>

      <!-- Consent Management -->
      <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ $t('gdpr.consent_management') }}</h2>
        <div class="space-y-4">
          <div v-for="type in consentTypes" :key="type" class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/30 rounded-lg">
            <div>
              <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $t(`gdpr.consent_${type}`) }}</p>
              <p class="text-xs text-gray-500 dark:text-gray-400">{{ $t(`gdpr.consent_${type}_desc`) }}</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
              <input type="checkbox" :checked="isConsented(type)" @change="toggleConsent(type, $event)" class="sr-only peer" :disabled="type === 'privacy_policy'">
              <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-600 peer-checked:bg-primary-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full"></div>
            </label>
          </div>
        </div>
      </div>

      <!-- Data Export (Art. 20) -->
      <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">{{ $t('gdpr.data_export') }}</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">{{ $t('gdpr.data_export_desc') }}</p>
        <button @click="requestExport" class="px-4 py-2 bg-primary-600 text-white text-sm rounded-lg hover:bg-primary-700">{{ $t('gdpr.request_export') }}</button>

        <div v-if="exports.length" class="mt-4 space-y-2">
          <div v-for="exp in exports" :key="exp.id" class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/30 rounded-lg text-sm">
            <div>
              <span class="text-gray-700 dark:text-gray-300">{{ formatDate(exp.created_at) }}</span>
              <span class="ml-2 px-2 py-0.5 rounded-full text-xs" :class="exp.status === 'completed' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'">{{ exp.status }}</span>
            </div>
            <a v-if="exp.status === 'completed'" :href="`/gdpr/export/${exp.id}/download`" class="text-primary-600 hover:text-primary-700 text-sm">{{ $t('common.export') }}</a>
          </div>
        </div>
      </div>

      <!-- Right to Deletion (Art. 17) -->
      <div class="bg-white dark:bg-gray-800 rounded-xl border border-red-200 dark:border-red-800 p-6">
        <h2 class="text-lg font-semibold text-red-600 mb-2">{{ $t('gdpr.right_to_deletion') }}</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">{{ $t('gdpr.deletion_desc') }}</p>
        <button @click="requestDeletion" class="px-4 py-2 bg-red-600 text-white text-sm rounded-lg hover:bg-red-700">{{ $t('gdpr.request_deletion') }}</button>
      </div>
    </div>
  </AppShell>
</template>

<script setup>
import { computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import AppShell from '@/Components/Layout/AppShell.vue';

const props = defineProps({
  consents: { type: Array, default: () => [] },
  exports: { type: Array, default: () => [] },
});

const page = usePage();
const locale = computed(() => page.props.locale || 'de');

const consentTypes = ['privacy_policy', 'data_processing', 'marketing', 'analytics'];

function isConsented(type) {
  return props.consents.find(c => c.consent_type === type)?.consented ?? false;
}

function toggleConsent(type, event) {
  router.post('/gdpr/consent', { consent_type: type, consented: event.target.checked }, { preserveState: true });
}

function requestExport() {
  router.post('/gdpr/export');
}

function requestDeletion() {
  if (confirm(locale.value === 'de' ? 'Sind Sie sicher? Alle Ihre Daten werden unwiderruflich gelöscht.' : 'Are you sure? All your data will be permanently deleted.')) {
    router.post('/gdpr/deletion');
  }
}

function formatDate(d) {
  if (!d) return '';
  return new Date(d).toLocaleDateString(locale.value === 'de' ? 'de-DE' : 'en-US', { year: 'numeric', month: 'short', day: 'numeric' });
}
</script>
