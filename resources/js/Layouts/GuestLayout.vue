<script setup>
import ApplicationLogo from './Partials/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
import {inject} from "vue";

const highContrast = inject('highContrast')
</script>

<template>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
        <div :class="{'high-contrast':highContrast}"
             class="w-full sm:max-w-md mt-6 px-6 py-4 bg-gray-800 shadow-md overflow-hidden sm:rounded-lg"
        >
            <slot />
        </div>
    </div>
</template>

<style scoped>
.high-contrast {
    @apply bg-black border border-[#FFFF00FF]
}
</style>
