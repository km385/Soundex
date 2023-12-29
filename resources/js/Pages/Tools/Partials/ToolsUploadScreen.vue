<script setup>
import UploadFile from "@/Pages/Tools/Partials/UploadFile.vue";
import {inject} from "vue";

const emits = defineEmits(['file'])

const props = defineProps({
    title: {
        required: true,
        type: String,
    },
    description: {
        required: true,
        type: String,
    },
    allowVideo: {
        required: false,
        type: Boolean,
        default: false
    }

})

function passFileToParent(file) {
    emits('file', file)
}

const highContrast = inject('highContrast')
</script>

<template>
    <div class="flex flex-col flex-grow justify-center items-center">
        <!-- Upload File Section -->
        <div
            :class="{'high-contrast-text':highContrast}"
            class="mb-5 text-center">
            <p class="text-5xl font-bold mb-2">{{ props.title }}</p>
            <p class="text-3xl">{{ props.description }}</p>
        </div>
        <UploadFile @file="passFileToParent" :allow-video="allowVideo"/>
    </div>
</template>

<style scoped>
.high-contrast-text {
    @apply text-xl bg-black text-[#FFFF00FF]
}
.high-contrast-button {
    @apply text-xl border border-[#FFFF00FF] bg-black text-[#FFFF00FF] focus:border-[#FFFF00FF] focus:ring-[#FFFF00FF] hover:bg-yellow-300 hover:text-black
}

.high-contrast-button-selected {
    @apply bg-yellow-300 text-black
}
</style>
