<template>
  <header class="fixed top-0 left-0 right-0 z-40 h-16 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 shadow-sm">
    <div class="flex items-center justify-between h-full px-4 lg:px-6">
      <!-- Left: Hamburger + Logo -->
      <div class="flex items-center gap-3">
        <button
          class="lg:hidden p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700"
          @click="$emit('toggle-sidebar')"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
        </button>
        <div class="flex items-center gap-2">
          <div class="w-8 h-8 bg-primary-600 rounded-lg flex items-center justify-center">
            <span class="text-white font-bold text-sm">SK</span>
          </div>
          <span class="hidden sm:block font-semibold text-gray-900 dark:text-white text-lg">SmartKPI</span>
        </div>
      </div>

      <!-- Center: Search (desktop) -->
      <div class="hidden md:flex flex-1 max-w-md mx-8">
        <div class="relative w-full">
          <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <input
            type="search"
            :placeholder="$t('common.search') + '...'"
            class="w-full pl-10 pr-4 py-2 bg-gray-100 dark:bg-gray-700 border-0 rounded-lg text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-primary-500"
          />
        </div>
      </div>

      <!-- Right: Actions -->
      <div class="flex items-center gap-2">
        <!-- Language Switcher -->
        <button
          class="flex items-center gap-1 px-2 py-1.5 rounded-lg text-sm font-medium hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
          :class="locale === 'de' ? 'text-primary-600 dark:text-primary-400' : 'text-gray-500 dark:text-gray-400'"
          @click="$emit('change-locale', locale === 'de' ? 'en' : 'de')"
        >
          <span class="text-base">{{ locale === 'de' ? '🇩🇪' : '🇬🇧' }}</span>
          <span class="hidden sm:inline uppercase">{{ locale }}</span>
        </button>

        <!-- Dark Mode Toggle -->
        <button
          class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700 transition-colors"
          @click="$emit('toggle-dark')"
        >
          <svg v-if="isDark" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
          </svg>
          <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
          </svg>
        </button>

        <!-- Notifications -->
        <button class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700 relative">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
          </svg>
          <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full"></span>
        </button>

        <!-- User Avatar + Dropdown -->
        <div v-if="user" class="relative ml-2" ref="dropdownRef">
          <button
            class="flex items-center gap-2 p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
            @click="showDropdown = !showDropdown"
          >
            <div class="w-8 h-8 bg-primary-100 dark:bg-primary-900 rounded-full flex items-center justify-center">
              <span class="text-primary-700 dark:text-primary-300 font-medium text-sm">
                {{ user.name?.charAt(0)?.toUpperCase() }}
              </span>
            </div>
            <span class="hidden md:block text-sm font-medium text-gray-700 dark:text-gray-300">
              {{ user.name }}
            </span>
            <svg class="w-4 h-4 text-gray-400 hidden md:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>

          <!-- Dropdown Menu -->
          <div v-if="showDropdown" class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 py-1 z-50">
            <div class="px-4 py-2 border-b border-gray-100 dark:border-gray-700">
              <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ user.name }}</p>
              <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ user.email }}</p>
            </div>
            <form method="POST" action="/logout">
              <input type="hidden" name="_token" :value="csrfToken" />
              <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                {{ $t('nav.logout') }}
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

defineProps({
  user: { type: Object, default: null },
  locale: { type: String, default: 'de' },
  isDark: { type: Boolean, default: false },
});

defineEmits(['toggle-sidebar', 'toggle-dark', 'change-locale']);

const showDropdown = ref(false);
const dropdownRef = ref(null);
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

function handleClickOutside(e) {
  if (dropdownRef.value && !dropdownRef.value.contains(e.target)) {
    showDropdown.value = false;
  }
}

onMounted(() => document.addEventListener('click', handleClickOutside));
onUnmounted(() => document.removeEventListener('click', handleClickOutside));
</script>
