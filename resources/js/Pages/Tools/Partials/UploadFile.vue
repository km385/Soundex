<script setup>
import {ref, watch} from "vue";

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

watch(uploadedFile, (value) => {
    emit('file', uploadedFile.value)
})
</script>

<template>
    <div v-if="isButton">
        <label for="fileInput"
               class="cursor-pointer inline-flex bg-blue-400 text-white rounded py-2 px-4 mt-5 mr-3 hover:bg-blue-500">
            <svg class="-ml-1 mr-2 h-5 w-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Add new song
        </label>
        <input ref="fileInput" type="file" class="hidden" name="fileInput" id="fileInput" @change="handleFileChange">
    </div>
    <div id="dragZone" class="p-6 bg-gray-800 rounded-3xl shadow-lg cursor-pointer hover:brightness-125" v-if="!isButton"
         @click="onClick" @dragover="onDragOver" @dragleave.self="onDragLeave" @drop="onDrop">

        <div
            class="flex items-center justify-center w-[300px] h-[300px] border-[5px] border-dashed border-blue-500 rounded-3xl ">
            <div class="flex flex-col items-center">
                <!--                <img class="w-1/2 h-1/2 mb-3" src="../../../../images/file_icon.webp" alt="file image">-->
                <svg xmlns="http://www.w3.org/2000/svg" height="200" viewBox="0 -960 960 960"
                     preserveAspectRatio="none">
                    <path fill="#000000"
                          d="M240-160q-33 0-56.5-23.5T160-240v-80q0-17 11.5-28.5T200-360q17 0 28.5 11.5T240-320v80h480v-80q0-17 11.5-28.5T760-360q17 0 28.5 11.5T800-320v80q0 33-23.5 56.5T720-160H240Zm200-486-75 75q-12 12-28.5 11.5T308-572q-11-12-11.5-28t11.5-28l144-144q6-6 13-8.5t15-2.5q8 0 15 2.5t13 8.5l144 144q12 12 11.5 28T652-572q-12 12-28.5 12.5T595-571l-75-75v286q0 17-11.5 28.5T480-320q-17 0-28.5-11.5T440-360v-286Z"/>
                </svg>
                <p class="text-blue-700 font-bold">Upload new file</p>
            </div>
        </div>
        <input ref="fileInput" class="hidden" type="file" name="fileInput" id="fileInput" @change="handleFileChange">
    </div>
</template>

<style scoped>

</style>
