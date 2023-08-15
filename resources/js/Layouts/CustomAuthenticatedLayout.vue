<template>
    <Head>
        <title>
            Default title
        </title>
    </Head>
    <section class="p-6 bg-gray-200">
        <header class="flex justify-between ">
            <div class="flex items-center">
                <h1 class="font-bold text-lg">
                    Soundex
                </h1>
                <p class="text-sm ml-4" v-if="$page.props.auth.user">
                    Welcome back, {{ $page.props.auth.user.name }}
                </p>
            </div>


            <Nav />

        </header>
    </section>
    <section class="p-6">

        <div class="max-w-3xl mx-auto">
            <slot/>
        </div>
    </section>
</template>

<script>
// import Nav from "@/Shared/Nav.vue"
import {Head} from "@inertiajs/vue3";
import DropdownLink from "@/Components/DropdownLink.vue";
import NavLink from "@/Components/NavLink.vue";
import Nav from "@/Layouts/Nav.vue";

export default {
    name: "Layout",
    components: {Nav, NavLink, DropdownLink, Head},
}
</script>

<style scoped>

</style>
