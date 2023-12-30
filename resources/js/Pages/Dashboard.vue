<script setup>
import {Head, Link, usePage} from '@inertiajs/vue3';
import Chart from "@/Pages/Profile/Partials/Chart.vue";
import SvgComp from "@/Components/SvgComp.vue";
import {computed} from "vue";

const page = usePage()

const userDate = computed(() => {
    const timestamp = page.props.auth.user.created_at
    const dateObject = new Date(timestamp);

    const year = dateObject.getFullYear();
    const month = (dateObject.getMonth() + 1).toString().padStart(2, '0'); // Months are zero-indexed
    const day = dateObject.getDate().toString().padStart(2, '0');

    return `${day} ${month} ${year}`;

})
</script>

<template>
    <Head title="Dashboard"/>

    <!--    <AuthenticatedLayout>-->
    <!--        <template #header>-->
    <!--            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard</h2>-->
    <!--        </template>-->
    <div class="max-w-3xl mx-auto rounded-lg bg-gray-700 px-4 mt-10">
        <div class="flex rounded-lg px-4 w-full pt-2">
            <div class="">
                <SvgComp name="user" class="w-40"/>
            </div>
            <div class=" flex flex-col pl-1 grow ">
                <div class="text-6xl font-bold ">
                    {{ page.props.auth.user.nickname }}
                </div>
                <div class="">
                    Joined {{ userDate }}
                </div>
            </div>
            <div class="">
                <Link :href="route('profile.edit')">
                    <SvgComp name="gear" class="w-10 cursor-pointer hover:rotate-90 duration-500"/>
                </Link>
            </div>
        </div>
    </div>
    <div class="mx-auto max-w-3xl mt-20 lg:mt-10 flex bg-gray-700 rounded-lg items-center px-10">

        <!--        <nav-link href="change-password">-->
        <!--            Change Password-->
        <!--        </nav-link>-->
        <Chart class="mt-10 w-1/3 pb-5 "/>

        <div class="bg-red-700 flex rounded-lg p-2 mt-10 self-start ">
            <div class="flex flex-col justify-center items-center mr-2">
                <p class="text-orange-600">23</p>
                <p>Total files</p>
            </div>

            <div class="flex flex-col justify-center items-center mr-2">
                <p class="text-orange-600">200 MB</p>
                <p>Max storage</p>
            </div>

            <div class="flex flex-col justify-center items-center">
                <p class="text-orange-600">23</p>
                <p>Used storage</p>
            </div>

        </div>


    </div>

    <!--        <div class="py-12">-->
    <!--            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">-->
    <!--                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">-->
    <!--                    <div class="p-6 text-gray-900">You're logged in!</div>-->
    <!--                </div>-->
    <!--            </div>-->
    <!--        </div>-->
    <!--    </AuthenticatedLayout>-->
</template>
