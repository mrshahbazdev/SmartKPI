<script setup>
import AppShell from '@/Components/Layout/AppShell.vue'
import { useI18n } from 'vue-i18n'
import { Link } from '@inertiajs/vue3'

const { t, locale } = useI18n()

const sections = [
    {
        href: '/settings/white-label',
        icon: '🎨',
        label_en: 'White Label',
        label_de: 'White Label',
        desc_en: 'Customize branding, logo, and colors',
        desc_de: 'Branding, Logo und Farben anpassen',
    },
    {
        href: '/settings/ai',
        icon: '🤖',
        label_en: 'AI Settings',
        label_de: 'KI-Einstellungen',
        desc_en: 'Configure your OpenAI API key',
        desc_de: 'OpenAI API-Schlüssel konfigurieren',
    },
    {
        href: '/settings/audit-trail',
        icon: '📋',
        label_en: 'Audit Trail',
        label_de: 'Audit-Protokoll',
        desc_en: 'View system activity log',
        desc_de: 'Systemaktivitätsprotokoll anzeigen',
    },
    {
        href: '/gdpr',
        icon: '🔒',
        label_en: 'GDPR / DSGVO',
        label_de: 'DSGVO',
        desc_en: 'Privacy, data export and deletion',
        desc_de: 'Datenschutz, Datenexport und Löschung',
    },
]
</script>

<template>
    <AppShell :title="t('settings.title')">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <Link v-for="section in sections" :key="section.href" :href="section.href"
                class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:border-blue-300 dark:hover:border-blue-600 transition-colors group">
                <div class="text-3xl mb-3">{{ section.icon }}</div>
                <h3 class="font-semibold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                    {{ locale === 'de' ? section.label_de : section.label_en }}
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    {{ locale === 'de' ? section.desc_de : section.desc_en }}
                </p>
            </Link>
        </div>
    </AppShell>
</template>
