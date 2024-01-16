<script setup>

import SaveToLibraryButton from "@/Pages/Tools/Partials/SaveToLibraryButton.vue";
import LoadingScreen from "@/Pages/Tools/Partials/LoadingScreen.vue";
import Wavesurfer from "@/Pages/Tools/Partials/Wavesurfer.vue";
import UploadFile from "@/Pages/Tools/Partials/UploadFile.vue";
import DownloadTempFile from "@/Pages/Tools/Partials/DownloadTempFileButton.vue";
import SidebarLayout from "@/Layouts/SidebarLayout.vue";
import {usePage} from "@inertiajs/vue3";
import {v4 as uuidv4} from "uuid";
import {computed, inject, onMounted, onUnmounted, reactive, ref, watch} from "vue";
import {disconnectFromPrivate, disconnectFromPublic, subToChannel, subToPrivate} from "@/subscriptions/subs.js";
import axios from "axios";
import FileInfo from "@/Pages/Tools/Partials/FileInfo.vue";
import ResultOptionsScreen from "@/Pages/Tools/Partials/ResultOptionsScreen.vue";
import ToolsUploadScreen from "@/Pages/Tools/Partials/ToolsUploadScreen.vue";
import MainToolsWindow from "@/Pages/Tools/Partials/MainToolsWindow.vue";
import {useI18n} from "vue-i18n";

defineOptions({
    layout: SidebarLayout
})
const page = usePage()
const v18n = useI18n()
const guestId = page.props.auth.user ? `${page.props.auth.user.id}-${uuidv4()}` : uuidv4()
const isLoading = ref(false)


const uploadedFile = ref({})
const isFileUploaded = ref(false)
const fileToDownloadLink = ref("")

const isError = ref(false)
const error = ref("")
const isMounted = ref(true)
onMounted(() => {
    isMounted.value = true
    if(page.props.auth.user){
        subToPrivate(guestId, handleSubToPrivate)
    } else {
        subToChannel(guestId, handleSubToPublic)
    }
})

onUnmounted(() => {

    isMounted.value = false
    if(page.props.auth.user) {
        disconnectFromPrivate(guestId)
    } else {
        disconnectFromPublic(guestId)
    }

})

function handleSubToPublic(event) {
    if(!isMounted.value) return

    if(event.fileName === "ERROR") {
        error.value = v18n.t('error')
        isError.value = true
    } else {
        fileToDownloadLink.value = event.fileName
    }
    isLoading.value = false
}

function handleSubToPrivate(event) {
    if(!isMounted.value) return

    if(event.fileName === "ERROR") {
        error.value = v18n.t('error')
        isError.value = true
    } else {
        fileToDownloadLink.value = event.fileName
    }
    isLoading.value = false
}

async function onSubmit(){

    axios.interceptors.request.use(req => {
        return req
    })

    const formData = new FormData();

    formData.append('file', uploadedFile.value)
    formData.append('guestId', guestId)
    formData.append('volume', percentageVolumeChange.value / 100)

    try {
        isLoading.value = true
        const res = await axios.post('/tools/volumechanger', formData)

    }catch (e) {

    }
}

async function getFile(file) {

    uploadedFile.value = file;
    isFileUploaded.value = true
}

const waveformRef = ref(null)

const startVolumeValue = 0.33333
const volumeValue = ref(startVolumeValue)
watch(volumeValue, (value) => {
    waveformRef.value.changeVolume(value)
})

const percentageVolumeChange = computed(() => {
    return Math.round(((((volumeValue.value - startVolumeValue) / startVolumeValue)) + Number.EPSILON) * 100)
})

const highContrast = inject('highContrast')

</script>

<template>

    <loading-screen v-if="isLoading" />
    <MainToolsWindow v-if="!isLoading">
        <ToolsUploadScreen v-if="!isFileUploaded" :title="$t('volumeChanger.title')" :description="$t('volumeChanger.description')"
                           @file="getFile"/>


        <div v-if="isFileUploaded && !fileToDownloadLink"
             :class="{'high-contrast-input':highContrast}"
             class="mt-20 lg:mt-10 p-6 bg-gray-800 rounded-lg shadow-lg">

            <button type="button" @click="isFileUploaded = false;isError = false"
                    :class="{'high-contrast-button':highContrast}"
                    class="bg-blue-400 text-white rounded py-2 px-4 hover:bg-blue-500 mb-4">{{ $t("tools.changeFile") }}</button>

            <FileInfo :file-size="uploadedFile.size" :file-name="uploadedFile.name" />

            <Wavesurfer ref="waveformRef" v-if="isFileUploaded" :file="uploadedFile" :show-region="false"
                        :show-controls="true" :allow-second-region="false" :allow-volume-control="false"/>


            <input type="range" min="0" max="1" step="0.0001"
                   :class="{'custom-slider-thumb-high-contrast':highContrast}"
                   class="mt-3 w-full h-4 bg-gray-200 rounded-lg  cursor-pointer dark:bg-gray-700 custom-slider-thumb "
                   id="myRange" v-model="volumeValue">
            {{(percentageVolumeChange<0?"":"+")}}{{ percentageVolumeChange }}%

            <div class="mt-6">
                <button type="button" @click="onSubmit"
                        :class="{'high-contrast-button':highContrast}"
                        class="bg-blue-400 text-white rounded py-2 px-4 hover:bg-blue-500">{{ $t("tools.submit") }}</button>
            </div>

        </div>

        <ResultOptionsScreen v-if="fileToDownloadLink" @go-back="fileToDownloadLink = ''"
                             :file-to-download-link="fileToDownloadLink" :file-to-download-name="uploadedFile.name"/>

        <div v-if="isError" class="text-red-500">
            <!-- Error Handling Section -->
            <p class="p-6 bg-gray-800 rounded-lg shadow-lg">{{ error }}</p>
        </div>
    </MainToolsWindow>



</template>

<style scoped>

.high-contrast-button {
    @apply text-xl border border-[#FFFF00FF] bg-black text-[#FFFF00FF] focus:border-[#FFFF00FF] focus:ring-[#FFFF00FF] hover:bg-yellow-300 hover:text-black
}

.high-contrast-input {
    @apply text-xl border border-[#FFFF00FF] bg-black text-[#FFFF00FF]
}

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

.custom-slider-thumb-high-contrast{
    -webkit-appearance: none;
}

.custom-slider-thumb-high-contrast::-webkit-slider-thumb {
    -webkit-appearance: none;
    border: none;
    height: 30px;
    width: 30px;
    border-radius: 50%;
    background: black;
}

.custom-slider-thumb-high-contrast:focus {
    outline: none;
}

.custom-slider-thumb-high-contrast::-webkit-slider-runnable-track {
    background: yellow;
    border-radius: 16px;
}
</style>
