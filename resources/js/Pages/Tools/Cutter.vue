<script setup>
import {onMounted, reactive, ref, watch} from "vue";
import axios from "axios";
import {usePage} from "@inertiajs/vue3";
import { v4 as uuidv4 } from 'uuid';
import {subToChannel, subToPrivate} from "@/Subscriptions/subs.js";
import SidebarLayout from "@/Layouts/SidebarLayout.vue";
import SaveToLibraryButton from "./Partials/SaveToLibraryButton.vue";
import DownloadTempFile from "./Partials/DownloadTempFileButton.vue";
import LoadingScreen from "./Partials/LoadingScreen.vue";
import Wavesurfer from "./Partials/Wavesurfer.vue";
import UploadFile from "./Partials/UploadFile.vue";
import SelectExtension from "@/Pages/Tools/Partials/SelectExtension.vue";
import FileInfo from "@/Pages/Tools/Partials/FileInfo.vue";
import ChooseFile from "@/Pages/Tools/Partials/ChooseFile.vue";
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

const mainRegionData = reactive({
    start: 0,
    end: 0
})

const secondaryRegionData = reactive({
    start: null,
    end: null
})

const regionCheckboxValue = ref(false)
watch(regionCheckboxValue, (value) => {
    console.log(`check ${value}`)
})

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

async function onCutClicked(){
    const start = mainRegionData.start
    const end = mainRegionData.end
    console.log(start)
    console.log(end)
    axios.interceptors.request.use(req => {
        console.log(req)
        return req
    })

    const formData = new FormData();
    formData.append('start', start);
    formData.append('end', end);
    if(secondaryRegionData.start != null && secondaryRegionData.end != null) {
        formData.append('start2', secondaryRegionData.start)
        formData.append('end2', secondaryRegionData.end)
    }
    formData.append('file', uploadedFile.value)
    formData.append('guestId', guestId)

    try {
        isLoading.value = true
        const res = await axios.post('/tools/cutFile', formData)
        console.log(res.data.message)
    }catch (e) {
        console.log(e)
    }
}

async function getFile(file) {
    console.log('get file')
    // reset checkbox upon change of files, keep for reference
    // regionCheckboxValue.value = false
    // await nextTick()
    uploadedFile.value = file;
    isFileUploaded.value = true
}

function getRegionData(data) {
    switch (data.id){
        case 0:
            mainRegionData.start = data.start
            mainRegionData.end = data.end
            break
        case 1:
            secondaryRegionData.start = data.start
            secondaryRegionData.end = data.end
            break
    }
}

</script>

<template>
    <loading-screen v-if="isLoading" />
    <div class="max-w-3xl mx-auto text-white flex flex-col h-screen" v-if="!isLoading">
        <div class="flex flex-col flex-grow justify-center items-center" v-if="!isFileUploaded">
            <!-- Upload File Section -->
            <div class="mb-5 text-center">
                <p class="text-5xl font-bold mb-2">{{ $t('cutter.title') }}</p>
                <p class="text-3xl">{{ $t('cutter.description') }}</p>
            </div>
            <UploadFile @file="getFile"/>
        </div>

        <ChooseFile v-if="!isFileUploaded" @file-chosen="getFile" />

        <div v-if="isFileUploaded && !fileToDownloadLink" class="mt-10 p-6 bg-gray-800 rounded-lg shadow-lg">
            <!-- File Information Section -->
            <!--            <div class="p-6 bg-gray-800 rounded-lg shadow-lg">-->
            <button type="button" @click="isFileUploaded = false" class="bg-blue-400 text-white rounded py-2 px-4 hover:bg-blue-500 mb-4">Change File</button>

            <FileInfo :file-size="uploadedFile.size" :file-name="uploadedFile.name" />
            <Wavesurfer v-if="isFileUploaded" :file="uploadedFile" :show-region="true" :show-controls="true" :allow-second-region="true" @region-coords="getRegionData" />

            <div class="mt-6">
                <button type="button" @click="onCutClicked" class="bg-blue-400 text-white rounded py-2 px-4 hover:bg-blue-500">Cut</button>
            </div>

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
