<script setup>
import DownloadTempFileButton from "@/Pages/Tools/Partials/DownloadTempFileButton.vue";
import SaveToLibraryButton from "@/Pages/Tools/Partials/SaveToLibraryButton.vue";
import {usePage} from "@inertiajs/vue3";
import {ref} from "vue";

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

</script>

<template>
    <div class="text-white flex flex-col flex-grow justify-center items-center">

        <!-- File Download Section -->
        <div class="p-6 bg-gray-800 rounded-lg shadow-lg">
            <audio controls v-if="showAudioTag"
                   :src="'/files/' + fileToDownloadLink"></audio>
            <button @click="showAudioTag = true"
                    class="bg-blue-400 text-white rounded py-2 px-4 mt-4 hover:bg-blue-500">
                {{ $t("resultOptionsScreen.hearAudio") }}
            </button>

            <p>{{ $t("resultOptionsScreen.downloadInfo") }}</p>
            <button class="bg-blue-400 text-white rounded py-2 px-4 mt-4 hover:bg-blue-500" @click="emits('goBack')">
                {{ $t("resultOptionsScreen.goBack") }}
            </button>
            <DownloadTempFileButton :filename="fileToDownloadName" :token="fileToDownloadLink" :show-button="showButton"/>
            <SaveToLibraryButton v-if="page.props.auth.user" :file-link="fileToDownloadLink"/>
        </div>
    </div>
</template>
