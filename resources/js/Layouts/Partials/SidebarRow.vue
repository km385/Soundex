<script setup>
import {computed, inject} from "vue";
import SvgComp from "@/Components/SvgComp.vue";


    const props = defineProps({
        text: {
            type: String,
            required: true
        },
        icon: {
            type: String,
            required: true
        },
        showText: {
            type: Boolean,
            required: true
        },
    })



    const highContrast = inject('highContrast')

</script>

<template>
        <div
            :class="{'high-contrast-button':highContrast}"
            class="flex items-center h-14 w-full duration-200 hover:bg-gray-500 cursor-pointer relative rounded-lg">
            <div class="h-full mr-5 ml-2 flex items-center">
                <SvgComp :name="icon" class="w-12" />
            </div>

            <transition name="slide-fade">

                <div v-if="showText" class="flex-grow text-2xl ">
                    <p>{{text}}</p>
                </div></transition>

        </div>
</template>

<style scoped>
/* unwrap */
.slide-fade-enter-active {
    transition: all 0.5s cubic-bezier(1, 0.5, 0.8, 1);
}

/* collapse */
.slide-fade-leave-active {
    transition: all 0.1s ease-out;
}

.slide-fade-enter-from,
.slide-fade-leave-to {
    transform: translateX(-20px);
    opacity: 0;
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
