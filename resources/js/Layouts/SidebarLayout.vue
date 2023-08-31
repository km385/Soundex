<script setup>

import {ref} from "vue";
import {Link} from '@inertiajs/vue3';
import NavLink from "@/Components/NavLink.vue";
import SidebarRow from "@/Components/SidebarRow.vue";

const isSidebarCollapsed = ref(false)

function onClick(e) {
    isSidebarCollapsed.value = !isSidebarCollapsed.value
}

const isToolsPickerExpanded = ref(false)
const toolsMenu = {
    expandTools: () => {
        console.log('expand tools')
        if(!isSidebarCollapsed.value) {
            isToolsPickerExpanded.value = true
        }
    },
    collapseTools: () => {
        console.log('collapse tools')
        isToolsPickerExpanded.value = false
    },
    mouseOverToolsEnter: () => {
        console.log('mouse enter')
        isToolsPickerExpanded.value = true
    },
    mouseOverToolsLeave: () => {
        console.log('mouse left')
        isToolsPickerExpanded.value = false
    },

}

const isLangPickerExpanded = ref(false)
const langMenu = {
    expandLang: () => {
        console.log('expand tools')
        if(!isSidebarCollapsed.value) {
            isLangPickerExpanded.value = true
        }
    },
    collapseLang: () => {
        console.log('collapse tools')
        isLangPickerExpanded.value = false
    },
    mouseOverLangEnter: () => {
        console.log('mouse enter')
        isLangPickerExpanded.value = true
    },
    mouseOverLangLeave: () => {
        console.log('mouse left')
        isLangPickerExpanded.value = false
    },

}


</script>

<template>
    <div class="">
        <div :class="{'w-48 transition-w duration-500': !isSidebarCollapsed, 'w-20 transition-w duration-500': isSidebarCollapsed}"
             class="h-screen bg-[#2D2D30] text-white flex flex-col fixed top-0 left-0 ">
            <div class="flex mr-2 flex-none cursor-pointer"
                 :class="{'justify-center' : isSidebarCollapsed, 'justify-end ' : !isSidebarCollapsed}">
                <!--                burger icon-->
                <img src="../../images/menu_FILL0_wght400_GRAD0_opsz24.png" v-on:click="onClick" class="w-14">
            </div>

            <div id="tools" class="flex flex-col items-center grow">
                <!--                tools links section-->
                <Link href="/" class="flex items-center w-full">
                    <SidebarRow icon="home_FILL0_wght400_GRAD0_opsz24.png" text="home" :show-text="isSidebarCollapsed"/>
                </Link>
                <!--                    </Link>-->

                <SidebarRow icon="construction_FILL0_wght400_GRAD0_opsz24.png" text="tools"
                            :show-text="isSidebarCollapsed"
                            @mouseenter="toolsMenu.expandTools" @mouseleave="toolsMenu.collapseTools">
                    <div v-if="isToolsPickerExpanded"
                         class="w-48 bg-[#2D2D30] text-white absolute left-48 top-0 text-xl"
                         @mouseenter="toolsMenu.mouseOverToolsEnter" @mouseleave="toolsMenu.mouseOverToolsLeave">
                        <!--            expanded tools-->
                        <ul>
                            <Link href="/cutter">
                                <li class="hover:bg-gray-500 cursor-pointer">
                                    Cutter
                                </li>
                            </Link>


                            <Link href="/metachange">
                                <li class="hover:bg-gray-500 cursor-pointer">
                                    Change metadata
                                </li>
                            </Link>


                            <Link href="/speedup">
                                <li class="hover:bg-gray-500 cursor-pointer">
                                    SpeedUp
                                </li>
                            </Link>


                            <Link href="/merge">
                                <li class="hover:bg-gray-500 cursor-pointer">
                                    Merge
                                </li>
                            </Link>

                            <Link href="/recorder">
                                <li class="hover:bg-gray-500 cursor-pointer">
                                    Recorder
                                </li>
                            </Link>


                            <Link href="/layermixer">
                                <li class="hover:bg-gray-500 cursor-pointer">
                                    LayerMix
                                </li>
                            </Link>

                        </ul>
                    </div>
                </SidebarRow>
                <Link href="#" class="flex items-center w-full">
                    <SidebarRow icon="folder_open_FILL0_wght400_GRAD0_opsz24.png" text="files" :show-text="isSidebarCollapsed"/>
                </Link>

                <SidebarRow icon="help_FILL0_wght400_GRAD0_opsz24.png" text="help" :show-text="isSidebarCollapsed"/>
                <SidebarRow icon="language_FILL0_wght400_GRAD0_opsz24.png" text="lang" :show-text="isSidebarCollapsed"
                            @mouseenter="langMenu.expandLang" @mouseleave="langMenu.collapseLang">
                    <div v-if="isLangPickerExpanded"
                         class="w-48 bg-[#2D2D30] text-white absolute left-48 top-0 text-xl"
                         @mouseenter="langMenu.mouseOverLangEnter" @mouseleave="langMenu.mouseOverLangLeave">
                        <!--            expanded tools-->
                        <ul>
                            <Link href="#">
                                <li class="hover:bg-gray-500 cursor-pointer">
                                    English
                                </li>
                            </Link>


                            <Link href="#">
                                <li class="hover:bg-gray-500 cursor-pointer">
                                    Polish
                                </li>
                            </Link>


                        </ul>
                    </div>
                </SidebarRow>

            </div>


            <div id="bottom" class="flex-none">
                <!--                user links section-->
                <SidebarRow icon="contrast_FILL0_wght400_GRAD0_opsz24.png" text="contrast" :show-text="isSidebarCollapsed"/>
                <SidebarRow icon="account_circle_FILL0_wght400_GRAD0_opsz24.png" text="user" :show-text="isSidebarCollapsed"/>
            </div>

        </div>


        <div class="max-w-3xl mx-auto">
            <slot/>
        </div>

    </div>

</template>

<style scoped>

</style>
