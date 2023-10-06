<template>
    <div class="h-full overflow-x-hidden mt-5">

        <div class="grid gap-4 grid-cols-4 md:grid-cols-2 lg:grid-cols-6 lg:gap-7 overflow-x-hidden">
            <div v-for="song in songs" :key="song.id" class="p-4 cursor-pointer relative">
                <div @click="playSelectedSong(song)"
                    class="bg-gray-300 hover:bg-gray-100 transition duration-300 shadow-md rounded-tr-3xl p-4">
                    <button @click.stop="showContextMenu(song)"
                        class="absolute top-8 right-8 bg-transparent border-none cursor-pointer text-gray-500 hover:text-gray-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path
                                d="M10 12a2 2 0 100-4 2 2 0 000 4zM2 12a2 2 0 100-4 2 2 0 000 4zm16 0a2 2 0 100-4 2 2 0 000 4z" />
                        </svg>
                    </button>
                    <img src="https://d1csarkz8obe9u.cloudfront.net/posterpreviews/artistic-album-cover-design-template-d12ef0296af80b58363dc0deef077ecc_screen.jpg?ts=1561488440"
                        alt="Song Cover" class="w-16 h-16 object-cover rounded-lg mb-4">
                    <h3 class="text-xl font-semibold truncate">{{ song.title }}</h3>
                    <p class="text-gray-600">{{ song.artist }}</p>
                </div>
                <!-- Context Menu -->
                <div v-if="song.showContextMenu"
                    class="absolute top-12 right-2 bg-white border border-gray-300 shadow-md p-2 z-10 rounded-md context-menu">
                    <button class="block text-gray-700 hover:text-gray-900 cursor-pointer">Info</button>
                    <button class="block text-gray-700 hover:text-gray-900 cursor-pointer">Edit</button>
                    <button @click.stop="deleteContextMenu(song)"
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

const emits = defineEmits(['playSong']);

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