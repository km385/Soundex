<script setup>
import {usePage} from "@inertiajs/vue3";
import {v4 as uuidv4} from "uuid";
import {onMounted, ref} from "vue";
import {subToChannel, subToPrivate} from "@/subscriptions/subs.js";
import LoadingScreen from "@/Pages/Tools/Partials/LoadingScreen.vue";
import SelectExtension from "@/Pages/Tools/Partials/SelectExtension.vue";
import SaveToLibraryButton from "@/Pages/Tools/Partials/SaveToLibraryButton.vue";
import Wavesurfer from "@/Pages/Tools/Partials/Wavesurfer.vue";
import DownloadTempFile from "@/Pages/Tools/Partials/DownloadTempFileButton.vue";
import UploadFile from "@/Pages/Tools/Partials/UploadFile.vue";
import axios from "axios";

const page = usePage()
const guestId = page.props.auth.user ? page.props.auth.user.id : uuidv4()
const isLoading = ref(false)

const uploadedFile = ref({})
const isFileUploaded = ref(false)
const fileToDownloadLink = ref("")
const extension = ref("")

const isError = ref(false)
const error = ref("")

onMounted(() => {
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
        fileToDownloadLink.value = event.fileName
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
        fileToDownloadLink.value = event.fileName
    }
    isLoading.value = false
}

async function getFile(file) {
    console.log('get file')
    // reset checkbox upon change of files, keep for reference
    // regionCheckboxValue.value = false
    // await nextTick()
    uploadedFile.value = file;
    isFileUploaded.value = true
}

async function onSubmit() {
    const formData = new FormData()
    formData.append('file', uploadedFile.value)
    formData.append('guestId', guestId)
    formData.append('extension', extension.value)
    formData.append('bitrate', selectedBitrate.value)

    try {
        isLoading.value = true
        const res = await axios.post('/tools/converter', formData)
        console.log(res.data.message)
    } catch (e) {
        console.log(e)
    }

}

function getExtension(data) {
    extension.value = data
    console.log(data)
}

const bitrates = ref([64, 128, 192, 256, 320]);
const selectedBitrate = ref(192);

const selectBitrate = (bitrate) => {
    selectedBitrate.value = bitrate;
    console.log(selectedBitrate.value)
};

</script>

<template>
    <loading-screen v-if="isLoading" />
    <div class="max-w-3xl mx-auto text-white flex flex-col h-screen" v-if="!isLoading">
        <div class="flex flex-col flex-grow justify-center items-center" v-if="!isFileUploaded">
            <!-- Upload File Section -->
            <div class="mb-5 text-center">
                <p class="text-5xl font-bold mb-2">Converter Tool</p>
                <p class="text-3xl">Change your song extension</p>
            </div>
            <UploadFile @file="getFile" />
        </div>

        <div v-if="isFileUploaded && !fileToDownloadLink" class="mt-10 p-6 bg-gray-800 rounded-lg shadow-lg">
            <!-- File Information Section -->
            <!--            <div class="p-6 bg-gray-800 rounded-lg shadow-lg">-->
            <button type="button" @click="isFileUploaded = false" class="bg-blue-400 text-white rounded py-2 px-4 hover:bg-blue-500 mb-4">Change File</button>

            <div class="my-4">
                <p class="text-lg">File Name: {{ uploadedFile.name }}</p>
                <p class="text-sm">File Size: {{ (uploadedFile.size / 1024 / 1024).toFixed(2) }} MB</p>
            </div>
<!--            nice info here :)-->
            <div id="app">
                <button
                    v-for="bitrate in bitrates"
                    :key="bitrate"
                    @click="selectBitrate(bitrate)"
                    class="bg-blue-400 text-white rounded py-2 px-2 mr-2 hover:bg-blue-500"
                    :class="{ 'bg-green-800 hover:bg-green-800 drop-shadow-lg':selectedBitrate === bitrate }"
                >
                    {{ bitrate }} Kbps
                </button>
            </div>
            <div class="mt-6">
                <button type="button" @click="onSubmit" class="bg-blue-400 text-white rounded py-2 px-4 hover:bg-blue-500">Submit</button>
            </div>
            <select-extension @extension="getExtension"/>
            <!--            </div>-->
        </div>

        <div v-if="fileToDownloadLink" class="text-white flex flex-col flex-grow justify-center items-center">
            <!-- File Download Section -->
            <div class="p-6 bg-gray-800 rounded-lg shadow-lg">
                <p>You can now download your new file</p>
                <button class="bg-blue-400 text-white rounded py-2 px-4 mt-4 hover:bg-blue-500" @click="fileToDownloadLink = ''">Go Back</button>
                <DownloadTempFile :filename="uploadedFile.name" :token="fileToDownloadLink" />
                <SaveToLibraryButton v-if="page.props.auth.user" :file-link="fileToDownloadLink" />
            </div>
        </div>

        <div v-if="isError" class="text-red-500">
            <!-- Error Handling Section -->
            <p class="p-6 bg-gray-800 rounded-lg shadow-lg">{{ error }}</p>
        </div>
    </div>
</template>

<style scoped>

</style>
