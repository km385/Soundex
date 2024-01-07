<script setup>
import DangerButton from './DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from './Modal.vue';
import SecondaryButton from './SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import {inject, nextTick, ref} from 'vue';

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;

    nextTick(() => passwordInput.value.focus());
};

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;

    form.reset();
};

const highContrast = inject('highContrast')
</script>

<template>
    <section class="space-y-6">
        <header>
            <h2 :class="{'high-contrast-text':highContrast}" class="text-lg font-medium text-white">{{$t('profileEdit.deleteAccount.header')}}</h2>

            <p :class="{'high-contrast-text':highContrast}" class="mt-1 text-sm text-gray-200">
                {{$t('profileEdit.deleteAccount.desc')}}
            </p>
        </header>

        <DangerButton @click="confirmUserDeletion">{{$t('profileEdit.deleteAccount.deleteButton')}}</DangerButton>

        <Modal :show="confirmingUserDeletion" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    {{$t('profileEdit.deleteAccount.confirmation.header')}}
                </h2>

                <p class="mt-1 text-sm text-gray-600">
                    {{$t('profileEdit.deleteAccount.confirmation.desc')}}
                </p>

                <div class="mt-6">
                    <InputLabel for="password" :value="$t('profileEdit.deleteAccount.confirmation.password')" class="sr-only" />

                    <TextInput
                        id="password"
                        ref="passwordInput"
                        v-model="form.password"
                        type="password"
                        class="mt-1 block w-3/4"
                        :placeholder="$t('profileEdit.deleteAccount.confirmation.password')"
                        @keyup.enter="deleteUser"
                    />

                    <InputError :message="form.errors.password" class="mt-2" />
                </div>

                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="closeModal"> {{$t('profileEdit.deleteAccount.confirmation.cancel')}} </SecondaryButton>

                    <DangerButton
                        class="ml-3"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        @click="deleteUser"
                    >
                        {{$t('profileEdit.deleteAccount.confirmation.delete')}}
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </section>
</template>

<style scoped>
.high-contrast-text {
    @apply text-[#FFFF00FF]
}
</style>
