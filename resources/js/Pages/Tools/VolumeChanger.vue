<script setup>

import SaveToLibraryButton from "@/Pages/Tools/Partials/SaveToLibraryButton.vue";
import LoadingScreen from "@/Pages/Tools/Partials/LoadingScreen.vue";
import Wavesurfer from "@/Pages/Tools/Partials/Wavesurfer.vue";
import UploadFile from "@/Pages/Tools/Partials/UploadFile.vue";
import DownloadTempFile from "@/Pages/Tools/Partials/DownloadTempFileButton.vue";
import SidebarLayout from "@/Layouts/SidebarLayout.vue";
import {usePage} from "@inertiajs/vue3";
import {v4 as uuidv4} from "uuid";
import {computed, onMounted, reactive, ref, watch} from "vue";
import {subToChannel, subToPrivate} from "@/subscriptions/subs.js";
import axios from "axios";
import FileInfo from "@/Pages/Tools/Partials/FileInfo.vue";

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

async function onSubmit(){

    axios.interceptors.request.use(req => {
        console.log(req)
        return req
    })

    const formData = new FormData();

    formData.append('file', uploadedFile.value)
    formData.append('guestId', guestId)
    formData.append('volume', percentageVolumeChange.value / 100)

    try {
        isLoading.value = true
        const res = await axios.post('/tools/volumechanger', formData)
        console.log(res.data.message)
    }catch (e) {
        console.log(e)
    }
}

async function getFile(file) {
    console.log('get file')
    uploadedFile.value = file;
    isFileUploaded.value = true
}

const waveformRef = ref(null)


// control volume of child waveform by reference
const startVolumeValue = 0.33333
const volumeValue = ref(startVolumeValue)
watch(volumeValue, (value) => {
    waveformRef.value.changeVolume(value)
})

const percentageVolumeChange = computed(() => {
    return Math.round(((((volumeValue.value - startVolumeValue) / startVolumeValue)) + Number.EPSILON) * 100)
})

</script>

<template>

    <loading-screen v-if="isLoading" />
    <div class="max-w-3xl mx-auto text-white flex flex-col h-screen" v-if="!isLoading">
        <div class="flex flex-col flex-grow justify-center items-center" v-if="!isFileUploaded">
            <!-- Upload File Section -->
            <div class="mb-5 text-center">
                <p class="text-5xl font-bold mb-2">Volume Changer</p>
                <p class="text-3xl">Change the volume of your songs</p>
            </div>
            <UploadFile @file="getFile" />
        </div>

        <div v-if="isFileUploaded && !fileToDownloadLink" class="mt-10 p-6 bg-gray-800 rounded-lg shadow-lg">
            <!-- File Information Section -->
            <!--            <div class="p-6 bg-gray-800 rounded-lg shadow-lg">-->
            <button type="button" @click="isFileUploaded = false" class="bg-blue-400 text-white rounded py-2 px-4 hover:bg-blue-500 mb-4">Change File</button>

            <FileInfo :file-size="uploadedFile.size" :file-name="uploadedFile.name" />

            <Wavesurfer ref="waveformRef" v-if="isFileUploaded" :file="uploadedFile" :show-region="false"
                        :show-controls="true" :allow-second-region="false" :allow-volume-control="false"/>


            <input type="range" min="0" max="1" step="0.0001"
                   class="mt-3 w-full h-4 bg-gray-200 rounded-lg  cursor-pointer dark:bg-gray-700 custom-slider-thumb "
                   id="myRange" v-model="volumeValue">
            {{(percentageVolumeChange<0?"":"+")}}{{ percentageVolumeChange }}%



            <div class="mt-6">
                <button type="button" @click="onSubmit" class="bg-blue-400 text-white rounded py-2 px-4 hover:bg-blue-500">Submit</button>
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
.custom-slider-thumb{
    -webkit-appearance: none;
}

.custom-slider-thumb::-webkit-slider-thumb {
    -webkit-appearance: none;
    border: none;
    height: 30px;
    width: 30px;
    border-radius: 50%;
    background: goldenrod;
}

.custom-slider-thumb:focus {
    outline: none;
}
</style>
