<script setup>
import {inject, ref} from "vue";

const props = defineProps({
    fileLink: String
})

const error = ref("")
const isError = ref(false)

const but = ref(null)

async function onClick() {
    const formData = new FormData()
    formData.append('token', props.fileLink)


    try {
        const res = await axios.post('/savetolibrary', formData)
        console.log(res)

    } catch (e) {
        if (e.response.data.message === "already saved") {
            error.value = "the file is already saved"
        } else if(e.response.data.message === "no space left") {
            error.value = "error - no space left"
        } else {
            error.value = "an error occurred"
        }
        isError.value = true
        but.value.disabled = true
    }

}

const highContrast = inject('highContrast')
</script>

<template>
    <button type="button" id="saveButton" ref="but" @click="onClick"
            class="bg-blue-400 text-white rounded py-2 px-4 mt-5 mr-3 hover:bg-blue-500"
            :class="{ 'bg-blue-400': !isError, 'bg-red-400 hover:bg-red-400': isError, 'high-contrast-button':highContrast }">
        {{ $t("resultOptionsScreen.saveToLibrary") }}
    </button>
    <div v-if="error" :class="{ 'text-green-500': !isError, 'text-red-500': isError }">
        {{ error }}
    </div>
</template>

<style scoped>
.high-contrast-button {
    @apply text-xl border border-[#FFFF00FF] bg-black text-[#FFFF00FF] focus:border-[#FFFF00FF] focus:ring-[#FFFF00FF] hover:bg-yellow-300 hover:text-black
}

.high-contrast-input {
    @apply text-xl border border-[#FFFF00FF] bg-black text-[#FFFF00FF]
}
</style>
