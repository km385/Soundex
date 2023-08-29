<script setup>
import CustomAuthenticatedLayout from "@/Layouts/CustomAuthenticatedLayout.vue";
import DownloadTempFileButton from "@/Pages/Tools/DownloadTempFileButton.vue";
import {subToPrivate, subToChannel} from "@/subscriptions/subs.js";
import {v4 as uuidv4} from "uuid";
import {useForm, usePage} from "@inertiajs/vue3";
import {onMounted, ref, watch} from "vue";
import axios from "axios";

import UploadFile from "@/Pages/Tools/UploadFile.vue";
import SaveToLibraryButton from "@/Pages/Tools/SaveToLibraryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import Wavesurfer from "@/Pages/Tools/Wavesurfer.vue";
import InputFieldWithLabel from "@/Components/InputFieldWithLabel.vue";
import SidebarLayout from "@/Layouts/SidebarLayout.vue";
const page = usePage()

const guestId = page.props.auth.user ? page.props.auth.user.id : uuidv4()
const fileUploaded = ref({})
const coverUploaded = ref({})
const coverUrl = ref("")
const isFileUploaded = ref(false)

const isCoverUploaded = ref(false)

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
}

function handleSubToPrivate(event) {
    console.log("the event has been successfully captured")
    console.log(event)
    downloadLink.value = event.fileName
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
        const res = await axios.post('/metachange', formData)
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

        <!--    usunieto form.submit i dziala-->
        <form>
            <InputFieldWithLabel label="Title" @update:model-value="form.title = $event"/>
            <InputFieldWithLabel label="Artist" @update:model-value="form.artist = $event"/>
            <InputFieldWithLabel label="Genre" @update:model-value="form.genre = $event"/>
            <InputFieldWithLabel label="Year" @update:model-value="form.year = $event"/>
            <InputFieldWithLabel label="Date" @update:model-value="form.date = $event"/>
            <InputFieldWithLabel label="Album" @update:model-value="form.album = $event"/>

            <div class="mb-6">
                <label for="cover" class="block mb-2 uppercase font-bold text-xs text-gray-700">
                    Cover
                </label>
                <!--            <div class="image-container">-->
                <input id="cover"
                       class="border border-gray-400 p-2 w-full"
                       name="cover"
                       type="file"
                       @change="onCoverUpload"
                >
                <!--@input="file = $event.target.files[0].name" -->

                <div v-if="isCoverUploaded" class="image-preview">
                    <img :src="coverUrl" alt="Cover Image"/>
                </div>
                <!--            </div>-->
            </div>

            <UploadFile @file="getFile"/>
            <button type="button"  @click="onSubmit" class="bg-blue-400 text-white rounded py-2 px-4 mt-5 mr-3 hover:bg-blue-500">Submit</button>

            <DownloadTempFileButton v-if="downloadLink" :filename="fileUploaded.name" :token="downloadLink"/>
            <SaveToLibraryButton v-if="downloadLink" :file-link="downloadLink"/>
        </form>
</template>


<style scoped>
.image-container {
    position: relative;
    display: flex;
    align-items: center;
}

.image-preview {
    margin-left: 10px;
    width: 300px; /* Adjust the size as needed */
    height: 300px; /* Adjust the size as needed */
    overflow: hidden;
}

.image-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
</style>
