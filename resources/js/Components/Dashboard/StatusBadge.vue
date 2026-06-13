<template>
  <span
    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium whitespace-nowrap"
    :class="classes"
  >
    <span class="w-1.5 h-1.5 rounded-full mr-1.5" :class="dotClass"></span>
    {{ label }}
  </span>
</template>

<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
  status: { type: String, default: 'on_target' },
});

const page = usePage();
const locale = computed(() => page.props.locale || 'de');

const classes = computed(() => ({
  on_target: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
  warning: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
  critical: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
}[props.status] || 'bg-gray-100 text-gray-600'));

const dotClass = computed(() => ({
  on_target: 'bg-green-500',
  warning: 'bg-yellow-500',
  critical: 'bg-red-500',
}[props.status] || 'bg-gray-400'));

const labels = {
  on_target: { de: 'Im Ziel', en: 'On Target' },
  warning: { de: 'Warnung', en: 'Warning' },
  critical: { de: 'Kritisch', en: 'Critical' },
};

const label = computed(() => labels[props.status]?.[locale.value] || props.status);
</script>
