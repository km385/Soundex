<script setup>
import { Head, Link } from '@inertiajs/vue3';
import SvgComp from "@/Components/SvgComp.vue";


const tools = [
    { svg: 'scissors', name: 'Cutter', link: '/tools/cutter' },
    { svg: 'tag', name: 'Tag Editor', link: '/tools/tageditor' },
    { svg: 'speedometer2', name: 'SpeedUp', link: '/tools/speedup' },
    { svg: 'bandaid', name: 'Merge', link: '/tools/merge' },
    { svg: 'filetype-mp3', name: 'Converter', link: '/tools/converter' },
    { svg: 'film', name: 'Video to Audio', link: '/tools/videotoaudio' },
    { svg: 'volume-up', name: 'Volume Changer', link: '/tools/volumechanger' },
    { svg: 'mic', name: 'Recorder', link: '/tools/recorder' },
    { svg: 'layers', name: 'LayerMix', link: '/tools/layermixer' },
    { svg: 'search', name: 'BPM Finder', link: '/tools/bpmFinder' },
    { svg: 'hammer', name: 'Diagnosis', link: '/tools/diagnosis' }
];

</script>

<template>

    <Head title="Welcome" />
    <div class="text-white flex flex-col items-center mt-20">
        <div class="px-20 text-center">
            <p class="text-5xl">Welcome to Soundex!</p>
            <p class="text-4xl mt-2">Thanks to our tools, you can quickly and easily edit your audio
                files.</p>
            <p class="text-2xl mt-4">You can also save your files on the cloud to have access to them everywhere.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 2xl:grid-cols-3 gap-8 mt-5 px-5 lg:px-0 w-4/5">
            <div v-for="tool in tools">
                <div class="flex w-full py-5 px-2 rounded-lg shadow-lg shadow-cyan-500 border border-white h-full hover:scale-105">
                    <div id="icon" class=" flex items-center text-white">
                        <SvgComp :name="tool.svg" class="w-[100px] h-[100px]"/>
                    </div>


                    <div class="flex flex-col px-4">
                        <div id="title">
                            <Link :href="tool.link">
                                <p class="text-3xl font-bold underline cursor-pointer w-fit hover:text-[#FECEAB]">
                                    {{ $t(`welcome.${tool.name}.title`) }}
                                </p>
                            </Link>

                        </div>
                        <div id="desc" class="overflow-y-auto max-h-[200px] scrollbar text-justify">
                            <p>
                                {{ $t(`welcome.${tool.name}.description`) }}

                            </p>
                        </div>

                    </div>
                </div>
            </div>
        </div>

    </div>





</template>



<style>

.scrollbar::-webkit-scrollbar {
    @apply w-2
}

.scrollbar::-webkit-scrollbar-track {

}

.scrollbar::-webkit-scrollbar-thumb {
    @apply bg-gray-700 rounded-lg
}

</style>
