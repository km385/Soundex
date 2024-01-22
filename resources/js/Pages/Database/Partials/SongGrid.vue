<template>
    <div class="h-full overflow-x-hidden mt-5 w-full">

        <div
            class="grid gap-4 grid-cols-1 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 lg:gap-7 2xl:grid-cols-5 2xl:gap-24 w-full overflow-x-hidden">
            <div v-for="(songs, album) in songsGrouped" :key="album" class="p-2 h-30  cursor-pointer relative min-h-30"  @click="emits('displayAlbum', songs)">
                <div
                    class=" hover:bg-gray-500 transition duration-300 shadow-md rounded-tr-3xl p-6 bg-gray-600 outline-gray-400 outline outline-offset-4 ">
                    <button @click.stop="showContextMenu(songs)"
                        class="absolute top-8 right-8 bg-transparent border-none cursor-pointer text-gray-500 hover:text-gray-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path
                                d="M10 12a2 2 0 100-4 2 2 0 000 4zM2 12a2 2 0 100-4 2 2 0 000 4zm16 0a2 2 0 100-4 2 2 0 000 4z" />
                        </svg>
                    </button>
                    <img src="https://img.freepik.com/premium-wektory/nuta-logo_535345-2135.jpg" alt="Song Cover"
                        class="w-16 h-16 object-cover rounded-lg mb-4">
                    <h3 class=" font-semibold truncate">{{ album }}</h3>
                </div>
                <!-- Context Menu -->
                <div v-if="songs.showContextMenu"
                    class="absolute top-12 right-2 bg-white border border-gray-300 shadow-md p-2 z-10 rounded-md context-menu">
                    <button class="block text-gray-700 hover:text-gray-900 cursor-pointer">Info</button>
                    <button class="block text-gray-700 hover:text-gray-900 cursor-pointer">Edit</button>
                    <button @click.stop="deleteContextMenu(album)"
                        class="block text-gray-700 hover:text-gray-900 cursor-pointer">Delete</button>
                </div>
            </div>
        </div>
    </div>
</template>
  
<script setup>

import { ref, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3'
const openContextMenu = ref(null);
const { songs } = defineProps(['songs']);

const songsGrouped = ref(groupSongsByAlbum(songs));

function groupSongsByAlbum(songs) {
    const groupedSongs = {};

    songs.forEach(song => {
        const album = song.album || 'unknown';
        if (!groupedSongs[album]) {
            groupedSongs[album] = { songs: [], showContextMenu: false };
        }
        groupedSongs[album].songs.push(song);
    });
   // console.log(groupedSongs)
    return groupedSongs
}
const emits = defineEmits(['playSong','displayAlbum']);

function playSelectedSong(song) {
    emits('playSong', song);
}

function showContextMenu(song) {
    if (openContextMenu.value) {
        openContextMenu.value.showContextMenu = false;
    }
    song.showContextMenu = !song.showContextMenu;
    openContextMenu.value = song.showContextMenu ? song : null;
}

function handleClickOutside(event) {
    if (openContextMenu.value && !event.target.closest('.context-menu')) {
        openContextMenu.value.showContextMenu = false;
        openContextMenu.value = null;
    }
}

function deleteContextMenu(song) {
    if (confirm("Are you sure you want to Delete")) {
        router.delete(route("Database.destroy", song.id));
    }
}

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});

</script>
  
<style scoped>
.context-menu {
    position: absolute;
    top: 20px;
    right: -18px;
    background: white;
    border: 1px solid #ccc;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    padding: 8px;
    border-radius: 4px;
}
</style>