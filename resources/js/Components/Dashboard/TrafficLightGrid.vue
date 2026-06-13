<template>
  <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">{{ $t('dashboard.kpi_status') }}</h3>
    <div class="flex items-center gap-4">
      <div class="flex items-center gap-2">
        <span class="w-3 h-3 rounded-full bg-green-500"></span>
        <span class="text-sm text-gray-600 dark:text-gray-400">{{ counts.on_target }}</span>
      </div>
      <div class="flex items-center gap-2">
        <span class="w-3 h-3 rounded-full bg-yellow-500"></span>
        <span class="text-sm text-gray-600 dark:text-gray-400">{{ counts.warning }}</span>
      </div>
      <div class="flex items-center gap-2">
        <span class="w-3 h-3 rounded-full bg-red-500"></span>
        <span class="text-sm text-gray-600 dark:text-gray-400">{{ counts.critical }}</span>
      </div>
    </div>
    <!-- Mini bar -->
    <div class="mt-3 h-2 rounded-full bg-gray-100 dark:bg-gray-700 overflow-hidden flex">
      <div class="bg-green-500 h-full" :style="{ width: pct('on_target') }"></div>
      <div class="bg-yellow-500 h-full" :style="{ width: pct('warning') }"></div>
      <div class="bg-red-500 h-full" :style="{ width: pct('critical') }"></div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  counts: { type: Object, default: () => ({ on_target: 0, warning: 0, critical: 0 }) },
});

function pct(status) {
  const total = props.counts.on_target + props.counts.warning + props.counts.critical;
  if (total === 0) return '0%';
  return ((props.counts[status] / total) * 100) + '%';
}
</script>
