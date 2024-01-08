<script setup>
import { ref, inject, watch, onMounted, onBeforeUnmount  } from "vue";
import VueNumericInput from '@chenfengyuan/vue-number-input';

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

const handleClick = () => {
  isMetronomeOn.value = !isMetronomeOn.value;
  if (isMetronomeOn.value) {
    startMetronome();
  }
};

const valueBPM = ref(props.selectedBPM)
const isMetronomeOn = ref(false)
const highContrast = inject('highContrast')
const beepTime = ref(0.0)
const audioContext = ref(null);

const valueOffset = ref(0)

watch(() => props.selectedBPM, (newValue) => {
  valueBPM.value = newValue;
});

function startMetronome() {
  schedule()
}

function schedule() {
  while (beepTime.value < audioContext.value.currentTime + 0.1) {
    playBeep(beepTime.value);
    updateTime();
  }
  if (isMetronomeOn.value) {
    setTimeout(schedule, 0.1);
  }
}

function playBeep(t) {
  var note = audioContext.value.createOscillator();
  note.frequency.value = 380;
  note.connect(audioContext.value.destination);
  note.start(t);
  note.stop(t + 0.05);
}

function updateTime() {
  beepTime.value += 60.0 / parseInt(valueBPM.value, 10);

}

onMounted(() => {
  audioContext.value = new (window.AudioContext || window.webkitAudioContext)();
});

onBeforeUnmount(() => {
  isMetronomeOn.value = false;
});
</script>

<template>
  <div>
    <div>
      <p>BPM selected: {{ valueBPM }}</p>
      <p>Current Time: {{ currentTime }}</p>
      <p>Current Duration: {{ currentDuration }}</p>
    </div>
    <div class="flex flex-col flex-wrap">
      <div class="mb-2">
        <label for="valueBPM">BPM:</label><br>
        <vue-numeric-input :class="{ ' high-contrast-numeric-input': highContrast }" class="text-black" id="valueBPM"
          size="small" v-model="valueBPM" :min="1" :max="320" inline controls rounded center />
      </div>

      <div class="mb-2">
        <label :class="{ 'high-contrast-label': highContrast }" for="valueOffset">Offset:</label><br>
        <vue-numeric-input :class="{ ' high-contrast-numeric-input': highContrast }" class="text-black" id="valueOffset"
          size="small" v-model="valueOffset" :min="0" :max="60" inline controls rounded center />
      </div>

      <div>
        <button @click="handleClick"
          :class="{ 'high-contrast-button': highContrast, 'bg-blue-400 text-white hover:bg-blue-500': !highContrast }"
          class=" rounded py-2 px-4 mt-2 ml-5 ">
          Metronome
        </button>
      </div>
    </div>


  </div>
</template>


<style>
.high-contrast-numeric-input>button,
.high-contrast-numeric-input>input,
.high-contrast-numeric-input {
  background: transparent !important;
  color: #FFFF00FF !important;
  font-size: 1.25rem
    /* 20px */
  ;
  line-height: 1.75rem
    /* 28px */
  ;
}

.high-contrast-numeric-input,
.high-contrast-numeric-input>button {
  border: solid 1px #FFFF00FF !important;
}

.high-contrast-numeric-input>input,
.high-contrast-numeric-input>button {
  border: none !important;
}

.high-contrast-numeric-input>button::after,
.high-contrast-numeric-input>button::before {
  background-color: #FFFF00FF !important;

}

.high-contrast-button {
  @apply text-xl border border-[#FFFF00FF] bg-black text-[#FFFF00FF] focus:border-[#FFFF00FF] focus:ring-[#FFFF00FF] hover:bg-yellow-300 hover:text-black
}
</style>
