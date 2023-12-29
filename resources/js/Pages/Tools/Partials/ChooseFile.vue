
<script setup>
import {ref, inject} from 'vue';
import axios from 'axios';

const showPopup = ref(false);
const songs = ref([]);
async function loadData() {
    const res = await axios.get('/songs')
    if (res.data === null) return
    songs.value = res.data.songs;
}

const showSongList = async () => {
    await loadData()
    showPopup.value = true
};

const closePopup = () => {
    showPopup.value = false
};

const emits = defineEmits(['fileChosen'])

async function chooseSong(song) {
    console.log(song.id)

    const res = await axios.get(`/songs/${song.id}`, {
        responseType: "blob"
    })

    const file = new File([new Blob([res.data])], song.title + '.' + song.extension)
    emits('fileChosen', file)
}

const highContrast = inject('highContrast')
</script>

<template>
    <div class="">
        <button @click="showSongList"
                :class="{'high-contrast-button':highContrast}"
                class="bg-blue-500 text-white rounded-lg p-2">Show Song List</button>

        <div v-if="showPopup"
             class="fixed inset-0 justify-center items-center z-10 flex flex-col bg-black bg-opacity-40 ">
            <div  class="flex justify-end w-[80%] lg:w-[60%] mt-10">

                <button @click="closePopup"
                        :class="{'high-contrast-input':highContrast}"
                        class="mt-4 p-2 bg-gray-700 text-white rounded-lg">&Cross;</button>
            </div>

            <div
                :class="{'high-contrast-input':highContrast}"
                class="p-4 mb-10 rounded-lg shadow-lg overflow-y-scroll text-2xl bg-gray-800 w-[80%] lg:w-[60%] scrollbar">
                <h2 class="text-2xl font-semibold mb-4">Songs</h2>
                <hr :class="{'high-contrast-input':highContrast}">
                <div
                    :class="{'high-contrast-input':highContrast}"
                    class="bg-gray-400 grid grid-cols-5 text-center mt-3 ">
                    <p class="min-w-min">id</p>
                    <p class="col-span-2">name</p>
                    <p class="col-span-2">title</p>
                </div>
                    <div v-for="(song, index) in songs" :key="song.id" @click="chooseSong(song)"
                         :class="{'high-contrast-input':highContrast}"
                         class="bg-gray-700 grid grid-cols-5 cursor-pointer my-2 group">
                        <p class="text-center px-2 ">{{ song.id }}</p>
                        <p class="truncate px-2 col-span-2 group-hover:whitespace-normal">{{ song.title + '.' + song.extension }}</p>
                        <p class="truncate px-2 col-span-2 group-hover:whitespace-normal">{{ song.title }}</p>

                    </div>
            </div>
        </div>
    </div>
</template>


<style scoped>
.scrollbar::-webkit-scrollbar {
    @apply w-2
}

.scrollbar::-webkit-scrollbar-track {

}

.scrollbar::-webkit-scrollbar-thumb {
    @apply bg-gray-700 rounded-lg
}

.high-contrast-border {
    @apply border-[#FFFF00FF]
}

.high-contrast-text {
    @apply text-[#FFFF00FF]
}

.high-contrast-input {
    @apply text-xl border border-[#FFFF00FF] bg-black text-[#FFFF00FF]
}

.high-contrast-button {
    @apply text-xl border border-[#FFFF00FF] bg-black text-[#FFFF00FF] focus:border-[#FFFF00FF] focus:ring-[#FFFF00FF] hover:bg-yellow-300 hover:text-black
}
</style>
