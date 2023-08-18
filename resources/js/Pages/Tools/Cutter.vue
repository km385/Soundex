<script setup>
import Wavesurfer from "@/Pages/Tools/Wavesurfer.vue";
import {nextTick, onMounted, reactive, ref, watch} from "vue";
import axios from "axios";
import UploadFile from "@/Pages/Tools/UploadFile.vue";
import CustomAuthenticatedLayout from "@/Layouts/CustomAuthenticatedLayout.vue";
import {usePage} from "@inertiajs/vue3";
import { v4 as uuidv4 } from 'uuid';
import {subToChannel, subToPrivate} from "@/subscriptions/subs.js";
import SaveToLibraryButton from "@/Pages/Tools/SaveToLibraryButton.vue";
import DownloadTempFile from "@/Pages/Tools/DownloadTempFileButton.vue";

const page = usePage()
let guestId = 123

const uploadedFile = ref({})
let isFileUploaded = ref(false)
let fileToDownloadName = ref("")

const mainRegionData = reactive({
    start: 0,
    end: 0
})

const secondaryRegionData = reactive({
    start: null,
    end: null
})
const volumeValue = ref(0.1)
// watch(volumeValue, (value, prevValue) => {
//     ws.value.setVolume(value)
// })

const regionCheckboxValue = ref(false)
watch(regionCheckboxValue, (value) => {
    console.log(`check ${value}`)
})

onMounted(() => {
    if(page.props.auth.user){
        guestId = page.props.auth.user.id
        subToPrivate(guestId, handleSubToPrivate)
    } else {
        guestId = uuidv4()
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

function onCutClicked(){
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

    axios.post('/cutFile', formData)
        .then(res => {
            if(res.data.error === "lol"){
                // TODO return error?
                console.log('error while uploading')
            }
            //
            if(res.data.message){
                console.log(res.data.message)
            }

            if(res.data.fileToDownload){
                fileToDownloadName.value = res.data.fileToDownload
            }
        })
        .catch(err => {
            console.log(err)
        })
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
    <CustomAuthenticatedLayout>
        <form>
            <div class="mb-6">
                <!--             todo add change file button -->
                <UploadFile v-if="!isFileUploaded" @file="getFile" />

                <div v-if="isFileUploaded">
                    <button type="button"  @click="onCutClicked" class="bg-blue-400 text-white rounded py-2 px-4 mt-5 mr-3 hover:bg-blue-500">Cut</button>
                    <label class="mr-3" for="regionCheckBox">Second region</label><input id="regionCheckBox" type="checkbox" v-model="regionCheckboxValue" class="bg-blue-400 text-white rounded py-2 px-4 mt-5 mr-3 hover:bg-blue-500">
                </div>

            </div>
        </form>

        <Wavesurfer v-if="isFileUploaded" :file="uploadedFile" :show-region="true" :show-controls="true" :second-region="regionCheckboxValue" @region-coords="getRegionData" />
        <!--                <input type="range"-->
        <!--                       style="appearance: slider-vertical"-->
        <!--                       class=""-->
        <!--                       id="volume"-->
        <!--                       name="volume"-->
        <!--                       min="0"-->
        <!--                       max="1"-->
        <!--                       step="0.1"-->
        <!--                       v-model="volumeValue"-->
        <!--                       v-if="isFileUploaded">-->



        <DownloadTempFile v-if="fileToDownloadName" :filename="uploadedFile.name" :token="fileToDownloadName"/>
        <SaveToLibraryButton v-if="fileToDownloadName && page.props.auth.user" :file-link="fileToDownloadName"/>

    </CustomAuthenticatedLayout>

</template>

<style scoped>

</style>
