<script setup>
import {inject, ref} from "vue";

const props = defineProps({
    label: {
        type: String,
        required: true
    },
    type: {
        type: String,
        required: false,
        default: "text"
    }
})
const emit = defineEmits(['update:modelValue'])
const input = ref(null)

const highContrast = inject('highContrast')

</script>

<template>
    <div class="mb-6">
        <label :for="label"
               :class="{
               'high-contrast-label': highContrast }"
               class="block mb-2 uppercase font-bold  text-xs text-white ">
            {{ label }}
        </label>
        <input :id="label"
               :class="{ 'high-contrast-input': highContrast }"
               class="bg-gray-500 text-white border border-gray-500 rounded-lg focus:border-blue-500 focus:ring-blue-500 w-full"
               name="title"
               :type="type"
               ref="input"
               @input="emit('update:modelValue', $event.target.value)"
        >

    </div>
</template>

<style scoped>

.high-contrast-label {
    color: yellow;
    font-size: 1rem; /* 16px */
    line-height: 1.5rem; /* 24px */
}

.high-contrast-input {
    background-color: black;
    color: yellow;
    border: 2px solid yellow;
    @apply text-2xl
}

.high-contrast-input:focus {
    --tw-ring-color: yellow;
    border: 2px solid yellow;

}

</style>
