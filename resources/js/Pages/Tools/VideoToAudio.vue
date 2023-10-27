<script setup>

import LoadingScreen from "@/Pages/Tools/Partials/LoadingScreen.vue";
import DownloadTempFileButton from "@/Pages/Tools/Partials/DownloadTempFileButton.vue";
import SaveToLibraryButton from "@/Pages/Tools/Partials/SaveToLibraryButton.vue";
import InputFieldWithLabel from "@/Pages/Tools/Partials/InputFieldWithLabel.vue";
import UploadFile from "@/Pages/Tools/Partials/UploadFile.vue";
import SelectExtension from "@/Pages/Tools/Partials/SelectExtension.vue";
import {usePage} from "@inertiajs/vue3";
import {v4 as uuidv4} from "uuid";
import {onMounted, ref} from "vue";
import {subToChannel, subToPrivate} from "@/subscriptions/subs.js";
import axios from "axios";

const page = usePage()
const guestId = page.props.auth.user ? page.props.auth.user.id : uuidv4()
const isLoading = ref(false);

const fileUploaded = ref({})
const isFileUploaded = ref(false)

const downloadLink = ref("")

const isError = ref(false)
const error = ref("")

onMounted(() => {
    console.log(guestId)
    if(page.props.auth.user){
        subToPrivate(guestId, handleSubToPrivate)
    } else {
        subToChannel(guestId, handleSubToPublic)
    }
})
function handleSubToPublic(event) {
    console.log("the event has been successfully captured")
    console.log(event)

    if(event.fileName === "ERROR") {
        error.value = "error has occurred"
        isError.value = true
    } else {
        downloadLink.value = event.fileName
    }
    isLoading.value = false
}

function handleSubToPrivate(event) {
    console.log("the event has been successfully captured")
    console.log(event)

    if(event.fileName === "ERROR") {
        error.value = "error has occurred"
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
        console.log(res.data.message)
    } catch (err) {
        if(err.response.data.error){
            console.log('no file')
        } else {
            console.log(err)
        }
    }

}

function getFile(file) {
    console.log('file received')
    fileUploaded.value = file
    isFileUploaded.value = true
    onSubmit()
}
</script>

<template>

    <loading-screen v-if="isLoading" />

    <div class="max-w-3xl mx-auto flex flex-col h-screen text-white" v-if="!isLoading">
        <div v-if="!isFileUploaded" class="flex flex-col flex-grow justify-center items-center">
            <div class="mb-5 text-center">
                <p class="text-5xl font-bold mb-2">Video To Audio</p>
                <p class="text-3xl">Extract audio from any video</p>
            </div>
            <UploadFile @file="getFile" :allow-video="true"/>
        </div>

        <!--    usunieto form.submit i dziala-->

        <div v-if="isFileUploaded && !downloadLink" class="mt-10 grid lg:grid-cols-2 gap-4 sm:grid-cols-1 sm:mx-10 lg:mx-0 p-6 bg-gray-800 rounded-lg shadow-lg" id="form">

            <div v-if="isFileUploaded && !downloadLink">
                <button type="button"  @click="onSubmit" class="bg-blue-400 text-white rounded py-2 px-4 mt-5 mr-3 hover:bg-blue-500">Submit</button>
                <button type="button"  @click="isFileUploaded = false" class="bg-blue-400 text-white rounded py-2 px-4 mt-5 mr-3 hover:bg-blue-500">Change file</button>
            </div>
        </div>

        <div v-if="downloadLink" class="text-white flex flex-col justify-center items-center h-screen">
            <p >You can now download your new file</p>
            <button class="bg-blue-400 text-white rounded py-2 px-4 mt-5 mr-3 hover:bg-blue-500" @click="downloadLink = ''; isFileUploaded = false">go back</button>
            <DownloadTempFileButton :filename="fileUploaded.name" :token="downloadLink"/>
            <SaveToLibraryButton v-if="page.props.auth.user" :file-link="downloadLink"/>
        </div>

        <div v-if="isError">
            <p>{{ error }}</p>
        </div>

    </div>

</template>

<style scoped>

</style>
