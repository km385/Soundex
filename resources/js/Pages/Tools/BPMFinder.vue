<script>
import { ref } from "vue";
const isLoading = ref(false)
</script>

<script setup>
import DownloadTempFileButton from "@/Pages/Tools/Partials/DownloadTempFileButton.vue";
import { subToPrivate, subToChannel } from "@/subscriptions/subs.js";
import { v4 as uuidv4 } from "uuid";
import { usePage } from "@inertiajs/vue3";
import { onMounted, ref, watch } from "vue";
import axios from "axios";
import UploadFile from "@/Pages/Tools/Partials/UploadFile.vue";
import SaveToLibraryButton from "@/Pages/Tools/Partials/SaveToLibraryButton.vue";
import SidebarLayout from "@/Layouts/SidebarLayout.vue";

defineOptions({
    layout: (h, page) => h(SidebarLayout, { isLoading: isLoading.value }, () => page)
})
const page = usePage()
const guestId = page.props.auth.user ? page.props.auth.user.id : uuidv4()
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
}

</script>
<template>
    <div class="max-w-3xl mx-auto" v-if="!isLoading">
        <div v-if="!isFileUploaded" class="flex justify-center items-center h-screen">
            <UploadFile @file="getFile" />
        </div>

        <div v-if="isFileUploaded && !downloadLink">
            <button type="button" @click="onSubmit"
                class="bg-blue-400 text-white rounded py-2 px-4 mt-5 mr-3 hover:bg-blue-500">Submit</button>
            <button type="button" @click="isFileUploaded = false"
                class="bg-blue-400 text-white rounded py-2 px-4 mt-5 mr-3 hover:bg-blue-500">Change file</button>
        </div>

        <div v-if="downloadLink" class="text-white flex flex-col justify-center items-center h-screen w-full">
            <button class="bg-blue-400 text-white rounded py-2 px-4 mt-5 mr-3 hover:bg-blue-500"
                @click="downloadLink = null">go back</button>
            <DownloadTempFileButton :filename="fileUploaded.name" :token="downloadLink" :show-button="false" />
        </div>

        <div v-if="isError">
            <p>{{ error }}</p>
        </div>

    </div>
</template>


<style scoped></style>