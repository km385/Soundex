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
        class="bg-gray-800 py-10">
        <div
            :class="{'high-contrast-input': highContrast}"
            class="max-w-3xl mx-auto rounded-lg bg-gray-700 px-4 shadow-xl">
            <div class="flex rounded-lg px-4 w-full pt-2">
                <div class="">
                    <SvgComp name="user" class="w-40"/>
                </div>
                <div class=" flex flex-col pl-1 grow ">
                    <div class="text-6xl font-bold ">
                        {{ page.props.auth.user.nickname }}
                    </div>
                    <div class="">
                        Joined {{ userDate }}
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
            class="mx-auto max-w-3xl mt-20 lg:mt-10 flex bg-gray-700 rounded-lg items-center justify-around px-10  shadow-xl">

            <DoughnutChart class="mt-10 w-1/3 pb-5 " />
            <NumberOfFilesChart class="mt-10 ml-5 w-1/3 pb-5"/>

        </div>

        <div
            :class="{'high-contrast-input':highContrast}"
            class="mx-auto max-w-3xl mt-20 mb-10 lg:mt-10 flex bg-gray-700 rounded-lg items-center px-10 justify-center shadow-xl">
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
