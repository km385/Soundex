<script setup>
import {inject, onMounted, onUnmounted, ref} from "vue";
import {v4 as uuidv4 } from 'uuid';
import {usePage} from "@inertiajs/vue3";
import {disconnectFromPrivate, disconnectFromPublic, subToChannel, subToPrivate} from "@/Subscriptions/subs.js";
import Wavesurfer from "./Partials/Wavesurfer.vue";
import UploadFile from "./Partials/UploadFile.vue";
import DownloadTempFile from "./Partials/DownloadTempFileButton.vue";
import SaveToLibraryButton from "./Partials/SaveToLibraryButton.vue";
import SidebarLayout from "@/Layouts/SidebarLayout.vue";
import LoadingScreen from "./Partials/LoadingScreen.vue";
import FileInfo from "@/Pages/Tools/Partials/FileInfo.vue";
import ResultOptionsScreen from "@/Pages/Tools/Partials/ResultOptionsScreen.vue";
import ToolsUploadScreen from "@/Pages/Tools/Partials/ToolsUploadScreen.vue";
import MainToolsWindow from "@/Pages/Tools/Partials/MainToolsWindow.vue";
defineOptions({
    layout: SidebarLayout
})
const highContrast = inject('highContrast')

const uploadedFile = ref({})

const speedValue = ref(null)
const pitchValue = ref(null)
const isUploaded = ref(false)
const fileToDownloadLink = ref("")


const page = usePage()
const guestId = page.props.auth.user ? `${page.props.auth.user.id}-${uuidv4()}` : uuidv4()
const isLoading = ref(false)

const isError = ref(false)
const error = ref("")
const isMounted = ref(true)
onMounted(() => {
    isMounted.value = true
    console.log(guestId)
    if(page.props.auth.user){
        subToPrivate(guestId, handleSubToPrivate)
    } else {
        subToChannel(guestId, handleSubToPublic)
    }
})

onUnmounted(() => {
    console.log('unmounted')
    isMounted.value = false
    if(page.props.auth.user) {
        disconnectFromPrivate(guestId)
    } else {
        disconnectFromPublic(guestId)
    }

})

function handleSubToPublic(event) {
    if(!isMounted.value) return

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
    if(!isMounted.value) return

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
    if(pitchValue.value == null) {
        pitchValue.value = 1.06
    }

    const formData = new FormData()
    formData.append('file', uploadedFile.value)
    formData.append('speedValue', speedValue.value)
    formData.append('pitchValue', pitchValue.value)
    formData.append('guestId', guestId)

    try {
        isLoading.value = true
        const res = await axios.post('/tools/speedup', formData)
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
    <loading-screen v-if="isLoading" />

    <MainToolsWindow v-if="!isLoading">
        <ToolsUploadScreen v-if="!isUploaded" :title="$t('speedUp.title')" :description="$t('speedUp.description')"
                           @file="getFile"/>

        <div v-if="isUploaded && !fileToDownloadLink"
             :class="{'high-contrast-input':highContrast}"
             class="mt-20 lg:mt-10 p-6 bg-gray-800 rounded-lg shadow-lg">
            <button type="button"  @click="isUploaded = false"
                    :class="{'high-contrast-button':highContrast}"
                    class="bg-blue-400 text-white rounded py-2 px-4 mt-5 mr-3 hover:bg-blue-500">{{ $t("tools.changeFile") }}</button>

            <FileInfo :file-size="uploadedFile.size" :file-name="uploadedFile.name" />

<!--            <Wavesurfer v-if="isUploaded" :file="uploadedFile" :show-region="false" :show-controls="true"/>-->
            <div class="flex flex-col items-start">
                <div class="w-auto mb-3 mt-3">
                    <label for="pitch" class="block font-medium text-sm mb-1" >{{ $t("speedUp.pitch") }}</label>
                    <input type="text" id="pitch" placeholder="1.06"
                           :class="{'high-contrast-input':highContrast}"
                           class="text-black bg-gray-50 border border-gray-500 rounded-lg focus:border-blue-500 focus:ring-blue-500" v-model="pitchValue">
                </div>
                <div class="w-auto">
                    <label for="speed" class="block font-medium text-sm mb-1" >{{ $t("speedUp.speed") }}</label>
                    <input type="text" id="speed" placeholder="1.20"
                           :class="{'high-contrast-input':highContrast}"
                           class="text-black bg-gray-50 border border-gray-500 rounded-lg focus:border-blue-500 focus:ring-blue-500" v-model="speedValue">
                </div>
                <button @click="onUploadButtonClick"
                        :class="{'high-contrast-button':highContrast}"
                        class="bg-blue-400 text-white rounded-lg py-2 px-4 mt-5 mr-3 hover:bg-blue-500 mb-3">
                    {{ $t("tools.submit") }}
                </button>

            </div>
        </div>

        <ResultOptionsScreen v-if="fileToDownloadLink" @go-back="fileToDownloadLink = ''"
                             :file-to-download-link="fileToDownloadLink" :file-to-download-name="uploadedFile.name"/>

        <div v-if="isError">
            <p>{{ error }}</p>
        </div>

    </MainToolsWindow>
</template>

<style scoped>
.high-contrast-input {
    @apply text-xl border border-[#FFFF00FF] bg-black text-[#FFFF00FF] focus:border-[#FFFF00FF] focus:ring-[#FFFF00FF]
}

.high-contrast-button {
    @apply text-xl border border-[#FFFF00FF] bg-black text-[#FFFF00FF] focus:border-[#FFFF00FF] focus:ring-[#FFFF00FF] hover:bg-yellow-300 hover:text-black
}
</style>
