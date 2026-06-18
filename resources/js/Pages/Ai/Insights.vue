<script setup>
import AppShell from '@/Components/Layout/AppShell.vue'
import { useI18n } from 'vue-i18n'
import { useForm, router } from '@inertiajs/vue3'
import { ref } from 'vue'

const { t } = useI18n()

defineProps({ insights: Object, kpis: Array })

const selectedKpi = ref('')
const form = useForm({ kpi_id: '' })

const generate = () => {
    form.kpi_id = selectedKpi.value
    form.post('/ai/insights/generate', { preserveScroll: true })
}

const dismiss = (id) => router.post(`/ai/insights/${id}/dismiss`, {}, { preserveScroll: true })
</script>

<template>
    <AppShell :title="t('ai.nl_insights')">
        <div class="space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">{{ t('ai.generate_insight') }}</h3>
                <div class="flex flex-col sm:flex-row gap-3">
                    <select v-model="selectedKpi" class="flex-1 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-white px-3 py-2">
                        <option value="">{{ t('ai.select_kpi') }}</option>
                        <option v-for="kpi in kpis" :key="kpi.id" :value="kpi.id">
                            {{ $i18n.locale === 'de' ? kpi.name_de : kpi.name_en }}
                        </option>
                    </select>
                    <button @click="generate" :disabled="!selectedKpi || form.processing"
                        class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700 disabled:opacity-50 transition-colors whitespace-nowrap">
                        📊 {{ t('ai.generate_report') }}
                    </button>
                </div>
            </div>

            <div class="space-y-4">
                <div v-for="item in insights?.data" :key="item.id"
                    class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <span class="text-lg">📊</span>
                            <h3 class="font-semibold text-gray-900 dark:text-white">
                                {{ $i18n.locale === 'de' ? item.title_de : item.title_en }}
                            </h3>
                        </div>
                        <div class="flex items-center gap-2">
                            <span v-if="item.metadata?.performance_score" class="text-sm font-bold"
                                :class="item.metadata.performance_score >= 70 ? 'text-green-600' :
                                    item.metadata.performance_score >= 40 ? 'text-yellow-600' : 'text-red-600'">
                                {{ item.metadata.performance_score }}/100
                            </span>
                            <span v-if="item.metadata?.trend" class="px-2 py-0.5 rounded-full text-xs font-medium"
                                :class="item.metadata.trend === 'improving' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' :
                                    item.metadata.trend === 'declining' ? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300' :
                                    'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'">
                                {{ item.metadata.trend }}
                            </span>
                        </div>
                    </div>

                    <div class="prose prose-sm dark:prose-invert max-w-none text-gray-700 dark:text-gray-300 whitespace-pre-wrap">
                        {{ $i18n.locale === 'de' ? item.content_de : item.content_en }}
                    </div>

                    <div v-if="item.metadata?.key_findings?.length" class="mt-4 border-t border-gray-100 dark:border-gray-700 pt-3">
                        <h4 class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-2 uppercase">{{ t('ai.key_findings') }}</h4>
                        <ul class="space-y-1">
                            <li v-for="finding in item.metadata.key_findings" :key="finding" class="text-sm text-gray-600 dark:text-gray-400 flex items-start gap-2">
                                <span class="text-green-500 mt-0.5">•</span> {{ finding }}
                            </li>
                        </ul>
                    </div>

                    <div class="mt-3 flex items-center justify-between text-xs text-gray-400">
                        <span>{{ new Date(item.created_at).toLocaleDateString() }}</span>
                        <button @click="dismiss(item.id)" class="hover:text-gray-600 dark:hover:text-gray-300">{{ t('ai.dismiss') }}</button>
                    </div>
                </div>

                <div v-if="!insights?.data?.length" class="text-center py-12 text-gray-400 dark:text-gray-500">
                    <span class="text-4xl block mb-2">📊</span>
                    {{ t('ai.no_insights') }}
                </div>
            </div>
        </div>
    </AppShell>
</template>
