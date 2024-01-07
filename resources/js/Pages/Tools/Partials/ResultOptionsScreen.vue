<script setup>
import DownloadTempFileButton from "@/Pages/Tools/Partials/DownloadTempFileButton.vue";
import SaveToLibraryButton from "@/Pages/Tools/Partials/SaveToLibraryButton.vue";
import { usePage } from "@inertiajs/vue3";
import { inject, ref } from "vue";
import Wavesurfer from "@/Pages/Tools/Partials/Wavesurfer.vue";
import Metronome from "@/Pages/Tools/Partials/Metronome.vue";
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
    bpmArray: {
        required: false,
        type: Array,
        default: null,
    }
})

const emits = defineEmits(['goBack'])

const showAudioTag = ref(false)
const showTable = ref(false)
const highContrast = inject('highContrast')

const file = ref(null)
async function makeFile() {
    if (file.value !== null) {
        return
    }
    const response = await axios.get(`/files/${props.fileToDownloadLink}`, {
        responseType: 'blob',
    })

    file.value = new File([response.data], '')
    showAudioTag.value = true
}
function toggleTableVisibility() {
    showTable.value = !showTable.value;
}
</script>

<template>
    <div class="text-white flex flex-col flex-grow justify-center items-center">

        <!-- File Download Section -->
        <div :class="{ 'high-contrast-input': highContrast }" class="p-6 bg-gray-800 rounded-lg shadow-lg w-full">
            <div v-if="showAudioTag">
                <Wavesurfer :file="file" :show-controls="true" />
                <Metronome v-if="bpmArray !== null" />
            </div>
            <button @click="makeFile" :class="{ 'high-contrast-button': highContrast }"
                class="bg-blue-400 text-white rounded py-2 px-4 mt-10 hover:bg-blue-500 ">
                {{ $t("resultOptionsScreen.hearAudio") }}
            </button>
            <!-- TODO: formatowanie tabelki, przekazywanie value do metronomu-->
            <div v-if="bpmArray !== null">

                <p v-html="$t('resultOptionsScreen.bpmFound', [bpmArray[0].BPM])"></p>
                <br>
                <p>{{ $t('resultOptionsScreen.bpmDescription') }}</p>
                <button :class="{ 'high-contrast-button': highContrast }"
                    class="bg-blue-400 text-white rounded py-2 px-4 mt-4 hover:bg-blue-500" @click="toggleTableVisibility">
                    {{ $t('resultOptionsScreen.tableShowButton') }}
                </button>
                <div v-if="showTable">
                    <table>
                        <thead>
                            <tr>
                                <th>{{ $t('resultOptionsScreen.tableHeaderBpm') }}</th>
                                <th>{{ $t('resultOptionsScreen.tableHeaderCount') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(item, index) in bpmArray.slice(1)" :key="index">
                                <td>{{ item.BPM }}</td>
                                <td>{{ item.Count }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <p v-else>
                {{ $t("resultOptionsScreen.downloadInfo") }}
            </p>
            <button :class="{ 'high-contrast-button': highContrast }"
                class="bg-blue-400 text-white rounded py-2 px-4 mt-4 hover:bg-blue-500" @click="emits('goBack')">
                {{ $t("resultOptionsScreen.goBack") }}
            </button>



            <DownloadTempFileButton v-if="bpmArray == null" :filename="fileToDownloadName" :token="fileToDownloadLink" />
            <SaveToLibraryButton v-if="page.props.auth.user" :file-link="fileToDownloadLink" />
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
