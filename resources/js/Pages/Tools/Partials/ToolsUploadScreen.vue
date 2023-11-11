<script setup>
import UploadFile from "@/Pages/Tools/Partials/UploadFile.vue";

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
</script>

<template>
    <div class="flex flex-col flex-grow justify-center items-center">
        <!-- Upload File Section -->
        <div class="mb-5 text-center">
            <p class="text-5xl font-bold mb-2">{{ props.title }}</p>
            <p class="text-3xl">{{ props.description }}</p>
        </div>
        <UploadFile @file="passFileToParent" :allow-video="allowVideo"/>
    </div>
</template>
