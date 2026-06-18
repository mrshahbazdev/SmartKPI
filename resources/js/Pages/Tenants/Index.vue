<template>
  <AppShell>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
            {{ $t('tenants.title') }}
          </h1>
          <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
            {{ $t('tenants.subtitle') }}
          </p>
        </div>
        <button
          @click="showCreate = true"
          class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors"
        >
          {{ $t('tenants.create') }}
        </button>
      </div>

      <!-- Tenants List -->
      <div class="space-y-4">
        <div
          v-for="tenant in tenants"
          :key="tenant.id"
          class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden"
        >
          <!-- Tenant Header -->
          <div class="p-4 flex items-center justify-between border-b border-gray-100 dark:border-gray-700">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 bg-primary-600 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
              </div>
              <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ tenant.name }}</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400">ID: {{ tenant.id }}</p>
              </div>
            </div>
            <div class="flex items-center gap-2">
              <button
                @click="editTenant = { ...tenant }"
                class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                :title="$t('common.edit')"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
              </button>
              <button
                @click="deleteTenant(tenant.id)"
                class="p-1.5 text-gray-400 hover:text-red-600"
                :title="$t('common.delete')"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
              </button>
            </div>
          </div>

          <!-- Domains -->
          <div class="p-4">
            <div class="flex items-center justify-between mb-3">
              <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $t('tenants.domains') }}</h3>
              <button
                @click="addDomainTo = tenant.id; newDomain = ''"
                class="text-xs text-primary-600 hover:text-primary-700 font-medium"
              >
                + {{ $t('tenants.add_domain') }}
              </button>
            </div>

            <!-- Add domain form -->
            <div v-if="addDomainTo === tenant.id" class="flex gap-2 mb-3">
              <input
                v-model="newDomain"
                type="text"
                :placeholder="$t('tenants.domain_placeholder')"
                class="flex-1 px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                @keyup.enter="saveDomain(tenant.id)"
              />
              <button
                @click="saveDomain(tenant.id)"
                class="px-3 py-2 bg-primary-600 text-white text-sm rounded-lg hover:bg-primary-700"
              >
                {{ $t('common.save') }}
              </button>
              <button
                @click="addDomainTo = null"
                class="px-3 py-2 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm rounded-lg hover:bg-gray-300 dark:hover:bg-gray-500"
              >
                {{ $t('common.cancel') }}
              </button>
            </div>

            <!-- Domain list -->
            <div v-if="tenant.domains?.length" class="space-y-2">
              <div
                v-for="domain in tenant.domains"
                :key="domain.id"
                class="flex items-center justify-between px-3 py-2 bg-gray-50 dark:bg-gray-700/50 rounded-lg"
              >
                <div class="flex items-center gap-2">
                  <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                  </svg>
                  <span class="text-sm font-medium text-gray-900 dark:text-white">{{ domain.domain }}</span>
                </div>
                <button
                  @click="removeDomain(tenant.id, domain.id)"
                  class="p-1 text-gray-400 hover:text-red-500"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>
            </div>
            <p v-else class="text-sm text-gray-400 dark:text-gray-500 italic">
              {{ $t('tenants.no_domains') }}
            </p>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="!tenants?.length" class="text-center py-16">
        <svg class="mx-auto w-12 h-12 text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
        </svg>
        <p class="text-gray-500 dark:text-gray-400">{{ $t('tenants.no_tenants') }}</p>
      </div>

      <!-- Create Tenant Modal -->
      <Teleport to="body">
        <div v-if="showCreate" class="fixed inset-0 z-50 flex items-center justify-center p-4">
          <div class="absolute inset-0 bg-black/50" @click="showCreate = false"></div>
          <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-md p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ $t('tenants.create') }}</h3>
            <form @submit.prevent="createTenant">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('tenants.name') }}</label>
              <input
                v-model="createForm.name"
                type="text"
                required
                class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500"
              />
              <div class="flex justify-end gap-2 mt-4">
                <button type="button" @click="showCreate = false" class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
                  {{ $t('common.cancel') }}
                </button>
                <button type="submit" class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700">
                  {{ $t('common.save') }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </Teleport>

      <!-- Edit Tenant Modal -->
      <Teleport to="body">
        <div v-if="editTenant" class="fixed inset-0 z-50 flex items-center justify-center p-4">
          <div class="absolute inset-0 bg-black/50" @click="editTenant = null"></div>
          <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-md p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ $t('tenants.edit') }}</h3>
            <form @submit.prevent="updateTenant">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('tenants.name') }}</label>
              <input
                v-model="editTenant.name"
                type="text"
                required
                class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500"
              />
              <div class="flex justify-end gap-2 mt-4">
                <button type="button" @click="editTenant = null" class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
                  {{ $t('common.cancel') }}
                </button>
                <button type="submit" class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700">
                  {{ $t('common.save') }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </Teleport>
    </div>
  </AppShell>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppShell from '@/Components/Layout/AppShell.vue';

const props = defineProps({
  tenants: { type: Array, default: () => [] },
});

const showCreate = ref(false);
const editTenant = ref(null);
const addDomainTo = ref(null);
const newDomain = ref('');
const createForm = ref({ name: '' });

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

function createTenant() {
  router.post('/tenants', createForm.value, {
    preserveScroll: true,
    onSuccess: () => {
      showCreate.value = false;
      createForm.value = { name: '' };
    },
  });
}

function updateTenant() {
  router.put(`/tenants/${editTenant.value.id}`, { name: editTenant.value.name }, {
    preserveScroll: true,
    onSuccess: () => { editTenant.value = null; },
  });
}

function deleteTenant(id) {
  if (confirm('Are you sure?')) {
    router.delete(`/tenants/${id}`, { preserveScroll: true });
  }
}

function saveDomain(tenantId) {
  if (!newDomain.value.trim()) return;
  router.post(`/tenants/${tenantId}/domains`, { domain: newDomain.value.trim() }, {
    preserveScroll: true,
    onSuccess: () => {
      addDomainTo.value = null;
      newDomain.value = '';
    },
  });
}

function removeDomain(tenantId, domainId) {
  if (confirm('Remove this domain?')) {
    router.delete(`/tenants/${tenantId}/domains/${domainId}`, { preserveScroll: true });
  }
}
</script>
