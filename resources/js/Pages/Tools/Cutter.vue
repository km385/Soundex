<script setup>
import {inject, onMounted, onUnmounted, reactive, ref, watch} from "vue";
import axios from "axios";
import {usePage} from "@inertiajs/vue3";
import {v4 as uuidv4} from 'uuid';
import {subToChannel, subToPrivate, disconnectFromPublic, disconnectFromPrivate} from "@/Subscriptions/subs.js";
import SidebarLayout from "@/Layouts/SidebarLayout.vue";
import LoadingScreen from "./Partials/LoadingScreen.vue";
import Wavesurfer from "./Partials/Wavesurfer.vue";
import FileInfo from "@/Pages/Tools/Partials/FileInfo.vue";
import ResultOptionsScreen from "@/Pages/Tools/Partials/ResultOptionsScreen.vue";
import ToolsUploadScreen from "@/Pages/Tools/Partials/ToolsUploadScreen.vue";
import MainToolsWindow from "@/Pages/Tools/Partials/MainToolsWindow.vue";
// component data => layout props
// choose manually persistent layout and give it its props and children
// use h(type, props, children) render function
defineOptions({
    layout: SidebarLayout
})
const page = usePage()
const guestId = page.props.auth.user ? `${page.props.auth.user.id}-${uuidv4()}` : uuidv4()
const isLoading = ref(false)

const highContrast = inject('highContrast')
const isMounted = ref(true)

const uploadedFile = ref({})
const isFileUploaded = ref(false)
const fileToDownloadLink = ref("")

const isError = ref(false)
const error = ref("")

const mainRegionData = reactive({
    start: 0,
    end: 0
})

const secondaryRegionData = reactive({
    start: null,
    end: null
})

const regionCheckboxValue = ref(false)
watch(regionCheckboxValue, (value) => {
    console.log(`check ${value}`)
})

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

function handleSubToPublic(event) {
    if(!isMounted.value) return

    console.log("the event has been successfully captured")
    console.log(event)

    if (event.fileName === "ERROR") {
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

    if (event.fileName === "ERROR") {
        error.value = "error has occurred"
        isError.value = true
    } else {
        fileToDownloadLink.value = event.fileName
    }
    isLoading.value = false
}

async function onCutClicked() {
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
    if (secondaryRegionData.start != null && secondaryRegionData.end != null) {
        formData.append('start2', secondaryRegionData.start)
        formData.append('end2', secondaryRegionData.end)
    }
    formData.append('file', uploadedFile.value)
    formData.append('guestId', guestId)

    try {
        isLoading.value = true
        const res = await axios.post('/tools/cutFile', formData)
        console.log(res.data.message)
    } catch (e) {
        console.log(e)
    }
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
    switch (data.id) {
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

onUnmounted(() => {
    console.log('unmounted')
    isMounted.value = false
    if(page.props.auth.user) {
        disconnectFromPrivate(guestId)
    } else {
        disconnectFromPublic(guestId)
    }

})

</script>

<template>
    <loading-screen v-if="isLoading"/>
    <MainToolsWindow v-if="!isLoading">
        <ToolsUploadScreen v-if="!isFileUploaded" :title="$t('cutter.title')" :description="$t('cutter.description')"
                           @file="getFile"/>

        <div v-if="isFileUploaded && !fileToDownloadLink"
             :class="{ 'high-contrast-input' : highContrast }"
             class="mt-20 pt-6 p-6 bg-gray-800 rounded-lg shadow-lg lg:mt-10 ">
            <!-- File Information Section -->
            <!--            <div class="p-6 bg-gray-800 rounded-lg shadow-lg">-->
            <button type="button" @click="isFileUploaded = false"
                    :class="{ 'high-contrast-button' : highContrast }"
                    class="bg-blue-400 text-white rounded py-2 px-4 hover:bg-blue-500 mb-4">{{ $t("tools.changeFile") }}
            </button>

            <FileInfo :file-size="uploadedFile.size" :file-name="uploadedFile.name"/>
            <Wavesurfer v-if="isFileUploaded" :file="uploadedFile" :show-region="true" :show-controls="true"
                        :allow-second-region="true" @region-coords="getRegionData"/>

            <div class="mt-6">
                <button type="button" @click="onCutClicked"
                        :class="{'high-contrast-button': highContrast }"
                        class="bg-blue-400 text-white rounded py-2 px-4 hover:bg-blue-500">{{ $t("tools.submit") }}
                </button>
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
.high-contrast-input {
    @apply text-xl border border-[#FFFF00FF] bg-black text-[#FFFF00FF]
}
.high-contrast-button {
    @apply text-xl border border-[#FFFF00FF] bg-black text-[#FFFF00FF] focus:border-[#FFFF00FF] focus:ring-[#FFFF00FF] hover:bg-yellow-300 hover:text-black
}
</style>
