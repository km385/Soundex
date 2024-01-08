<script setup>
import { ref, inject, watch, onMounted, onBeforeUnmount } from "vue";
import VueNumericInput from '@chenfengyuan/vue-number-input';

const props = defineProps({
  selectedBPM: {
    required: true,
    type: Number,
  },
  isPlaying: {
    required: false,
    type: Boolean,
    default: false,
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
const ftStartedPlaying = ref(false)
const ftStoppedPlaying = ref(false)
watch(() => props.selectedBPM, (newValue) => {
  valueBPM.value = newValue;
});

watch(() => props.isPlaying, (newValue) => {
  if (newValue == true && isMetronomeOn.value == false && ftStartedPlaying.value == false) {
    handleClick();
    ftStartedPlaying.value = true
  } else if (newValue == false && isMetronomeOn.value == true && ftStoppedPlaying.value == false) {
    isMetronomeOn.value = false
    ftStoppedPlaying.value = true
  }
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
  var gainNode = audioContext.value.createGain();
  note.frequency.value = 380;
  note.connect(gainNode);
  gainNode.connect(audioContext.value.destination);
  gainNode.gain.value = 2;
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
    <div class="flex flex-row flex-wrap items-center">
      <div class="mb-2">
        <label for="valueBPM">BPM:</label><br>
        <vue-numeric-input :class="{ ' high-contrast-numeric-input': highContrast }" class="text-black" id="valueBPM"
          size="small" v-model="valueBPM" :min="1" :max="320" inline controls rounded center />
      </div>
      <div>
        <button @click="handleClick" :class="{
          'high-contrast-button': highContrast, 'high-contrast-metronome-on': highContrast && isMetronomeOn,
          'bg-blue-500': !highContrast && isMetronomeOn, 'bg-blue-400 text-white hover:bg-blue-500': !highContrast
        }"
          class=" rounded py-2 px-4 mt-2 ml-5 ">
          Metronome
        </button>
      </div>
    </div>


  </div>
</template>


<style>
.high-contrast-metronome-on {
  background-color: #20200b !important;
}

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
