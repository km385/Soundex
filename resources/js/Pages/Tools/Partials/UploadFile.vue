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
    event.preventDefault()
    document.getElementById('dragZone').classList.add('brightness-125')

}
function onDragLeave(event) {
    document.getElementById('dragZone').classList.remove('brightness-125')
    event.preventDefault()
}


function onClick(event) {
    fileInput.value.click()
}

function handleFileChange(event) {

    if (event.target.files[0] !== undefined) {
        const file = event.target.files[0]
        if (checkIfAudioFile(file)) {
            uploadedFile.value = file
        } else {
            alert("Please upload an audio file.");
        }

    }
}

function checkIfAudioFile(file) {
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

            <SvgComp name="plus-lg" :class="{'high-contrast-text':highContrast}" class="w-5 mr-2 -ml-1 text-gray-700" />

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
