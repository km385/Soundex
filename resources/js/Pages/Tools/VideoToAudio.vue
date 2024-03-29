<script setup>

import LoadingScreen from "@/Pages/Tools/Partials/LoadingScreen.vue";
import DownloadTempFileButton from "@/Pages/Tools/Partials/DownloadTempFileButton.vue";
import SaveToLibraryButton from "@/Pages/Tools/Partials/SaveToLibraryButton.vue";
import InputFieldWithLabel from "@/Pages/Tools/Partials/InputFieldWithLabel.vue";
import UploadFile from "@/Pages/Tools/Partials/UploadFile.vue";
import SelectExtension from "@/Pages/Tools/Partials/SelectExtension.vue";
import {usePage} from "@inertiajs/vue3";
import {v4 as uuidv4} from "uuid";
import {onMounted, onUnmounted, ref} from "vue";
import {disconnectFromPrivate, disconnectFromPublic, subToChannel, subToPrivate} from "@/subscriptions/subs.js";
import axios from "axios";
import ResultOptionsScreen from "@/Pages/Tools/Partials/ResultOptionsScreen.vue";
import ToolsUploadScreen from "@/Pages/Tools/Partials/ToolsUploadScreen.vue";
import MainToolsWindow from "@/Pages/Tools/Partials/MainToolsWindow.vue";
import {useI18n} from "vue-i18n";

const page = usePage()
const v18n = useI18n()
const guestId = page.props.auth.user ? `${page.props.auth.user.id}-${uuidv4()}` : uuidv4()
const isLoading = ref(false);

const fileUploaded = ref({})
const isFileUploaded = ref(false)

const downloadLink = ref("")

const isError = ref(false)
const error = ref("")
const isMounted = ref(true)
onMounted(() => {
    isMounted.value = true

    if(page.props.auth.user){
        subToPrivate(guestId, handleSubToPrivate)
    } else {
        subToChannel(guestId, handleSubToPublic)
    }
})

onUnmounted(() => {

    isMounted.value = false
    if(page.props.auth.user) {
        disconnectFromPrivate(guestId)
    } else {
        disconnectFromPublic(guestId)
    }

})
function handleSubToPublic(event) {
    if(!isMounted.value) return

    if(event.fileName === "ERROR") {
        error.value = v18n.t('error')
        isError.value = true
    } else {
        downloadLink.value = event.fileName
    }
    isLoading.value = false
}

function handleSubToPrivate(event) {
    if(!isMounted.value) return

    if(event.fileName === "ERROR") {
        error.value = v18n.t('error')
        isError.value = true
    } else {
        downloadLink.value = event.fileName
    }
    isLoading.value = false
}

async function onSubmit() {
    const formData = new FormData();
    formData.append('guestId', guestId);
    formData.append('file', fileUploaded.value)

    try {
        isLoading.value = true
        const res = await axios.post('/tools/videotoaudio', formData)

    } catch (err) {
        if(err.response.data.error){

        } else {

        }
    }

}

function getFile(file) {

    fileUploaded.value = file
    isFileUploaded.value = true
    onSubmit()
}
</script>

<template>

    <loading-screen v-if="isLoading" />

    <MainToolsWindow v-if="!isLoading">
        <ToolsUploadScreen v-if="!isFileUploaded" :title="$t('videoToAudio.title')" :description="$t('videoToAudio.description')"
                           @file="getFile" :allow-video="true"/>

        <div v-if="isFileUploaded && !downloadLink" class="mt-10 grid lg:grid-cols-2 gap-4 sm:grid-cols-1 sm:mx-10 lg:mx-0 p-6 bg-gray-800 rounded-lg shadow-lg" id="form">

            <div v-if="isFileUploaded && !downloadLink">
                <button type="button"  @click="onSubmit" class="bg-blue-400 text-white rounded py-2 px-4 mt-5 mr-3 hover:bg-blue-500">Submit</button>
                <button type="button"  @click="isFileUploaded = false;isError = false" class="bg-blue-400 text-white rounded py-2 px-4 mt-5 mr-3 hover:bg-blue-500">Change file</button>
            </div>
        </div>

        <ResultOptionsScreen v-if="downloadLink" @go-back="downloadLink = ''; isFileUploaded = false"
                             :file-to-download-link="downloadLink" :file-to-download-name="fileUploaded.name"/>

        <div v-if="isError">
            <p>{{ error }}</p>
        </div>

    </MainToolsWindow>

</template>

<style scoped>

</style>
