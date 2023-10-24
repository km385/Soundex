<template>
  <div
    class="display-options bg-[conic-gradient(at_bottom_right,_var(--tw-gradient-stops))] from-slate-900 via-purple-900 to-slate-900 transition-w duration-500 border-black border-b-2 z-10 w-full ">
    <ul class="flex pt-2 pl-2">
      <li class="mr-3">
        <button :class="{
          'border-black border-2 border-b-0 bg-[#171515] rounded-t-lg  ': selectedDisplay === 'grid',
          ' hover:border-[#B5446E]  hover:bg-gray-700 rounded-t-lg border-b-gray-800   ': selectedDisplay !== 'grid',
        }" class="inline-block py-1 px-3 pb-2 font-bold text-[#dbd2fd] " @click="changeDisplay('grid')"> Grid View
        </button>
      </li>
      <li class="mr-3">
        <button :class="{
          'border-black border-2 border-b-0 bg-[#171515] rounded-t-lg  ': selectedDisplay === 'list',
          ' hover:border-[#B5446E]  hover:bg-gray-700 rounded-t-lg border-b-gray-800   ': selectedDisplay !== 'list',
        }" class="inline-block py-1 px-3 pb-2 font-bold text-[#dbd2fd] " @click="changeDisplay('list')"> List View
        </button>
      </li>
    </ul>
  </div>
</template>
  
<script setup>
import { ref } from 'vue';

const emits = defineEmits(['selectedDisplay']);
const selectedDisplay = ref('grid');

function changeDisplay(viewType) {
  selectedDisplay.value = viewType;
  emits('selectedDisplay', viewType);
}


</script>
  
<style scoped>
.border-bottom-minimal:hover {
  border-bottom: 0.3rem;
}
</style>
  