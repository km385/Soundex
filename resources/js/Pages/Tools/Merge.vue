<script setup>
import {onMounted, ref} from "vue";
import axios from "axios";
import {usePage} from "@inertiajs/vue3";
import {v4 as uuidv4} from "uuid";
import {subToChannel, subToPrivate} from "@/Subscriptions/subs.js";
import SidebarLayout from "@/Layouts/SidebarLayout.vue";
import Wavesurfer from "./Partials/Wavesurfer.vue";
import UploadFile from "./Partials/UploadFile.vue";
import DownloadTempFile from "./Partials/DownloadTempFileButton.vue";
import SaveToLibraryButton from "./Partials/SaveToLibraryButton.vue";
import LoadingScreen from "./Partials/LoadingScreen.vue";
import ResultOptionsScreen from "@/Pages/Tools/Partials/ResultOptionsScreen.vue";
import ToolsUploadScreen from "@/Pages/Tools/Partials/ToolsUploadScreen.vue";
defineOptions({
    layout: SidebarLayout
})

const isFileUploaded = ref(false)
const fileToDownloadLink = ref("")
const page = usePage()

let guestId = page.props.auth.user ? page.props.auth.user.id : uuidv4()
const isLoading = ref(false)

const form = ref({
    files: [],
});

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

function getFile(file) {
    form.value.files.push(file);

    isFileUploaded.value = true
    console.log(file)
}

function getWaveformId(fileName) {
    // Remove spaces and special characters from the filename to get a valid HTML id
    return `waveform-${fileName.replace(/\s+/g, '-').replace(/[^\w-]/g, '')}`;
}

async function onMergeClicked(){
    if(form.value.files.length < 2) {
        error.value = "submit at least 2 files"
        isError.value = true
        return
    }

    const formData = new FormData()
    form.value.files.forEach((file) => {
        formData.append(getWaveformId(file.name), file)
    })
    formData.append('guestId', guestId)

    console.log('files to be send')
    for (const value of formData.values()) {
        console.log(value);
    }

    try {
        isLoading.value = true
        const res = await axios.post('/tools/merge', formData)
        console.log(res.data.message)
    } catch (e) {
        console.log(e)
    }

}

function onUpClicked(name) {
    const index = form.value.files.findIndex((file) => file.name === name);

    if (index > 0) {
        // Move the file up one position in the array
        const fileToMove = form.value.files[index];
        form.value.files.splice(index, 1); // Remove the file from the current position
        form.value.files.splice(index - 1, 0, fileToMove); // Insert the file one position up
    }
}

function onDownClicked(name) {
    const index = form.value.files.findIndex((file) => file.name === name);

    if (index < form.value.files.length - 1) {
        // Move the file up one position in the array
        const fileToMove = form.value.files[index];
        form.value.files.splice(index, 1); // Remove the file from the current position
        form.value.files.splice(index + 1, 0, fileToMove); // Insert the file one position up
    }
}

function onDeleteClicked(name) {
    const index = form.value.files.findIndex((file) => file.name === name);

    const fileToMove = form.value.files[index];
    form.value.files.splice(index, 1); // Remove the file from the current position

    if(form.value.files.length === 0) {
        isFileUploaded.value = false
    }

}

function downloadFile() {

    axios.get(`/files/${fileToDownloadLink.value}`, {
        responseType: 'blob',
    })
        .then((response) => {
            const url = window.URL.createObjectURL(new Blob([response.data]));
            const link = document.createElement('a');
            link.href = url;
            link.setAttribute('download', 'merged.mp3');
            document.body.appendChild(link);
            link.click();
        })
        .catch((error) => {
            console.log(error);
        });
}

</script>

<template>
    <loading-screen v-if="isLoading" />

    <div class="max-w-3xl mx-auto text-white flex flex-col h-screen" v-if="!isLoading">

        <ToolsUploadScreen v-if="!isFileUploaded" :title="$t('merge.title')" :description="$t('merge.description')"
                           @file="getFile"/>

        <div class="mt-10 p-6 bg-gray-800 rounded-lg shadow-lg" v-if="isFileUploaded && !fileToDownloadLink">
            <div class="mb-5">
                <upload-file @file="getFile" :is-button="true" />
            </div>
            <div v-for="file in form.files" :key="file.name" v-if="!fileToDownloadLink">
                <p>{{ file.name }}</p>
                <div class="flex group mb-10">
                    <div style="width: 100%" class="mr-4">
                        <Wavesurfer v-if="isFileUploaded" :file="file" :show-controls="false" :show-region="false"
                                    :id="getWaveformId(file.name)"/>
                    </div>
                    <div
                        class="flex flex-col justify-between opacity-30 group-hover:opacity-100 transition-opacity duration-300">
                        <button @click="onUpClicked(file.name)">{{ $t("tools.upSong") }}</button>
                        <button @click="onDeleteClicked(file.name)">{{ $t("tools.deleteSong") }}</button>
                        <button @click="onDownClicked(file.name)">{{ $t("tools.downSong") }}</button>
                    </div>
                </div>
            </div>
            <div v-if="isFileUploaded">
                <button type="button" @click="onMergeClicked"
                        class="bg-blue-400 text-white rounded py-2 px-4 mt-5 mr-3 hover:bg-blue-500">{{ $t("tools.submit") }}
                </button>
            </div>

        </div>

        <ResultOptionsScreen v-if="fileToDownloadLink" @go-back="fileToDownloadLink = ''"
                             :file-to-download-link="fileToDownloadLink" :file-to-download-name="'merged.mp3'"/>

        <div v-if="isError">
            <p>{{ error }}</p>
        </div>

    </div>
</template>

<style scoped>

</style>

