<script setup>
const props = defineProps({
  selectedBPM: {
    required: true,
    type: Number,
  },
  currentTime: {
    required: true,
    type: Number,
  },
  currentDuration: {
    required: true,
    type: Number,
  },
});
</script>

<template>
  <div>
    <p>BPM selected: {{ selectedBPM }}</p>
    <p>Current Time: {{ currentTime }}</p>
    <p>Current Duration: {{ currentDuration }}</p>
  </div>
</template>
