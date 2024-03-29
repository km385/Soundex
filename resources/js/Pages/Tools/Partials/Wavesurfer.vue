<script setup>
import WaveSurfer from "wavesurfer.js";
import { inject, nextTick, onMounted, reactive, ref, watch } from "vue";
import RegionsPlugin from "wavesurfer.js/dist/plugins/regions";

const ws = reactive({
    wsInstance: WaveSurfer,
    durationTime: 0,
    currentTime: 0,
})


const props = defineProps({
    file: {
        type: File,
        required: true
    },
    id: {
        type: String,
        default: "waveform"
    },
    showRegion: Boolean,
    showControls: Boolean,
    allowSecondRegion: Boolean,
    allowVolumeControl: {
        default: true,
    }
})


const highContrast = inject('highContrast')

let regions = reactive({})

// expose set volume to parent component
defineExpose({
    changeVolume,
    onPlayClicked,
    onStopClicked
})

const emit = defineEmits(['regionCoords', 'isPlayingChanged'])

function changeVolume(value) {
    ws.wsInstance.setVolume(value)
}

const startVolumeValue = 0.5
const volumeValue = ref(startVolumeValue)

watch(volumeValue, (value) => {
    ws.wsInstance.setVolume(value)
})




watch(highContrast, (newValue) => {

    ws.wsInstance.setOptions({
        waveColor: newValue ? '#FFFF00FF' : '#FECEAB',
    })
})

const isPlaying = ref(false)

function onPlayClicked() {
    ws.wsInstance.play()
    isPlaying.value = !isPlaying.value
}

function onStopClicked() {
    ws.wsInstance.pause()
    isPlaying.value = !isPlaying.value
}

watch(() => props.file, (value) => {
    const blob = new Blob([value])
    ws.wsInstance.load(URL.createObjectURL(blob))
})

watch(() => isPlaying.value, (value) => {
    emit('isPlayingChanged', value);
});
const regionCheckboxValue = ref(false)

watch(regionCheckboxValue, (value) => {
    if (value) {
        addSecondRegion()
    } else {
        regions.getRegions()[1].remove()
    }
})

function addSecondRegion() {
    const region = regions.addRegion({
        start: 0,
        end: 1,
        color: 'rgba(0,255,10,0.5)',
        drag: true,
        resize: true,

    })
    region.setOptions({
        start: ws.durationTime * 0.3,
        end: ws.durationTime * 0.4
    })

    changeHandleStyles(region)
}

function changeHandleStyles(region) {
    for (const child of region.element.children) {
        child.innerHTML = '&vellip;'
        child.style.color = 'white'
        child.style.width = '6px'
        child.style.backgroundColor = '#282828'
        child.style.display = 'flex'
        child.style.alignItems = 'center'
        child.style.justifyContent = 'center'
        child.style.borderWidth = '0px'
    }
}

function formatTime(time) {
    const hours = Math.floor(time / 3600);
    const minutes = Math.floor((time % 3600) / 60);
    const seconds = Math.floor(time % 60);
    return `${String(hours).padStart(2, "0")}:${String(minutes).padStart(2, "0")}:${String(seconds).padStart(2, "0")}`;

}

onMounted(() => {
    ws.wsInstance = WaveSurfer.create({
        container: `#${props.id}`,
        waveColor: highContrast.value ? '#FFFF00FF' : '#FECEAB',
        progressColor: '#383351',
        cursorColor: 'black',
        normalize: true,
        cursorWidth: 5,
        url: URL.createObjectURL(props.file)
    })
    ws.wsInstance.setVolume(startVolumeValue)

    if (props.showRegion) {
        regions = ws.wsInstance.registerPlugin(RegionsPlugin.create())
        const region = regions.addRegion({
            start: 0,
            end: 1,
            color: 'rgba(0,255,10,0.5)',
            drag: true,
            resize: true,

        })

        changeHandleStyles(region)
    }

    // if(regionCheckboxValue.value){
    //     // when user goes back from download screen and had second region selected
    //     addSecondRegion()
    // }


    ws.wsInstance.on("audioprocess", () => {
        // since the event is triggered a lot, it might be good to consider a global ref to the region to
        // avoid allocation

        ws.currentTime = ws.wsInstance.getCurrentTime()

        if (props.showRegion) {
            // keep cursor in the region while dragging it
            const region = regions.getRegions()[0]
            if (ws.currentTime >= region.end || ws.currentTime <= region.start) {
                // +0.001 for firefox
                ws.wsInstance.setTime(region.start + 0.001)
            }
        }
    })

    ws.wsInstance.on("ready", (duration) => {
        ws.durationTime = duration

        if (props.showRegion) {
            regions.getRegions()[0].setOptions({
                start: duration * 0.1,
                end: duration * 0.2
            })
            // emit first coords
            const region = regions.getRegions()[0]
            const regionData = {
                id: 0,
                start: region.start,
                end: region.end
            }
            emit('regionCoords', regionData)

            // if(regionCheckboxValue.value){
            //     regions.getRegions()[1].setOptions({
            //         start: duration * 0.3,
            //         end: duration * 0.4
            //     })
            // }
        }
    })

    if (props.showRegion) {
        regions.on('region-updated', (region) => {
            const regionIndex = regions.getRegions().indexOf(region)
            const regionData = {
                id: regionIndex,
                start: region.start,
                end: region.end
            }
            emit('regionCoords', regionData)
        })
    }

})

</script>

<template>
    <div class="p-4 rounded-lg shadow-xl">

        <div :id="id"></div>
    </div>
    <div v-if="showControls">
        <div class="flex justify-between">
            <p>{{ formatTime(ws.currentTime) }}</p>
            <p>{{ formatTime(ws.durationTime) }}</p>
        </div>
        <div>
            <div class="flex justify-between">
                <div class="flex items-center mt-5">
                    <button type="button" v-if="!isPlaying" @click="onPlayClicked" :class="{
                        'high-contrast-button': highContrast, 'high-contrast-metronome-on': highContrast && isPlaying,
                        'bg-blue-500': !highContrast && isPlaying, 'bg-blue-400 text-white hover:bg-blue-500': !highContrast
                    }" class="bg-blue-400 text-white rounded p-2 mr-3 hover:bg-blue-500 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" height="30" viewBox="0 -960 960 960" width="30">
                            <path :fill="highContrast ? '#FFFF00FF' : '#F2F3F5'"
                                d="M381-239q-20 13-40.5 1.5T320-273v-414q0-24 20.5-35.5T381-721l326 207q18 12 18 34t-18 34L381-239Zm19-241Zm0 134 210-134-210-134v268Z" />
                        </svg>

                    </button>
                    <button type="button" v-if="isPlaying" @click="onStopClicked" :class="{
                        'high-contrast-button': highContrast, 'high-contrast-metronome-on': highContrast && isPlaying,
                        'bg-blue-500': !highContrast && isPlaying, 'bg-blue-400 text-white hover:bg-blue-500': !highContrast
                    }" class="bg-blue-400 text-white rounded p-2 mr-3 hover:bg-blue-500 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" height="30" viewBox="0 -960 960 960" width="30">
                            <path :fill="highContrast ? '#FFFF00FF' : '#F2F3F5'"
                                d="M600-200q-33 0-56.5-23.5T520-280v-400q0-33 23.5-56.5T600-760h80q33 0 56.5 23.5T760-680v400q0 33-23.5 56.5T680-200h-80Zm-320 0q-33 0-56.5-23.5T200-280v-400q0-33 23.5-56.5T280-760h80q33 0 56.5 23.5T440-680v400q0 33-23.5 56.5T360-200h-80Zm320-80h80v-400h-80v400Zm-320 0h80v-400h-80v400Zm0-400v400-400Zm320 0v400-400Z" />
                        </svg>

                    </button>
                    <input v-if="allowVolumeControl" type="range" min="0" max="1" step="0.01"
                        :class="{ 'range1': highContrast }" class="slider" id="myRange" v-model="volumeValue">
                    <div class="inline" v-if="allowSecondRegion">
                        <input id="regionCheckBox" type="checkbox" v-model="regionCheckboxValue"
                            :class="{ 'high-contrast-checkbox': highContrast }"
                            class=" ml-2 form-checkbox accent-blue-600 transition duration-150 ease-in-out focus:ring focus:ring-blue-400" />
                        <label class="ml-2" for="regionCheckBox">{{ $t("wavesurfer.secondRegion") }}</label>
                    </div>
                </div>
                <slot name="metronome" class="flex items-center mt-5"></slot>
            </div>

        </div>
    </div>
</template>

<style scoped>
.high-contrast-metronome-on {
    background-color: #20200b !important;
}

.high-contrast-input {
    @apply text-xl border border-[#FFFF00FF] bg-black text-[#FFFF00FF]
}

.high-contrast-checkbox {
    @apply border border-[#FFFF00FF] text-[#FFFF00FF] focus:ring-0 focus:ring-offset-0 bg-black checked:text-black checked:border-[#FFFF00FF]
}

.high-contrast-button {
    @apply text-xl border border-[#FFFF00FF] bg-black text-[#FFFF00FF] focus:border-[#FFFF00FF] focus:ring-[#FFFF00FF] hover:bg-yellow-300 hover:text-black
}

.range1 {
    -webkit-appearance: none;
    appearance: none;
    cursor: pointer;
    outline: none;
    overflow: hidden;
    border-radius: 16px;
}

.range1::-webkit-slider-runnable-track {
    height: 15px;
    background: yellow;
    border-radius: 16px;
}

.range1::-moz-range-track {
    height: 15px;
    background: yellow;
    border-radius: 16px;
}

.range1::-webkit-slider-thumb {
    -webkit-appearance: none;
    appearance: none;
    height: 15px;
    width: 15px;
    background-color: black;
    border-radius: 50%;
    border: 2px solid #f50;
    box-shadow: -407px 0 0 400px #f50;
}

.range1::-moz-range-thumb {
    height: 15px;
    width: 15px;
    background-color: black;
    border-radius: 50%;
    border: 1px solid #f50;
    box-shadow: -407px 0 0 400px #f50;
}
</style>
