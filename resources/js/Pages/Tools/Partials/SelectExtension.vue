<script setup>
import {onMounted, ref, watch} from "vue";
import {useStore} from "vuex";

const extension = ref("mp3")
const emit = defineEmits(['extension'])
const store = useStore()

watch(extension, () => {
    emit('extension', extension.value)
})

onMounted(() => {
    emit('extension', extension.value)
})
</script>

<template>
    <label for="selectExtension"
           :class="{'text-[#ffff00]': store.state.highContrast }">{{ $t('converter.changeExtension') }}</label>
    <select id="selectExtension" required
            :class="{'high-contrast-select': store.state.highContrast }"
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
