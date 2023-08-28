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

const uploadedFile = ref({})
const speedValue = ref(null)
const tempoValue = ref(null)
const isUploaded = ref(false)


const fileToDownloadName = ref("")
const page = usePage()

const guestId = page.props.auth.user ? page.props.auth.user.id : uuidv4()

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
    fileToDownloadName.value = event.fileName
}
function handleSubToPrivate(event) {
    console.log("the event has been successfully captured")
    console.log(event)
    fileToDownloadName.value = event.fileName
}

function getFile(file) {
    console.log('file received')
    uploadedFile.value = file
    isUploaded.value = true
}

function onUploadButtonClick() {
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

    axios.post('/speedup', formData)
        .then(res => {
            console.log(res.data.message)
        })
        .catch(err => {
            console.log(err)
        })
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
    <SidebarLayout>
        <Wavesurfer v-if="isUploaded" :file="uploadedFile" :show-region="false" :show-controls="true"/>
        <div class="flex flex-col items-start ">
            <UploadFile v-if="!isUploaded" @file="getFile" />
            <div class="w-auto mb-3 mt-3">
                <input type="text" placeholder="1.06" class="border-2 border-black my-3 rounded" v-model="tempoValue">
            </div>
            <div class="w-auto">
                <input type="text" placeholder="1.20" class="border-2 border-black mb-3 rounded" v-model="speedValue">
            </div>
            <button @click="onUploadButtonClick" class="bg-blue-400 text-white rounded py-2 px-4 mt-5 mr-3 hover:bg-blue-500 mb-3">
                Upload
            </button>
        </div>
        <DownloadTempFile v-if="fileToDownloadName" :filename="uploadedFile.name" :token="fileToDownloadName"/>
        <SaveToLibraryButton v-if="fileToDownloadName && page.props.auth.user" :file-link="fileToDownloadName"/>

    </SidebarLayout>
</template>

<style scoped>

</style>
