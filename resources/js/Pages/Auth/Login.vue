<template>
  <GuestLayout>
    <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-6">
      {{ $t('auth.login') }}
    </h2>

    <form @submit.prevent="submit" class="space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
          {{ $t('auth.email') }}
        </label>
        <input
          v-model="form.email"
          type="email"
          required
          autofocus
          class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:ring-primary-500 focus:border-primary-500"
        />
        <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
          {{ $t('auth.password') }}
        </label>
        <input
          v-model="form.password"
          type="password"
          required
          class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:ring-primary-500 focus:border-primary-500"
        />
      </div>

      <div class="flex items-center justify-between">
        <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
          <input
            v-model="form.remember"
            type="checkbox"
            class="rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500"
          />
          {{ $t('auth.remember_me') }}
        </label>
      </div>

      <button
        type="submit"
        :disabled="form.processing"
        class="w-full py-2.5 px-4 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg shadow-sm transition-colors disabled:opacity-50"
      >
        {{ $t('auth.login') }}
      </button>

      <p class="text-center text-sm text-gray-500 dark:text-gray-400">
        <a href="/register" class="text-primary-600 hover:text-primary-500">
          {{ $t('auth.register') }}
        </a>
      </p>
    </form>
  </GuestLayout>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import GuestLayout from '@/Components/Layout/GuestLayout.vue';

const form = useForm({
  email: '',
  password: '',
  remember: false,
});

function submit() {
  form.post('/login', {
    onFinish: () => form.reset('password'),
  });
}
</script>
