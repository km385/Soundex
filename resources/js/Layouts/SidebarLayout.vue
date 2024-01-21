<script setup>

import {ref, provide, inject, onMounted, computed} from "vue";
import {Link, usePage} from '@inertiajs/vue3';
import SidebarRow from "./Partials/SidebarRow.vue";
import {useI18n} from "vue-i18n";
import SvgComp from "@/Components/SvgComp.vue";

const page = usePage()
const i18 = useI18n()
const highContrast = inject('highContrast')


const iconSize = ref({
    width: 50,
    height: 50
})
const iconColor = "#faf8f6"

const isSmall = ref(false)

window.addEventListener('resize', (event) => {
    isSmall.value = window.innerWidth <= 764;
})

const isSidebarOpen = ref(true)
const isToolsMenuOpen = ref(false)
const isLangMenuOpen = ref(false)
const isUserMenuOpen = ref(false)

provide('isSidebarCollapsed', isSidebarOpen);

onMounted(() => {
    isSmall.value = window.innerWidth <= 768
})

function openSidebar() {
    isSidebarOpen.value = !isSidebarOpen.value
    isToolsMenuOpen.value = false
    isLangMenuOpen.value = false
    isUserMenuOpen.value = false
}

function setLang(lang) {
    i18.locale.value = lang
    setCookie("locale", lang, 60 * 60 * 24 * 30)
}

function setCookie(key, value, expiresInSeconds) {
    let now = new Date();
    let time = now.getTime();
    let expireTime = time + 1000 * expiresInSeconds;
    now.setTime(expireTime);
    document.cookie = `${key}=${value};expires=${now.toUTCString()}`

}

const tools = [
    { name: 'cutter', component: 'Tools/Cutter', link: '/tools/cutter' },
    { name: 'tagEditor', component: 'Tools/TagEditor', link: '/tools/tageditor' },
    { name: 'speedUp', component: 'Tools/SpeedUp', link: '/tools/speedup' },
    { name: 'merge', component: 'Tools/Merge', link: '/tools/merge' },
    { name: 'converter', component: 'Tools/Converter', link: '/tools/converter' },
    { name: 'videoToAudio', component: 'Tools/VideoToAudio', link: '/tools/videotoaudio' },
    { name: 'volumeChanger', component: 'Tools/VolumeChanger', link: '/tools/volumechanger' },
    { name: 'recorder', component: 'Tools/Recorder', link: '/tools/recorder' },
    { name: 'layerMixer', component: 'Tools/LayerMixer', link: '/tools/layermixer' },
    { name: 'bpmFinder', component: 'Tools/BPMFinder', link: '/tools/bpmFinder' },
    { name: 'diagnosis', component: 'Tools/Diagnosis', link: '/tools/diagnosis' }
];

const sortedTools = tools.sort((a, b) => a.name.localeCompare(b.name));

function toggleCookieValue(cookieName) {
    const currentValue = getCookieValue(cookieName);
    const booleanValue = currentValue === 'true';
    const newValue = !booleanValue;
    document.cookie = `${cookieName}=${newValue}; expires=${getCookieExpirationDate()}; path=/; SameSite=Lax`
    return newValue;
}

function changeContrast() {
    const newContrastValue = toggleCookieValue("highContrast");
    highContrast.value = newContrastValue
}

function getCookieExpirationDate() {
    const date = new Date();
    date.setFullYear(date.getFullYear() + 1);
    return date.toUTCString();
}

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

</script>

<template>
    <div :class="{'bg-black':highContrast}" class="flex">
        <!-- sidebar-->
        <div
            class="z-[999] sidebar h-screen bg-[#2B2B2B] pt-8 absolute md:static duration-500 flex flex-col text-white select-none border-r-[1px] border-gray-600"
            :class="{'w-72': isSidebarOpen, 'w-20': !isSidebarOpen, 'hidden': !isSidebarOpen && isSmall, 'high-contrast-input':highContrast}"
        >
            <!--  burger icon  -->
            <div class="flex justify-end mr-5">
                <SvgComp name="burger" class="w-12 cursor-pointer hover:bg-gray-500 duration-300 p-1 rounded-full" @click="openSidebar"/>
            </div>
            <!-- main options-->
            <div id="tools" class="flex flex-col items-center grow overflow-y-auto overflow-x-hidden no-scrollbar">
                <Link href="/" class="flex items-center w-full">
                    <SidebarRow @click="isSmall ? isSidebarOpen = false : ''" class="group" icon="home" :text="$t('sidebar.home')"
                                :show-text="isSidebarOpen" />


                </Link>

                <SidebarRow @click="isToolsMenuOpen = !isToolsMenuOpen; isSidebarOpen = true"
                            :text="$t('sidebar.tools')"
                            :show-text="isSidebarOpen"
                            :has-menu="true"
                            :rotate-icon="isToolsMenuOpen"
                            icon="tool"
                            class="group"
                >


                </SidebarRow>
                    <Transition name="slide-fade">
                        <ul v-if="isToolsMenuOpen && isSidebarOpen" class="w-[80%]">
                            <Link v-for="(tool, index) in sortedTools" :key="tool.link" :href="tool.link">
                                <li
                                    @click="isSmall ? isSidebarOpen = false : ''"
                                    class="hover:bg-gray-500 cursor-pointer py-1 pl-2 rounded-md"
                                    :class="{
                                            'bg-gray-500' : page.component === tool.component,
                                            'rounded-t-lg': index === 0,
                                            'rounded-b-lg': index === tools.length-1,
                                            'high-contrast-button':highContrast,
                                            'high-contrast-button-selected':page.component === tool.component
                                            && highContrast
                                        }"
                                >
                                    {{ $t(`sidebar.${tool.name}`) }}
                                </li>
                            </Link>
                        </ul>
                    </Transition>

                <Link href="/database" class="flex items-center w-full">
                    <SidebarRow @click="isSmall ? isSidebarOpen = false : ''" icon="files" :text="$t('sidebar.files')"
                                :show-text="isSidebarOpen" class="group" />
                </Link>

                <Link href="/help" class="flex items-center w-full">
                    <SidebarRow @click="isSmall ? isSidebarOpen = false : ''" class="group" icon="help" :text="$t('sidebar.help')" :show-text="isSidebarOpen">

                    </SidebarRow>
                </Link>


                <SidebarRow
                    @click="isLangMenuOpen = !isLangMenuOpen; isSidebarOpen = true" class="group"
                    icon="lang" :text="$t('sidebar.lang')" :show-text="isSidebarOpen" :has-menu="true"
                    :rotate-icon="isLangMenuOpen"
                />


                <Transition name="slide-fade">
                    <ul v-if="isLangMenuOpen && isSidebarOpen" class="w-[80%]">
                        <li @click="setLang('en')" class="hover:bg-gray-500 cursor-pointer rounded-md py-1 pl-2"
                            :class="{ 'bg-gray-500' : i18.locale.value === 'en',
                                        'high-contrast-button':highContrast,
                                        'high-contrast-button-selected':i18.locale.value === 'en' && highContrast
                                    }">
                            English
                        </li>

                        <li @click="setLang('pl')" class="hover:bg-gray-500 cursor-pointer rounded-md py-1 pl-2"
                            :class="{
                                    'bg-gray-500' : i18.locale.value === 'pl',
                                    'high-contrast-button-selected' : i18.locale.value === 'pl' && highContrast,
                                    'high-contrast-button':highContrast}">
                            Polish
                        </li>
                    </ul>
                </Transition>

            </div>

            <hr
                :class="{'high-contrast-input':highContrast}"
                class="border-gray-500 mb-2 border-1 mx-2">
            <!-- bottom options -->
            <div id="bottom" class="flex-none">
                <SidebarRow icon="contrast" :text="$t('sidebar.contrast')"
                            :show-text="isSidebarOpen" @click="changeContrast" class="group" />
                <div class="relative flex justify-center" v-if="page.props.auth.user">
                    <div class="absolute w-[98%] bg-[#171515] translate-y-[-105%] rounded-lg border border-gray-500" v-if="isUserMenuOpen"
                         @click="isUserMenuOpen = !isUserMenuOpen">
                        <Link href="/dashboard">
                            <div
                                :class="{'high-contrast-button': highContrast}"
                                class="border-b py-2 hover:bg-gray-500 rounded-t-lg">
                                <p class="pl-2">{{$t('sidebar.profile')}}</p>
                            </div>
                        </Link>
                        <Link href="/logout" method="post" as="button" class="w-full text-left">
                            <div
                                :class="{'high-contrast-button': highContrast}"
                                class="py-2 hover:bg-gray-500 rounded-b-lg">
                                <p class="pl-2">{{$t('sidebar.logout')}}</p>
                            </div>
                        </Link>
                    </div>
                    <SidebarRow icon="user" class="group"
                                :text="page.props.auth.user.nickname"
                                :show-text="isSidebarOpen" @click="isUserMenuOpen = !isUserMenuOpen; isSidebarOpen = true" />

                </div>

                <div
                    class="relative flex justify-center" v-else>
                    <div class="absolute w-[98%] bg-[#171515] translate-y-[-105%] rounded-lg border border-gray-500" v-if="isUserMenuOpen"
                         @click="isUserMenuOpen = !isUserMenuOpen">

                        <Link href="/register">
                            <div
                                :class="{'high-contrast-button': highContrast}"
                                class="border-b py-2 hover:bg-gray-500 rounded-t-lg">
                                <p class="pl-2">{{$t('sidebar.register')}}</p>
                            </div>
                        </Link>

                        <Link href="/login">
                            <div
                                :class="{'high-contrast-button': highContrast}"
                                class="py-2 hover:bg-gray-500 rounded-b-lg">
                                <p class="pl-2">{{$t('sidebar.login')}}</p>
                            </div>
                        </Link>
                    </div>
                    <SidebarRow icon="user" :text="$t('sidebar.user')"
                                :show-text="isSidebarOpen" class="group"
                                @click="isUserMenuOpen = !isUserMenuOpen;isSidebarOpen = true" />
                </div>
            </div>
        </div>


        <!-- burger icon for small screens -->
        <Transition name="slide-fade">

        <div :class="{'high-contrast-input':highContrast}" class="z-[998] absolute text-white text-4xl top-0 left-0 bg-gray-900 items-center flex w-full"
              v-if="!isSidebarOpen && isSmall">
            <span class="pl-5">soundex</span>
            <div class="flex grow pr-3">
                <div class="grow"></div>
                <SvgComp name="burger" class="w-12 p-1 rounded-full z-[998] cursor-pointer hover:bg-gray-500 duration-300 " @click="openSidebar"/>
            </div>
        </div>
        </Transition>
        <!-- main content -->
        <div @click="isSmall ? isSidebarOpen = false : ''"
            :class="{'bg-black': highContrast, 'brightness-50':isSmall && isSidebarOpen}"
            class="flex-1 overflow-y-auto">
            <div class="flex justify-center h-screen">
                <div class="w-screen">
                    <slot />
                </div>
            </div>
        </div>

    </div>
</template>

<style scoped>
.high-contrast-input {
    @apply text-xl border border-[#FFFF00FF] bg-black text-[#FFFF00FF]
}
.high-contrast-button {
    @apply text-xl border border-[#FFFF00FF] bg-black text-[#FFFF00FF] focus:border-[#FFFF00FF] focus:ring-[#FFFF00FF] hover:bg-yellow-300 hover:text-black
}

.high-contrast-button-selected {
    @apply bg-yellow-300 text-black
}

.slide-fade-enter-active,
.slide-fade-leave-active {
    transition: opacity 0.3s, transform 0.3s
}


.slide-fade-enter-from, .slide-fade-leave-to {
    opacity: 0;
    transform: translateY(-20px);
}

.no-scrollbar::-webkit-scrollbar {
    display: none;
}

.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}

</style>
