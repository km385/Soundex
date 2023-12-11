<script setup>
import {onMounted, ref} from "vue";
import axios from "axios";
import {subToChannel, subToPrivate} from "@/Subscriptions/subs.js";
import {usePage} from "@inertiajs/vue3";
import {v4 as uuidv4} from "uuid";
import SidebarLayout from "@/Layouts/SidebarLayout.vue";
import LoadingScreen from "./Partials/LoadingScreen.vue";
import UploadFile from "./Partials/UploadFile.vue";
import Wavesurfer from "./Partials/Wavesurfer.vue";
import DownloadTempFileButton from "./Partials/DownloadTempFileButton.vue";
import SaveToLibraryButton from "./Partials/SaveToLibraryButton.vue";
import ResultOptionsScreen from "@/Pages/Tools/Partials/ResultOptionsScreen.vue";
import ToolsUploadScreen from "@/Pages/Tools/Partials/ToolsUploadScreen.vue";
defineOptions({
    layout: SidebarLayout
})

const page = usePage()
const guestId = page.props.auth.user ? page.props.auth.user.id : uuidv4()
const isLoading = ref(false)
const uploadedFiles = ref([]);

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

function getFile(file) {
    if(uploadedFiles.value.length >= 2) return
    uploadedFiles.value.push(file)
    isFileUploaded.value = true
}


async function onMergeClicked(){
    if(uploadedFiles.value.length < 2) {
        error.value = "add foreground and background"
        isError.value = true
        return
    }

    const formData = new FormData()
    formData.append('foreground', uploadedFiles.value[0])
    formData.append('background', uploadedFiles.value[1])
    formData.append('guestId', guestId)

    try {
        isLoading.value = true
        const res = await axios.post('/tools/layermixer', formData)
        console.log(res.data.message)
    } catch (err) {
        console.log(err)
    }

}

function getWaveformId(fileName) {
    // Remove spaces and special characters from the filename to get a valid HTML id
    return `waveform-${fileName.replace(/\s+/g, '-').replace(/[^\w-]/g, '')}`;
}

function onUpClicked(name, array) {
    const index = array.findIndex((file) => file.name === name);

    if (index > 0) {
        // Move the file up one position in the array
        const fileToMove = array[index];
        array.splice(index, 1); // Remove the file from the current position
        array.splice(index - 1, 0, fileToMove); // Insert the file one position up
    }
}

function onDownClicked(name, array) {
    const index = array.findIndex((file) => file.name === name);

    if (index < array.length - 1) {
        // Move the file up one position in the array
        const fileToMove = array[index];
        array.splice(index, 1); // Remove the file from the current position
        array.splice(index + 1, 0, fileToMove); // Insert the file one position up
    }
}
function onDeleteClicked(name, array) {
    const index = array.findIndex((file) => file.name === name);

    const fileToMove = array[index];
    array.splice(index, 1); // Remove the file from the current position

}
</script>

<template>
    <loading-screen v-if="isLoading" />

    <div class="max-w-3xl mx-auto text-white flex flex-col h-screen">
        <ToolsUploadScreen v-if="!isFileUploaded" :title="$t('layerMixer.title')" :description="$t('layerMixer.description')"
                           @file="getFile"/>

        <div v-if="isFileUploaded && !downloadLink" class="mt-10 p-6 bg-gray-800 rounded-lg shadow-lg">
            <div class="mb-5">
                <upload-file @file="getFile" :is-button="true" />
            </div>
            <div v-for="(file, index) in uploadedFiles" :key="file.name">
                <div class="flex group mb-10">
                    <div style="width: 100%">
                        <p v-if="index === 0">{{ $t("layerMixer.foreground") }}</p>
                        <p v-else-if="index === 1">{{ $t("layerMixer.background") }} </p>
                        <Wavesurfer v-if="isFileUploaded" :file="file" :id="getWaveformId(file.name)" />
                    </div>
                    <div
                        class="flex flex-col justify-between opacity-30 group-hover:opacity-100 transition-opacity duration-300">
                        <button @click="onUpClicked(file.name, uploadedFiles)">{{ $t("tools.upSong") }}</button>
                        <button @click="onDeleteClicked(file.name, uploadedFiles)">{{ $t("tools.deleteSong") }}</button>
                        <button @click="onDownClicked(file.name, uploadedFiles)">{{ $t("tools.downSong") }}</button>
                    </div>

                </div>
            </div>

            <div v-if="isFileUploaded">
                <button type="button" @click="onMergeClicked"
                        class="bg-blue-400 text-white rounded py-2 px-4 mt-5 mr-3 hover:bg-blue-500">{{ $t("tools.submit") }}
                </button>
            </div>
        </div>

        <ResultOptionsScreen v-if="downloadLink" @go-back="downloadLink = ''"
                             :file-to-download-link="downloadLink" :file-to-download-name="'mixed_file.mp3'"/>

        <div v-if="isError">
            <p>{{ error }}</p>
        </div>

    </div>
</template>

<style scoped>

</style>
