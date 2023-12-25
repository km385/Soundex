<script setup>

import {ref, provide, inject, onMounted} from "vue";
import {Link, usePage} from '@inertiajs/vue3';
import SidebarRow from "./Partials/SidebarRow.vue";
import {useI18n} from "vue-i18n";

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
    let expireTime = time + 1000 * expiresInSeconds; // to milliseconds
    now.setTime(expireTime);
    document.cookie = `${key}=${value};expires=${now.toUTCString()}`

}

const tools = [
    { name: 'Cutter', component: 'Tools/Cutter', link: '/tools/cutter' },
    { name: 'Tag Editor', component: 'Tools/TagEditor', link: '/tools/tageditor' },
    { name: 'SpeedUp', component: 'Tools/SpeedUp', link: '/tools/speedup' },
    { name: 'Merge', component: 'Tools/Merge', link: '/tools/merge' },
    { name: 'Converter', component: 'Tools/Converter', link: '/tools/converter' },
    { name: 'Video to Audio', component: 'Tools/VideoToAudio', link: '/tools/videotoaudio' },
    { name: 'Volume Changer', component: 'Tools/VolumeChanger', link: '/tools/volumechanger' },
    { name: 'Recorder', component: 'Tools/Recorder', link: '/tools/recorder' },
    { name: 'LayerMix', component: 'Tools/LayerMixer', link: '/tools/layermixer' },
    { name: 'BPM Finder', component: 'Tools/BPMFinder', link: '/tools/bpmFinder' },
    { name: 'Diagnosis', component: 'Tools/Diagnosis', link: '/tools/diagnosis' }
];

const sortedTools = tools.sort((a, b) => a.name.localeCompare(b.name));

function toggleCookieValue(cookieName) {
    const currentValue = getCookieValue(cookieName);
    const booleanValue = currentValue === 'true';
    const newValue = !booleanValue;
    document.cookie = `${cookieName}=${newValue}; expires=${getCookieExpirationDate()}; path=/`;
    return newValue;
}

function changeContrast() {
    const newContrastValue = toggleCookieValue("highContrast");
    highContrast.value = newContrastValue
    console.log(`High Contrast is now ${newContrastValue ? 'enabled' : 'disabled'}`);
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
    <div class="flex">
        <!-- sidebar-->
        <div
            class="z-[999] sidebar h-screen bg-[#2B2B2B] pt-8 absolute md:static duration-300 flex flex-col text-white select-none"
            :class="{'w-72': isSidebarOpen, 'w-20': !isSidebarOpen, 'hidden': !isSidebarOpen && isSmall, 'high-contrast-input':highContrast}"
        >
            <!--  burger icon  -->
            <div class="flex justify-end mr-5">

                <svg @click="openSidebar" xmlns="http://www.w3.org/2000/svg" :height="iconSize.height" viewBox="0 -960 960 960"
                     :width="iconSize.width" class="cursor-pointer ml-2 ">
                    <path :fill="highContrast ? '#FFFF00FF' : iconColor"
                          d="M160-240q-17 0-28.5-11.5T120-280q0-17 11.5-28.5T160-320h640q17 0 28.5 11.5T840-280q0 17-11.5 28.5T800-240H160Zm0-200q-17 0-28.5-11.5T120-480q0-17 11.5-28.5T160-520h640q17 0 28.5 11.5T840-480q0 17-11.5 28.5T800-440H160Zm0-200q-17 0-28.5-11.5T120-680q0-17 11.5-28.5T160-720h640q17 0 28.5 11.5T840-680q0 17-11.5 28.5T800-640H160Z"/>
                </svg>
            </div>
            <!-- main options-->
            <div id="tools" class="flex flex-col items-center grow overflow-y-auto overflow-x-hidden ">
                <Link href="/" class="flex items-center w-full">
                    <SidebarRow class="group" icon="home_FILL0_wght400_GRAD0_opsz24.png" :text="$t('sidebar.home')"
                                :show-text="isSidebarOpen">
                        <template v-slot:icon>
                            <svg xmlns="http://www.w3.org/2000/svg" :height="iconSize.height" viewBox="0 -960 960 960"
                                 :width="iconSize.width">
                                <path :fill="highContrast ? '#FFFF00FF' : iconColor"
                                      :class="{'group-hover:fill-black':highContrast}"
                                      d="M240-200h120v-200q0-17 11.5-28.5T400-440h160q17 0 28.5 11.5T600-400v200h120v-360L480-740 240-560v360Zm-80 0v-360q0-19 8.5-36t23.5-28l240-180q21-16 48-16t48 16l240 180q15 11 23.5 28t8.5 36v360q0 33-23.5 56.5T720-120H560q-17 0-28.5-11.5T520-160v-200h-80v200q0 17-11.5 28.5T400-120H240q-33 0-56.5-23.5T160-200Zm320-270Z"/>
                            </svg>
                        </template>
                    </SidebarRow>
                </Link>

                <SidebarRow @click="isToolsMenuOpen = !isToolsMenuOpen; isSidebarOpen = true"
                            icon="construction_FILL0_wght400_GRAD0_opsz24.png" :text="$t('sidebar.tools')"
                            :show-text="isSidebarOpen"
                            class="group"
                >
                    <template v-slot:icon>
                        <svg xmlns="http://www.w3.org/2000/svg" :height="iconSize.height" viewBox="0 -960 960 960"
                             :width="iconSize.width">
                            <path :fill="highContrast ? '#FFFF00FF' : iconColor"
                                  :class="{'group-hover:fill-black':highContrast}"
                                  d="M714-162 537-339l84-84 177 177q17 17 17 42t-17 42q-17 17-42 17t-42-17Zm-552 0q-17-17-17-42t17-42l234-234-68-68q-11 11-28 11t-28-11l-23-23v90q0 14-12 19t-22-5L106-576q-10-10-5-22t19-12h90l-22-22q-12-12-12-28t12-28l114-114q20-20 43-29t47-9q20 0 37.5 6t34.5 18q8 5 8.5 14t-6.5 16l-76 76 22 22q11 11 11 28t-11 28l68 68 90-90q-4-11-6.5-23t-2.5-24q0-59 40.5-99.5T701-841q8 0 15 .5t14 2.5q9 3 11.5 12.5T737-809l-65 65q-6 6-6 14t6 14l44 44q6 6 14 6t14-6l65-65q7-7 16.5-5t12.5 12q2 7 2.5 14t.5 15q0 59-40.5 99.5T701-561q-12 0-24-2t-23-7L246-162q-17 17-42 17t-42-17Z"/>
                        </svg>
                    </template>

                </SidebarRow>

                    <ul v-if="isToolsMenuOpen" class="w-[80%]">
                        <Link v-for="(tool, index) in sortedTools" :key="tool.link" :href="tool.link">
                            <li
                                @click="isSmall ? isSidebarOpen = false : ''"
                                class="hover:bg-gray-500 cursor-pointer py-1 pl-2 rounded-md"
                                :class="{
                                            'bg-gray-500' : page.component === tool.component,
                                            'rounded-t-lg': index === 0,
                                            'rounded-b-lg': index === tools.length-1,
                                            'high-contrast-button':highContrast,
                                            'high-contrast-button-selected':page.component === tool.component && highContrast
                                        }"
                            >
                                {{ tool.name }}
                            </li>
                        </Link>
                    </ul>
                <Link href="/database" class="flex items-center w-full">
                    <SidebarRow icon="folder_open_FILL0_wght400_GRAD0_opsz24.png" :text="$t('sidebar.files')"
                                :show-text="isSidebarOpen" class="group">
                        <template v-slot:icon>
                            <svg xmlns="http://www.w3.org/2000/svg" :height="iconSize.height" viewBox="0 -960 960 960"
                                 :width="iconSize.width" >
                                <path :fill="highContrast ? '#FFFF00FF' : iconColor" :class="{'group-hover:fill-black':highContrast}"
                                      d="M160-160q-33 0-56.5-23.5T80-240v-480q0-33 23.5-56.5T160-800h207q16 0 30.5 6t25.5 17l57 57h320q33 0 56.5 23.5T880-640H447l-80-80H160v480l79-263q8-26 29.5-41.5T316-560h516q41 0 64.5 32.5T909-457l-72 240q-8 26-29.5 41.5T760-160H160Zm84-80h516l72-240H316l-72 240Zm-84-262v-218 218Zm84 262 72-240-72 240Z"/>
                            </svg>
                        </template>
                    </SidebarRow>
                </Link>

                <SidebarRow class="group" icon="help_FILL0_wght400_GRAD0_opsz24.png" :text="$t('sidebar.help')" :show-text="isSidebarOpen">
                    <template v-slot:icon>
                        <svg xmlns="http://www.w3.org/2000/svg" :height="iconSize.height" viewBox="0 -960 960 960"
                             :width="iconSize.width">
                            <path :fill="highContrast ? '#FFFF00FF' : iconColor" :class="{'group-hover:fill-black':highContrast}"
                                  d="M478-240q21 0 35.5-14.5T528-290q0-21-14.5-35.5T478-340q-21 0-35.5 14.5T428-290q0 21 14.5 35.5T478-240Zm2 160q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Zm0-80q134 0 227-93t93-227q0-134-93-227t-227-93q-134 0-227 93t-93 227q0 134 93 227t227 93Zm0-320Zm4-172q25 0 43.5 16t18.5 40q0 22-13.5 39T502-525q-23 20-40.5 44T444-427q0 14 10.5 23.5T479-394q15 0 25.5-10t13.5-25q4-21 18-37.5t30-31.5q23-22 39.5-48t16.5-58q0-51-41.5-83.5T484-720q-38 0-72.5 16T359-655q-7 12-4.5 25.5T368-609q14 8 29 5t25-17q11-15 27.5-23t34.5-8Z"/>
                        </svg>
                    </template>
                </SidebarRow>

                <SidebarRow
                    @click="isLangMenuOpen = !isLangMenuOpen; isSidebarOpen = true" class="group"
                    icon="language_FILL0_wght400_GRAD0_opsz24.png" :text="$t('sidebar.lang')" :show-text="isSidebarOpen"
                >
                    <template v-slot:icon>
                        <svg xmlns="http://www.w3.org/2000/svg" :height="iconSize.height" viewBox="0 -960 960 960"
                             :width="iconSize.width">
                            <path :fill="highContrast ? '#FFFF00FF' : iconColor" :class="{'group-hover:fill-black':highContrast}"
                                  d="M480-80q-82 0-155-31.5t-127.5-86Q143-252 111.5-325T80-480q0-83 31.5-155.5t86-127Q252-817 325-848.5T480-880q83 0 155.5 31.5t127 86q54.5 54.5 86 127T880-480q0 82-31.5 155t-86 127.5q-54.5 54.5-127 86T480-80Zm0-82q26-36 45-75t31-83H404q12 44 31 83t45 75Zm-104-16q-18-33-31.5-68.5T322-320H204q29 50 72.5 87t99.5 55Zm208 0q56-18 99.5-55t72.5-87H638q-9 38-22.5 73.5T584-178ZM170-400h136q-3-20-4.5-39.5T300-480q0-21 1.5-40.5T306-560H170q-5 20-7.5 39.5T160-480q0 21 2.5 40.5T170-400Zm216 0h188q3-20 4.5-39.5T580-480q0-21-1.5-40.5T574-560H386q-3 20-4.5 39.5T380-480q0 21 1.5 40.5T386-400Zm268 0h136q5-20 7.5-39.5T800-480q0-21-2.5-40.5T790-560H654q3 20 4.5 39.5T660-480q0 21-1.5 40.5T654-400Zm-16-240h118q-29-50-72.5-87T584-782q18 33 31.5 68.5T638-640Zm-234 0h152q-12-44-31-83t-45-75q-26 36-45 75t-31 83Zm-200 0h118q9-38 22.5-73.5T376-782q-56 18-99.5 55T204-640Z"/>
                        </svg>
                    </template>

                </SidebarRow>
                <ul v-if="isLangMenuOpen" class="w-[80%]">
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
            </div>

            <hr
                :class="{'high-contrast-input':highContrast}"
                class="border-gray-500 mb-2 border-1 mx-2">
            <!-- bottom options -->
            <div id="bottom" class="flex-none">
                <SidebarRow icon="contrast_FILL0_wght400_GRAD0_opsz24.png" :text="$t('sidebar.contrast')"
                            :show-text="isSidebarOpen" @click="changeContrast" class="group">
                    <template v-slot:icon>
                        <svg xmlns="http://www.w3.org/2000/svg" :height="iconSize.height" viewBox="0 -960 960 960"
                             :width="iconSize.width">
                            <path :fill="highContrast ? '#FFFF00FF' : iconColor" :class="{'group-hover:fill-black':highContrast}"
                                  d="M480-80q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Zm40-83q119-15 199.5-104.5T800-480q0-123-80.5-212.5T520-797v634Z"/>
                        </svg>
                    </template>
                </SidebarRow>
                <div class="relative flex justify-center" v-if="page.props.auth.user">
                    <div class="absolute w-[98%] bg-[#171515] translate-y-[-105%] rounded-lg" v-if="isUserMenuOpen"
                         @click="isUserMenuOpen = !isUserMenuOpen">
                        <Link href="/dashboard">
                            <div
                                :class="{'high-contrast-button': highContrast}"
                                class="border-b py-2 hover:bg-red-500 rounded-t-lg">
                                <p class="pl-2">Profile</p>
                            </div>
                        </Link>
                        <Link href="/logout" method="post">
                            <div
                                :class="{'high-contrast-button': highContrast}"
                                class="py-2 hover:bg-red-500 rounded-b-lg">
                                <p class="pl-2">Log out</p>
                            </div>
                        </Link>
                    </div>
                    <SidebarRow icon="account_circle_FILL0_wght400_GRAD0_opsz24.png" class="group"
                                :text="page.props.auth.user.nickname"
                                :show-text="isSidebarOpen" @click="isUserMenuOpen = !isUserMenuOpen; isSidebarOpen = true">
                        <template v-slot:icon>
                            <svg xmlns="http://www.w3.org/2000/svg" :height="iconSize.height" viewBox="0 -960 960 960"
                                 :width="iconSize.width">
                                <path :fill="highContrast ? '#FFFF00FF' : iconColor" :class="{'group-hover:fill-black':highContrast}"
                                      d="M234-276q51-39 114-61.5T480-360q69 0 132 22.5T726-276q35-41 54.5-93T800-480q0-133-93.5-226.5T480-800q-133 0-226.5 93.5T160-480q0 59 19.5 111t54.5 93Zm246-164q-59 0-99.5-40.5T340-580q0-59 40.5-99.5T480-720q59 0 99.5 40.5T620-580q0 59-40.5 99.5T480-440Zm0 360q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Zm0-80q53 0 100-15.5t86-44.5q-39-29-86-44.5T480-280q-53 0-100 15.5T294-220q39 29 86 44.5T480-160Zm0-360q26 0 43-17t17-43q0-26-17-43t-43-17q-26 0-43 17t-17 43q0 26 17 43t43 17Zm0-60Zm0 360Z"/>
                            </svg>
                        </template>
                    </SidebarRow>
                </div>

                <div
                    class="relative flex justify-center" v-else>
                    <div class="absolute w-[98%] bg-[#171515] translate-y-[-105%] rounded-lg" v-if="isUserMenuOpen"
                         @click="isUserMenuOpen = !isUserMenuOpen">

                        <Link href="/register">
                            <div
                                :class="{'high-contrast-button': highContrast}"
                                class="border-b py-2 hover:bg-red-500 rounded-t-lg">
                                <p class="pl-2">Register</p>
                            </div>
                        </Link>

                        <Link href="/login">
                            <div
                                :class="{'high-contrast-button': highContrast}"
                                class="py-2 hover:bg-red-500 rounded-b-lg">
                                <p class="pl-2">Login</p>
                            </div>
                        </Link>
                    </div>
                    <SidebarRow icon="account_circle_FILL0_wght400_GRAD0_opsz24.png" :text="$t('sidebar.user')"
                                :show-text="isSidebarOpen" class="group"
                                @click="isUserMenuOpen = !isUserMenuOpen;isSidebarOpen = true">
                        <template v-slot:icon>
                            <svg xmlns="http://www.w3.org/2000/svg" :height="iconSize.height" viewBox="0 -960 960 960"
                                 :width="iconSize.width">
                                <path :fill="highContrast ? '#FFFF00FF' : iconColor" :class="{'group-hover:fill-black':highContrast}"
                                      d="M234-276q51-39 114-61.5T480-360q69 0 132 22.5T726-276q35-41 54.5-93T800-480q0-133-93.5-226.5T480-800q-133 0-226.5 93.5T160-480q0 59 19.5 111t54.5 93Zm246-164q-59 0-99.5-40.5T340-580q0-59 40.5-99.5T480-720q59 0 99.5 40.5T620-580q0 59-40.5 99.5T480-440Zm0 360q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Zm0-80q53 0 100-15.5t86-44.5q-39-29-86-44.5T480-280q-53 0-100 15.5T294-220q39 29 86 44.5T480-160Zm0-360q26 0 43-17t17-43q0-26-17-43t-43-17q-26 0-43 17t-17 43q0 26 17 43t43 17Zm0-60Zm0 360Z"/>
                            </svg>
                        </template>
                    </SidebarRow>
                </div>
            </div>

        </div>


        <!-- burger icon for small screens -->
        <span class="absolute text-white text-4xl top-5 left-4 cursor-pointer" @click="openSidebar" v-if="!isSidebarOpen && isSmall">
                <svg xmlns="http://www.w3.org/2000/svg" :height="iconSize.height" viewBox="0 -960 960 960"
                     :width="iconSize.width" class="px-2 bg-gray-900 rounded-md">
                        <path :fill="highContrast ? '#FFFF00FF' : iconColor"
                              d="M160-240q-17 0-28.5-11.5T120-280q0-17 11.5-28.5T160-320h640q17 0 28.5 11.5T840-280q0 17-11.5 28.5T800-240H160Zm0-200q-17 0-28.5-11.5T120-480q0-17 11.5-28.5T160-520h640q17 0 28.5 11.5T840-480q0 17-11.5 28.5T800-440H160Zm0-200q-17 0-28.5-11.5T120-680q0-17 11.5-28.5T160-720h640q17 0 28.5 11.5T840-680q0 17-11.5 28.5T800-640H160Z"/>
                    </svg>
        </span>
        <!-- main content -->
        <div
            :class="{'bg-black': highContrast}"
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

</style>
