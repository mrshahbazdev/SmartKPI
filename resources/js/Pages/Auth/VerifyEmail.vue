<script setup>
import { useForm } from '@inertiajs/vue3'
import GuestLayout from '@/Components/Layout/GuestLayout.vue'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

const form = useForm({})

const resend = () => {
    form.post('/email/verification-notification')
}
</script>

<template>
    <GuestLayout>
        <div class="text-center">
            <div class="text-4xl mb-4">&#9993;</div>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">
                {{ t('auth.verify_email_title', 'Verify Your Email') }}
            </h2>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                {{ t('auth.verify_email_message', 'Please check your email for a verification link.') }}
            </p>

            <div v-if="$page.props.flash?.message" class="mb-4 text-sm text-green-600 dark:text-green-400">
                {{ $page.props.flash.message }}
            </div>

            <button @click="resend" :disabled="form.processing"
                class="w-full py-2.5 px-4 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg shadow-sm transition-colors disabled:opacity-50">
                {{ t('auth.resend_verification', 'Resend Verification Email') }}
            </button>
        </div>
    </GuestLayout>
</template>
