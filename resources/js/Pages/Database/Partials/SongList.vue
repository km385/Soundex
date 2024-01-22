<template>
    <div class="h-full overflow-x-hidden text-white px-4 w-full ">
        <table class="w-full select-none table-auto text-left text-sm font-light px-4">
            <thead class=" pb-5 border-b-2 font-medium dark:border-neutral-500">
                <tr>
                    <th class="cursor-pointer px-6 py-4" @click="sortByColumn('id')">ID</th>
                    <th class="cursor-pointer px-6 py-4" @click="sortByColumn('title')">Title</th>
                    <th class="cursor-pointer px-6 py-4" @click="sortByColumn('artist')">Artist</th>
                    <th class="cursor-pointer px-6 py-4" @click="sortByColumn('album')">Album</th>
                    <th class="cursor-pointer px-6 py-4" @click="sortByColumn('composer')">Composer</th>
                    <th class="cursor-pointer px-6 py-4" @click="sortByColumn('genre')">Genre</th>
                    <th class="cursor-pointer px-6 py-4">Duration</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="song in sortedSongs" :key="song.id" class="border-b dark:border-neutral-700 hover:bg-gray-800">
                    <td>{{ song.id }}</td>
                    <td class="max-w-xs truncate">{{ song.title }}</td>

                    <td>{{ song.artist }}</td>
                    <td>{{ song.album }}</td>
                    <td>{{ song.composer }}</td>
                    <td>{{ song.genre }}</td>
                    <td>{{ formatDuration(song.duration_sec) }}</td>
                    <td>
                        <button @click="emits('playSong', song)" class="flex flex-row">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7V5z" />
                            </svg>
                            <!-- <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-6 w-6" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="12" y1="2" x2="12" y2="22"></line>
                                <line x1="2" y1="12" x2="22" y2="12"></line>
                            </svg> -->

                        </button>
                    </td>
                    <td style="position: relative;">
                        <button @click.stop="showContextMenu(song)"
                            class="m-4 top-6 right-6 bg-transparent border-none cursor-pointer text-gray-500 hover:text-gray-700">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path
                                    d="M10 12a2 2 0 100-4 2 2 0 000 4zM2 12a2 2 0 100-4 2 2 0 000 4zm16 0a2 2 0 100-4 2 2 0 000 4z" />
                            </svg>
                        </button>
                        <!-- Context Menu -->
                        <div v-if="song.showContextMenu"
                            class="absolute top-12 right-2 bg-white border border-gray-300 shadow-md p-2 rounded-md context-menu z-10 ">
                            <button class="block text-gray-700 hover:text-gray-900 cursor-pointer" @click="emits('infoSong', song);
                            closeContextMenu()
                                ">Info</button>
                            <button class="block text-gray-700 hover:text-gray-900 cursor-pointer" @click="
                                emits('editSong', song);
                            closeContextMenu()">Edit</button>
                            <button @click.stop="deleteContextMenu(song)"
                                class="block hover:text-gray-900 cursor-pointer text-red-800">Delete</button>
                        </div>
                    </td>

                </tr>
            </tbody>
        </table>


    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { router } from '@inertiajs/vue3'

const { songs } = defineProps(['songs']);
const emits = defineEmits(['playSong', 'infoSong', 'editSong']);
const openContextMenu = ref(null);

function showContextMenu(song) {
    if (openContextMenu.value) {
        openContextMenu.value.showContextMenu = false;
    }
    song.showContextMenu = !song.showContextMenu;
    openContextMenu.value = song.showContextMenu ? song : null;
}
function closeContextMenu() {
    openContextMenu.value.showContextMenu = false;
    openContextMenu.value = null;
}
function handleClickOutside(event) {
    if (openContextMenu.value && !event.target.closest('.context-menu')) {
        closeContextMenu()
    }
}
function deleteContextMenu(song) {
    closeContextMenu();
    if (confirm("Are you sure you want to Delete")) {
        const url = route("song.destroy", { song: song}); 
       // console.log('DELETE request URL:', url);
        axios.delete(url)
            .then(response => {
              // console.log(response.data);
            })
            .catch(error => {
                console.error(error);
            })
            .finally(() => {
                window.location.reload();
            });
    }
}



const formatDuration = (durationInSeconds) => {
    const minutes = Math.floor(durationInSeconds / 60);
    const seconds = durationInSeconds % 60;
    const formattedMinutes = String(minutes).padStart(2, '0');
    const formattedSeconds = String(seconds).padStart(2, '0');

    return `${formattedMinutes}:${formattedSeconds}`;
}

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});

const sortOrder = ref(null);
const sortBy = ref(null);
const sortByColumn = (column) => {
    if (!(sortBy.value === column)) {
        sortOrder.value = 'asc';
        sortBy.value = column;
    } else {
        if (sortOrder.value === "desc") {
            sortOrder.value = null;
            sortBy.value = null;
        } else if (sortOrder.value === "asc") {
            sortOrder.value = "desc";
        } else {
            sortOrder.value = "asc"
        }
    }
};

const sortedSongs = computed(() => {
    if (!sortBy.value) return songs.slice();

    return [...songs].sort((a, b) => {
        const aValue = a[sortBy.value];
        const bValue = b[sortBy.value];

        if (!aValue && !bValue) return 0;
        if (!aValue) return 1;
        if (!bValue) return -1;

        if (sortOrder.value === 'asc') {
            if (aValue < bValue) return -1;
            if (aValue > bValue) return 1;
            return 0;
        } else if (sortOrder.value === 'desc') {
            if (aValue < bValue) return 1;
            if (aValue > bValue) return -1;
            return 0;
        }
    });
});

</script>

<style scoped>
.context-menu {
    position: absolute;
    top: 18px;
    right: -18px;
    background: white;
    border: 1px solid #ccc;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    padding: 8px;
    border-radius: 4px;
}
</style>
