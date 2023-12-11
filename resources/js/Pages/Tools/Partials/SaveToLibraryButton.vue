<script setup>
import {ref} from "vue";

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
        } else {
            error.value = "an error occurred"
        }
        isError.value = true
        but.value.disabled = true
    }

}
</script>

<template>
    <button type="button" id="saveButton" ref="but" @click="onClick"
            class="bg-blue-400 text-white rounded py-2 px-4 mt-5 mr-3 hover:bg-blue-500"
            :class="{ 'bg-blue-400': !isError, 'bg-red-400 hover:bg-red-400': isError }">
        {{ $t("resultOptionsScreen.saveToLibrary") }}
    </button>
    <div v-if="error" :class="{ 'text-green-500': !isError, 'text-red-500': isError }">
        {{ error }}
    </div>
</template>

<style scoped>

</style>
