<script>
import {ref} from "vue";

const isLoading = ref(false)
</script>

<script setup>

import {onMounted, ref} from "vue";
import axios from "axios";
import Wavesurfer from "@/Pages/Tools/Wavesurfer.vue";
import UploadFile from "@/Pages/Tools/UploadFile.vue";
import {usePage} from "@inertiajs/vue3";
import {v4 as uuidv4} from "uuid";
import SidebarLayout from "@/Layouts/SidebarLayout.vue";
import {subToChannel, subToPrivate} from "@/subscriptions/subs.js";
import DownloadTempFile from "@/Pages/Tools/DownloadTempFileButton.vue";
import SaveToLibraryButton from "@/Pages/Tools/SaveToLibraryButton.vue";

defineOptions({
    layout: ( h, page ) => h( SidebarLayout, {  isLoading : isLoading.value } , () => page )
})
const isFileUploaded = ref(false)
const fileToDownloadLink = ref(null)

const page = usePage()
let guestId = page.props.auth.user ? page.props.auth.user.id : uuidv4()

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
        const res = await axios.post('/merge', formData)
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
    <div class="max-w-3xl mx-auto text-white" v-if="!isLoading">
        <div class="mb-6" v-if="!fileToDownloadLink">
            <div class="mt-6">
                <UploadFile @file="getFile"/>
            </div>

            <div v-if="isFileUploaded">
                <button type="button" @click="onMergeClicked"
                        class="bg-blue-400 text-white rounded py-2 px-4 mt-5 mr-3 hover:bg-blue-500">Merge
                </button>
            </div>
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
                    <button @click="onUpClicked(file.name)">up</button>
                    <button @click="onDeleteClicked(file.name)">delete</button>
                    <button @click="onDownClicked(file.name)">down</button>
                </div>
            </div>
        </div>

        <div v-if="fileToDownloadLink" class="text-white flex flex-col justify-center items-center h-screen">
            <p >You can now download your new file</p>
            <button class="bg-blue-400 text-white rounded py-2 px-4 mt-5 mr-3 hover:bg-blue-500" @click="fileToDownloadLink = null">go back</button>
            <DownloadTempFile :filename="'merged.mp3'" :token="fileToDownloadLink"/>
            <SaveToLibraryButton v-if="page.props.auth.user" :file-link="fileToDownloadLink"/>
        </div>
    </div>
</template>

<style scoped>

</style>

