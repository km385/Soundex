<script setup>
import {ref} from "vue";

const props = defineProps({
    label: {
        type: String,
        required: true
    }
})
const emit = defineEmits(['update:modelValue'])
const input = ref(null)

</script>

<template>
    <div class="mb-6">
        <label :for="label" class="block mb-2 uppercase font-bold text-xs text-gray-700">
            {{ label }}
        </label>
        <input :id="label"
               class="bg-gray-50 border border-gray-500 rounded-lg focus:border-blue-500 focus:ring-blue-500 w-full"
               name="title"
               type="text"
               ref="input"
               @input="emit('update:modelValue', $event.target.value)"
        >

    </div>
</template>

<style scoped>

</style>
