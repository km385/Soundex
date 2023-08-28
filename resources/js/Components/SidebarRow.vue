<script setup>
    import {computed} from "vue";
    import { Link } from '@inertiajs/vue3';


    const props = defineProps({
        text: {
            type: String,
            required: true
        },
        icon: {
            type: String,
            required: true
        },
        showText: {
            type: Boolean,
            required: true
        }
    })

    const url = computed(() => {
        return new URL(`../../images/${props.icon}`, import.meta.url)
    })
</script>

<template>

    <div class="flex items-center h-14 w-full duration-200 hover:bg-gray-500 cursor-pointer">
        <img class="h-full mr-5 ml-2 justify-self-start" :src="url" alt="small icon">
        <div v-if="!showText" class="flex-grow text-2xl ">
            <p>{{text}}</p>
        </div>
    </div>
</template>

<style scoped>

</style>
