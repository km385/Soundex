<template>
    <div v-if="showButton" class="mt-6 flex items-center">
        <a class="bg-blue-400 text-white rounded py-2 px-4 hover:bg-blue-500 whitespace-nowrap" href="#"
            @click="downloadFile">Download file</a>
        <p class="w-full ml-3">localhost:8000/files/{{ token }}</p>
    </div>

    <div v-else class="mt-6 flex items-center">
        <p class="w-full ml-3">BPM found in your song:{{ token }}</p>
    </div>
</template>

<script setup>
import axios from "axios";

const props = defineProps({
    token: {
        type: String
    }, filename: {
        type: String
    }, showButton: {
        type: Boolean,
        default: true
    }});

function downloadFile() {
    console.log(props.token);
    axios
        .get(`/files/${props.token}`, {
            responseType: 'blob',
        })
        .then((response) => {
            // get extension from received file (it has random name)
            const arrivedName = response.headers['content-disposition']
            const newExtension = arrivedName.split('.')[1];

            // get name without the extension from the prop
            const newName = props.filename.split('.')
            newName.pop();
            const fileNameWithoutExtension = newName.join('.');

            const url = window.URL.createObjectURL(new Blob([response.data]));
            const link = document.createElement('a');
            link.href = url;
            link.setAttribute('download', fileNameWithoutExtension + '.' + newExtension);
            document.body.appendChild(link);
            link.click();
        })
        .catch((error) => {
            console.log(error);
        });
}
</script>
