<script setup>
import {Head, Link, usePage} from '@inertiajs/vue3';
import DoughnutChart from "@/Pages/Profile/Partials/DoughnutChart.vue";
import SvgComp from "@/Components/SvgComp.vue";
import {computed, inject, onMounted} from "vue";
import ToolsUseChart from "@/Pages/Profile/Partials/ToolsUseChart.vue";
import NumberOfFilesChart from "@/Pages/Profile/Partials/NumberOfFilesChart.vue";

const page = usePage()

const userDate = computed(() => {
    const timestamp = page.props.auth.user.created_at
    const dateObject = new Date(timestamp);

    const year = dateObject.getFullYear();
    const month = (dateObject.getMonth() + 1).toString().padStart(2, '0'); // Months are zero-indexed
    const day = dateObject.getDate().toString().padStart(2, '0');

    return `${day} ${month} ${year}`;

})

const highContrast = inject('highContrast')
</script>

<template>
    <Head title="Dashboard"/>

    <div
        :class="{'high-contrast-bg':highContrast}"
        class="py-10">
        <div
            :class="{'high-contrast-input': highContrast}"
            class="max-w-3xl mx-auto rounded-lg bg-gray-800 w-[90%] flex justify-center shadow-xl mt-10">
            <div
                :class="{'high-contrast-text':highContrast}"
                class="flex rounded-lg px-4 w-full py-4 text-white">
                <div class="mx-2">
                    <SvgComp name="user" :class="{'high-contrast-border':highContrast}" class="w-20 lg:w-40 border-2 border-white"/>
                </div>
                <div class=" flex flex-col pl-1 grow truncate">
                    <div class="text-6xl font-bold">
                        {{ page.props.auth.user.nickname }}
                    </div>
                    <div class="">
                        {{ $t('dashboard.joined')}} {{ userDate }}
                    </div>
                </div>
                <div class="">
                    <Link :href="route('profile.edit')">
                        <SvgComp name="gear" class="w-10 cursor-pointer hover:rotate-90 duration-500"/>
                    </Link>
                </div>
            </div>
        </div>
        <div
            :class="{'high-contrast-input':highContrast}"
            class="mx-auto max-w-3xl w-[90%] py-5 mt-20 lg:mt-10 flex flex-col lg:flex-row bg-gray-800 rounded-lg items-center justify-around px-10 shadow-xl">


            <div>
                <DoughnutChart class="pb-5 "/>
                <p :class="{'high-contrast-text':highContrast}" class="text-white text-center">
                    {{ (page.props.auth.user.storage_used / 1024).toFixed(3) }} MB / 200MB {{ $t('dashboard.space') }}</p>
            </div>
            <div class='lg:ml-5 mt-5 lg:mt-0'>
                <NumberOfFilesChart class=" pb-5"/>
                <p :class="{'high-contrast-text':highContrast}" class="text-white text-center">
                    {{ page.props.auth.user.files_stored }} / 50 {{ $t('dashboard.files') }}</p>

            </div>

        </div>

        <div
            :class="{'high-contrast-input':highContrast}"
            class="mx-auto max-w-3xl w-[90%] mt-20 mb-10 lg:mt-10 flex bg-gray-800 rounded-lg items-center px-10 justify-center shadow-xl">
            <div class="py-10 w-full my-10 ">
                <ToolsUseChart />
            </div>


        </div>
    </div>

</template>

<style scoped>

.high-contrast-bg {
    @apply bg-black
}

.high-contrast-text {
    @apply text-[#FFFF00FF]
}

.high-contrast-border {
    @apply border-[#FFFF00FF]
}

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
