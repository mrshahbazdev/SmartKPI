<template>
  <AppShell>
    <div class="space-y-6">
      <!-- Breadcrumb + Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-1">
            <a href="/dashboard" class="hover:text-primary-600">{{ $t('nav.dashboard') }}</a>
            <span>/</span>
            <a v-if="department.company" :href="`/dashboard/company/${department.company.id}`" class="hover:text-primary-600">{{ department.company.name }}</a>
            <span v-if="department.company">/</span>
            <span>{{ department.name }}</span>
          </div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ department.name }}</h1>
        </div>
        <!-- Date range selector -->
        <select v-model="selectedDays" @change="changeRange" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
          <option :value="7">7 {{ $t('common.days') }}</option>
          <option :value="30">30 {{ $t('common.days') }}</option>
          <option :value="90">90 {{ $t('common.days') }}</option>
          <option :value="365">{{ $t('common.ytd') }}</option>
        </select>
      </div>

      <!-- Traffic Light Status -->
      <TrafficLightGrid :counts="statusCounts" />

      <!-- KPI Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        <KpiCard v-for="kpi in kpis" :key="kpi.id" :kpi="kpi" />
      </div>

      <!-- No KPIs -->
      <div v-if="!kpis.length" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-12 text-center">
        <p class="text-gray-500 dark:text-gray-400">{{ $t('kpi.no_kpis') }}</p>
      </div>
    </div>
  </AppShell>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppShell from '@/Components/Layout/AppShell.vue';
import KpiCard from '@/Components/Dashboard/KpiCard.vue';
import TrafficLightGrid from '@/Components/Dashboard/TrafficLightGrid.vue';

const props = defineProps({
  department: { type: Object, required: true },
  kpis: { type: Array, default: () => [] },
  statusCounts: { type: Object, default: () => ({}) },
  days: { type: Number, default: 30 },
});

const selectedDays = ref(props.days);

function changeRange() {
  router.get(`/dashboard/department/${props.department.id}`, { days: selectedDays.value }, { preserveState: true });
}
</script>
