<script setup>

import { ref, provide} from "vue";
import { Link, usePage } from '@inertiajs/vue3';
//import NavLink from "@/Components/NavLink.vue";
import SidebarRow from "@/Components/SidebarRow.vue";
import LoadingScreen from "@/Pages/Tools/Partials/LoadingScreen.vue";

const page = usePage()

const isSidebarCollapsed = ref(false);
const onClick = () => isSidebarCollapsed.value = !isSidebarCollapsed.value;


const isToolsPickerExpanded = ref(false)
const toolsMenu = {
    expandTools: () => {
        if (!isSidebarCollapsed.value) {
            isToolsPickerExpanded.value = true
        }
    },
    collapseTools: () => {
        isToolsPickerExpanded.value = false
    },
    mouseOverToolsEnter: () => {
        isToolsPickerExpanded.value = true
    },
    mouseOverToolsLeave: () => {
        isToolsPickerExpanded.value = false
    },

}

const isLangPickerExpanded = ref(false)
const langMenu = {
    expandLang: () => {
        if (!isSidebarCollapsed.value) {
            isLangPickerExpanded.value = true
        }
    },
    collapseLang: () => {
        isLangPickerExpanded.value = false
    },
    mouseOverLangEnter: () => {
        isLangPickerExpanded.value = true
    },
    mouseOverLangLeave: () => {
        isLangPickerExpanded.value = false
    },

}

const userDropDown = ref(false)
provide('isSidebarCollapsed', isSidebarCollapsed);
</script>

<template>
    <div :class="{ 'w-48 transition-w duration-500': !isSidebarCollapsed, 'w-20 transition-w duration-500': isSidebarCollapsed }"
        class="bg-[#2B2B2B] text-white flex flex-col fixed h-full">
        <div class="flex mr-2 flex-none"
            :class="{ 'justify-center': isSidebarCollapsed, 'justify-end ': !isSidebarCollapsed }">
            <!--                burger icon-->
            <img src="../../images/menu_FILL0_wght400_GRAD0_opsz24.png" v-on:click="onClick" class="w-14 cursor-pointer"
                alt="burger icon">
        </div>

        <div id="tools" class="flex flex-col items-center grow">
            <!--                tools links section-->
            <Link href="/" class="flex items-center w-full">
            <SidebarRow icon="home_FILL0_wght400_GRAD0_opsz24.png" text="home" :show-text="isSidebarCollapsed" />
            </Link>

            <SidebarRow icon="construction_FILL0_wght400_GRAD0_opsz24.png" text="tools" :show-text="isSidebarCollapsed"
                @mouseenter="toolsMenu.expandTools" @mouseleave="toolsMenu.collapseTools">
                <div v-if="isToolsPickerExpanded"
                    class="w-48 bg-[#2D2D30] text-white absolute left-48 top-0 text-xl rounded-lg text-center"
                    @mouseenter="toolsMenu.mouseOverToolsEnter" @mouseleave="toolsMenu.mouseOverToolsLeave">
                    <!--            expanded tools-->
                    <ul>
                        <Link href="/tools/cutter">
                        <li class="hover:bg-gray-500 cursor-pointer rounded-t-lg py-1">
                            Cutter
                        </li>
                        </Link>


                        <Link href="/tools/tageditor">
                        <li class="hover:bg-gray-500 cursor-pointer py-1">
                            Tag Editor
                        </li>
                        </Link>


                        <Link href="/tools/speedup">
                        <li class="hover:bg-gray-500 cursor-pointer py-1">
                            SpeedUp
                        </li>
                        </Link>


                        <Link href="/tools/merge">
                        <li class="hover:bg-gray-500 cursor-pointer py-1">
                            Merge
                        </li>
                        </Link>

                        <Link href="/tools/recorder">
                        <li class="hover:bg-gray-500 cursor-pointerpy-1">
                            Recorder
                        </li>
                        </Link>


                        <Link href="/tools/layermixer">
                        <li class="hover:bg-gray-500 cursor-pointer rounded-b-lg py-1">
                            LayerMix
                        </li>
                        </Link>

                        <Link href="/tools/bpmFinder">
                        <li class="hover:bg-gray-500 cursor-pointer rounded-b-lg py-1">
                            BPM Finder
                        </li>
                        </Link>

                    </ul>
                </div>
            </SidebarRow>

            <Link href="/database" class="flex items-center w-full">
            <SidebarRow icon="folder_open_FILL0_wght400_GRAD0_opsz24.png" text="files" :show-text="isSidebarCollapsed" />
            </Link>

            <SidebarRow icon="help_FILL0_wght400_GRAD0_opsz24.png" text="help" :show-text="isSidebarCollapsed" />

            <SidebarRow icon="language_FILL0_wght400_GRAD0_opsz24.png" text="lang" :show-text="isSidebarCollapsed"
                @mouseenter="langMenu.expandLang" @mouseleave="langMenu.collapseLang">
                <div v-if="isLangPickerExpanded"
                    class="w-48 bg-[#2D2D30] text-white absolute left-48 top-0 text-xl rounded-lg text-center"
                    @mouseenter="langMenu.mouseOverLangEnter" @mouseleave="langMenu.mouseOverLangLeave">
                    <!--            expanded tools-->
                    <ul>
                        <Link href="#">
                        <li class="hover:bg-gray-500 cursor-pointer rounded-t-lg">
                            English
                        </li>
                        </Link>


                        <Link href="#">
                        <li class="hover:bg-gray-500 cursor-pointer rounded-b-lg">
                            Polish
                        </li>
                        </Link>


                    </ul>
                </div>
            </SidebarRow>

        </div>

        <hr class="border-gray-500 mb-2 border-1 mx-2">
        <div id="bottom" class="flex-none">
            <!--                user links section-->
            <SidebarRow icon="contrast_FILL0_wght400_GRAD0_opsz24.png" text="contrast" :show-text="isSidebarCollapsed" />
            <div class="relative flex justify-center" v-if="page.props.auth.user">
                <div class="absolute w-[98%] bg-[#171515] translate-y-[-105%] rounded-lg" v-if="userDropDown"
                    @click="userDropDown = !userDropDown">
                    <Link href="dashboard">
                    <div class="border-b py-2 hover:bg-red-500 rounded-t-lg">
                        <p class="pl-2">Profile</p>
                    </div>
                    </Link>
                    <Link href="logout" method="post">
                    <div class="py-2 hover:bg-red-500 rounded-b-lg">
                        <p class="pl-2">Log out</p>
                    </div>
                    </Link>
                </div>
                <SidebarRow icon="account_circle_FILL0_wght400_GRAD0_opsz24.png" :text="page.props.auth.user.nickname"
                    :show-text="isSidebarCollapsed" @click="userDropDown = !userDropDown" />
            </div>

            <div class="relative flex justify-center" v-else>
                <div class="absolute w-[98%] bg-[#171515] translate-y-[-105%] rounded-lg" v-if="userDropDown"
                    @click="userDropDown = !userDropDown">

                    <Link href="register">
                    <div class="border-b py-2 hover:bg-red-500 rounded-t-lg">
                        <p class="pl-2">Register</p>
                    </div>
                    </Link>

                    <Link href="login">
                    <div class="py-2 hover:bg-red-500 rounded-b-lg">
                        <p class="pl-2">Login</p>
                    </div>
                    </Link>
                </div>
                <SidebarRow icon="account_circle_FILL0_wght400_GRAD0_opsz24.png" text="User" :show-text="isSidebarCollapsed"
                    @click="userDropDown = !userDropDown" />
            </div>
        </div>

    </div>

    <!--    Background color set in app.blade.php-->
    <div
        :class="{ 'ml-48 transition-w duration-500': !isSidebarCollapsed, 'ml-20 transition-w duration-500': isSidebarCollapsed }">
        <div class="flex justify-center h-screen">
            <div class="w-screen">
                <slot />
            </div>
        </div>
    </div>
</template>

