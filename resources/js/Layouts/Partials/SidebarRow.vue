<script setup>
    import {computed} from "vue";
    import { Link } from '@inertiajs/vue3';


    const props = defineProps({
        text: {
            type: String,
            required: true
        },
        icon: {
            type: String,
            required: true
        },
        showText: {
            type: Boolean,
            required: true
        }
    })

    const url = computed(() => {
        return new URL(`../../../images/${props.icon}`, import.meta.url)
    })

</script>

<template>
        <div class="flex items-center h-14 w-full duration-200 hover:bg-gray-500 cursor-pointer relative rounded-lg">
            <img class="h-full mr-5 ml-2 justify-self-start" :src="url" alt="small icon">
            <transition name="slide-fade">

                <div v-if="!showText" class="flex-grow text-2xl ">
                    <p>{{text}}</p>
                </div></transition>


<!--            use this to add expanded menu for a row-->
            <slot />
        </div>
</template>

<style scoped>
/* unwrap */
.slide-fade-enter-active {
    transition: all 0.5s cubic-bezier(1, 0.5, 0.8, 1);
}

/* collapse */
.slide-fade-leave-active {
    transition: all 0.1s ease-out;
}

.slide-fade-enter-from,
.slide-fade-leave-to {
    transform: translateX(-20px);
    opacity: 0;
}
</style>
