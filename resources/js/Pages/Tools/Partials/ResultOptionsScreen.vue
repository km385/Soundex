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
    <div class="text-white flex flex-col flex-grow justify-center items-center mt-20 lg:mt-10">

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
            <!-- TODO:  przekazywanie value do metronomu-->
            <div v-if="bpmArray !== null">
                <p v-html="$t('resultOptionsScreen.bpmFound', [bpmArray[0].BPM])" class="mt-3"></p>
                <p class="mt-3">{{ $t('resultOptionsScreen.bpmDescription') }}</p>
                <button :class="{ 'high-contrast-button': highContrast }"
                    class="bg-blue-400 text-white rounded py-2 px-4 mt-4 hover:bg-blue-500" @click="toggleTableVisibility">
                    {{ $t('resultOptionsScreen.tableShowButton') }}
                </button>
                <div v-if="showTable">
                    <div :class="{
                        'border-[#FFFF00FF]': highContrast,
                        'border-gray-300': !highContrast
                    }" class="my-4 rounded-lg border overflow-hidden">
                        <div class="flex flex-col">
                            <div class="-m-1.5 overflow-x-auto">
                                <div class="p-1.5 min-w-full inline-block align-middle">
                                    <table :class="{
                                        'divide-[#FFFF00FF]': highContrast,
                                        'divide-gray-300': !highContrast
                                    }" class="min-w-full divide-y table-auto text-center">
                                        <thead :class="{ 'bg-gray-500': !highContrast }" class="rounded-lg">
                                            <tr :class="{
                                                'text-[#FFFF00FF] divide-[#FFFF00FF] text-xl': highContrast,
                                                'text-gray-100 text-lg': !highContrast
                                            }" class="divide-x">
                                                <th scope="col" class="px-6 py-3  font-extrabold ">
                                                    {{ $t('resultOptionsScreen.tableHeaderBpm') }}
                                                </th>
                                                <th scope="col" class="px-6 py-3  font-extrabold ">
                                                    {{ $t('resultOptionsScreen.tableHeaderCount') }}
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody :class="{
                                            'divide-[#FFFF00FF]  ': highContrast,
                                            'divide-gray-200 bg-[#343541]': !highContrast
                                        }" class="divide-y ">
                                            <tr :class="{
                                                'divide-[#FFFF00FF]': highContrast,
                                                'hover:bg-gray-600': !highContrast
                                            }" class="transition duration-300 ease-in-out   divide-x"
                                                v-for="(item, index) in bpmArray.slice(1)" :key="index">
                                                <td :class="{ 'text-gray-200': !highContrast }"
                                                    class="px-6 py-4 whitespace-nowrap  font-medium ">
                                                    {{ item.BPM + " BPM" }}
                                                </td>
                                                <td :class="{ 'text-gray-200': !highContrast }"
                                                    class="px-6 py-4 whitespace-nowrap  font-medium ">
                                                    {{ item.Count }}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
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
            <div :class="{ 'float-right ': bpmArray !== null }">
                <SaveToLibraryButton v-if="page.props.auth.user" :file-link="fileToDownloadLink" />
            </div>
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
