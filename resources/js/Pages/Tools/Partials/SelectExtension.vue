<script setup>
import {onMounted, ref, watch} from "vue";

    const extension = ref("mp3")
    const emit = defineEmits(['extension'])

    watch(extension, () => {
        emit('extension', extension.value)
    })

    onMounted(() => {
        emit('extension', extension.value)
    })
</script>

<template>
    <label for="selectExtension" class="text-white">{{ $t('converter.changeExtension') }}</label>
    <select id="selectExtension" required class="text-black ml-2" v-model="extension">
        <option selected value="mp3">Mp3</option>
        <option value="flac">Flac</option>
    </select>
</template>

<style scoped>

</style>
