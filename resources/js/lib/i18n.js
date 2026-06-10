import i18next from "i18next";
import LanguageDetector from "i18next-browser-languagedetector";
import HttpBackend from "i18next-http-backend";
import { createI18nStore } from "svelte-i18next";

await i18next
    .use(HttpBackend)
    .use(LanguageDetector)
    .init({
        fallbackLng: "en",
        debug: false,

        backend: {
            loadPath: "/api/locale/{{lng}}.json",
        },

        interpolation: {
            escapeValue: false,
        },

        returnEmptyString: false,
        returnNull: false,
    });

export const i18n = createI18nStore(i18next);
export default i18n;
