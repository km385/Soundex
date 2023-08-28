<template>

    <Head title="Welcome" />
    <SidebarLayout>
        home

    </SidebarLayout>


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
