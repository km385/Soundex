<script setup>
import {inject, onMounted, ref, watch} from "vue";

const extension = ref("mp3")
const emit = defineEmits(['extension'])
const highContrast = inject('highContrast')

watch(extension, () => {
    emit('extension', extension.value)
})

onMounted(() => {
    emit('extension', extension.value)
})
</script>

<template>
    <label for="selectExtension"
           :class="{'text-[#ffff00]': highContrast }">{{ $t('converter.changeExtension') }}</label>
    <select id="selectExtension" required
            :class="{'high-contrast-select': highContrast }"
            class="text-black ml-2" v-model="extension">
        <option selected value="mp3">Mp3</option>
        <option value="flac">Flac</option>
    </select>
</template>

<style scoped>

.high-contrast-select {
    /* High-contrast styles go here */
    background-color: black;
    color: yellow;
    border: 2px solid yellow;
    @apply text-2xl
}

</style>
