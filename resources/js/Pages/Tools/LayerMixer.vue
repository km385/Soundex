<script>
import {ref} from "vue";

const isLoading = ref(false)
</script>

<script setup>
import UploadFile from "@/Pages/Tools/UploadFile.vue";
import Wavesurfer from "@/Pages/Tools/Wavesurfer.vue";
import DownloadTempFileButton from "@/Pages/Tools/DownloadTempFileButton.vue";
import SaveToLibraryButton from "@/Pages/Tools/SaveToLibraryButton.vue";
import {onMounted, ref} from "vue";
import axios from "axios";
import {subToChannel, subToPrivate} from "@/subscriptions/subs.js";
import {usePage} from "@inertiajs/vue3";

import {v4 as uuidv4} from "uuid";
import SidebarLayout from "@/Layouts/SidebarLayout.vue";

defineOptions({
    layout: ( h, page ) => h( SidebarLayout, {  isLoading : isLoading.value } , () => page )
})
const page = usePage()
const guestId = page.props.auth.user ? page.props.auth.user.id : uuidv4()
const uploadedFiles = ref([]);

const isFileUploaded = ref(false)

const downloadLink = ref("")
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
    downloadLink.value = event.fileName
    isLoading.value = false
}
function handleSubToPrivate(event) {
    console.log("the event has been successfully captured")
    console.log(event)
    downloadLink.value = event.fileName
    isLoading.value = false
}

function getFile(file) {
    if(uploadedFiles.value.length >= 2) return
    uploadedFiles.value.push(file)
    isFileUploaded.value = true
}


async function onMergeClicked(){
    const formData = new FormData()
    formData.append('foreground', uploadedFiles.value[0])
    formData.append('background', uploadedFiles.value[1])
    formData.append('guestId', guestId)

    try {
        isLoading.value = true
        const res = await axios.post('/layermixer', formData)
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
    <div class="max-w-3xl mx-auto text-white" v-if="!isLoading">
        <div class="flex justify-center items-center" v-if="!downloadLink">
            <UploadFile @file="getFile" />
        </div>

        <div v-if="isFileUploaded && !downloadLink">
            <div v-for="(file, index) in uploadedFiles" :key="file.name">
                <div class="flex group">
                    <div style="width: 100%">
                        <p v-if="index === 0">foreground</p>
                        <p v-else-if="index === 1">background - will determine the duration of the final song</p>
                        <Wavesurfer v-if="isFileUploaded" :file="file" :id="getWaveformId(file.name)" />
                    </div>
                    <div
                        class="flex flex-col justify-between opacity-30 group-hover:opacity-100 transition-opacity duration-300">
                        <button @click="onUpClicked(file.name, uploadedFiles)">up</button>
                        <button @click="onDeleteClicked(file.name, uploadedFiles)">delete</button>
                        <button @click="onDownClicked(file.name, uploadedFiles)">down</button>
                    </div>

                </div>
            </div>

            <div v-if="isFileUploaded">
                <button type="button" @click="onMergeClicked"
                        class="bg-blue-400 text-white rounded py-2 px-4 mt-5 mr-3 hover:bg-blue-500">Merge
                </button>
            </div>
        </div>

        <div v-if="downloadLink" class="text-white flex flex-col justify-center items-center h-screen">
            <p >You can now download your new file</p>
            <button class="bg-blue-400 text-white rounded py-2 px-4 mt-5 mr-3 hover:bg-blue-500" @click="downloadLink = null">go back</button>
            <DownloadTempFileButton :token="downloadLink" :filename="'mixed_file.mp3'"/>
            <SaveToLibraryButton v-if=" page.props.auth.user" :file-link="downloadLink" />
        </div>
    </div>
</template>

<style scoped>

</style>
