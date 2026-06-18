<template>
  <AppShell>
    <div class="space-y-6">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $t('webhook.title') }}</h1>

      <!-- Add webhook form -->
      <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">{{ $t('webhook.add') }}</h2>
        <form @submit.prevent="addWebhook" class="flex flex-col sm:flex-row gap-3">
          <input v-model="form.url" type="url" required :placeholder="$t('webhook.url_placeholder')" class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm" />
          <select v-model="form.event" required class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
            <option value="">{{ $t('webhook.select_event') }}</option>
            <option value="problem.created">problem.created</option>
            <option value="problem.resolved">problem.resolved</option>
            <option value="action.completed">action.completed</option>
            <option value="kpi.threshold_breach">kpi.threshold_breach</option>
            <option value="kpi.value_entered">kpi.value_entered</option>
            <option value="goal.achieved">goal.achieved</option>
          </select>
          <input v-model="form.secret" type="text" :placeholder="$t('webhook.secret')" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm sm:w-40" />
          <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-primary-600 text-white text-sm rounded-lg hover:bg-primary-700 disabled:opacity-50 shrink-0">{{ $t('common.save') }}</button>
        </form>
      </div>

      <!-- Webhooks list -->
      <div class="space-y-3">
        <div v-for="w in webhooks.data" :key="w.id"
          class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
          <div class="flex flex-col sm:flex-row sm:items-center gap-3">
            <div class="flex-1 min-w-0">
              <p class="text-sm font-mono text-gray-700 dark:text-gray-300 truncate">{{ w.url }}</p>
              <div class="flex items-center gap-2 mt-1 text-xs text-gray-500 dark:text-gray-400">
                <span class="px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700">{{ w.event }}</span>
                <span :class="w.is_active ? 'text-green-600' : 'text-red-500'">{{ w.is_active ? $t('alert.activate') : $t('alert.deactivate') }}</span>
                <span v-if="w.last_triggered_at" class="text-gray-400">{{ formatDate(w.last_triggered_at) }}</span>
                <span v-if="w.failure_count > 0" class="text-red-500">{{ w.failure_count }} {{ $t('webhook.failures') }}</span>
              </div>
            </div>
            <div class="flex items-center gap-2 shrink-0">
              <button @click="toggleWebhook(w)" class="px-3 py-1.5 text-xs rounded-lg"
                :class="w.is_active ? 'bg-red-50 text-red-600 hover:bg-red-100' : 'bg-green-50 text-green-600 hover:bg-green-100'">
                {{ w.is_active ? $t('alert.deactivate') : $t('alert.activate') }}
              </button>
              <button @click="testWebhook(w)" class="px-3 py-1.5 text-xs rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100">{{ $t('webhook.test') }}</button>
              <button @click="deleteWebhook(w)" class="px-3 py-1.5 text-xs rounded-lg bg-gray-50 text-gray-600 hover:bg-gray-100">{{ $t('common.delete') }}</button>
            </div>
          </div>
        </div>
      </div>

      <div v-if="!webhooks.data?.length" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-12 text-center">
        <p class="text-gray-500 dark:text-gray-400">{{ $t('webhook.no_webhooks') }}</p>
      </div>
    </div>
  </AppShell>
</template>

<script setup>
import { computed } from 'vue';
import { useForm, router, usePage } from '@inertiajs/vue3';
import AppShell from '@/Components/Layout/AppShell.vue';

const props = defineProps({ webhooks: { type: Object, default: () => ({ data: [] }) } });
const page = usePage();
const locale = computed(() => page.props.locale || 'de');

const form = useForm({ url: '', event: '', secret: '' });

function addWebhook() {
  form.post('/webhooks', { preserveState: true, onSuccess: () => form.reset() });
}

function toggleWebhook(w) {
  router.put(`/webhooks/${w.id}`, { is_active: !w.is_active }, { preserveState: true });
}

function testWebhook(w) {
  fetch(`/webhooks/${w.id}/test`, { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content, 'Accept': 'application/json' } })
    .then(r => r.json())
    .then(d => alert(d.success ? 'OK' : `Error: ${d.error || d.status}`));
}

function deleteWebhook(w) {
  if (confirm(locale.value === 'de' ? 'Webhook löschen?' : 'Delete webhook?')) {
    router.delete(`/webhooks/${w.id}`, { preserveState: true });
  }
}

function formatDate(d) {
  if (!d) return '';
  return new Date(d).toLocaleDateString(locale.value === 'de' ? 'de-DE' : 'en-US', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
}
</script>
