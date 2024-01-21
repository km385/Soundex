<script setup>
import {usePage} from "@inertiajs/vue3";
import {v4 as uuidv4} from "uuid";
import {inject, onMounted, onUnmounted, ref} from "vue";
import {disconnectFromPrivate, disconnectFromPublic, subToChannel, subToPrivate} from "@/subscriptions/subs.js";
import LoadingScreen from "@/Pages/Tools/Partials/LoadingScreen.vue";
import SelectExtension from "@/Pages/Tools/Partials/SelectExtension.vue";
import SaveToLibraryButton from "@/Pages/Tools/Partials/SaveToLibraryButton.vue";
import Wavesurfer from "@/Pages/Tools/Partials/Wavesurfer.vue";
import DownloadTempFile from "@/Pages/Tools/Partials/DownloadTempFileButton.vue";
import UploadFile from "@/Pages/Tools/Partials/UploadFile.vue";
import axios from "axios";
import FileInfo from "@/Pages/Tools/Partials/FileInfo.vue";
import ResultOptionsScreen from "@/Pages/Tools/Partials/ResultOptionsScreen.vue";
import ToolsUploadScreen from "@/Pages/Tools/Partials/ToolsUploadScreen.vue";
import MainToolsWindow from "@/Pages/Tools/Partials/MainToolsWindow.vue";
import FormatSelection from "@/Pages/Tools/Partials/FormatSelection.vue";
import {useI18n} from "vue-i18n";

const page = usePage()
const v18n = useI18n()
const guestId = page.props.auth.user ? `${page.props.auth.user.id}-${uuidv4()}` : uuidv4()
const isLoading = ref(false)

const uploadedFile = ref({})
const isFileUploaded = ref(false)
const fileToDownloadLink = ref("")
const extension = ref("")

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

async function getFile(file) {
    uploadedFile.value = file;
    isFileUploaded.value = true
}

async function onSubmit() {
    const formData = new FormData()
    formData.append('file', uploadedFile.value)
    formData.append('guestId', guestId)
    formData.append('extension', extension.value)
    formData.append('bitrate', selectedBitrate.value)

    try {
        isLoading.value = true
        const res = await axios.post('/tools/converter', formData)

    } catch (e) {

    }

}

function getExtension(data) {
    extension.value = data

}

const bitrates = ref([64, 128, 192, 256, 320]);
const selectedBitrate = ref(192);

const selectBitrate = (bitrate) => {
    selectedBitrate.value = bitrate;

};

const highContrast = inject('highContrast')

</script>

<template>
    <loading-screen v-if="isLoading" />
    <MainToolsWindow v-if="!isLoading">

      <ToolsUploadScreen v-if="!isFileUploaded" :title="$t('converter.title')" :description="$t('converter.description')"
                         @file="getFile"/>

        <div v-if="isFileUploaded && !fileToDownloadLink"
             :class="{'high-contrast-input':highContrast}"
             class="mt-20 lg:mt-10 p-6 bg-gray-800 rounded-lg shadow-lg">
            <!-- File Information Section -->
            <button type="button" @click="isFileUploaded = false;isError = false"
                    :class="{'high-contrast-button':highContrast}"
                    class="bg-blue-400 text-white rounded py-2 px-4 hover:bg-blue-500 mb-4">{{ $t('tools.changeFile') }}</button>

            <FileInfo :file-size="uploadedFile.size" :file-name="uploadedFile.name" />

            <div id="app" class="flex flex-col lg:flex-row" v-if="extension !== 'flac' && extension !== 'wav'">
                <button
                    v-for="bitrate in bitrates"
                    :key="bitrate"
                    @click="selectBitrate(bitrate)"
                    class="bg-blue-400 text-white rounded py-2 px-2 mr-2 hover:bg-blue-500 mt-2 lg:mt-0"
                    :class="{ 'bg-green-800 hover:bg-green-800 drop-shadow-lg':selectedBitrate === bitrate,
                     'high-contrast-button': highContrast,
                     'high-contrast-button-selected': highContrast && selectedBitrate === bitrate}"
                >
                    {{ bitrate }} Kbps
                </button>
            </div>
            <div class="mt-6">
                <button type="button" @click="onSubmit"
                        :class="{'high-contrast-button':highContrast}"
                        class="bg-blue-400 text-white rounded py-2 px-4 hover:bg-blue-500">{{ $t('tools.submit') }}</button>
            </div>
            <FormatSelection @extension="getExtension"/>
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

.high-contrast-button-selected {
    @apply bg-yellow-300 text-black
}

.high-contrast-input {
    @apply text-xl border border-[#FFFF00FF] bg-black text-[#FFFF00FF]
}
</style>
