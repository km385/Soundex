<script setup>
import DownloadTempFileButton from "@/Pages/Tools/Partials/DownloadTempFileButton.vue";
import SaveToLibraryButton from "@/Pages/Tools/Partials/SaveToLibraryButton.vue";
import {usePage} from "@inertiajs/vue3";
import {inject, ref} from "vue";
import Wavesurfer from "@/Pages/Tools/Partials/Wavesurfer.vue";
import axios from "axios";

const page = usePage()

const props = defineProps({
    fileToDownloadLink: {
        required: true,
        type: String
    },
    fileToDownloadName: {
        required: true,
        type: String
    },
    showButton: {
        required: false,
        type: Boolean,
        default: true
    }
})

const emits = defineEmits(['goBack'])

const showAudioTag = ref(false)

const highContrast = inject('highContrast')

const file = ref({})
async function makeFile() {
    const response = await axios.get(`/files/${props.fileToDownloadLink}`, {
        responseType: 'blob',
    })

    file.value = new File([response.data], '')
    showAudioTag.value = true
}

</script>

<template>
    <div class="text-white flex flex-col flex-grow justify-center items-center">

        <!-- File Download Section -->
        <div
            :class="{'high-contrast-input':highContrast}"
            class="p-6 bg-gray-800 rounded-lg shadow-lg">

            <Wavesurfer v-if="showAudioTag" :file="file" :show-controls="true" />
            <button @click="makeFile"
                    :class="{'high-contrast-button':highContrast}"
                    class="bg-blue-400 text-white rounded py-2 px-4 mt-10 hover:bg-blue-500 ">
                {{ $t("resultOptionsScreen.hearAudio") }}
            </button>

            <p>{{ $t("resultOptionsScreen.downloadInfo") }}</p>
            <button
                :class="{'high-contrast-button':highContrast}"
                class="bg-blue-400 text-white rounded py-2 px-4 mt-4 hover:bg-blue-500" @click="emits('goBack')">
                {{ $t("resultOptionsScreen.goBack") }}
            </button>
            <DownloadTempFileButton :filename="fileToDownloadName" :token="fileToDownloadLink" :show-button="showButton"/>
            <SaveToLibraryButton v-if="page.props.auth.user" :file-link="fileToDownloadLink"/>
        </div>
    </div>
</template>

<style scoped>
.high-contrast-button {
    @apply text-xl border border-[#FFFF00FF] bg-black text-[#FFFF00FF] focus:border-[#FFFF00FF] focus:ring-[#FFFF00FF] hover:bg-yellow-300 hover:text-black
}

.high-contrast-input {
    @apply text-xl border border-[#FFFF00FF] bg-black text-[#FFFF00FF]
}
</style>
