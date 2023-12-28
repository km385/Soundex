<script setup>

import SvgComp from "@/Components/SvgComp.vue";
import {ref} from "vue";

const questions = [
    { question: 'Best tool for cutting and trimming audio files online?', answer: 'The Cutter tool is your go-to option for precise audio editing. Easily remove unwanted sections and customize your favorite tracks with precision and ease.' },
    { question: 'Best tool for cutting and trimming audio files online?', answer: 'The Cutter tool is your go-to option for precise audio editing. Easily remove unwanted sections and customize your favorite tracks with precision and ease.' },
    { question: 'Best tool for cutting and trimming audio files online?', answer: 'The Cutter tool is your go-to option for precise audio editing. Easily remove unwanted sections and customize your favorite tracks with precision and ease.' },
    { question: 'Best tool for cutting and trimming audio files online?', answer: 'The Cutter tool is your go-to option for precise audio editing. Easily remove unwanted sections and customize your favorite tracks with precision and ease.' },
    { question: 'Best tool for cutting and trimming audio files online?', answer: 'The Cutter tool is your go-to option for precise audio editing. Easily remove unwanted sections and customize your favorite tracks with precision and ease.' },
    { question: 'Best tool for cutting and trimming audio files online?', answer: 'The Cutter tool is your go-to option for precise audio editing. Easily remove unwanted sections and customize your favorite tracks with precision and ease.' },
    { question: 'Best tool for cutting and trimming audio files online?', answer: 'The Cutter tool is your go-to option for precise audio editing. Easily remove unwanted sections and customize your favorite tracks with precision and ease.' },
    { question: 'Best tool for cutting and trimming audio files online?', answer: 'The Cutter tool is your go-to option for precise audio editing. Easily remove unwanted sections and customize your favorite tracks with precision and ease.' },
    { question: 'Best tool for cutting and trimming audio files online?', answer: 'The Cutter tool is your go-to option for precise audio editing. Easily remove unwanted sections and customize your favorite tracks with precision and ease.' },
    { question: 'Best tool for cutting and trimming audio files online?', answer: 'The Cutter tool is your go-to option for precise audio editing. Easily remove unwanted sections and customize your favorite tracks with precision and ease.' },

];

const openQuestions = ref({});

function toggleQuestion(index) {
    openQuestions.value[index] = !openQuestions.value[index];
}
</script>

<template>
<div class="flex flex-col items-center ">
    <div class="mt-20 lg:mt-10">
        <p class="text-5xl text-white">FAQ</p>
    </div>
    <div class="my-10 mx-10 text-white">
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
            <div v-for="(question, index) in questions" class="max-w-lg sm:w-[512px]">
                    <div @click="toggleQuestion(index)" class="bg-gray-400 flex items-center px-4 cursor-pointer ">
                        <div class="mr-2 grow">
                            <p>{{question.question}}</p>
                        </div>
                        <div class="w-10 py-1 sm:h-12">
                            <SvgComp v-if="openQuestions[index]" name="dash-lg" />
                            <SvgComp v-else name="plus-lg" />
                        </div>
                    </div>
                    <Transition name="slide-fade">
                        <div v-if="openQuestions[index]" class="text-justify px-4 mt-2">
                            {{question.answer}}
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


</style>
