<script setup>
import {ref, watch} from "vue";

const uploadedFile = ref({})
const fileInput = ref(null)
const emit = defineEmits(['file'])

function onDrop(event) {
    console.log('handle drag event')
    event.preventDefault()
    if (event.dataTransfer.items) {
        const item = event.dataTransfer.items[0]
        if (item.kind === "file") {
            uploadedFile.value = item.getAsFile()
        }
    } else {
        uploadedFile.value = event.dataTransfer.files[0]
    }
}

function onDragOver(event) {
    console.log('handle drag over')
    event.preventDefault()
}

function onClick(event) {
    fileInput.value.click()
}

function handleFileChange(event) {
    console.log('handle file input')
    if(event.target.files[0] !== undefined){
        uploadedFile.value = event.target.files[0]
    }
}

watch(uploadedFile, (value) => {
    emit('file', uploadedFile.value)
})
</script>

<template>
    <div id="drop_zone" class="flex items-center justify-center" @drop="onDrop" @dragover="onDragOver" @click="onClick">
        <div class="flex flex-col items-center">
<!--            <img class="w-1/2 h-1/2 mb-3" src="../../images/file_icon.webp" alt="file image">-->
            <p class="text-blue-700 font-bold">Upload new file</p>
        </div>
    </div>
    <input ref="fileInput" class="hidden" type="file" name="fileInput" id="fileInput" @change="handleFileChange">
</template>

<style scoped>
#drop_zone {
    border: 5px dashed blue;
    width: 300px;
    height: 300px;
}
</style>
