<script setup>
import {inject} from "vue";

const highContrast = inject('highContrast')
</script>

<template>
    <button :class="{'high-contrast':highContrast}"
        class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150"
    >
        <slot />
    </button>
</template>

<style scoped>
.high-contrast {
    @apply hover:text-black text-[#FFFF00FF] bg-black border border-[#FFFF00FF] hover:bg-[#FFFF00FF]
}
</style>
