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
        <label for="title" class="block mb-2 uppercase font-bold text-xs text-gray-700">
            {{ label }}
        </label>
        <input id="title"
               class="border border-gray-400 p-2 w-full"
               name="title"
               type="text"
               ref="input"
               @input="emit('update:modelValue', $event.target.value)"
        >

    </div>
</template>

<style scoped>

</style>
