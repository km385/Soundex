<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';
import {inject} from "vue";

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const highContrast = inject('highContrast')
</script>

<template>
    <Head title="Profile" />

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6 lg:mt-10 mt-14 px-6">
                <Link :href="route('dashboard')" as="button" :class="{'high-contrast-btn':highContrast}"
                      class="bg-blue-400 text-white rounded py-2 px-4 hover:bg-blue-500 mb-4 ">
                    {{ $t('resultOptionsScreen.goBack') }}
                </Link>
            </div>
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6 px-6">
                <div :class="{'high-contrast': highContrast}" class="p-4 sm:p-8 bg-gray-800 shadow sm:rounded-lg">
                    <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                        class="max-w-xl"
                    />
                </div>

                <div :class="{'high-contrast': highContrast}" class="p-4 sm:p-8 bg-gray-800 shadow sm:rounded-lg">
                    <UpdatePasswordForm class="max-w-xl" />
                </div>

                <div :class="{'high-contrast': highContrast}" class="p-4 sm:p-8 bg-gray-800 shadow sm:rounded-lg">
                    <DeleteUserForm class="max-w-xl" />
                </div>
            </div>
        </div>
</template>

<style scoped>
.high-contrast {
    @apply bg-black border border-[#FFFF00FF]
}

.high-contrast-btn {
    @apply bg-black border border-[#FFFF00FF] hover:bg-[#FFFF00FF] hover:text-black text-[#FFFF00FF]
}
</style>
