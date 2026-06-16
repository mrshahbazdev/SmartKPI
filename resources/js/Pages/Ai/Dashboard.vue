<script setup>
import AppShell from '@/Components/Layout/AppShell.vue'
import { useI18n } from 'vue-i18n'
import { Link } from '@inertiajs/vue3'

const { t } = useI18n()

defineProps({
    insights: Array,
    insightsByType: Object,
    stats: Object,
})

const typeIcons = {
    anomaly: '🔍',
    recommendation: '💡',
    insight: '📊',
    root_cause: '🔬',
    action_suggestion: '⚡',
}

const typeColors = {
    anomaly: 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300',
    recommendation: 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300',
    insight: 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300',
    root_cause: 'bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-300',
    action_suggestion: 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300',
}

const severityColors = {
    info: 'text-blue-600 dark:text-blue-400',
    warning: 'text-yellow-600 dark:text-yellow-400',
    critical: 'text-red-600 dark:text-red-400',
}
</script>

<template>
    <AppShell :title="t('ai.dashboard')">
        <div class="space-y-6">
            <!-- Stats Grid -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.total_insights }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ t('ai.total_insights') }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="text-2xl font-bold text-green-600">{{ stats.active_insights }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ t('ai.active') }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="text-2xl font-bold text-red-600">{{ stats.anomalies_detected }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ t('ai.anomalies') }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="text-2xl font-bold text-blue-600">{{ stats.recommendations }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ t('ai.recommendations') }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="text-2xl font-bold text-purple-600">{{ stats.root_causes }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ t('ai.root_causes') }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="text-2xl font-bold text-yellow-600">{{ stats.action_suggestions }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ t('ai.action_suggestions') }}</div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
                <Link :href="route('ai.anomalies')" class="flex flex-col items-center p-4 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 hover:border-red-300 dark:hover:border-red-600 transition-colors">
                    <span class="text-2xl mb-1">🔍</span>
                    <span class="text-xs font-medium text-gray-700 dark:text-gray-300">{{ t('ai.anomaly_detection') }}</span>
                </Link>
                <Link :href="route('ai.recommendations')" class="flex flex-col items-center p-4 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 hover:border-blue-300 dark:hover:border-blue-600 transition-colors">
                    <span class="text-2xl mb-1">💡</span>
                    <span class="text-xs font-medium text-gray-700 dark:text-gray-300">{{ t('ai.kpi_recommendations') }}</span>
                </Link>
                <Link :href="route('ai.insights')" class="flex flex-col items-center p-4 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 hover:border-green-300 dark:hover:border-green-600 transition-colors">
                    <span class="text-2xl mb-1">📊</span>
                    <span class="text-xs font-medium text-gray-700 dark:text-gray-300">{{ t('ai.nl_insights') }}</span>
                </Link>
                <Link :href="route('ai.action-suggestions')" class="flex flex-col items-center p-4 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 hover:border-yellow-300 dark:hover:border-yellow-600 transition-colors">
                    <span class="text-2xl mb-1">⚡</span>
                    <span class="text-xs font-medium text-gray-700 dark:text-gray-300">{{ t('ai.predictive_actions') }}</span>
                </Link>
                <Link :href="route('ai.root-causes')" class="flex flex-col items-center p-4 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 hover:border-purple-300 dark:hover:border-purple-600 transition-colors">
                    <span class="text-2xl mb-1">🔬</span>
                    <span class="text-xs font-medium text-gray-700 dark:text-gray-300">{{ t('ai.root_cause_analysis') }}</span>
                </Link>
                <Link :href="route('ai.chatbot')" class="flex flex-col items-center p-4 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 hover:border-cyan-300 dark:hover:border-cyan-600 transition-colors">
                    <span class="text-2xl mb-1">🤖</span>
                    <span class="text-xs font-medium text-gray-700 dark:text-gray-300">{{ t('ai.chatbot') }}</span>
                </Link>
            </div>

            <!-- Recent Insights Feed -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ t('ai.recent_insights') }}</h2>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    <div v-for="insight in insights" :key="insight.id" class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <div class="flex items-start gap-3">
                            <span class="text-lg">{{ typeIcons[insight.type] || '📌' }}</span>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <span :class="typeColors[insight.type]" class="px-2 py-0.5 rounded-full text-xs font-medium">
                                        {{ t('ai.type_' + insight.type) }}
                                    </span>
                                    <span v-if="insight.severity" :class="severityColors[insight.severity]" class="text-xs font-medium">
                                        {{ insight.severity }}
                                    </span>
                                    <span class="text-xs text-gray-400">{{ new Date(insight.created_at).toLocaleDateString() }}</span>
                                </div>
                                <h3 class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $i18n.locale === 'de' ? insight.title_de : insight.title_en }}
                                </h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 line-clamp-2">
                                    {{ $i18n.locale === 'de' ? insight.content_de : insight.content_en }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div v-if="!insights?.length" class="p-8 text-center text-gray-400 dark:text-gray-500">
                        <span class="text-4xl block mb-2">🤖</span>
                        {{ t('ai.no_insights_yet') }}
                    </div>
                </div>
            </div>
        </div>
    </AppShell>
</template>
