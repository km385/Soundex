
<script setup>
import { ref, onMounted } from 'vue';
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

    console.log(file)
}
</script>

<template>
    <div>
        <button @click="showSongList" class="bg-blue-500 text-white rounded-lg p-2">Show Song List</button>

        <div v-if="showPopup" class="fixed inset-0 z-10 flex items-center justify-center bg-black bg-opacity-40">

            <div class="p-4 w-1/2 h-1/2 rounded-lg shadow-lg overflow-y-scroll text-2xl bg-gray-800">
                <h2 class="text-2xl font-semibold mb-4">Songs</h2>
                <div class="bg-red-500 flex justify-around">
                    <p>id</p>
                    <p>name</p>
                    <p>title</p>
                </div>
                <div v-for="(song, index) in songs" :key="song.id" @click="chooseSong(song)" class="bg-red-500 flex justify-around cursor-pointer my-2 ">
                    <p>{{ song.id }}</p>
                    <p>{{ song.title + '.' + song.extension }}</p>
                    <p>{{ song.title }}</p>
                </div>
                <button @click="closePopup" class="mt-4 p-2 bg-red-500 text-white rounded-lg">Close</button>
            </div>
        </div>
    </div>
</template>

