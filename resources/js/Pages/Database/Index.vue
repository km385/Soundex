<template>
    <div>
        <DisplaySelector :selectedDisplay="grid" @selectedDisplay="updateDisplay" />
    </div>
    <div class="inline-flex w-full heightCalc overflow-hidden overflow-x-hidden">

        <DatabaseDashboard :isSidebarCollapsed="isSidebarCollapsed" :songs="songs" :selectedDisplay="selectedDisplay"
            @playSong="playSelectedSong" class="overflow-scroll" />
        <SongQueue />
    </div>
    <div class="w-30">
        <MediaPlayer :isSidebarCollapsed="isSidebarCollapsed" :selectedSong="selectedSong" />
    </div>
</template>

<script setup>
import DisplaySelector from '@/Pages/Database/Partials/DisplaySelector.vue';
import { ref, inject } from 'vue';
import DatabaseDashboard from '@/Pages/Database/DatabaseDashboard.vue';
import MediaPlayer from '@/Pages/Database/Partials/MusicPlayer.vue';
import SongQueue from '@/Pages/Database/Partials/SongQueue.vue';

defineProps({
    songs: Array,
});

const isSidebarCollapsed = inject('isSidebarCollapsed');
let selectedSong = ref("placeholder");

function playSelectedSong(song) {
    selectedSong.value = song;
}

function updateDisplay(viewType) {
    selectedDisplay.value = viewType;
}

const selectedDisplay = ref('grid');
</script>

<style scoped>
.heightCalc {
    height: calc(100% - 9rem);
}
</style>