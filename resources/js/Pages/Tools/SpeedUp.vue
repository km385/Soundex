<script>
import {ref} from "vue";

const isLoading = ref(false)
</script>

<script setup>

import UploadFile from "@/Pages/Tools/UploadFile.vue";
import {onMounted, ref} from "vue";
import Wavesurfer from "@/Pages/Tools/Wavesurfer.vue";
import {usePage} from "@inertiajs/vue3";
import { v4 as uuidv4 } from 'uuid';
import CustomAuthenticatedLayout from "@/Layouts/CustomAuthenticatedLayout.vue";
import DownloadTempFile from "@/Pages/Tools/DownloadTempFileButton.vue";
import SaveToLibraryButton from "@/Pages/Tools/SaveToLibraryButton.vue";
import {subToChannel, subToPrivate} from "@/subscriptions/subs.js";
import SidebarLayout from "@/Layouts/SidebarLayout.vue";

defineOptions({
    layout: ( h, page ) => h( SidebarLayout, {  isLoading : isLoading.value } , () => page )
})

const uploadedFile = ref({})
const speedValue = ref(null)
const tempoValue = ref(null)
const isUploaded = ref(false)


const fileToDownloadLink = ref("")
const page = usePage()

const guestId = page.props.auth.user ? page.props.auth.user.id : uuidv4()

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
    console.log('file received')
    uploadedFile.value = file
    isUploaded.value = true
}

async function onUploadButtonClick() {
    if(speedValue.value == null) {
        speedValue.value = 1.20
    }
    if(tempoValue.value == null) {
        tempoValue.value = 1.06
    }

    const formData = new FormData()
    formData.append('file', uploadedFile.value)
    formData.append('speedValue', speedValue.value)
    formData.append('tempoValue', tempoValue.value)
    formData.append('guestId', guestId)

    try {
        isLoading.value = true
        const res = await axios.post('/speedup', formData)
        console.log(res.data.message)
    } catch (e) {
        console.log(e)
    }

}
function changeHandleStyles(region){
    for(const child of region.element.children){
        child.innerHTML = '&vellip;'
        child.style.color = 'white'
        child.style.width = '6px'
        child.style.backgroundColor = '#282828'
        child.style.display = 'flex'
        child.style.alignItems = 'center'
        child.style.justifyContent = 'center'
        child.style.borderWidth = '0px'
    }
}
</script>

<template>
    <div class="max-w-3xl mx-auto text-white" v-if="!isLoading">
        <div v-if="!isUploaded" class="flex justify-center items-center h-screen">
            <UploadFile @file="getFile" />
        </div>

        <div v-if="isUploaded && !fileToDownloadLink">
            <Wavesurfer v-if="isUploaded" :file="uploadedFile" :show-region="false" :show-controls="true"/>
            <div class="flex flex-col items-start mt-10">
                <div class="w-auto mb-3 mt-3">
                    <label for="tempo" class="block font-medium text-sm mb-1" >Tempo</label>
                    <input type="text" id="tempo" placeholder="1.06" class="text-black bg-gray-50 border border-gray-500 rounded-lg focus:border-blue-500 focus:ring-blue-500" v-model="tempoValue">
                </div>
                <div class="w-auto">
                    <label for="speed" class="block font-medium text-sm mb-1" >Speed</label>
                    <input type="text" id="speed" placeholder="1.20" class="text-black bg-gray-50 border border-gray-500 rounded-lg focus:border-blue-500 focus:ring-blue-500" v-model="speedValue">
                </div>
                <button @click="onUploadButtonClick" class="bg-blue-400 text-white rounded-lg py-2 px-4 mt-5 mr-3 hover:bg-blue-500 mb-3">
                    Upload
                </button>
                <button type="button"  @click="isUploaded = false" class="bg-blue-400 text-white rounded py-2 px-4 mt-5 mr-3 hover:bg-blue-500">Change file</button>

            </div>
        </div>

        <div v-if="fileToDownloadLink" class="text-white flex flex-col justify-center items-center h-screen">
            <p >You can now download your new file</p>
            <button class="bg-blue-400 text-white rounded py-2 px-4 mt-5 mr-3 hover:bg-blue-500" @click="fileToDownloadLink = null">go back</button>
            <DownloadTempFile :filename="uploadedFile.name" :token="fileToDownloadLink"/>
            <SaveToLibraryButton v-if="page.props.auth.user" :file-link="fileToDownloadLink"/>
        </div>
    </div>
</template>

<style scoped>

</style>
