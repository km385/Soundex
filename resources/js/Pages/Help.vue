<script setup>

import SvgComp from "@/Components/SvgComp.vue";
import {inject, ref} from "vue";

const openQuestions = ref({});
function toggleQuestion(index) {
    openQuestions.value[index] = !openQuestions.value[index];
}

const highContrast = inject('highContrast')
</script>

<template>
<div

    class="flex flex-col items-center text-white ">
    <div
        :class="{'high-contrast-input':highContrast}"
        class="mt-20 lg:mt-10">
        <p class="text-5xl">FAQ</p>
    </div>
    <div class="my-10 mx-10 text-white">

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
            <div v-for="(content, index) in $tm('help')" class="">
                    <div @click="toggleQuestion(index)"
                         :class="{'high-contrast-input':highContrast}"
                         class="bg-gray-400 flex items-center px-4 cursor-pointer ">
                        <div class="mr-2 grow">
                            <p>{{$rt(content.question)}}</p>
                        </div>
                        <div class="w-10 py-1 sm:h-12">
                            <SvgComp v-if="openQuestions[index]" name="dash-lg" />
                            <SvgComp v-else name="plus-lg" />
                        </div>
                    </div>
                    <Transition name="slide-fade">
                        <div v-if="openQuestions[index]"
                             :class="{'high-contrast-input':highContrast}"
                             class="text-justify px-4 mt-2">
                            {{$rt(content.answer)}}
                        </div>
                    </Transition>
                </div>

        </div>
    </div>
</div>
</template>

<style scoped>
.slide-fade-enter-active,
.slide-fade-leave-active {
    transition: opacity 0.5s, transform 0.5s
}

.slide-fade-enter-from, .slide-fade-leave-to {
    opacity: 0;
    transform: translateY(-20px);
}

.high-contrast-input {
    @apply text-xl border border-[#FFFF00FF] bg-black text-[#FFFF00FF]
}

</style>
