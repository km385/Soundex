<script setup>
import {onMounted, ref} from "vue";
import axios from "axios";

const songs = ref()
onMounted(async () => {
    const res = await axios.get('/songs')
    if (res.data === null) return
    songs.value = res.data.songs
    console.log(res.data)
})

const emits = defineEmits(['fileChosen'])

async function chooseSong(song) {
    console.log(song.id)

    const res = await axios.get(`/songs/${song.id}`, {
        responseType: "blob"
    })

    const file = new File([new Blob([res.data])], song.originalName + '.' + song.extension)
    emits('fileChosen', file)

    console.log(file)
}


</script>

<template>
    <div class="bg-red-500 flex justify-around">
        <p>id</p>
        <p>name</p>
        <p>title</p>
    </div>
    <div v-for="(song, index) in songs" :key="song.id">
        <div class="bg-red-500 flex justify-around" @click="chooseSong(song)">
            <p>{{song.id}}</p>
            <p>{{song.originalName + '.' + song.extension}}</p>
            <p>{{song.title}}</p>

        </div>
    </div>
</template>

<style scoped>

</style>
