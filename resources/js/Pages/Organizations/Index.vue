<template>
  <AppShell>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
            {{ $t('org.org_tree') }}
          </h1>
        </div>
        <div class="flex gap-2">
          <button
            @click="showCreateCompany = true"
            class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors"
          >
            {{ $t('org.create_company') }}
          </button>
        </div>
      </div>

      <!-- Organization Tree (Desktop: interactive tree / Mobile: accordion) -->
      <div class="space-y-4">
        <div
          v-for="tenant in tenants"
          :key="tenant.id"
          class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden"
        >
          <!-- Holding Header -->
          <div class="p-4 bg-primary-50 dark:bg-primary-900/20 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 bg-primary-600 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
              </div>
              <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ tenant.name }}</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $t('org.holding') }}</p>
              </div>
            </div>
          </div>

          <!-- Companies -->
          <div class="divide-y divide-gray-100 dark:divide-gray-700">
            <div
              v-for="company in tenant.companies"
              :key="company.id"
              class="p-4"
            >
              <div class="flex items-center justify-between">
                <button
                  @click="toggleCompany(company.id)"
                  class="flex items-center gap-3 text-left"
                >
                  <svg
                    class="w-4 h-4 text-gray-400 transition-transform"
                    :class="{ 'rotate-90': expandedCompanies.includes(company.id) }"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24"
                  >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                  </svg>
                  <div>
                    <h3 class="font-medium text-gray-900 dark:text-white">{{ company.name }}</h3>
                    <p v-if="company.industry" class="text-xs text-gray-500">{{ company.industry }}</p>
                  </div>
                </button>
                <div class="flex items-center gap-2">
                  <span
                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                    :class="company.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'"
                  >
                    {{ company.is_active ? $t('common.active') : $t('common.inactive') }}
                  </span>
                  <button
                    @click="editingCompany = company"
                    class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                  </button>
                  <button
                    @click="addDepartmentTo = company.id"
                    class="p-1.5 text-gray-400 hover:text-primary-600"
                    :title="$t('org.create_department')"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                  </button>
                </div>
              </div>

              <!-- Departments (expanded) -->
              <div
                v-if="expandedCompanies.includes(company.id) && company.departments?.length"
                class="mt-3 ml-7 space-y-2"
              >
                <div
                  v-for="dept in company.departments"
                  :key="dept.id"
                  class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-gray-700/50"
                >
                  <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-gray-200 dark:bg-gray-600 rounded-lg flex items-center justify-center">
                      <span class="text-xs font-medium text-gray-600 dark:text-gray-300">
                        {{ dept.name.charAt(0) }}
                      </span>
                    </div>
                    <div>
                      <p class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ dept.name }}</p>
                      <p class="text-xs text-gray-500">
                        {{ dept.users_count }} {{ $t('nav.users') }}
                      </p>
                    </div>
                  </div>
                  <div class="flex items-center gap-2">
                    <span
                      class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                      :class="dept.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'"
                    >
                      {{ dept.is_active ? $t('common.active') : $t('common.inactive') }}
                    </span>
                    <button
                      @click="editingDepartment = dept"
                      class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                    >
                      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                      </svg>
                    </button>
                  </div>
                </div>
              </div>

              <!-- No departments message -->
              <div
                v-if="expandedCompanies.includes(company.id) && !company.departments?.length"
                class="mt-3 ml-7 text-sm text-gray-400 italic"
              >
                {{ $t('common.no_results') }}
              </div>
            </div>
          </div>
        </div>

        <!-- Empty state -->
        <div
          v-if="!tenants?.length"
          class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-12 text-center"
        >
          <p class="text-gray-500 dark:text-gray-400">{{ $t('common.no_results') }}</p>
        </div>
      </div>

      <!-- Create Company Modal -->
      <Teleport to="body">
        <div v-if="showCreateCompany" class="fixed inset-0 z-50 flex items-center justify-center p-4">
          <div class="absolute inset-0 bg-black/50" @click="showCreateCompany = false"></div>
          <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-md p-6 space-y-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $t('org.create_company') }}</h3>
            <form @submit.prevent="createCompany" class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('org.name') }}</label>
                <input v-model="companyForm.name" type="text" required class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('org.description') }}</label>
                <textarea v-model="companyForm.description" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" rows="2"></textarea>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('org.industry') }}</label>
                <input v-model="companyForm.industry" type="text" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
              </div>
              <div class="flex justify-end gap-3">
                <button type="button" @click="showCreateCompany = false" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">{{ $t('common.cancel') }}</button>
                <button type="submit" :disabled="companyForm.processing" class="px-4 py-2 bg-primary-600 text-white text-sm rounded-lg hover:bg-primary-700 disabled:opacity-50">{{ $t('common.save') }}</button>
              </div>
            </form>
          </div>
        </div>
      </Teleport>

      <!-- Create Department Modal -->
      <Teleport to="body">
        <div v-if="addDepartmentTo" class="fixed inset-0 z-50 flex items-center justify-center p-4">
          <div class="absolute inset-0 bg-black/50" @click="addDepartmentTo = null"></div>
          <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-md p-6 space-y-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $t('org.create_department') }}</h3>
            <form @submit.prevent="createDepartment" class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('org.name') }}</label>
                <input v-model="deptForm.name" type="text" required class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('org.description') }}</label>
                <textarea v-model="deptForm.description" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" rows="2"></textarea>
              </div>
              <div class="flex justify-end gap-3">
                <button type="button" @click="addDepartmentTo = null" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">{{ $t('common.cancel') }}</button>
                <button type="submit" :disabled="deptForm.processing" class="px-4 py-2 bg-primary-600 text-white text-sm rounded-lg hover:bg-primary-700 disabled:opacity-50">{{ $t('common.save') }}</button>
              </div>
            </form>
          </div>
        </div>
      </Teleport>
      <!-- Edit Company Modal -->
      <Teleport to="body">
        <div v-if="editingCompany" class="fixed inset-0 z-50 flex items-center justify-center p-4">
          <div class="absolute inset-0 bg-black/50" @click="editingCompany = null"></div>
          <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-md p-6 space-y-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $t('org.edit_company') }}</h3>
            <form @submit.prevent="updateCompany" class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('org.name') }}</label>
                <input v-model="editCompanyForm.name" type="text" required class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('org.description') }}</label>
                <textarea v-model="editCompanyForm.description" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" rows="2"></textarea>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('org.industry') }}</label>
                <input v-model="editCompanyForm.industry" type="text" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
              </div>
              <div class="flex justify-between">
                <button type="button" @click="deleteCompany" class="px-4 py-2 text-sm text-red-600 hover:text-red-800 hover:bg-red-50 rounded-lg">{{ $t('common.delete') }}</button>
                <div class="flex gap-3">
                  <button type="button" @click="editingCompany = null" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">{{ $t('common.cancel') }}</button>
                  <button type="submit" :disabled="editCompanyForm.processing" class="px-4 py-2 bg-primary-600 text-white text-sm rounded-lg hover:bg-primary-700 disabled:opacity-50">{{ $t('common.save') }}</button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </Teleport>

      <!-- Edit Department Modal -->
      <Teleport to="body">
        <div v-if="editingDepartment" class="fixed inset-0 z-50 flex items-center justify-center p-4">
          <div class="absolute inset-0 bg-black/50" @click="editingDepartment = null"></div>
          <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-md p-6 space-y-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $t('org.edit_department') }}</h3>
            <form @submit.prevent="updateDepartment" class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('org.name') }}</label>
                <input v-model="editDeptForm.name" type="text" required class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('org.description') }}</label>
                <textarea v-model="editDeptForm.description" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" rows="2"></textarea>
              </div>
              <div class="flex justify-between">
                <button type="button" @click="deleteDepartment" class="px-4 py-2 text-sm text-red-600 hover:text-red-800 hover:bg-red-50 rounded-lg">{{ $t('common.delete') }}</button>
                <div class="flex gap-3">
                  <button type="button" @click="editingDepartment = null" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">{{ $t('common.cancel') }}</button>
                  <button type="submit" :disabled="editDeptForm.processing" class="px-4 py-2 bg-primary-600 text-white text-sm rounded-lg hover:bg-primary-700 disabled:opacity-50">{{ $t('common.save') }}</button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </Teleport>
    </div>
  </AppShell>
</template>

<script setup>
import { ref, watch } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AppShell from '@/Components/Layout/AppShell.vue';

const props = defineProps({
  tenants: { type: Array, default: () => [] },
});

const expandedCompanies = ref([]);
const showCreateCompany = ref(false);
const addDepartmentTo = ref(null);
const editingCompany = ref(null);
const editingDepartment = ref(null);

const editCompanyForm = useForm({
  name: '',
  description: '',
  industry: '',
});

const editDeptForm = useForm({
  name: '',
  description: '',
});

watch(editingCompany, (val) => {
  if (val) {
    editCompanyForm.name = val.name || '';
    editCompanyForm.description = val.description || '';
    editCompanyForm.industry = val.industry || '';
  }
});

watch(editingDepartment, (val) => {
  if (val) {
    editDeptForm.name = val.name || '';
    editDeptForm.description = val.description || '';
  }
});

function toggleCompany(id) {
  const idx = expandedCompanies.value.indexOf(id);
  if (idx === -1) {
    expandedCompanies.value.push(id);
  } else {
    expandedCompanies.value.splice(idx, 1);
  }
}

const companyForm = useForm({
  tenant_id: '',
  name: '',
  description: '',
  industry: '',
  size: '',
  timezone: 'Europe/Berlin',
});

function createCompany() {
  if (props.tenants.length > 0) {
    companyForm.tenant_id = props.tenants[0].id;
  }
  companyForm.post('/organizations/companies', {
    onSuccess: () => {
      showCreateCompany.value = false;
      companyForm.reset();
    },
  });
}

const deptForm = useForm({
  company_id: '',
  name: '',
  description: '',
  industry_type: '',
  size: '',
});

function createDepartment() {
  deptForm.company_id = addDepartmentTo.value;
  deptForm.post('/organizations/departments', {
    onSuccess: () => {
      addDepartmentTo.value = null;
      deptForm.reset();
    },
  });
}

function updateCompany() {
  editCompanyForm.put(`/organizations/companies/${editingCompany.value.id}`, {
    preserveScroll: true,
    onSuccess: () => { editingCompany.value = null; },
  });
}

function deleteCompany() {
  if (confirm('Are you sure?')) {
    router.delete(`/organizations/companies/${editingCompany.value.id}`, {
      preserveScroll: true,
      onSuccess: () => { editingCompany.value = null; },
    });
  }
}

function updateDepartment() {
  editDeptForm.put(`/organizations/departments/${editingDepartment.value.id}`, {
    preserveScroll: true,
    onSuccess: () => { editingDepartment.value = null; },
  });
}

function deleteDepartment() {
  if (confirm('Are you sure?')) {
    router.delete(`/organizations/departments/${editingDepartment.value.id}`, {
      preserveScroll: true,
      onSuccess: () => { editingDepartment.value = null; },
    });
  }
}
</script>
