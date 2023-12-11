<script setup>
import {onMounted, reactive, ref, watch} from "vue";
import axios from "axios";
import {usePage} from "@inertiajs/vue3";
import {v4 as uuidv4} from 'uuid';
import {subToChannel, subToPrivate} from "@/Subscriptions/subs.js";
import SidebarLayout from "@/Layouts/SidebarLayout.vue";
import LoadingScreen from "./Partials/LoadingScreen.vue";
import Wavesurfer from "./Partials/Wavesurfer.vue";
import FileInfo from "@/Pages/Tools/Partials/FileInfo.vue";
import ResultOptionsScreen from "@/Pages/Tools/Partials/ResultOptionsScreen.vue";
import ToolsUploadScreen from "@/Pages/Tools/Partials/ToolsUploadScreen.vue";
// component data => layout props
// choose manually persistent layout and give it its props and children
// use h(type, props, children) render function
defineOptions({
    layout: SidebarLayout
})
const page = usePage()
const guestId = page.props.auth.user ? page.props.auth.user.id : uuidv4()
const isLoading = ref(false)


const uploadedFile = ref({})
const isFileUploaded = ref(false)
const fileToDownloadLink = ref("")

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
    }
    isLoading.value = false
}

async function onCutClicked() {


    const formData = new FormData();

    formData.append('file', uploadedFile.value)
    formData.append('guestId', guestId)

    try {
        isLoading.value = true
        const res = await axios.post('/tools/diagnosis', formData)
        console.log(res.data.message)
    } catch (e) {
        console.log(e)
    }
}

async function getFile(file) {
    console.log('get file')

    uploadedFile.value = file;
    isFileUploaded.value = true
}



</script>

<template>
    <loading-screen v-if="isLoading"/>
    <div class="max-w-3xl mx-auto text-white flex flex-col h-screen" v-if="!isLoading">
        <ToolsUploadScreen v-if="!isFileUploaded" :title="$t('diagnosis.title')" :description="$t('diagnosis.description')"
                           @file="getFile"/>

        <div v-if="isFileUploaded && !fileToDownloadLink" class="mt-10 p-6 bg-gray-800 rounded-lg shadow-lg">
            <!-- File Information Section -->
            <!--            <div class="p-6 bg-gray-800 rounded-lg shadow-lg">-->
            <button type="button" @click="isFileUploaded = false"
                    class="bg-blue-400 text-white rounded py-2 px-4 hover:bg-blue-500 mb-4">Change File
            </button>

            <FileInfo :file-size="uploadedFile.size" :file-name="uploadedFile.name"/>


            <div class="mt-6">
                <button type="button" @click="onCutClicked"
                        class="bg-blue-400 text-white rounded py-2 px-4 hover:bg-blue-500">Submit
                </button>
            </div>

        </div>

        <ResultOptionsScreen v-if="fileToDownloadLink" @go-back="fileToDownloadLink = ''"
                             :file-to-download-link="fileToDownloadLink" :file-to-download-name="uploadedFile.name"/>

        <div v-if="isError" class="text-red-500">
            <!-- Error Handling Section -->
            <p class="p-6 bg-gray-800 rounded-lg shadow-lg">{{ error }}</p>
        </div>
    </div>


</template>

