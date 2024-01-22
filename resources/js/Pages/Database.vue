<script setup>
import { Head, usePage } from '@inertiajs/vue3';
import { inject, ref } from "vue";
import Musicplayer from "@/Pages/Database/Partials/MusicPlayer.vue";
import SongList from '@/Pages/Database/Partials/SongList.vue';
import SongGrid from '@/Pages/Database/Partials/SongGrid.vue';
import UploadFile from "@/Pages/Database/Partials/UploadFile.vue";
import FileInfo from "@/Pages/Tools/Partials/FileInfo.vue";
import TagEditor from "@/Pages/Database/Partials/TagEditor.vue";
const showDesciption = ref(false)
const showPlaylist = ref(false)
const showTagEditor = ref(false)
const highContrast = inject('highContrast')
const songToPlay = ref([]);
const songToInfo = ref([]);
const songToEdit = ref([]);
const playlistArray = ref([]);
const props = defineProps({
    songs: Array,
});
const page = usePage()
const uploadedFile = ref({})
const isFileUploaded = ref(false)
const isError = ref(false)
const selectedGrid = ref("")
const selectedDisplay = ref('list');
const setActiveButton = (val) => {
    selectedDisplay.value = val;
};
function playSong(song) {
    songToPlay.value = song;
}
function infoSong(song) {
    songToInfo.value = filteredSongInfo(song);
    showDesciption.value = true;
}
function editSong(song) {
    songToEdit.value = filteredSongInfo(song);
    showTagEditor.value = true;
}
function displayAlbum(album) {
    selectedGrid.value = album.songs;
}
async function saveEdit(data) {
    // console.log(data);
    try {
        const { id, ...updatedMetadata } = data;
        showTagEditor.value=false;
        songToEdit.value=null;
        const res = await axios.post(`database/songs/change/${id}`, updatedMetadata);
        window.location.reload();
    } catch (error) {
        console.error(error);
        throw error;
    }
}
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleString();
}

function filteredSongInfo(song) {
    return Object.entries(song).filter(
        ([key, value]) =>
            key !== 'showContextMenu' &&
            key !== 'value' &&
            value !== null &&
            value !== ''
    );
}

async function onSubmit() {
    // console.log(props.songs.value)
    const formData = new FormData()
    formData.append('file', uploadedFile.value)
    try {
        isFileUploaded.value = false
        const res = await axios.post('/database/upload', formData)
        window.location.reload();
    } catch (e) {
        console.log(e)
    }
}

async function getFile(file) {
    uploadedFile.value = file;
    isFileUploaded.value = true
}

</script>

<template>
    <div class="z-0 bg-black">

        <Head title="Database" />
        <!-- Page Database -->
        <div :class="{ 'high-contrast-bg': highContrast }" class="relative w-full h-screen max-h-screen flex flex-col">
            <!-- Top Div -->
            <div class="background-photo text-center mb-2">
                <h1 class="text-2xl font-bold">Your Library:</h1>
            </div>

            <!-- Middle Div -->
            <div class="flex-grow flex relative overflow-auto">

                <!-- Navbar-->
                <div class="w-32 m-3 rounded-2xl background-photo">

                    <!-- Upload Button -->
                    <div>
                        <UploadFile @file="getFile" />
                    </div>

                    <!-- Absolute file upload -->
                    <div v-if="isFileUploaded"
                        class="fixed inset-0 bg-gray-800 bg-opacity-75 flex items-center justify-center">
                        <div :class="{ 'high-contrast-input': highContrast }"
                            class="mt-20 lg:mt-10 p-6 bg-gray-800 rounded-lg shadow-lg absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2">
                            <button type="button" @click="isFileUploaded = false; isError = false"
                                :class="{ 'high-contrast-button': highContrast }"
                                class="bg-blue-400 text-white rounded py-2 px-4 hover:bg-blue-500 mb-4">Go back</button>

                            <FileInfo :file-size="uploadedFile.size" :file-name="uploadedFile.name" />
                            <div class="mt-6">
                                <button type="button" @click="onSubmit" :class="{ 'high-contrast-button': highContrast }"
                                    class="bg-blue-400 text-white rounded py-2 px-4 hover:bg-blue-500">{{ $t('tools.submit')
                                    }}</button>
                            </div>
                        </div>
                    </div>

                    <!-- Absolute tag editor -->
                    <div v-if="showTagEditor"
                        class="fixed inset-0 bg-gray-800 bg-opacity-75 flex items-center justify-center">
                            <TagEditor :song="songToEdit" @discardEdit="showTagEditor=false; songToEdit=null" @saveEdit="saveEdit" />
           
                    </div>

                    <!-- View Controls -->
                    <div class="mt-6">
                        <button v-bind:class="{ 'bg-gray-700': selectedDisplay === 'list' }"
                            class="font-bold p-2 border border-gray-400 w-full hover:bg-gray-700 hover:scale-110"
                            @click="setActiveButton('list'); selectedGrid=''">
                            List View
                        </button>
                        <button v-bind:class="{ 'bg-gray-700': selectedDisplay === 'album' }"
                            class="font-bold p-2 border border-gray-400 w-full hover:bg-gray-700 hover:scale-110"
                            @click="setActiveButton('album'); selectedGrid=''">
                            Album View
                        </button>
                        <!-- <button v-bind:class="{ 'bg-gray-700': selectedDisplay === 'playlist' }"
                            class="font-bold p-2 border border-gray-400 w-full hover:bg-gray-700 hover:scale-110"
                            @click="setActiveButton('playlist')">
                            Playlist View
                        </button> -->
                    </div>


                </div>

                <!-- Main Content -->
                <div class="flex-grow rounded-2xl m-3 background-photo flex flex-col">
                    <!-- Header -->
                    <div class="inline-flex w-full heightCalc overflow-hidden overflow-x-hidden">

                        <SongList v-if="selectedDisplay == 'list'" :songs="songs" :selectedDisplay="selectedDisplay"
                            @playSong="playSong" @infoSong="infoSong" @editSong="editSong" class="overflow-scroll" />

                        <SongGrid v-else-if="selectedDisplay == 'album' && selectedGrid == ''" :songs="songs"
                            :selectedDisplay="selectedDisplay" @playSong="playSong" @infoSong="infoSong" @displayAlbum="displayAlbum"
                            class="overflow-scroll" />

                        <SongList v-else-if="selectedDisplay == 'album'" :songs="selectedGrid" :selectedDisplay="selectedDisplay"
                            @playSong="playSong" @infoSong="infoSong" class="overflow-scroll" />

                        <!-- <SongGrid v-else-if="selectedDisplay == 'playlist' && selectedGrid == ''" :songs="songs"
                            :selectedDisplay="selectedDisplay" @playSong="playSong" @infoSong="infoSong"
                            class="overflow-scroll" />

                        <SongList v-else-if="selectedDisplay == 'playlist'" :songs="selectedGrid"
                            :selectedDisplay="selectedDisplay" @playSong="playSong" @infoSong="infoSong"
                            class="overflow-scroll" /> -->

                    </div>
                </div>

                <!-- File desc   -->
                <transition name="fade" appear>
                    <div v-if="showDesciption"
                        class="w-60 background-photo m-3 rounded-2xl overflow-x-hidden overflow-y-auto flex flex-col pr-2">
                        <!-- X to close the div -->
                        <div class="close-button cursor-pointer items-end justify-end flex"
                            @click.stop="showDesciption = !showDesciption; songToInfo.value = []">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                class="h-6 w-6">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </div>

                        <!-- header -->
                        <div class="header">
                            <h1 class="text-l font-bold text-white mb-2 whitespace-nowrap">Song desciption:</h1>
                            <div class="h-1 w-52 bg-white mb-6"></div>
                        </div>

                        <!-- file desc -->
                        <div class="flex flex-grow flex-col items-left" v-for="(value, key) in songToInfo" :key="key">
                            <div
                                v-if="value[0] === 'showContextMenu' || value[0] === 'value' || value[1] === null || value[1] === ''">
                            </div>
                            <div v-else>
                                <div><span class="font-bold text-sm">{{ value[0] }}</span></div>
                                <div v-if="value[0] === 'created_at' || value[0] === 'updated_at'"
                                    class="ml-2 text-gray-300"><span>{{
                                        formatDate(value[1]) }}</span></div>
                                <div v-else class="ml-2 text-gray-300"><span>{{ value[1] }}</span></div>
                            </div>
                        </div>

                    </div>
                </transition>

                <!-- Playlist  -->
                <!-- <transition name="fade" appear>
                    <div v-show="showPlaylist"
                        class=" background-photo m-3 rounded-2xl overflow-x-hidden overflow-y-auto flex flex-col pr-2">
                        <span class="h-6 w-6 font-bold whitespace-nowrap" fill="none"> Save Clear
                        </span>
                        <div class="close-button cursor-pointer items-end justify-end flex"
                            @click.stop="showPlaylist = !showPlaylist; playlistArray.value = []">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                class="h-6 w-6">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </div>

                        <div class="header">
                            <h1 class="text-l font-bold text-white mb-2 whitespace-nowrap">Current Playlist:</h1>
                            <div class="h-1 w-52 bg-white mb-6"></div>
                            <span class="font-bold text-sm"></span>
                        </div>
                    </div>
                </transition> -->
            </div>
            <transition name="fade" appear>
                <Musicplayer v-if="songToPlay && songToPlay.id != null" :selectedSong="songToPlay" />
            </transition>
        </div>
    </div>
</template>
  


<style>
.background-photo {
    @apply bg-[url('https://i.stack.imgur.com/nvK7w.png')] bg-cover bg-center outline-[#2b2b2b91] outline outline-offset-4 p-2 text-white bg-blend-difference bg-black
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.5s;
}

.fade-enter,
.fade-leave-to {
    opacity: 0;
}

.high-contrast-bg {
    @apply bg-black
}

.high-contrast-text {
    @apply text-[#FFFF00FF]
}

.high-contrast-border {
    @apply border-[#FFFF00FF]
}

.high-contrast-input {
    @apply text-xl border border-[#FFFF00FF] bg-black text-[#FFFF00FF]
}

.high-contrast-button {
    @apply text-xl border border-[#FFFF00FF] bg-black text-[#FFFF00FF] focus:border-[#FFFF00FF] focus:ring-[#FFFF00FF] hover:bg-yellow-300 hover:text-black
}

.high-contrast-button-selected {
    @apply bg-yellow-300 text-black
}
</style>
