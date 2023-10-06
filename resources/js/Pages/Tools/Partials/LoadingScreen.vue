<script setup>
    import {computed} from "vue";

    const props = defineProps({
        isCollapsed: {
            type: Boolean,
            required: true
        }
    })
    const computedWidth = computed(() => (props.isCollapsed ? '5' : '12'))
</script>

<template>

    <div class="absolute bg-gray-800 flex justify-center items-center h-screen z-50 " :style="{ width: `calc(100vw - ${computedWidth}rem)` }">
        <img
            class="relative  "
            src="../../../../images/output-onlinegiftools.gif"
            alt="loading..."
            width="200"
        />
    </div>
</template>

<style scoped>

</style>
