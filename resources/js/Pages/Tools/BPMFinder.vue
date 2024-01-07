<script setup>
import { usePage } from "@inertiajs/vue3";
import { v4 as uuidv4 } from "uuid";
import { inject, onMounted, ref } from "vue";
import { subToChannel, subToPrivate } from "@/subscriptions/subs.js";
import LoadingScreen from "@/Pages/Tools/Partials/LoadingScreen.vue";
import axios from "axios";
import FileInfo from "@/Pages/Tools/Partials/FileInfo.vue";
import ResultOptionsScreen from "@/Pages/Tools/Partials/ResultOptionsScreen.vue";
import ToolsUploadScreen from "@/Pages/Tools/Partials/ToolsUploadScreen.vue";
import MainToolsWindow from "@/Pages/Tools/Partials/MainToolsWindow.vue";

const page = usePage()
const guestId = page.props.auth.user ? page.props.auth.user.id : uuidv4()
const isLoading = ref(false)
const uploadedFile = ref({})
const isFileUploaded = ref(false)
const fileToDownloadLink = ref("")
const bpmArray = ref("")
const isError = ref(false)
const error = ref("")

onMounted(() => {
    if (page.props.auth.user) {
        subToPrivate(guestId, handleSubToPrivate)
    } else {
        subToChannel(guestId, handleSubToPublic)
    }
})

function handleSubToPublic(event) {
    console.log("the event has been successfully captured")
    console.log(event)

    if (event.fileName === "ERROR") {
        error.value = "error has occurred"
        isError.value = true
    } else {
        fileToDownloadLink.value = event.fileName
        bpmArray.value = event.bpmArray
    }
    isLoading.value = false
}

function handleSubToPrivate(event) {
    console.log("the event has been successfully captured")
    console.log(event)

    if (event.fileName === "ERROR") {
        error.value = "error has occurred"
        isError.value = true
    } else {
        fileToDownloadLink.value = event.fileName
        bpmArray.value = event.bpmArray

    }
    isLoading.value = false
}

async function getFile(file) {
    console.log('get file')
    uploadedFile.value = file;
    isFileUploaded.value = true
}

async function onSubmit() {
    const formData = new FormData()
    formData.append('file', uploadedFile.value)
    formData.append('guestId', guestId)

    try {
        isLoading.value = true
        const res = await axios.post('/tools/bpmFinder', formData)
        console.log(res.data.message)
    } catch (e) {
        console.log(e)
    }

}



const highContrast = inject('highContrast')

</script>


<template>
    <loading-screen v-if="isLoading" />
    <MainToolsWindow v-if="!isLoading">

        <ToolsUploadScreen v-if="!isFileUploaded" :title="$t('bpmFinder.title')" :description="$t('bpmFinder.description')"
            @file="getFile" />


        <!-- after submit -->
        <div v-if="isFileUploaded && !fileToDownloadLink" :class="{ 'high-contrast-input': highContrast }"
            class="mt-20 lg:mt-10 p-6 bg-gray-800 rounded-lg shadow-lg">


            <button type="button" @click="isFileUploaded = false" :class="{ 'high-contrast-button': highContrast }"
                class="bg-blue-400 text-white rounded py-2 px-4 hover:bg-blue-500 mb-4">{{ $t('tools.changeFile')
                }}</button>

            <FileInfo :file-size="uploadedFile.size" :file-name="uploadedFile.name" />

            <div class="mt-6">
                <button type="button" @click="onSubmit" :class="{ 'high-contrast-button': highContrast }"
                    class="bg-blue-400 text-white rounded py-2 px-4 hover:bg-blue-500">{{ $t('tools.submit') }}</button>
            </div>
        </div>
        <!--after tool-->

        <ResultOptionsScreen v-if="fileToDownloadLink" @go-back="fileToDownloadLink = ''"
            :file-to-download-link="fileToDownloadLink"
            :file-to-download-name="uploadedFile.name"
            :bpmArray="bpmArray" />

        <!-- Error Handling Section -->
        <div v-if="isError" class="text-red-500">


            <p class="p-6 bg-gray-800 rounded-lg shadow-lg">{{ error }}</p>
        </div>
    </MainToolsWindow>
</template>

<style scoped>
.high-contrast-button {
    @apply text-xl border border-[#FFFF00FF] bg-black text-[#FFFF00FF] focus:border-[#FFFF00FF] focus:ring-[#FFFF00FF] hover:bg-yellow-300 hover:text-black
}

.high-contrast-button-selected {
    @apply bg-yellow-300 text-black
}

.high-contrast-input {
    @apply text-xl border border-[#FFFF00FF] bg-black text-[#FFFF00FF]
}
</style>
