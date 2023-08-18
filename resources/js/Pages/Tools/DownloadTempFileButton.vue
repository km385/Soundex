<script setup>
import axios from "axios";

const props = defineProps(['token', 'filename'])
function downloadFile() {

    axios.get(`/files/${props.token}`, {
        responseType: 'blob',
    })
        .then((response) => {
            const url = window.URL.createObjectURL(new Blob([response.data]));
            const link = document.createElement('a');
            link.href = url;
            link.setAttribute('download', props.filename);
            document.body.appendChild(link);
            link.click();
        })
        .catch((error) => {
            console.log(error);
        });
}

</script>

<template>
    <div class="mt-6 flex items-center">
        <a class="bg-blue-400 text-white rounded py-2 px-4 hover:bg-blue-500 whitespace-nowrap" href="#"
           @click="downloadFile">Download file</a>
        <p class="w-full ml-3">localhost:8000/files/{{ token }}</p>
    </div>
</template>

<style scoped>

</style>
