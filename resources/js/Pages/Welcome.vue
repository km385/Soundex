<template>

    <Head title="Welcome" />
    <div class="text-white flex justify-center items-center h-screen flex-col">
        <p class="text-4xl">Welcome to Soundex! Thanks to our tools, you can quickly and easily edit your audio files.</p>
        <p class="text-4xl">You can also save your files on the cloud to have access to them everywhere.</p>
    </div>



</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import CustomAuthenticatedLayout from "@/Layouts/CustomAuthenticatedLayout.vue";
import SidebarLayout from "@/Layouts/SidebarLayout.vue";

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
});
</script>

<style>


</style>
