<template>
  <div class="bg-gradient-to-r from-primary-600 to-primary-700 rounded-xl p-6 text-white">
    <div class="flex items-start justify-between">
      <div>
        <h2 class="text-lg font-semibold">{{ $t('dashboard.daily_focus') }}</h2>
        <p class="text-primary-100 text-sm mt-2">{{ focus?.title || $t('dashboard.no_focus') }}</p>
        <p v-if="focus?.description" class="text-primary-200 text-xs mt-1">{{ focus.description }}</p>
      </div>
      <span v-if="focus?.priority" class="px-2 py-1 bg-white/20 rounded text-xs font-medium">
        {{ focus.priority }}
      </span>
    </div>
  </div>
</template>

<script setup>
defineProps({
  focus: { type: Object, default: null },
});
</script>
