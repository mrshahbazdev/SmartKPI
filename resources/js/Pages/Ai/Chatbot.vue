<script setup>
import AppShell from '@/Components/Layout/AppShell.vue'
import { useI18n } from 'vue-i18n'
import { useForm, router, Link } from '@inertiajs/vue3'
import { ref, nextTick, watch } from 'vue'

const { t } = useI18n()

const props = defineProps({
    messages: Array,
    sessionId: String,
    sessions: Array,
})

const chatContainer = ref(null)
const isTyping = ref(false)

const form = useForm({
    message: '',
    session_id: props.sessionId,
})

const scrollToBottom = () => {
    nextTick(() => {
        if (chatContainer.value) {
            chatContainer.value.scrollTop = chatContainer.value.scrollHeight
        }
    })
}

watch(() => props.messages, scrollToBottom, { deep: true })

const sendMessage = () => {
    if (!form.message.trim()) return

    isTyping.value = true
    form.post(route('ai.chat.send'), {
        preserveScroll: true,
        onSuccess: () => {
            form.message = ''
            isTyping.value = false
            scrollToBottom()
        },
        onError: () => {
            isTyping.value = false
        },
    })
}

const newSession = () => {
    router.get(route('ai.chatbot'))
}

const quickQuestions = [
    { de: 'Welche KPIs haben sich verschlechtert?', en: 'Which KPIs have declined?' },
    { de: 'Zeige mir die offenen Probleme', en: 'Show me open problems' },
    { de: 'Was ist der beste KPI diesen Monat?', en: 'What is the best performing KPI this month?' },
    { de: 'Gib mir eine Zusammenfassung', en: 'Give me a summary' },
]
</script>

<template>
    <AppShell :title="t('ai.chatbot')">
        <div class="flex flex-col lg:flex-row gap-4 h-[calc(100vh-12rem)]">
            <!-- Chat Sessions Sidebar (desktop) -->
            <div class="hidden lg:block w-64 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="p-3 border-b border-gray-200 dark:border-gray-700">
                    <button @click="newSession" class="w-full px-3 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors">
                        + {{ t('ai.new_chat') }}
                    </button>
                </div>
                <div class="overflow-y-auto max-h-full">
                    <Link v-for="session in sessions" :key="session.session_id"
                        :href="route('ai.chatbot', { session: session.session_id })"
                        class="block p-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700"
                        :class="{ 'bg-blue-50 dark:bg-blue-900/20': session.session_id === sessionId }">
                        <div class="text-sm font-medium text-gray-700 dark:text-gray-300 truncate">
                            Chat {{ new Date(session.started_at).toLocaleDateString() }}
                        </div>
                        <div class="text-xs text-gray-400">{{ session.message_count }} {{ t('ai.messages') }}</div>
                    </Link>
                </div>
            </div>

            <!-- Chat Area -->
            <div class="flex-1 flex flex-col bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <!-- Messages -->
                <div ref="chatContainer" class="flex-1 overflow-y-auto p-4 space-y-4">
                    <!-- Welcome message -->
                    <div v-if="!messages?.length" class="text-center py-12">
                        <div class="text-5xl mb-4">🤖</div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">{{ t('ai.chat_welcome') }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6 max-w-md mx-auto">{{ t('ai.chat_description') }}</p>

                        <!-- Quick Questions -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-w-lg mx-auto">
                            <button v-for="q in quickQuestions" :key="q.en"
                                @click="form.message = $i18n.locale === 'de' ? q.de : q.en; sendMessage()"
                                class="text-left p-3 bg-gray-50 dark:bg-gray-700 rounded-lg text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                                {{ $i18n.locale === 'de' ? q.de : q.en }}
                            </button>
                        </div>
                    </div>

                    <!-- Chat Messages -->
                    <div v-for="msg in messages" :key="msg.id"
                        :class="msg.role === 'user' ? 'flex justify-end' : 'flex justify-start'">
                        <div :class="msg.role === 'user'
                            ? 'bg-blue-600 text-white rounded-2xl rounded-br-md max-w-[80%]'
                            : 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white rounded-2xl rounded-bl-md max-w-[80%]'"
                            class="px-4 py-3">
                            <div class="flex items-center gap-2 mb-1" v-if="msg.role === 'assistant'">
                                <span class="text-xs">🤖</span>
                                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">SmartKPI AI</span>
                            </div>
                            <div class="text-sm whitespace-pre-wrap" v-html="msg.content.replace(/\n/g, '<br>')"></div>
                            <div class="text-xs mt-1" :class="msg.role === 'user' ? 'text-blue-200' : 'text-gray-400'">
                                {{ new Date(msg.created_at).toLocaleTimeString() }}
                            </div>
                        </div>
                    </div>

                    <!-- Typing indicator -->
                    <div v-if="isTyping" class="flex justify-start">
                        <div class="bg-gray-100 dark:bg-gray-700 rounded-2xl rounded-bl-md px-4 py-3">
                            <div class="flex items-center gap-1">
                                <span class="text-xs">🤖</span>
                                <div class="flex gap-1">
                                    <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0s"></span>
                                    <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></span>
                                    <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.4s"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Input -->
                <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                    <form @submit.prevent="sendMessage" class="flex gap-2">
                        <input v-model="form.message" type="text"
                            :placeholder="t('ai.chat_placeholder')"
                            class="flex-1 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            :disabled="form.processing"
                            @keydown.enter.prevent="sendMessage" />
                        <button type="submit"
                            :disabled="form.processing || !form.message.trim()"
                            class="px-4 py-2.5 bg-blue-600 text-white rounded-xl text-sm font-medium hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                            {{ t('ai.send') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </AppShell>
</template>
