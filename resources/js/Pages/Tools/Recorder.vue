<script setup>
import {onMounted, ref} from "vue";
import {subToChannel, subToPrivate} from "@/Subscriptions/subs.js";
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
defineOptions({
    layout: SidebarLayout
})

const page = usePage()
const guestId = page.props.auth.user ? page.props.auth.user.id : uuidv4()
const isLoading = ref(false)

const recordingFile = ref({})
const isRecorded = ref(false)
const backgroundFile = ref({})
const isFileUploaded = ref(false)

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
    const recorder = await record()
    recorder.start()
    document.getElementById('stopButton').addEventListener('click', () => stopRecording(recorder));
}

async function stopRecording(recorder){
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
        console.log(result)
    }catch (error){
        console.log(`ERROR: ${error}`)
    }

}
function getFile(file) {
    console.log('file received')
    backgroundFile.value = file
    isFileUploaded.value = true
}

</script>

<template>
    <loading-screen v-if="isLoading" />

    <div class="max-w-3xl mx-auto text-white flex flex-col h-screen" v-if="!isLoading">
        <div v-if="!isFileUploaded" class="flex flex-col flex-grow justify-center items-center">
            <div class="mb-5 text-center">
                <p class="text-5xl font-bold mb-2">Recorder Tool</p>
                <p class="text-3xl">Record your voice over any song</p>
            </div>
            <UploadFile @file="getFile" />
        </div>

        <div v-if="isFileUploaded && !downloadLink" class="mt-10 p-6 bg-gray-800 rounded-lg shadow-lg">
            <button type="button"  @click="isFileUploaded = false" class="bg-blue-400 text-white rounded py-2 px-4 mt-5 mr-3 hover:bg-blue-500">Change file</button>
            <FileInfo :file-size="backgroundFile.size" :file-name="backgroundFile.name" />

            <Wavesurfer v-if="isFileUploaded" :file="backgroundFile" :show-controls="true" :id="'background'" />

            <div v-if="isRecorded">
                <hr class="mt-10 border-blue-600">
                <FileInfo :file-size="recordingFile.size" :file-name="recordingFile.name" />

                <Wavesurfer v-if="isRecorded" :file="recordingFile" :show-controls="true" :id="'recording'" />
            </div>
            <hr class="mt-10 border-blue-600">


            <div>
                <button @click="startRecording" class="bg-blue-400 text-white rounded py-2 px-4 mt-5 mr-3 hover:bg-blue-500">start</button>
                <button id="stopButton" class="bg-blue-400 text-white rounded py-2 px-4 mt-5 mr-3 hover:bg-blue-500">stop</button>
            </div>
            <div v-if="isRecorded && isFileUploaded">
                <button @click="onSend" class="bg-blue-400 text-white rounded py-2 px-4 mt-5 mr-3 hover:bg-blue-500">Send</button>
            </div>
        </div>

        <ResultOptionsScreen v-if="downloadLink" @go-back="downloadLink = ''"
                             :file-to-download-link="downloadLink" :file-to-download-name="'recording.mp3'"/>

        <div v-if="isError">
            <p>{{ error }}</p>
        </div>

    </div>
</template>
