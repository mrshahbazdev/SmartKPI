<script setup>
import AppShell from '@/Components/Layout/AppShell.vue'
import { useI18n } from 'vue-i18n'
import { useForm, router } from '@inertiajs/vue3'
import { ref } from 'vue'

const { t } = useI18n()

defineProps({ recommendations: Object, companies: Array })

const selectedCompany = ref('')
const form = useForm({ company_id: '' })

const generate = () => {
    form.company_id = selectedCompany.value
    form.post(route('ai.recommendations.generate'), { preserveScroll: true })
}

const apply = (id) => router.post(route('ai.apply', id), {}, { preserveScroll: true })
const dismiss = (id) => router.post(route('ai.dismiss', id), {}, { preserveScroll: true })
</script>

<template>
    <AppShell :title="t('ai.kpi_recommendations')">
        <div class="space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">{{ t('ai.generate_recommendations') }}</h3>
                <div class="flex flex-col sm:flex-row gap-3">
                    <select v-model="selectedCompany" class="flex-1 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-white px-3 py-2">
                        <option value="">{{ t('ai.select_company') }}</option>
                        <option v-for="c in companies" :key="c.id" :value="c.id">{{ c.name }}</option>
                    </select>
                    <button @click="generate" :disabled="!selectedCompany || form.processing"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 disabled:opacity-50 transition-colors whitespace-nowrap">
                        💡 {{ t('ai.get_recommendations') }}
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div v-for="item in recommendations?.data" :key="item.id"
                    class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="text-lg">💡</span>
                        <span v-if="item.metadata?.priority" class="px-2 py-0.5 rounded-full text-xs font-medium"
                            :class="item.metadata.priority === 'high' ? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300' :
                                item.metadata.priority === 'medium' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300' :
                                'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300'">
                            {{ item.metadata.priority }}
                        </span>
                        <span v-if="item.metadata?.category" class="text-xs text-gray-400">{{ item.metadata.category }}</span>
                    </div>
                    <h3 class="font-medium text-gray-900 dark:text-white text-sm">
                        {{ $i18n.locale === 'de' ? item.title_de : item.title_en }}
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        {{ $i18n.locale === 'de' ? item.content_de : item.content_en }}
                    </p>
                    <div v-if="item.metadata" class="mt-3 flex flex-wrap gap-2 text-xs text-gray-500 dark:text-gray-400">
                        <span v-if="item.metadata.unit">{{ t('ai.unit') }}: {{ item.metadata.unit }}</span>
                        <span v-if="item.metadata.target_value">{{ t('ai.target') }}: {{ item.metadata.target_value }}</span>
                        <span v-if="item.metadata.department">{{ t('ai.dept') }}: {{ item.metadata.department }}</span>
                    </div>
                    <div class="mt-3 flex gap-2">
                        <button @click="apply(item.id)" class="text-xs text-green-600 hover:text-green-800 font-medium">{{ t('ai.apply') }}</button>
                        <button @click="dismiss(item.id)" class="text-xs text-gray-400 hover:text-gray-600">{{ t('ai.dismiss') }}</button>
                    </div>
                </div>
            </div>

            <div v-if="!recommendations?.data?.length" class="text-center py-12 text-gray-400 dark:text-gray-500">
                <span class="text-4xl block mb-2">💡</span>
                {{ t('ai.no_recommendations') }}
            </div>
        </div>
    </AppShell>
</template>
