import { createI18n } from 'vue-i18n';
import de from './de.json';
import en from './en.json';

const i18n = createI18n({
    legacy: false,
    locale: document.documentElement.lang || 'de',
    fallbackLocale: 'en',
    messages: { de, en },
    numberFormats: {
        de: {
            decimal: { style: 'decimal', minimumFractionDigits: 2, maximumFractionDigits: 2 },
            percent: { style: 'percent', minimumFractionDigits: 1 },
            currency: { style: 'currency', currency: 'EUR' },
        },
        en: {
            decimal: { style: 'decimal', minimumFractionDigits: 2, maximumFractionDigits: 2 },
            percent: { style: 'percent', minimumFractionDigits: 1 },
            currency: { style: 'currency', currency: 'EUR' },
        },
    },
    datetimeFormats: {
        de: {
            short: { year: 'numeric', month: '2-digit', day: '2-digit' },
            long: { year: 'numeric', month: 'long', day: 'numeric', weekday: 'long' },
        },
        en: {
            short: { year: 'numeric', month: 'short', day: 'numeric' },
            long: { year: 'numeric', month: 'long', day: 'numeric', weekday: 'long' },
        },
    },
});

export default i18n;
