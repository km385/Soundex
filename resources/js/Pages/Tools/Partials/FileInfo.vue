<script setup>
    import {usePage} from "@inertiajs/vue3";

    const props = defineProps({
        fileName: {
            required: true,
            type: String
        },
        fileSize: {
            required: true,
            type: String
        }
    })
</script>

<template>
    <div class="my-4">
        <p class="text-lg">{{ $t("tools.fileName") }} {{ fileName }}</p>
        <p class="text-sm">{{ $t("tools.fileSize") }} {{ (fileSize / 1024 / 1024).toFixed(2) }} MB</p>
    </div>
</template>

<style scoped>

</style>
