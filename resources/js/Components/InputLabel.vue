<script setup>
import {inject} from "vue";

defineProps({
    value: {
        type: String,
    },
});

const highContrast = inject('highContrast')
</script>

<template>
    <label :class="{'high-contrast-text':highContrast}" class="block font-medium text-sm text-white">
        <span v-if="value">{{ value }}</span>
        <span v-else><slot /></span>
    </label>
</template>

<style scoped>
.high-contrast-text {
    @apply text-[#FFFF00FF]
}
</style>
