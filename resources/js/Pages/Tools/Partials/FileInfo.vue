<script setup>
    import {usePage} from "@inertiajs/vue3";
    import {useStore} from "vuex";

    const props = defineProps({
        fileName: {
            required: true,
            type: String
        },
        fileSize: {
            required: true,
            type: Number
        }
    })

    const store = useStore()
</script>

<template>
    <div class="my-4">
        <p
            :class="{'high-contrast1': store.state.highContrast }"
            class="text-lg">{{ $t("tools.fileName") }} {{ fileName }}</p>
        <p
            :class="{'high-contrast2': store.state.highContrast }"
            class="text-sm">{{ $t("tools.fileSize") }} {{ (fileSize / 1024 / 1024).toFixed(2) }} MB</p>
    </div>
</template>

<style scoped>
.high-contrast1 {
    @apply text-2xl font-bold bg-black text-[#FFFF00FF]

}

.high-contrast2 {
    @apply text-xl font-bold bg-black text-[#FFFF00FF]

}
</style>
