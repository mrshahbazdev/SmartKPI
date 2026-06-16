<script setup>
import AppShell from '@/Components/Layout/AppShell.vue'
import { useI18n } from 'vue-i18n'
import { useForm, router } from '@inertiajs/vue3'
import { ref } from 'vue'

const { t } = useI18n()

defineProps({ suggestions: Object, problems: Array })

const selectedProblem = ref('')
const form = useForm({ problem_id: '' })

const generate = () => {
    form.problem_id = selectedProblem.value
    form.post(route('ai.actions.suggest'), { preserveScroll: true })
}

const apply = (id) => router.post(route('ai.apply', id), {}, { preserveScroll: true })
const dismiss = (id) => router.post(route('ai.dismiss', id), {}, { preserveScroll: true })
</script>

<template>
    <AppShell :title="t('ai.predictive_actions')">
        <div class="space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">{{ t('ai.suggest_actions_for') }}</h3>
                <div class="flex flex-col sm:flex-row gap-3">
                    <select v-model="selectedProblem" class="flex-1 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-white px-3 py-2">
                        <option value="">{{ t('ai.select_problem') }}</option>
                        <option v-for="p in problems" :key="p.id" :value="p.id">
                            [{{ p.severity }}] {{ p.title }}
                        </option>
                    </select>
                    <button @click="generate" :disabled="!selectedProblem || form.processing"
                        class="px-4 py-2 bg-yellow-600 text-white rounded-lg text-sm font-medium hover:bg-yellow-700 disabled:opacity-50 transition-colors whitespace-nowrap">
                        ⚡ {{ t('ai.suggest_actions') }}
                    </button>
                </div>
            </div>

            <div class="space-y-4">
                <div v-for="item in suggestions?.data" :key="item.id"
                    class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="text-lg">⚡</span>
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium"
                            :class="item.metadata?.priority === 'high' ? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300' :
                                item.metadata?.priority === 'medium' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300' :
                                'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300'">
                            {{ item.metadata?.priority || 'medium' }}
                        </span>
                        <span v-if="item.metadata?.confidence" class="text-xs text-gray-400">
                            {{ Math.round(item.metadata.confidence * 100) }}% {{ t('ai.confidence') }}
                        </span>
                        <span v-if="item.metadata?.estimated_days" class="text-xs text-gray-400">
                            ~{{ item.metadata.estimated_days }} {{ t('ai.days') }}
                        </span>
                    </div>
                    <h3 class="font-medium text-gray-900 dark:text-white text-sm">
                        {{ $i18n.locale === 'de' ? item.title_de : item.title_en }}
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        {{ $i18n.locale === 'de' ? item.content_de : item.content_en }}
                    </p>
                    <div class="mt-3 flex gap-2">
                        <button @click="apply(item.id)" class="px-3 py-1 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 rounded-lg text-xs font-medium hover:bg-green-200 dark:hover:bg-green-900/50 transition-colors">
                            {{ t('ai.apply_action') }}
                        </button>
                        <button @click="dismiss(item.id)" class="text-xs text-gray-400 hover:text-gray-600 px-3 py-1">{{ t('ai.dismiss') }}</button>
                    </div>
                </div>

                <div v-if="!suggestions?.data?.length" class="text-center py-12 text-gray-400 dark:text-gray-500">
                    <span class="text-4xl block mb-2">⚡</span>
                    {{ t('ai.no_suggestions') }}
                </div>
            </div>
        </div>
    </AppShell>
</template>
