<template>
    <div class="mt-6">
        <div :class="{'text-yellow-300':highContrast}" class="text-white">
            <p class="text-xl font-bold mb-1">{{$t('downloadTempFileButton.fileIsReady')}}</p>
            <p>{{$t('downloadTempFileButton.instruction')}}</p>
            <p>{{$t('downloadTempFileButton.howMuchTime')}}</p>
        </div>
        <div class="flex items-center mt-2">
            <a
                :class="{'high-contrast-button':highContrast}"
                class="bg-blue-400 text-white rounded py-2 px-4 hover:bg-blue-500 whitespace-nowrap" href="#"
                @click="downloadFile">{{ $t("resultOptionsScreen.downloadFile") }}</a>
            <input type="text" id="textToCopy"
                   :class="{'high-contrast-button':highContrast}"
                   class="w-full md:w-1/3 ml-3 cursor-pointer hover:bg-blue-500 text-white bg-blue-400 rounded-lg" @click="copyToClipboard" :value="'localhost:8000/files/'+ token">
            <SvgComp name="copy" :class="{'text-yellow-300':highContrast}" class="w-12 text-white cursor-pointer" @click="copyToClipboard"/>
        </div>
    </div>

</template>

<script setup>
import axios from "axios";
import {inject, onMounted} from "vue";
import SvgComp from "@/Components/SvgComp.vue";

function copyToClipboard() {
    navigator.clipboard
        .writeText(document.getElementById('textToCopy').value)
}
const highContrast = inject('highContrast')

const props = defineProps({
    token: {
        type: String
    }, filename: {
        type: String
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

<style scoped>
.high-contrast-button {
    @apply text-xl border border-[#FFFF00FF] bg-black text-[#FFFF00FF] focus:border-[#FFFF00FF] focus:ring-[#FFFF00FF] hover:bg-yellow-300 hover:text-black
}

.high-contrast-input {
    @apply text-xl border border-[#FFFF00FF] bg-black text-[#FFFF00FF]
}
</style>
