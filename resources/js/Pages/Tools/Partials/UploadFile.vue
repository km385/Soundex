<script setup>
import {inject, ref, watch} from "vue";
import ChooseFile from "@/Pages/Tools/Partials/ChooseFile.vue";
import {usePage} from "@inertiajs/vue3";
import SvgComp from "@/Components/SvgComp.vue";

const uploadedFile = ref({})
const fileInput = ref(null)
const emit = defineEmits(['file'])

const props = defineProps({
    isButton: Boolean,
    allowVideo: {
        default: false,
        type: Boolean
    }
})

const page = usePage()

function onDrop(event) {
    console.log('handle drag event')
    event.preventDefault()
    if (event.dataTransfer.items) {
        const item = event.dataTransfer.items[0]
        if (item.kind === "file") {
            const file = item.getAsFile()
            if (checkIfAudioFile(file)) {
                uploadedFile.value = file
            } else {
                alert("Please upload an audio file.");
            }
        }
    } else {
        const file = event.dataTransfer.files[0]
        if (checkIfAudioFile(file)) {
            uploadedFile.value = file
        } else {
            alert("Please upload an audio file.");
        }
    }
}

function onDragOver(event) {
    console.log('handle drag over')

    event.preventDefault()
    document.getElementById('dragZone').classList.add('brightness-125')

}
function onDragLeave(event) {
    console.log('leave')
    document.getElementById('dragZone').classList.remove('brightness-125')
    event.preventDefault()
}


function onClick(event) {
    fileInput.value.click()
}

function handleFileChange(event) {
    console.log('handle file input')
    if (event.target.files[0] !== undefined) {
        const file = event.target.files[0]
        if (checkIfAudioFile(file)) {
            uploadedFile.value = file
        } else {
            alert("Please upload an audio file.");
        }

    }
}

// TODO: MIME type is determine based on extension, double check on the server (mime_content_type possibly)
function checkIfAudioFile(file) {
    console.log(file.type)
    // Check the MIME type of the file
    if (props.allowVideo) {
        return file.type.startsWith("video/")
    }
    return file.type.startsWith("audio/");
}

function getUsersFile(file) {
    uploadedFile.value = file
}

watch(uploadedFile, (value) => {
    emit('file', uploadedFile.value)
})

const highContrast = inject('highContrast')
</script>

<template>
    <div v-if="isButton">
        <label for="fileInput"
               :class="{'high-contrast-button': highContrast}"
               class="cursor-pointer inline-flex bg-blue-400 text-white rounded py-2 px-4 mt-5 mr-3 hover:bg-blue-500">
            <svg
                :class="{ 'text-yellow-300': highContrast }"
                class="-ml-1 mr-2 h-5 w-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            {{ $t("tools.addNewSong") }}
        </label>
        <input ref="fileInput" type="file" class="hidden" name="fileInput" id="fileInput" @change="handleFileChange">
    </div>
    <div id="dragZone"
         :class="{'high-contrast-input':highContrast}"
         class="p-6 bg-gray-800 rounded-3xl shadow-lg cursor-pointer hover:brightness-125 hover:scale-105 " v-if="!isButton"
         @click="onClick" @dragover="onDragOver" @dragleave.self="onDragLeave" @drop="onDrop">

        <div
            :class="{'high-contrast-border':highContrast}"
            class="flex items-center justify-center w-[300px] h-[300px] border-[5px] border-dashed border-blue-500 rounded-3xl ">
            <div class="flex flex-col items-center">
                <!--                <img class="w-1/2 h-1/2 mb-3" src="../../../../images/file_icon.webp" alt="file image">-->
                <SvgComp name="upload"
                         :class="{'high-contrast-text': highContrast}"
                         class="text-black w-[200px]"/>
                <p :class="{'high-contrast-text': highContrast}" class="text-blue-700 font-bold select-none">{{ $t("upload.title") }}</p>
            </div>
        </div>
        <input ref="fileInput" class="hidden" type="file" name="fileInput" id="fileInput" @change="handleFileChange">
    </div>
    <div v-if="page.props.auth.user">
        <ChooseFile @file-chosen="getUsersFile" class="mt-10" />
    </div>

</template>

<style scoped>

.high-contrast-border {
    @apply border-[#FFFF00FF]
}

.high-contrast-text {
    @apply text-[#FFFF00FF]
}

.high-contrast-input {
    @apply text-xl border border-[#FFFF00FF] bg-black text-[#FFFF00FF]
}

.high-contrast-button {
    @apply text-xl border border-[#FFFF00FF] bg-black text-[#FFFF00FF] focus:border-[#FFFF00FF] focus:ring-[#FFFF00FF] hover:bg-yellow-300 hover:text-black
}
</style>
