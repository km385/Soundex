<script setup>
import {inject, onMounted, onUnmounted, ref} from "vue";
import {disconnectFromPrivate, disconnectFromPublic, subToChannel, subToPrivate} from "@/Subscriptions/subs.js";
import {v4 as uuidv4} from "uuid";
import {usePage} from "@inertiajs/vue3";
import SidebarLayout from "@/Layouts/SidebarLayout.vue";
import Wavesurfer from "./Partials/Wavesurfer.vue";
import UploadFile from "./Partials/UploadFile.vue";
import DownloadTempFile from "./Partials/DownloadTempFileButton.vue";
import SaveToLibraryButton from "./Partials/SaveToLibraryButton.vue";
import LoadingScreen from "./Partials/LoadingScreen.vue";
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

const recordingFile = ref({})
const isRecorded = ref(false)
const backgroundFile = ref({})
const isFileUploaded = ref(false)

const downloadLink = ref("")

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
        downloadLink.value = event.fileName
    }
    isLoading.value = false
}
function handleSubToPrivate(event) {
    if(!isMounted.value) return

    if(event.fileName === "ERROR") {
        error.value = v18n.t('error')
        isError.value = true
    } else {
        downloadLink.value = event.fileName
    }
    isLoading.value = false
}

function record(){
    return new Promise(resolve => {
        navigator.mediaDevices.getUserMedia({ audio: true })
            .then(stream => {
                const mediaRecorder = new MediaRecorder(stream);
                const audioChunks = [];

                // mediaRecorder.addEventListener("dataavailable", event => {
                //     audioChunks.push(event.data);
                // });

                mediaRecorder.ondataavailable = (event) => {
                    audioChunks.push(event.data);
                }

                const start = () => {
                    mediaRecorder.start();
                };

                const stop = () => {
                    return new Promise(resolve => {
                        mediaRecorder.addEventListener("stop", () => {
                            const audioBlob = new Blob(audioChunks);
                            const audioUrl = URL.createObjectURL(audioBlob);
                            const audio = new Audio(audioUrl);
                            const play = () => {
                                audio.play();
                            };
                            resolve({ audioBlob, audioUrl, play });
                        });

                        mediaRecorder.stop();
                    });
                };

                resolve({ start, stop });
            });
    });
}

const sleep = time => new Promise(resolve => setTimeout(resolve, time));
async function startRecording(){
    // use exposed method to control wavesurfer component
    wave.value.onPlayClicked()

    const recorder = await record()
    recorder.start()
    document.getElementById('stopButton').addEventListener('click', () => stopRecording(recorder));
}

async function stopRecording(recorder){
    wave.value.onStopClicked()

    const audio = await recorder.stop()

    recordingFile.value = new File([audio.audioBlob], "recording.webm")
    isRecorded.value = true
}

async function onSend(){
    const formData = new FormData()
    formData.append('recording', recordingFile.value)
    formData.append('background', backgroundFile.value)
    formData.append('guestId', guestId)

    try {
        isLoading.value = true
        const result = await axios.post('recorder', formData)
    }catch (error){
    }

}
function getFile(file) {
    backgroundFile.value = file
    isFileUploaded.value = true
}

const wave = ref(null)

const highContrast = inject('highContrast')

</script>

<template>
    <loading-screen v-if="isLoading" />

    <MainToolsWindow v-if="!isLoading">
        <ToolsUploadScreen v-if="!isFileUploaded" :title="$t('recorder.title')" :description="$t('recorder.description')"
                           @file="getFile"/>

        <div v-if="isFileUploaded && !downloadLink"
             :class="{'high-contrast-input':highContrast}"
             class="mt-20 lg:mt-10 p-6 bg-gray-800 rounded-lg shadow-lg">
            <button type="button"  @click="isFileUploaded = false;isError = false"
                    :class="{'high-contrast-button':highContrast}"
                    class="bg-blue-400 text-white rounded py-2 px-4 mt-5 mr-3 hover:bg-blue-500">{{ $t("tools.changeFile") }}</button>
            <FileInfo :file-size="backgroundFile.size" :file-name="backgroundFile.name" />

            <Wavesurfer v-if="isFileUploaded" :file="backgroundFile" :show-controls="true" :id="'background'" ref="wave"/>

            <div v-if="isRecorded">
                <hr
                    :class="{'high-contrast-input':highContrast}"
                    class="mt-10 border-blue-600">
                <FileInfo :file-size="recordingFile.size" :file-name="recordingFile.name" />

                <Wavesurfer v-if="isRecorded" :file="recordingFile" :show-controls="true" :id="'recording'" />
            </div>
            <hr
                :class="{'high-contrast-input': highContrast}"
                class="mt-10 border-blue-600">


            <div>
                <button @click="startRecording"
                        :class="{'high-contrast-button':highContrast}"
                        class="bg-blue-400 text-white rounded py-2 px-4 mt-5 mr-3 hover:bg-blue-500">start</button>
                <button id="stopButton"
                        :class="{'high-contrast-button':highContrast}"
                        class="bg-blue-400 text-white rounded py-2 px-4 mt-5 mr-3 hover:bg-blue-500">stop</button>
            </div>
            <div v-if="isRecorded && isFileUploaded">
                <button @click="onSend"
                        :class="{'high-contrast-button':highContrast}"
                        class="bg-blue-400 text-white rounded py-2 px-4 mt-5 mr-3 hover:bg-blue-500">{{ $t("tools.submit") }}</button>
            </div>
        </div>

        <ResultOptionsScreen v-if="downloadLink" @go-back="downloadLink = ''"
                             :file-to-download-link="downloadLink" :file-to-download-name="'recording.mp3'"/>

        <div v-if="isError">
            <p>{{ error }}</p>
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
