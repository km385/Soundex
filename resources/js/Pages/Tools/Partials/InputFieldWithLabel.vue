<script setup>
import {ref} from "vue";

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

</script>

<template>
    <div class="mb-6">
        <label :for="label" class="block mb-2 uppercase font-bold text-xs text-white">
            {{ label }}
        </label>
        <input :id="label"
               class="bg-gray-500 text-white border border-gray-500 rounded-lg focus:border-blue-500 focus:ring-blue-500 w-full"
               name="title"
               :type="type"
               ref="input"
               @input="emit('update:modelValue', $event.target.value)"
        >

    </div>
</template>

<style scoped>

</style>
