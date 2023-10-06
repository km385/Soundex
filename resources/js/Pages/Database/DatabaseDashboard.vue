<template>
  <div class="h-full overflow-x-hidden cheatWidth">
    <div class="mt-16 ml-5 p-5mb-4 text-4xl font-black leading-none tracking-tight text-gray-900 md:text-5xl lg:text-6xl dark:text-white">
        <h1>Your Library :</h1>
    </div>
    <div v-if="selectedDisplay == 'grid'">
      <SongGrid @playSong="playSelectedSong" :songs="songs" class=""/>
    </div>
    
    <div v-else-if="selectedDisplay == 'list'">
      <SongList @playSong="playSelectedSong" :songs="songs" class=""/>
    </div>
  </div>
</template>

<script setup>
import SongGrid from '@/Pages/Database/Partials/SongGrid.vue';
import SongList from '@/Pages/Database/Partials/SongList.vue';

const {songs,selectedDisplay } = defineProps({
  songs: Array,
  selectedDisplay: String,
});

const emits = defineEmits(['playSong']);

function playSelectedSong(song) {
    emits('playSong', song);
}
</script>

<style scoped>
  .cheatWidth{
    width:300%;
  }
</style>
