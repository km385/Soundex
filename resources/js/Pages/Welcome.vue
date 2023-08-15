<script setup>
import { Head, Link } from '@inertiajs/vue3';
import CustomAuthenticatedLayout from "@/Layouts/CustomAuthenticatedLayout.vue";

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

<template>

    <Head title="Welcome" />
    <CustomAuthenticatedLayout>
        home page
    </CustomAuthenticatedLayout>


</template>

<style>


</style>
