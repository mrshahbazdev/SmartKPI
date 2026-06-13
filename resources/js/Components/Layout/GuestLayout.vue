<template>
  <div class="min-h-screen flex flex-col items-center justify-center bg-gray-50 dark:bg-gray-900 px-4 py-12">
    <div class="w-full max-w-md">
      <!-- Logo -->
      <div class="flex flex-col items-center mb-8">
        <div class="w-12 h-12 bg-primary-600 rounded-xl flex items-center justify-center mb-3">
          <span class="text-white font-bold text-xl">SK</span>
        </div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">SmartKPI</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $t('app.tagline') }}</p>
      </div>

      <!-- Language Toggle -->
      <div class="flex justify-center mb-6">
        <div class="inline-flex rounded-lg bg-gray-100 dark:bg-gray-800 p-1">
          <button
            class="px-3 py-1.5 text-sm rounded-md transition-colors"
            :class="locale === 'de' ? 'bg-white dark:bg-gray-700 text-primary-600 shadow-sm' : 'text-gray-500'"
            @click="changeLocale('de')"
          >
            DE
          </button>
          <button
            class="px-3 py-1.5 text-sm rounded-md transition-colors"
            :class="locale === 'en' ? 'bg-white dark:bg-gray-700 text-primary-600 shadow-sm' : 'text-gray-500'"
            @click="changeLocale('en')"
          >
            EN
          </button>
        </div>
      </div>

      <!-- Card -->
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 sm:p-8">
        <slot />
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const locale = computed(() => page.props.locale || 'de');

function changeLocale(newLocale) {
  window.location.href = `/locale/${newLocale}`;
}
</script>
