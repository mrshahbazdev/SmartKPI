<template>
  <div :class="{ dark: isDark }" class="min-h-screen bg-gray-50 dark:bg-gray-900 transition-colors">
    <!-- Topbar -->
    <Topbar
      :user="user"
      :locale="locale"
      :is-dark="isDark"
      @toggle-sidebar="sidebarOpen = !sidebarOpen"
      @toggle-dark="toggleDark"
      @change-locale="changeLocale"
    />

    <div class="flex pt-16">
      <!-- Desktop Sidebar -->
      <Sidebar
        :collapsed="sidebarCollapsed"
        :current-route="currentRoute"
        class="hidden lg:block"
        @toggle="sidebarCollapsed = !sidebarCollapsed"
      />

      <!-- Mobile Drawer -->
      <MobileDrawer
        :open="sidebarOpen"
        :current-route="currentRoute"
        @close="sidebarOpen = false"
      />

      <!-- Main Content -->
      <main
        class="flex-1 min-h-[calc(100vh-4rem)] transition-all duration-300"
        :class="[sidebarCollapsed ? 'lg:ml-16' : 'lg:ml-64']"
      >
        <div class="p-4 md:p-6 lg:p-8">
          <slot />
        </div>
      </main>
    </div>

    <!-- Mobile Bottom Nav -->
    <BottomNav
      :current-route="currentRoute"
      class="lg:hidden"
    />
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import Topbar from './Topbar.vue';
import Sidebar from './Sidebar.vue';
import BottomNav from './BottomNav.vue';
import MobileDrawer from './MobileDrawer.vue';

const page = usePage();

const user = computed(() => page.props.auth?.user);
const locale = computed(() => page.props.locale || 'de');
const currentRoute = computed(() => page.props.currentRoute || '');

const sidebarOpen = ref(false);
const sidebarCollapsed = ref(false);

const isDark = ref(localStorage.getItem('theme') === 'dark');

function toggleDark() {
  isDark.value = !isDark.value;
  localStorage.setItem('theme', isDark.value ? 'dark' : 'light');
}

function changeLocale(newLocale) {
  window.location.href = `/locale/${newLocale}`;
}
</script>
