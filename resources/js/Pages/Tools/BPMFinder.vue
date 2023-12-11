<script setup>
import { v4 as uuidv4 } from "uuid";
import { usePage } from "@inertiajs/vue3";
import { onMounted, ref } from "vue";
import axios from "axios";
import { subToPrivate, subToChannel } from "@/Subscriptions/subs.js";
import UploadFile from "./Partials/UploadFile.vue";
import DownloadTempFileButton from "./Partials/DownloadTempFileButton.vue";
import SidebarLayout from "@/Layouts/SidebarLayout.vue";
import LoadingScreen from "./Partials/LoadingScreen.vue";
import ResultOptionsScreen from "@/Pages/Tools/Partials/ResultOptionsScreen.vue";
import ToolsUploadScreen from "@/Pages/Tools/Partials/ToolsUploadScreen.vue";

defineOptions({
    layout: SidebarLayout
})

const page = usePage()
const guestId = page.props.auth.user ? page.props.auth.user.id : uuidv4()
const isLoading = ref(false);
const fileUploaded = ref({})
const isFileUploaded = ref(false)
const downloadLink = ref("")
const isError = ref(false)
const error = ref("")

onMounted(() => {
    console.log(guestId)
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
        downloadLink.value = event.fileName
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
        downloadLink.value = event.fileName
    }
    isLoading.value = false
}

const form = ref({
    coverRef: File,
    fileRef: File,
})

async function onSubmit() {
    const formData = new FormData()
    Object.keys(form.value).forEach(key => {
        const value = form.value[key]
        formData.append(key, value)
        console.log(key, value)
    })
    formData.append('guestId', guestId)

    try {
        isLoading.value = true
        const res = await axios.post('/tools/bpmFinder', formData)
        console.log(res.data.message)
    } catch (err) {
        if (err.response.data.error) {
            console.log('no file')
        } else {
            console.log(err)
        }
    }
}

function getFile(file) {
    console.log('file received')
    fileUploaded.value = file
    form.value.fileRef = file
    isFileUploaded.value = true
    // submit file immediately
    onSubmit()
    isLoading.value = true

}

</script>
<template>
    <loading-screen v-if="isLoading" />
    <div class="max-w-3xl mx-auto h-screen text-white flex flex-col" v-if="!isLoading">

        <ToolsUploadScreen v-if="!isFileUploaded" :title="$t('bpmFinder.title')" :description="$t('bpmFinder.description')"
                           @file="getFile"/>

        <div v-if="isFileUploaded && !downloadLink">
            <button type="button" @click="onSubmit"
                class="bg-blue-400 text-white rounded py-2 px-4 mt-5 mr-3 hover:bg-blue-500">Submit</button>
            <button type="button" @click="isFileUploaded = false"
                class="bg-blue-400 text-white rounded py-2 px-4 mt-5 mr-3 hover:bg-blue-500">Change file</button>
        </div>

        <ResultOptionsScreen v-if="downloadLink" @go-back="downloadLink = ''; isFileUploaded = false"
                             :file-to-download-link="downloadLink" :file-to-download-name="fileUploaded.name"
                             :show-button="false"/>

        <div v-if="isError">
            <p>{{ error }}</p>
        </div>

    </div>
</template>


<style scoped></style>
