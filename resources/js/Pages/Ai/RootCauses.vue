<script setup>
import AppShell from '@/Components/Layout/AppShell.vue'
import { useI18n } from 'vue-i18n'
import { useForm, router } from '@inertiajs/vue3'
import { ref } from 'vue'

const { t } = useI18n()

defineProps({ analyses: Object, problems: Array })

const selectedProblem = ref('')
const form = useForm({ problem_id: '' })

const analyze = () => {
    form.problem_id = selectedProblem.value
    form.post('/ai/root-causes/analyze', { preserveScroll: true })
}

const dismiss = (id) => router.post(`/ai/insights/${id}/dismiss`, {}, { preserveScroll: true })
</script>

<template>
    <AppShell :title="t('ai.root_cause_analysis')">
        <div class="space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">{{ t('ai.analyze_root_cause') }}</h3>
                <div class="flex flex-col sm:flex-row gap-3">
                    <select v-model="selectedProblem" class="flex-1 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-white px-3 py-2">
                        <option value="">{{ t('ai.select_problem') }}</option>
                        <option v-for="p in problems" :key="p.id" :value="p.id">
                            [{{ p.severity }}] {{ p.title }}
                        </option>
                    </select>
                    <button @click="analyze" :disabled="!selectedProblem || form.processing"
                        class="px-4 py-2 bg-purple-600 text-white rounded-lg text-sm font-medium hover:bg-purple-700 disabled:opacity-50 transition-colors whitespace-nowrap">
                        🔬 {{ t('ai.analyze') }}
                    </button>
                </div>
            </div>

            <div class="space-y-4">
                <div v-for="item in analyses?.data" :key="item.id"
                    class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <span class="text-lg">🔬</span>
                            <h3 class="font-semibold text-gray-900 dark:text-white">
                                {{ $i18n.locale === 'de' ? item.title_de : item.title_en }}
                            </h3>
                        </div>
                        <span v-if="item.metadata?.confidence" class="text-xs text-gray-400 bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">
                            {{ Math.round(item.metadata.confidence * 100) }}% {{ t('ai.confidence') }}
                        </span>
                    </div>

                    <div class="prose prose-sm dark:prose-invert max-w-none text-gray-700 dark:text-gray-300 whitespace-pre-wrap mb-4">
                        {{ $i18n.locale === 'de' ? item.content_de : item.content_en }}
                    </div>

                    <!-- Probable Causes -->
                    <div v-if="item.metadata?.probable_causes?.length" class="border-t border-gray-100 dark:border-gray-700 pt-3 mt-3">
                        <h4 class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-2 uppercase">{{ t('ai.probable_causes') }}</h4>
                        <div class="space-y-2">
                            <div v-for="cause in item.metadata.probable_causes" :key="cause.cause_en" class="flex items-center gap-3">
                                <div class="w-16 bg-gray-100 dark:bg-gray-700 rounded-full h-2">
                                    <div class="bg-purple-600 h-2 rounded-full" :style="{ width: (cause.likelihood * 100) + '%' }"></div>
                                </div>
                                <span class="text-xs text-gray-500 w-10">{{ Math.round(cause.likelihood * 100) }}%</span>
                                <span class="text-sm text-gray-700 dark:text-gray-300">
                                    {{ $i18n.locale === 'de' ? cause.cause_de : cause.cause_en }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Investigation Steps -->
                    <div v-if="item.metadata?.recommended_investigation?.length" class="border-t border-gray-100 dark:border-gray-700 pt-3 mt-3">
                        <h4 class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-2 uppercase">{{ t('ai.investigation_steps') }}</h4>
                        <ol class="space-y-1 list-decimal list-inside">
                            <li v-for="(step, i) in item.metadata.recommended_investigation" :key="i" class="text-sm text-gray-600 dark:text-gray-400">
                                {{ step }}
                            </li>
                        </ol>
                    </div>

                    <div class="mt-3 flex items-center justify-between text-xs text-gray-400">
                        <span>{{ new Date(item.created_at).toLocaleDateString() }}</span>
                        <button @click="dismiss(item.id)" class="hover:text-gray-600 dark:hover:text-gray-300">{{ t('ai.dismiss') }}</button>
                    </div>
                </div>

                <div v-if="!analyses?.data?.length" class="text-center py-12 text-gray-400 dark:text-gray-500">
                    <span class="text-4xl block mb-2">🔬</span>
                    {{ t('ai.no_analyses') }}
                </div>
            </div>
        </div>
    </AppShell>
</template>
