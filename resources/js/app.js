import './bootstrap';
import '../css/app.css';

import { createSSRApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy/dist/vue.m';
import { Link } from "@inertiajs/vue3";
import { createI18n } from "vue-i18n";
import SidebarLayout from "@/Layouts/SidebarLayout.vue";
import { createStore } from "vuex";

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

import en from '@/locales/en.json'
import pl from '@/locales/pl.json'


const store = createStore({
    state() {
        return {
            highContrast: false
        }
    },
    mutations: {
        change(state) {
            console.log("change mutation")
            state.highContrast = !state.highContrast
            document.cookie = `highContrast=${state.highContrast}; expires=${getCookieExpirationDate()}; path=/`
        },
        init(state) {
            console.log("init mutation")
            const cookieValue = getCookieValue("highContrast")

            if(cookieValue !== undefined) {
                console.log("has value")
                state.highContrast = cookieValue
            } else {
                console.log("doesn't have value")

                state.highContrast = false;
                document.cookie = `highContrast=false; expires=${getCookieExpirationDate()}; path=/`
            }

        }
    },
    actions: {
        init({ commit }){
            console.log("init action")
            commit("init")
        }
    }
})

store.dispatch("init")

function getCookieValue(cookieName) {
    const name = `${cookieName}=`;
    const decodedCookie = decodeURIComponent(document.cookie);
    const cookieArray = decodedCookie.split(';');
    for (let i = 0; i < cookieArray.length; i++) {
        let cookie = cookieArray[i].trim();
        if (cookie.indexOf(name) === 0) {
            return cookie.substring(name.length, cookie.length);
        }
    }
    return undefined;
}

function getCookieExpirationDate() {
    const date = new Date();
    date.setFullYear(date.getFullYear() + 1);
    return date.toUTCString();
}

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: name => {
        const pages = import.meta.glob('./Pages/**/*.vue', { eager: true })
        let page = pages[`./Pages/${name}.vue`]
        page.default.layout = page.default.layout || SidebarLayout

        return page
    },
    // resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {

        const i18n = createI18n({
            locale: props.initialPage.props.locale,
            legacy: false, // allow for composition api
            fallbackLocale: 'en',
            messages: { en, pl },
        })

        return createSSRApp({ render: () => h(App, props) })
            .use(plugin)
            .use(i18n)
            .use(store)
            .use(ZiggyVue, Ziggy)
            .component('Link', Link)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
