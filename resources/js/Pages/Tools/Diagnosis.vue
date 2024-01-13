<script setup>
import {inject, onMounted, onUnmounted, ref} from "vue";
import axios from "axios";
import {usePage} from "@inertiajs/vue3";
import {v4 as uuidv4} from 'uuid';
import {disconnectFromPrivate, disconnectFromPublic, subToChannel, subToPrivate} from "@/Subscriptions/subs.js";
import SidebarLayout from "@/Layouts/SidebarLayout.vue";
import LoadingScreen from "./Partials/LoadingScreen.vue";
import ToolsUploadScreen from "@/Pages/Tools/Partials/ToolsUploadScreen.vue";
import MainToolsWindow from "@/Pages/Tools/Partials/MainToolsWindow.vue";

defineOptions({
    layout: SidebarLayout
})
const page = usePage()
const guestId = page.props.auth.user ? `${page.props.auth.user.id}-${uuidv4()}` : uuidv4()
const isLoading = ref(false)


const uploadedFile = ref({})
const isFileUploaded = ref(false)
const isFileReceived = ref(false)

const isError = ref(false)
const error = ref("")

const numberOfErrors = ref(null)
const isMounted = ref(true)
onMounted(() => {
    isMounted.value = true
    if (page.props.auth.user) {
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

const linkToFile = ref("")
const fileInfo = ref({

})

function formatDuration(value) {
    let originalDurationNumber = parseFloat(value);

    if (!isNaN(originalDurationNumber)) {
        const time = originalDurationNumber.toFixed()
        const minutes = Math.floor(time / 60);
        const seconds = (time % 60);
        const formattedMinutes = String(minutes).padStart(2, '0');
        const formattedSeconds = String(seconds).padStart(2, '0');
        return `${formattedMinutes}:${formattedSeconds}`;
    } else {
        console.error('Duration is not a valid number.');
        return '---'
    }
}

function setFileInfo(data) {
    const propertiesToAssign = [
        'name',
        'extension',
        'bitrate',
        'duration',
        'sample_rate',
        'title',
        'artist',
        'genre',
        'album',
        'numberOfErrors',
        'state'
    ];

    for (const property of propertiesToAssign) {
        fileInfo.value[property] = data[property];
        if(fileInfo.value[property] === "") {
            fileInfo.value[property] = "---"
        }
        if(property === 'bitrate' && fileInfo.value[property] !== "---") {
            fileInfo.value[property] /= 1000
            fileInfo.value[property] += ' kbit/s'
        }

        if(property === 'sample_rate' && fileInfo.value[property] !== "---") {
            fileInfo.value[property] /= 1000
            fileInfo.value[property] += ' kHz'
        }

        if(property === 'duration' && fileInfo.value[property] !== "---") {
            fileInfo.value[property] = formatDuration(fileInfo.value[property])
        }
    }
    fileInfo.value['numberOfErrors'] = data.numberOfErrors
    data.numberOfErrors > 0 ? fileInfo.value['state'] = "bad" : fileInfo.value['state'] = "good"

}

function handleSubToPublic(event) {
    if(!isMounted.value) return

    console.log("the event has been successfully captured")
    console.log(event)
    if (event.fileName === "ERROR") {
        error.value = "error has occurred"
        isError.value = true
    } else {

        let jsonData = JSON.parse(event.fileName);
        setFileInfo(jsonData)

        linkToFile.value = jsonData.path_to_saved_file
        numberOfErrors.value = jsonData.numberOfErrors
        isFileReceived.value = true


    }
    isLoading.value = false
}

function handleSubToPrivate(event) {
    if(!isMounted.value) return

    console.log("the event has been successfully captured")
    console.log(event)

    if (event.fileName === "ERROR") {
        error.value = "error has occurred"
        isError.value = true
    } else {
        let jsonData = JSON.parse(event.fileName);
        setFileInfo(jsonData)

        linkToFile.value = jsonData.path_to_saved_file
        numberOfErrors.value = jsonData.numberOfErrors
        isFileReceived.value = true
    }
    isLoading.value = false
}

async function onSubmitClicked() {


    const formData = new FormData();

    formData.append('file', uploadedFile.value)
    formData.append('guestId', guestId)

    try {
        isLoading.value = true
        const res = await axios.post('/tools/diagnosis', formData)
        console.log(res.data.message)
    } catch (e) {
        console.log(e)
    }
}

async function getFile(file) {
    console.log('get file')

    uploadedFile.value = file;
    isFileUploaded.value = true

    await onSubmitClicked()
}

function downloadFile() {
    console.log('pobieranie')
    axios
        .get(`/file/${linkToFile.value}`, {
            responseType: 'blob',
        })
        .then((response) => {


            const url = window.URL.createObjectURL(new Blob([response.data]));
            const link = document.createElement('a');
            link.href = url;
            link.setAttribute('download', "errors.txt");
            document.body.appendChild(link);
            link.click();
        })
        .catch((error) => {
            console.log(error);
        });
}

const highContrast = inject('highContrast')

</script>

<template>
    <loading-screen v-if="isLoading"/>
    <MainToolsWindow v-if="!isLoading">
        <ToolsUploadScreen v-if="!isFileUploaded" :title="$t('diagnosis.title')" :description="$t('diagnosis.description')"
                           @file="getFile"/>

        <div v-if="isFileReceived"
             :class="{'high-contrast-input': highContrast}"
             class="mt-20 lg:mt-10 p-6 bg-gray-800 rounded-lg shadow-lg">
            <button type="button" @click="isFileUploaded = false; isFileReceived = false"
                    :class="{ 'high-contrast-button' : highContrast }"
                    class="bg-blue-400 text-white rounded py-2 px-4 hover:bg-blue-500 mb-4">{{ $t("tools.changeFile") }}
            </button>

            <div

                class="grid grid-cols-1 lg:grid-cols-3 gap-4 my-5 text-black">
                <div v-for="(value, key, index) in fileInfo" :key="key"
                     :class="{
                        'high-contrast-input':highContrast,
                        'lg:col-span-3': index >= Object.keys(fileInfo).length - 2,
                        'bg-red-700': numberOfErrors > 0 && key === 'state',
                        'bg-green-700': numberOfErrors === 0 && key === 'state'
                     }"
                     class="p-4 bg-gray-300 rounded-md shadow-md">
                    <p class="text-lg font-semibold mb-2 capitalize">{{
                            $t(`diagnosis.data.${key}`)
                        }}</p>
                    <p v-if="key !== 'state'">{{ value }}</p>
                    <p v-else>{{ $t(`diagnosis.${value}`) }}</p>
                </div>
            </div>



            <div
                v-if="fileInfo['numberOfErrors'] > 0"
                class="flex items-center justify-between mb-4 mt-4">
                <h2
                    :class="{'text-yellow-300':highContrast}"
                    class="text-xl font-semibold text-white">{{ $t('diagnosis.downloadText') }}</h2>
                <a
                    :class="{'high-contrast-button': highContrast}"
                    class="ml-3 bg-blue-400 text-white rounded py-2 px-4 hover:bg-blue-500 whitespace-nowrap"
                    href="#"
                    @click="downloadFile"
                >
                    <b>{{ $t('diagnosis.download') }}</b> <br> {{ $t("resultOptionsScreen.downloadFile") }}
                </a>
            </div>
        </div>
<!--        <ResultOptionsScreen v-if="fileToDownloadLink" @go-back="fileToDownloadLink = ''"-->
<!--                             :file-to-download-link="fileToDownloadLink" :file-to-download-name="uploadedFile.name"/>-->

        <div v-if="isError" class="text-red-500">
            <!-- Error Handling Section -->
            <p class="p-6 bg-gray-800 rounded-lg shadow-lg">{{ error }}</p>
        </div>
    </MainToolsWindow>


</template>

<style scoped>
.high-contrast-input {
    @apply text-xl border border-[#FFFF00FF] bg-black text-[#FFFF00FF]
}
.high-contrast-button {
    @apply text-xl border border-[#FFFF00FF] bg-black text-[#FFFF00FF] focus:border-[#FFFF00FF] focus:ring-[#FFFF00FF] hover:bg-yellow-300 hover:text-black
}
</style>
