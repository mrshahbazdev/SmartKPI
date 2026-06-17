<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    requirements: Array,
})

const step = ref(0)
const loading = ref(false)
const error = ref('')
const success = ref('')

// Language selection
const locale = ref('de')

// Labels based on locale
const t = computed(() => locale.value === 'de' ? {
    title: 'SmartKPI Installation',
    subtitle: 'Willkommen beim Setup-Assistenten',
    step_language: 'Sprache',
    step_requirements: 'Systemprüfung',
    step_database: 'Datenbank',
    step_setup: 'Installation',
    step_admin: 'Administrator',
    step_done: 'Fertig',
    choose_language: 'Sprache wählen / Choose Language',
    next: 'Weiter',
    back: 'Zurück',
    requirements_title: 'Systemanforderungen',
    requirements_desc: 'Prüfung der Server-Voraussetzungen',
    all_passed: 'Alle Anforderungen erfüllt!',
    has_issues: 'Einige Anforderungen sind nicht erfüllt',
    required: 'Erforderlich',
    current: 'Aktuell',
    status: 'Status',
    db_title: 'Datenbank-Konfiguration',
    db_desc: 'Verbindungsdaten Ihrer Datenbank eingeben',
    db_type: 'Datenbanktyp',
    db_host: 'Host',
    db_port: 'Port',
    db_name: 'Datenbankname',
    db_user: 'Benutzername',
    db_pass: 'Passwort',
    test_connection: 'Verbindung testen',
    connection_ok: 'Verbindung erfolgreich!',
    app_name: 'App Name',
    app_url: 'App URL',
    setup_title: 'Installation ausführen',
    setup_desc: 'Datenbank-Tabellen erstellen und Grunddaten laden',
    run_install: 'Installation starten',
    installing: 'Installiere...',
    install_done: 'Installation erfolgreich!',
    admin_title: 'Administrator erstellen',
    admin_desc: 'Erstellen Sie das erste Admin-Konto',
    admin_name: 'Name',
    admin_email: 'E-Mail',
    admin_password: 'Passwort',
    admin_confirm: 'Passwort bestätigen',
    create_admin: 'Administrator erstellen',
    done_title: 'Installation abgeschlossen!',
    done_desc: 'SmartKPI wurde erfolgreich installiert.',
    go_to_login: 'Zum Login',
    optional: 'Optional',
} : {
    title: 'SmartKPI Installation',
    subtitle: 'Welcome to the Setup Wizard',
    step_language: 'Language',
    step_requirements: 'System Check',
    step_database: 'Database',
    step_setup: 'Installation',
    step_admin: 'Administrator',
    step_done: 'Complete',
    choose_language: 'Choose Language / Sprache wählen',
    next: 'Next',
    back: 'Back',
    requirements_title: 'System Requirements',
    requirements_desc: 'Checking server prerequisites',
    all_passed: 'All requirements met!',
    has_issues: 'Some requirements are not met',
    required: 'Required',
    current: 'Current',
    status: 'Status',
    db_title: 'Database Configuration',
    db_desc: 'Enter your database connection details',
    db_type: 'Database Type',
    db_host: 'Host',
    db_port: 'Port',
    db_name: 'Database Name',
    db_user: 'Username',
    db_pass: 'Password',
    test_connection: 'Test Connection',
    connection_ok: 'Connection successful!',
    app_name: 'App Name',
    app_url: 'App URL',
    setup_title: 'Run Installation',
    setup_desc: 'Create database tables and load initial data',
    run_install: 'Start Installation',
    installing: 'Installing...',
    install_done: 'Installation complete!',
    admin_title: 'Create Administrator',
    admin_desc: 'Create the first admin account',
    admin_name: 'Name',
    admin_email: 'Email',
    admin_password: 'Password',
    admin_confirm: 'Confirm Password',
    create_admin: 'Create Administrator',
    done_title: 'Installation Complete!',
    done_desc: 'SmartKPI has been successfully installed.',
    go_to_login: 'Go to Login',
    optional: 'Optional',
})

const steps = computed(() => [
    { title: t.value.step_language },
    { title: t.value.step_requirements },
    { title: t.value.step_database },
    { title: t.value.step_setup },
    { title: t.value.step_admin },
    { title: t.value.step_done },
])

const allRequirementsPassed = computed(() =>
    props.requirements?.every(r => r.passed || r.optional) ?? false
)

// DB form
const dbForm = ref({
    app_name: 'SmartKPI',
    app_url: window.location.origin,
    db_connection: 'mysql',
    db_host: '127.0.0.1',
    db_port: '3306',
    db_database: '',
    db_username: '',
    db_password: '',
})

const dbTested = ref(false)

// Admin form
const adminForm = ref({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
})

const adminCreated = ref(false)
const adminEmail = ref('')

function selectLanguage(lang) {
    locale.value = lang
}

function updatePort() {
    dbForm.value.db_port = dbForm.value.db_connection === 'mysql' ? '3306' : '5432'
}

async function testDb() {
    loading.value = true
    error.value = ''
    success.value = ''
    try {
        const res = await fetch('/install/test-db', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' },
            body: JSON.stringify(dbForm.value),
        })
        const data = await res.json()
        if (data.success) {
            dbTested.value = true
            success.value = t.value.connection_ok
        } else {
            error.value = data.message
        }
    } catch (e) {
        error.value = e.message
    }
    loading.value = false
}

async function saveEnv() {
    loading.value = true
    error.value = ''
    try {
        const res = await fetch('/install/environment', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' },
            body: JSON.stringify({ ...dbForm.value, app_locale: locale.value }),
        })
        const data = await res.json()
        if (data.success) {
            step.value = 3
        } else {
            error.value = data.message || 'Failed to save configuration'
        }
    } catch (e) {
        error.value = e.message
    }
    loading.value = false
}

async function runInstall() {
    loading.value = true
    error.value = ''
    success.value = ''
    try {
        const res = await fetch('/install/migrate', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' },
        })
        const data = await res.json()
        if (data.success) {
            success.value = t.value.install_done
            setTimeout(() => { step.value = 4; success.value = '' }, 1500)
        } else {
            error.value = data.message
        }
    } catch (e) {
        error.value = e.message
    }
    loading.value = false
}

async function createAdmin() {
    loading.value = true
    error.value = ''
    try {
        const res = await fetch('/install/admin', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' },
            body: JSON.stringify(adminForm.value),
        })
        const data = await res.json()
        if (data.success) {
            adminCreated.value = true
            adminEmail.value = data.email
            step.value = 5
        } else {
            error.value = data.message
        }
    } catch (e) {
        error.value = e.message
    }
    loading.value = false
}

function goToLogin() {
    window.location.href = '/login'
}
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <div class="flex min-h-screen">
            <!-- Sidebar (desktop) -->
            <div class="hidden lg:flex lg:w-80 lg:flex-col bg-white border-r border-gray-200 p-8">
                <div class="mb-8">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center">
                            <span class="text-white font-bold text-lg">SK</span>
                        </div>
                        <h1 class="text-xl font-bold text-gray-900">{{ t.title }}</h1>
                    </div>
                    <p class="text-sm text-gray-500">{{ t.subtitle }}</p>
                </div>
                <div class="space-y-3 flex-1">
                    <div v-for="(s, i) in steps" :key="i" class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium shrink-0"
                            :class="i < step ? 'bg-green-100 text-green-700' : i === step ? 'bg-blue-100 text-blue-700 ring-2 ring-blue-500' : 'bg-gray-100 text-gray-400'">
                            <span v-if="i < step">&#10003;</span>
                            <span v-else>{{ i + 1 }}</span>
                        </div>
                        <span class="text-sm" :class="i === step ? 'text-gray-900 font-medium' : 'text-gray-500'">{{ s.title }}</span>
                    </div>
                </div>
            </div>

            <!-- Mobile progress -->
            <div class="lg:hidden fixed top-0 inset-x-0 z-50 bg-white border-b border-gray-200 px-4 py-3">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-gray-700">{{ step + 1 }}/{{ steps.length }}</span>
                    <span class="text-xs text-gray-500">{{ steps[step]?.title }}</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-1.5">
                    <div class="bg-blue-500 h-1.5 rounded-full transition-all" :style="{ width: ((step + 1) / steps.length * 100) + '%' }"></div>
                </div>
            </div>

            <!-- Content -->
            <div class="flex-1 flex items-center justify-center p-6 lg:p-12 pt-20 lg:pt-12">
                <div class="w-full max-w-lg">

                    <!-- Error / Success alerts -->
                    <div v-if="error" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">{{ error }}</div>
                    <div v-if="success" class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">{{ success }}</div>

                    <!-- Step 0: Language -->
                    <div v-if="step === 0" class="space-y-6">
                        <h2 class="text-2xl font-bold text-gray-900">{{ t.choose_language }}</h2>
                        <div class="grid grid-cols-2 gap-4">
                            <button @click="selectLanguage('de')"
                                class="p-6 rounded-xl border-2 text-center transition-all"
                                :class="locale === 'de' ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300'">
                                <span class="text-3xl block mb-2">&#127465;&#127466;</span>
                                <span class="font-medium text-gray-900">Deutsch</span>
                            </button>
                            <button @click="selectLanguage('en')"
                                class="p-6 rounded-xl border-2 text-center transition-all"
                                :class="locale === 'en' ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300'">
                                <span class="text-3xl block mb-2">&#127468;&#127463;</span>
                                <span class="font-medium text-gray-900">English</span>
                            </button>
                        </div>
                        <button @click="step = 1" class="w-full py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors">
                            {{ t.next }}
                        </button>
                    </div>

                    <!-- Step 1: Requirements -->
                    <div v-if="step === 1" class="space-y-6">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900">{{ t.requirements_title }}</h2>
                            <p class="text-sm text-gray-500 mt-1">{{ t.requirements_desc }}</p>
                        </div>

                        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="bg-gray-50 border-b">
                                        <th class="text-left px-4 py-2 font-medium text-gray-600">Component</th>
                                        <th class="text-left px-4 py-2 font-medium text-gray-600">{{ t.required }}</th>
                                        <th class="text-left px-4 py-2 font-medium text-gray-600">{{ t.current }}</th>
                                        <th class="text-center px-4 py-2 font-medium text-gray-600">{{ t.status }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="req in requirements" :key="req.name" class="border-b last:border-0">
                                        <td class="px-4 py-2 text-gray-900">{{ req.name }}</td>
                                        <td class="px-4 py-2 text-gray-500">{{ req.required }}</td>
                                        <td class="px-4 py-2" :class="req.passed ? 'text-green-600' : req.optional ? 'text-yellow-600' : 'text-red-600'">
                                            {{ req.current }}
                                        </td>
                                        <td class="px-4 py-2 text-center">
                                            <span v-if="req.passed" class="text-green-500">&#10003;</span>
                                            <span v-else-if="req.optional" class="text-xs text-yellow-600 bg-yellow-50 px-2 py-0.5 rounded">{{ t.optional }}</span>
                                            <span v-else class="text-red-500">&#10007;</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="p-3 rounded-lg text-sm" :class="allRequirementsPassed ? 'bg-green-50 text-green-700' : 'bg-yellow-50 text-yellow-700'">
                            {{ allRequirementsPassed ? t.all_passed : t.has_issues }}
                        </div>

                        <div class="flex gap-3">
                            <button @click="step = 0" class="px-6 py-3 bg-gray-100 text-gray-700 rounded-lg font-medium hover:bg-gray-200 transition-colors">
                                {{ t.back }}
                            </button>
                            <button @click="step = 2" :disabled="!allRequirementsPassed"
                                class="flex-1 py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                                {{ t.next }}
                            </button>
                        </div>
                    </div>

                    <!-- Step 2: Database -->
                    <div v-if="step === 2" class="space-y-6">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900">{{ t.db_title }}</h2>
                            <p class="text-sm text-gray-500 mt-1">{{ t.db_desc }}</p>
                        </div>

                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ t.app_name }}</label>
                                    <input v-model="dbForm.app_name" type="text" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ t.app_url }}</label>
                                    <input v-model="dbForm.app_url" type="url" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">{{ t.db_type }}</label>
                                <select v-model="dbForm.db_connection" @change="updatePort" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="mysql">MySQL</option>
                                    <option value="pgsql">PostgreSQL</option>
                                </select>
                            </div>

                            <div class="grid grid-cols-3 gap-4">
                                <div class="col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ t.db_host }}</label>
                                    <input v-model="dbForm.db_host" type="text" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ t.db_port }}</label>
                                    <input v-model="dbForm.db_port" type="text" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">{{ t.db_name }}</label>
                                <input v-model="dbForm.db_database" type="text" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ t.db_user }}</label>
                                    <input v-model="dbForm.db_username" type="text" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ t.db_pass }}</label>
                                    <input v-model="dbForm.db_password" type="password" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                                </div>
                            </div>

                            <button @click="testDb" :disabled="loading || !dbForm.db_database || !dbForm.db_username"
                                class="w-full py-2.5 bg-gray-800 text-white rounded-lg text-sm font-medium hover:bg-gray-900 disabled:opacity-50 transition-colors">
                                {{ loading ? '...' : t.test_connection }}
                            </button>
                        </div>

                        <div class="flex gap-3">
                            <button @click="step = 1; error = ''; success = ''" class="px-6 py-3 bg-gray-100 text-gray-700 rounded-lg font-medium hover:bg-gray-200 transition-colors">
                                {{ t.back }}
                            </button>
                            <button @click="saveEnv(); error = ''; success = ''" :disabled="loading || !dbTested"
                                class="flex-1 py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                                {{ t.next }}
                            </button>
                        </div>
                    </div>

                    <!-- Step 3: Run Installation -->
                    <div v-if="step === 3" class="space-y-6">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900">{{ t.setup_title }}</h2>
                            <p class="text-sm text-gray-500 mt-1">{{ t.setup_desc }}</p>
                        </div>

                        <div class="bg-white rounded-xl border border-gray-200 p-6 text-center space-y-4">
                            <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto">
                                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
                                </svg>
                            </div>
                            <p class="text-sm text-gray-600">
                                {{ locale === 'de' ? 'Datenbank-Tabellen werden erstellt, Rollen und Berechtigungen konfiguriert und Beispieldaten geladen.' : 'Database tables will be created, roles and permissions configured, and sample data loaded.' }}
                            </p>
                            <button @click="runInstall" :disabled="loading"
                                class="px-8 py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 disabled:opacity-50 transition-colors">
                                {{ loading ? t.installing : t.run_install }}
                            </button>
                        </div>

                        <button @click="step = 2; error = ''; success = ''" class="px-6 py-3 bg-gray-100 text-gray-700 rounded-lg font-medium hover:bg-gray-200 transition-colors">
                            {{ t.back }}
                        </button>
                    </div>

                    <!-- Step 4: Create Admin -->
                    <div v-if="step === 4" class="space-y-6">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900">{{ t.admin_title }}</h2>
                            <p class="text-sm text-gray-500 mt-1">{{ t.admin_desc }}</p>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">{{ t.admin_name }}</label>
                                <input v-model="adminForm.name" type="text" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">{{ t.admin_email }}</label>
                                <input v-model="adminForm.email" type="email" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">{{ t.admin_password }}</label>
                                <input v-model="adminForm.password" type="password" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">{{ t.admin_confirm }}</label>
                                <input v-model="adminForm.password_confirmation" type="password" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                            </div>
                        </div>

                        <button @click="createAdmin" :disabled="loading || !adminForm.name || !adminForm.email || !adminForm.password || adminForm.password !== adminForm.password_confirmation"
                            class="w-full py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                            {{ loading ? '...' : t.create_admin }}
                        </button>
                    </div>

                    <!-- Step 5: Done -->
                    <div v-if="step === 5" class="space-y-6 text-center">
                        <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto">
                            <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900">{{ t.done_title }}</h2>
                            <p class="text-gray-500 mt-2">{{ t.done_desc }}</p>
                        </div>

                        <div class="bg-gray-50 rounded-xl p-4 text-sm text-left space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-500">Email:</span>
                                <span class="font-medium text-gray-900">{{ adminEmail }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Role:</span>
                                <span class="font-medium text-gray-900">Super Admin</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">URL:</span>
                                <span class="font-medium text-gray-900">{{ dbForm.app_url }}</span>
                            </div>
                        </div>

                        <button @click="goToLogin"
                            class="w-full py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors">
                            {{ t.go_to_login }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
