<script setup>
import {inject} from "vue";

const highContrast = inject('highContrast')
</script>

<template>
    <button
        :class="{'high-contrast':highContrast}"
        class="inline-flex items-center px-4 py-2 bg-blue-400 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-300 focus:bg-blue-300 active:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
    >
        <slot />
    </button>
</template>

<style scoped>
.high-contrast {
    @apply border border-[#FFFF00FF] bg-black text-[#FFFF00FF] hover:bg-[#FFFF00FF] hover:text-black
}
</style>
