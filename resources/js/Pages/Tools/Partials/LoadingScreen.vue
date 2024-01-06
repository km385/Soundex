<script setup>
import SvgComp from "@/Components/SvgComp.vue";
import {inject} from "vue";

const highContrast = inject('highContrast')
</script>

<template>

    <div :class="{'high-contrast':highContrast}" class="bg-gray-800 flex justify-center items-center h-screen z-50">
        <SvgComp name="loading" class="w-[200px] h-[200px] icon-path"/>
    </div>
</template>

<style scoped>
@keyframes rotateIcon {
    from {
        transform: rotate(0deg);
        transform-origin: center center;
    }
    to {
        transform: rotate(360deg);
        transform-origin: center center;
    }
}

.icon-path {
    animation: rotateIcon 2s linear infinite;
}

.high-contrast {
    @apply bg-black text-yellow-300
}
</style>
