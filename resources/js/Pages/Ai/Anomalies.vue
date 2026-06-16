<script setup>
import AppShell from '@/Components/Layout/AppShell.vue'
import { useI18n } from 'vue-i18n'
import { useForm, router } from '@inertiajs/vue3'
import { ref } from 'vue'

const { t } = useI18n()

defineProps({ anomalies: Object, kpis: Array })

const selectedKpi = ref('')
const form = useForm({ kpi_id: '' })

const runDetection = () => {
    form.kpi_id = selectedKpi.value
    form.post(route('ai.anomalies.detect'), { preserveScroll: true })
}

const dismiss = (id) => router.post(route('ai.dismiss', id), {}, { preserveScroll: true })
</script>

<template>
    <AppShell :title="t('ai.anomaly_detection')">
        <div class="space-y-6">
            <!-- Trigger Panel -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">{{ t('ai.run_anomaly_scan') }}</h3>
                <div class="flex flex-col sm:flex-row gap-3">
                    <select v-model="selectedKpi" class="flex-1 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-white px-3 py-2">
                        <option value="">{{ t('ai.select_kpi') }}</option>
                        <option v-for="kpi in kpis" :key="kpi.id" :value="kpi.id">
                            {{ $i18n.locale === 'de' ? kpi.name_de : kpi.name_en }}
                        </option>
                    </select>
                    <button @click="runDetection" :disabled="!selectedKpi || form.processing"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700 disabled:opacity-50 transition-colors whitespace-nowrap">
                        🔍 {{ t('ai.detect_anomalies') }}
                    </button>
                </div>
            </div>

            <!-- Results -->
            <div class="space-y-4">
                <div v-for="item in anomalies?.data" :key="item.id"
                    class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="text-lg">🔍</span>
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium"
                                    :class="item.severity === 'critical' ? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300' :
                                        item.severity === 'warning' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300' :
                                        'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300'">
                                    {{ item.severity }}
                                </span>
                                <span v-if="item.metadata?.confidence" class="text-xs text-gray-400">
                                    {{ Math.round(item.metadata.confidence * 100) }}% {{ t('ai.confidence') }}
                                </span>
                            </div>
                            <h3 class="font-medium text-gray-900 dark:text-white text-sm">
                                {{ $i18n.locale === 'de' ? item.title_de : item.title_en }}
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                {{ $i18n.locale === 'de' ? item.content_de : item.content_en }}
                            </p>
                            <div v-if="item.kpi_definition" class="mt-2 text-xs text-gray-400">
                                KPI: {{ $i18n.locale === 'de' ? item.kpi_definition.name_de : item.kpi_definition.name_en }}
                            </div>
                        </div>
                        <button @click="dismiss(item.id)" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 text-xs">
                            {{ t('ai.dismiss') }}
                        </button>
                    </div>
                </div>

                <div v-if="!anomalies?.data?.length" class="text-center py-12 text-gray-400 dark:text-gray-500">
                    <span class="text-4xl block mb-2">🔍</span>
                    {{ t('ai.no_anomalies') }}
                </div>
            </div>
        </div>
    </AppShell>
</template>
