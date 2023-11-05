import './bootstrap';
import '../css/app.css';

import { createSSRApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy/dist/vue.m';
import { Link } from "@inertiajs/vue3";
import { createI18n } from "vue-i18n";
import SidebarLayout from "@/Layouts/SidebarLayout.vue";
const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

import en from '@/locales/en.json'
import pl from '@/locales/pl.json'



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
            .use(ZiggyVue, Ziggy)
            .component('Link', Link)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
