<script setup>
import {inject, ref, watch} from "vue";
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

function checkIfAudioFile(file) {
    console.log(file.type)
    if (props.allowVideo) {
        return file.type.startsWith("video/")
    }
    return file.type.startsWith("audio/");
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
            
        </label>
        <input ref="fileInput" type="file" class="hidden" name="fileInput" id="fileInput" @change="handleFileChange">
    </div>
    <div id="dragZone"
         :class="{'high-contrast-input':highContrast}"
         class="p-2 bg-gray-800 rounded-sm shadow-lg cursor-pointer hover:brightness-125 hover:scale-105 hover:bg-green-800 outline-dashed outline-green-800
          outline-offset-4 whitespace-nowrap " v-if="!isButton"
         @click="onClick" @dragover="onDragOver" @dragleave.self="onDragLeave" @drop="onDrop">
         <div>
            <p>UPLOAD FILE</p>
         </div>
        <input ref="fileInput" class="hidden" type="file" name="fileInput" id="fileInput" @change="handleFileChange">
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
