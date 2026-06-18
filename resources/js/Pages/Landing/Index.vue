<template>
  <div class="min-h-screen bg-white dark:bg-gray-900">
    <!-- Navbar -->
    <nav class="fixed top-0 inset-x-0 z-50 bg-white/80 dark:bg-gray-900/80 backdrop-blur-lg border-b border-gray-100 dark:border-gray-800">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between h-16">
        <span class="text-xl font-bold text-primary-600">SmartKPI</span>
        <div class="hidden sm:flex items-center gap-6 text-sm text-gray-600 dark:text-gray-400">
          <a href="#features">{{ t('landing.features') }}</a>
          <a href="#pricing">{{ t('landing.pricing') }}</a>
          <a href="/login" class="text-primary-600 font-medium">{{ t('auth.login') }}</a>
          <a href="/register" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">{{ t('landing.get_started') }}</a>
        </div>
        <div class="flex sm:hidden items-center gap-3">
          <a href="/login" class="text-sm text-primary-600">{{ t('auth.login') }}</a>
          <a href="/register" class="px-3 py-1.5 bg-primary-600 text-white text-sm rounded-lg">{{ t('landing.get_started') }}</a>
        </div>
      </div>
    </nav>

    <!-- Hero -->
    <section class="pt-32 pb-20 px-4">
      <div class="max-w-4xl mx-auto text-center">
        <h1 class="text-4xl sm:text-6xl font-bold text-gray-900 dark:text-white leading-tight">
          {{ t('landing.hero_title') }}
        </h1>
        <p class="mt-6 text-lg sm:text-xl text-gray-500 dark:text-gray-400 max-w-2xl mx-auto">
          {{ t('landing.hero_subtitle') }}
        </p>
        <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4">
          <a href="/register" class="w-full sm:w-auto px-8 py-4 bg-primary-600 text-white font-medium rounded-xl hover:bg-primary-700 text-center">
            {{ t('landing.start_free_trial') }}
          </a>
          <a href="#features" class="w-full sm:w-auto px-8 py-4 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-medium rounded-xl hover:bg-gray-200 text-center">
            {{ t('landing.learn_more') }}
          </a>
        </div>
      </div>
    </section>

    <!-- Features -->
    <section id="features" class="py-20 bg-gray-50 dark:bg-gray-800/30 px-4">
      <div class="max-w-6xl mx-auto">
        <h2 class="text-3xl font-bold text-gray-900 dark:text-white text-center mb-12">{{ t('landing.features') }}</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <div v-for="(f, i) in features" :key="i" class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700">
            <div class="w-12 h-12 bg-primary-100 dark:bg-primary-900/30 rounded-xl flex items-center justify-center text-2xl mb-4">{{ f.icon }}</div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">{{ f.title }}</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ f.desc }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Stats -->
    <section class="py-16 px-4">
      <div class="max-w-4xl mx-auto grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
        <div v-for="(s, i) in stats" :key="i">
          <p class="text-3xl font-bold text-primary-600">{{ s.value }}</p>
          <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ s.label }}</p>
        </div>
      </div>
    </section>

    <!-- CTA -->
    <section class="py-20 px-4">
      <div class="max-w-3xl mx-auto text-center bg-primary-600 rounded-3xl p-12">
        <h2 class="text-3xl font-bold text-white mb-4">{{ t('landing.cta_title') }}</h2>
        <p class="text-primary-100 mb-8">{{ t('landing.cta_subtitle') }}</p>
        <a href="/register" class="inline-block px-8 py-4 bg-white text-primary-600 font-medium rounded-xl hover:bg-gray-50">
          {{ t('landing.start_free_trial') }}
        </a>
      </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-50 dark:bg-gray-800/50 border-t border-gray-200 dark:border-gray-700 py-12 px-4">
      <div class="max-w-6xl mx-auto flex flex-col md:flex-row items-center justify-between gap-4 text-sm text-gray-500">
        <span>&copy; {{ new Date().getFullYear() }} SmartKPI. {{ t('landing.all_rights') }}</span>
        <div class="flex items-center gap-6">
          <a href="/gdpr" class="hover:text-gray-700">{{ t('gdpr.title') }}</a>
          <a href="/pricing" class="hover:text-gray-700">{{ t('landing.pricing') }}</a>
          <button @click="toggleLocale" class="hover:text-gray-700">{{ locale === 'de' ? 'EN' : 'DE' }}</button>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const page = usePage();
const locale = computed(() => page.props.locale || 'de');
const { t } = useI18n();

const features = computed(() => [
  { icon: '📊', title: t('landing.feat_kpi'), desc: t('landing.feat_kpi_desc') },
  { icon: '🔍', title: t('landing.feat_detection'), desc: t('landing.feat_detection_desc') },
  { icon: '🎯', title: t('landing.feat_actions'), desc: t('landing.feat_actions_desc') },
  { icon: '🌐', title: t('landing.feat_cross'), desc: t('landing.feat_cross_desc') },
  { icon: '📈', title: t('landing.feat_forecast'), desc: t('landing.feat_forecast_desc') },
  { icon: '🔒', title: t('landing.feat_security'), desc: t('landing.feat_security_desc') },
]);

const stats = computed(() => [
  { value: '50+', label: t('landing.stat_kpis') },
  { value: '5', label: t('landing.stat_levels') },
  { value: 'DE/EN', label: t('landing.stat_bilingual') },
  { value: '100%', label: t('landing.stat_gdpr') },
]);

function toggleLocale() {
  router.get(`/locale/${locale.value === 'de' ? 'en' : 'de'}`);
}
</script>
