import Vue from 'vue';
import locales from './locales';
import VueI18n from 'vue-i18n';

Vue.use(VueI18n);

console.log(locales);

export default new VueI18n({
    locale: 'fr',
    fallbackLocale: 'fr',
    // messages comes from locales.json
    messages: {
        fr: locales,
        en: locales
    }
});