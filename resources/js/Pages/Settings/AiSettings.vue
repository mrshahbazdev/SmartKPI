<script setup>
import AppShell from '@/Components/Layout/AppShell.vue'
import { useI18n } from 'vue-i18n'
import { useForm, router } from '@inertiajs/vue3'
import { ref } from 'vue'

const { t } = useI18n()

const props = defineProps({
    hasApiKey: Boolean,
    maskedKey: String,
})

const showInput = ref(!props.hasApiKey)
const form = useForm({
    openai_api_key: '',
})

const save = () => {
    form.put('/settings/ai', {
        preserveScroll: true,
        onSuccess: () => {
            form.openai_api_key = ''
            showInput.value = false
        },
    })
}

const remove = () => {
    if (confirm(t('ai_settings.confirm_remove'))) {
        router.delete('/settings/ai', { preserveScroll: true })
        showInput.value = true
    }
}
</script>

<template>
    <AppShell :title="t('ai_settings.title')">
        <div class="max-w-2xl space-y-6">
            <!-- Info Card -->
            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-4 border border-blue-200 dark:border-blue-800">
                <div class="flex items-start gap-3">
                    <span class="text-2xl">🤖</span>
                    <div>
                        <h3 class="text-sm font-semibold text-blue-900 dark:text-blue-200">{{ t('ai_settings.info_title') }}</h3>
                        <p class="text-sm text-blue-700 dark:text-blue-300 mt-1">{{ t('ai_settings.info_description') }}</p>
                        <a href="https://platform.openai.com/api-keys" target="_blank" rel="noopener"
                            class="inline-flex items-center gap-1 text-sm text-blue-600 dark:text-blue-400 hover:underline mt-2">
                            {{ t('ai_settings.get_key_link') }}
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Current Status -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ t('ai_settings.api_key') }}</h2>

                <!-- Has key -->
                <div v-if="hasApiKey && !showInput" class="space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                        <span class="text-sm font-medium text-green-700 dark:text-green-400">{{ t('ai_settings.key_active') }}</span>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg px-4 py-3 font-mono text-sm text-gray-600 dark:text-gray-400">
                        {{ maskedKey }}
                    </div>
                    <div class="flex gap-3">
                        <button @click="showInput = true"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors">
                            {{ t('ai_settings.change_key') }}
                        </button>
                        <button @click="remove"
                            class="px-4 py-2 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 rounded-lg text-sm font-medium hover:bg-red-200 dark:hover:bg-red-900/50 transition-colors">
                            {{ t('ai_settings.remove_key') }}
                        </button>
                    </div>
                </div>

                <!-- No key or changing -->
                <div v-else class="space-y-4">
                    <div v-if="!hasApiKey" class="flex items-center gap-3">
                        <div class="w-3 h-3 bg-gray-400 rounded-full"></div>
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ t('ai_settings.no_key') }}</span>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            {{ t('ai_settings.enter_key') }}
                        </label>
                        <input v-model="form.openai_api_key" type="password"
                            placeholder="sk-..."
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-4 py-2.5 text-sm font-mono focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                        <p v-if="form.errors.openai_api_key" class="mt-1 text-sm text-red-600">{{ form.errors.openai_api_key }}</p>
                    </div>

                    <div class="flex gap-3">
                        <button @click="save" :disabled="form.processing || !form.openai_api_key.trim()"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                            {{ t('ai_settings.save_key') }}
                        </button>
                        <button v-if="hasApiKey" @click="showInput = false"
                            class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                            {{ t('common.cancel') }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Security Notice -->
            <div class="bg-gray-50 dark:bg-gray-800/50 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
                <div class="flex items-start gap-3">
                    <span class="text-lg">🔒</span>
                    <div>
                        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ t('ai_settings.security_title') }}</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ t('ai_settings.security_description') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </AppShell>
</template>
