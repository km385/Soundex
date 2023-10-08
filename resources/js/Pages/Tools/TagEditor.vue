<script setup>
import {subToPrivate, subToChannel} from "@/Subscriptions/subs.js";
import {v4 as uuidv4} from "uuid";
import {usePage} from "@inertiajs/vue3";
import {onMounted, ref} from "vue";
import axios from "axios";
import SidebarLayout from "@/Layouts/SidebarLayout.vue";
import UploadFile from "./Partials/UploadFile.vue";
import DownloadTempFileButton from "./Partials/DownloadTempFileButton.vue";
import SaveToLibraryButton from "./Partials/SaveToLibraryButton.vue";
import InputFieldWithLabel from "./Partials/InputFieldWithLabel.vue";
import LoadingScreen from "./Partials/LoadingScreen.vue";

defineOptions({
    layout: SidebarLayout
})

const page = usePage()
const guestId = page.props.auth.user ? page.props.auth.user.id : uuidv4()
const isLoading = ref(false);

const fileUploaded = ref({})
const coverUploaded = ref({})
const coverUrl = ref("")
const isFileUploaded = ref(false)

const isCoverUploaded = ref(false)

const downloadLink = ref("")

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
        downloadLink.value = event.fileName
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
        downloadLink.value = event.fileName
    }
    isLoading.value = false
}

const form = ref({
    artist: '',
    title: '',
    genre: '',
    year: '',
    date: '',
    album: '',
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
        const res = await axios.post('/tools/tagEditor', formData)
        console.log(res.data.message)
    } catch (err) {
        if(err.response.data.error){
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

function onCoverUpload(event) {
    isCoverUploaded.value = true
    coverUploaded.value = event.target.files[0]
    form.coverRef = event.target.files[0]
    coverUrl.value = URL.createObjectURL(event.target.files[0])
    form.value.coverRef = event.target.files[0]
}

function updateTitle(e) {
    form.value.title = e
}

</script>
<template>
    <loading-screen v-if="isLoading" />

    <div class="max-w-3xl mx-auto flex flex-col h-screen text-white" v-if="!isLoading">
        <div v-if="!isFileUploaded" class="flex flex-col flex-grow justify-center items-center">
            <div class="mb-5 text-center">
                <p class="text-5xl font-bold mb-2">Tag Editor</p>
                <p class="text-3xl">Change information about your songs</p>
            </div>
            <UploadFile @file="getFile"/>
        </div>

        <!--    usunieto form.submit i dziala-->

        <div v-if="isFileUploaded && !downloadLink" class="mt-10 grid lg:grid-cols-2 gap-4 sm:grid-cols-1 sm:mx-10 lg:mx-0 p-6 bg-gray-800 rounded-lg shadow-lg" id="form">
            <div class="my-4 lg:col-span-2 text-white">
                <p class="text-lg">File Name: {{ fileUploaded.name }}</p>
                <p class="text-sm">File Size: {{ (fileUploaded.size / 1024 / 1024).toFixed(2) }} MB</p>
            </div>
            <InputFieldWithLabel label="Title" @update:model-value="form.title = $event"/>
            <InputFieldWithLabel label="Artist" @update:model-value="form.artist = $event"/>
            <InputFieldWithLabel label="Genre" @update:model-value="form.genre = $event"/>
            <InputFieldWithLabel label="Year" @update:model-value="form.year = $event"/>
            <InputFieldWithLabel label="Date" @update:model-value="form.date = $event"/>
            <InputFieldWithLabel label="Album" @update:model-value="form.album = $event"/>

            <div class="mb-6 lg:col-span-2">
                <label for="cover" class="block mb-2 uppercase font-bold text-xs text-white">
                    Cover
                </label>
                <!--            <div class="image-container">-->
                <input id="cover"
                       class="border border-gray-400 p-2 w-full text-white border-none rounded-lg bg-gray-500"
                       name="cover"
                       type="file"
                       @change="onCoverUpload"
                >
                <!--@input="file = $event.target.files[0].name" -->

                <div v-if="isCoverUploaded" class="w-[200px] h-[200px] ml-[10px] mt-2 ">
                    <img :src="coverUrl" alt="Cover Image" class="w-full h-full object-cover"/>
                </div>
                <!--            </div>-->
            </div>

            <div v-if="isFileUploaded && !downloadLink">
                <button type="button"  @click="onSubmit" class="bg-blue-400 text-white rounded py-2 px-4 mt-5 mr-3 hover:bg-blue-500">Submit</button>
                <button type="button"  @click="isFileUploaded = false" class="bg-blue-400 text-white rounded py-2 px-4 mt-5 mr-3 hover:bg-blue-500">Change file</button>
            </div>
        </div>

        <div v-if="downloadLink" class="text-white flex flex-col justify-center items-center h-screen">
            <p >You can now download your new file</p>
            <button class="bg-blue-400 text-white rounded py-2 px-4 mt-5 mr-3 hover:bg-blue-500" @click="downloadLink = null">go back</button>
            <DownloadTempFileButton :filename="fileUploaded.name" :token="downloadLink"/>
            <SaveToLibraryButton v-if="page.props.auth.user" :file-link="downloadLink"/>
        </div>

        <div v-if="isError">
            <p>{{ error }}</p>
        </div>

    </div>
</template>


<style scoped>

</style>
