<script setup>

import {ref} from "vue";
import {Link} from '@inertiajs/vue3';
import NavLink from "@/Components/NavLink.vue";
import SidebarRow from "@/Components/SidebarRow.vue";

const isCollapsed = ref(false)
const isToolsExpanded = ref(false)

function onClick(e) {
    isCollapsed.value = !isCollapsed.value
}

function expandTools() {
    console.log('expland tools')

    isToolsExpanded.value = true

}

function collapseTools() {
    console.log('collapse tools')
    isToolsExpanded.value = false
}

function mouseOverToolsEnter() {
    console.log('mouse enter')
    isToolsExpanded.value = true
}

function mouseOverToolsLeave() {
    console.log('mouse left')
    isToolsExpanded.value = false
}
</script>

<template>
    <div class="flex ">
        <div :class="{'w-48 transition-w duration-500': !isCollapsed, 'w-20 transition-w duration-500': isCollapsed}"
             class="h-screen bg-[#2D2D30] text-white flex flex-col fixed top-0 left-0 ">
            <div class="flex mr-2 flex-none cursor-pointer"
                 :class="{'justify-center' : isCollapsed, 'justify-end ' : !isCollapsed}">
                <!--                burger icon-->
                <img src="../../images/menu_FILL0_wght400_GRAD0_opsz24.png" v-on:click="onClick" class="w-14">
            </div>

            <div id="tools" class="flex flex-col items-center grow">
                <!--                tools links section-->
                <Link href="/" class="flex items-center w-full">
                    <SidebarRow icon="home_FILL0_wght400_GRAD0_opsz24.png" text="home" :show-text="isCollapsed"/>
                </Link>
                <!--                    </Link>-->

                <SidebarRow icon="construction_FILL0_wght400_GRAD0_opsz24.png" text="tools" :show-text="isCollapsed"
                            @mouseenter="expandTools" @mouseleave="collapseTools"/>
                <Link href="#" class="flex items-center w-full">
                    <SidebarRow icon="folder_open_FILL0_wght400_GRAD0_opsz24.png" text="files" :show-text="isCollapsed"/>
                </Link>

                <SidebarRow icon="help_FILL0_wght400_GRAD0_opsz24.png" text="help" :show-text="isCollapsed"/>
                <SidebarRow icon="language_FILL0_wght400_GRAD0_opsz24.png" text="lang" :show-text="isCollapsed"/>

            </div>


            <div id="bottom" class="flex-none">
                <!--                user links section-->
                <SidebarRow icon="contrast_FILL0_wght400_GRAD0_opsz24.png" text="contrast" :show-text="isCollapsed"/>
                <SidebarRow icon="account_circle_FILL0_wght400_GRAD0_opsz24.png" text="user" :show-text="isCollapsed"/>
            </div>

        </div>

        <div v-if="isToolsExpanded" class="w-48 bg-[#2D2D30] text-white absolute left-48 top-20 text-xl"
             @mouseenter="mouseOverToolsEnter" @mouseleave="mouseOverToolsLeave">
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

        <div class="max-w-3xl mx-auto">
            <slot/>
        </div>

    </div>

</template>

<style scoped>

</style>
