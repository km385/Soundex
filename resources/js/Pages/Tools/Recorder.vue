<script>
import {ref} from "vue";

const isLoading = ref(false)
</script>

<script setup>
import Wavesurfer from "@/Pages/Tools/Wavesurfer.vue";
import {onMounted, ref} from "vue";
import UploadFile from "@/Pages/Tools/UploadFile.vue";

defineOptions({
    layout: ( h, page ) => h( SidebarLayout, {  isLoading : isLoading.value } , () => page )
})
const page = usePage()
const guestId = page.props.auth.user ? page.props.auth.user.id : uuidv4()

const recordingFile = ref({})
const isRecorded = ref(false)
const backgroundFile = ref(null)
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
        const result = await axios.post('/recorder', formData)
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
import CustomAuthenticatedLayout from "@/Layouts/CustomAuthenticatedLayout.vue";
import {subToChannel, subToPrivate} from "@/subscriptions/subs.js";
import {v4 as uuidv4} from "uuid";
import {usePage} from "@inertiajs/vue3";
import DownloadTempFile from "@/Pages/Tools/DownloadTempFileButton.vue";
import SaveToLibraryButton from "@/Pages/Tools/SaveToLibraryButton.vue";
import SidebarLayout from "@/Layouts/SidebarLayout.vue";
</script>

<template>
    <div class="max-w-3xl mx-auto text-white" v-if="!isLoading">
        <div v-if="!isFileUploaded" class="flex justify-center items-center h-screen">
            <UploadFile @file="getFile" />
        </div>

        <div v-if="isFileUploaded && !downloadLink">
            <button @click="startRecording" class="bg-blue-400 text-white rounded py-2 px-4 mt-5 mr-3 hover:bg-blue-500">start</button>
            <button id="stopButton" class="bg-blue-400 text-white rounded py-2 px-4 mt-5 mr-3 hover:bg-blue-500">stop</button>
            <button @click="onSend" class="bg-blue-400 text-white rounded py-2 px-4 mt-5 mr-3 hover:bg-blue-500">Send</button>
            <button type="button"  @click="isFileUploaded = false" class="bg-blue-400 text-white rounded py-2 px-4 mt-5 mr-3 hover:bg-blue-500">Change file</button>

            <Wavesurfer v-if="isRecorded" :file="recordingFile" :show-controls="true"/>
        </div>

        <div v-if="downloadLink" class="text-white flex flex-col justify-center items-center h-screen">
            <p >You can now download your new file</p>
            <button class="bg-blue-400 text-white rounded py-2 px-4 mt-5 mr-3 hover:bg-blue-500" @click="downloadLink = null">go back</button>
            <DownloadTempFile :filename="'recording.mp3'" :token="downloadLink"/>
            <SaveToLibraryButton v-if=" page.props.auth.user" :file-link="downloadLink"/>
        </div>
    </div>
</template>

<style scoped>

</style>
