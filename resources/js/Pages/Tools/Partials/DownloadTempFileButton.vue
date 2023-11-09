<template>
    <div v-if="showButton" class="mt-6 flex items-center">
        <a class="bg-blue-400 text-white rounded py-2 px-4 hover:bg-blue-500 whitespace-nowrap" href="#"
            @click="downloadFile">Download file</a>
        <input type="text" id="textToCopy" class="w-full ml-3 cursor-pointer text-black bg-blue-400 rounded-lg" @click="copyToClipboard" :value="'localhost:8000/files/'+ token">
        <svg xmlns="http://www.w3.org/2000/svg" height="58" viewBox="0 -960 960 960" width="58" @click="copyToClipboard" class="cursor-pointer">
            <path
                d="M360-240q-33 0-56.5-23.5T280-320v-480q0-33 23.5-56.5T360-880h360q33 0 56.5 23.5T800-800v480q0 33-23.5 56.5T720-240H360Zm0-80h360v-480H360v480ZM200-80q-33 0-56.5-23.5T120-160v-520q0-17 11.5-28.5T160-720q17 0 28.5 11.5T200-680v520h400q17 0 28.5 11.5T640-120q0 17-11.5 28.5T600-80H200Zm160-240v-480 480Z"/>
        </svg>
    </div>

    <div v-else class="mt-6 flex items-center">
        <p class="w-full ml-3">BPM found in your song:{{ token }}</p>
    </div>
</template>

<script setup>
import axios from "axios";
import {onMounted} from "vue";

function copyToClipboard() {
    navigator.clipboard
        .writeText(document.getElementById('textToCopy').value)
}

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
