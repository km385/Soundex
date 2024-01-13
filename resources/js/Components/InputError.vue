<script setup>
import {inject} from "vue";

defineProps({
    message: {
        type: String,
    },
});

const highContrast = inject('highContrast')
</script>

<template>
    <div v-show="message">
        <p :class="{'high-contrast':highContrast}" class="text-sm text-red-600">
            {{ message }}
        </p>
    </div>
</template>

<style scoped>
.high-contrast {
    @apply text-yellow-300
}
</style>
