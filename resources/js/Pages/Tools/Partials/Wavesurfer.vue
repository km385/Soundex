<script setup>
import WaveSurfer from "wavesurfer.js";
import { nextTick, onMounted, reactive, ref, watch } from "vue";
import RegionsPlugin from "wavesurfer.js/dist/plugins/regions";

const ws = reactive({
    wsInstance: WaveSurfer,
    durationTime: 0,
    currentTime: 0,
})

let regions = reactive({})

const volumeValue = ref(0.1)
watch(volumeValue, (value) => {
    ws.wsInstance.setVolume(value)
})

const regionCheckboxValue = ref(false)
watch(regionCheckboxValue, (value) => {

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
    allowSecondRegion: Boolean
})
const emit = defineEmits(['regionCoords'])

onMounted(() => {
    console.log('on mounted')
    console.log(props.id)
    console.log(props.file)

    ws.wsInstance = WaveSurfer.create({
        container: `#${props.id}`,
        waveColor: '#FECEAB',
        progressColor: '#383351',
        cursorColor: 'black',
        normalize: true,
        cursorWidth: 5,
        url: URL.createObjectURL(props.file)
    })
    ws.wsInstance.setVolume(0.1)

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
                ws.wsInstance.setTime(region.start)
            }
        }
    })

    ws.wsInstance.on("ready", (duration) => {
        console.log('ready event')
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
            console.log('on update-end')
            const regionIndex = regions.getRegions().indexOf(region)
            const regionData = {
                id: regionIndex,
                start: region.start,
                end: region.end
            }
            // console.log(regionData)
            emit('regionCoords', regionData)
        })
    }

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

watch(regionCheckboxValue, (value) => {
    if (value) {
        addSecondRegion()
    } else {
        regions.getRegions()[1].remove()
    }
    console.log(regions.getRegions())
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

</script>

<template>
    <div class="p-4 rounded-lg shadow-xl">

        <div :id="id"></div>
    </div>
    <div v-if="showControls">
        <div class="flex justify-between">
            <!--            <input type="text" v-model="ws.currentTime" class="border-none w-auto">-->
            <p>{{ formatTime(ws.currentTime) }}</p>
            <p>{{ formatTime(ws.durationTime) }}</p>
        </div>
        <div>
            <div class="flex items-center mt-5">
<!--                TODO: add style when button pressed-->
                <button type="button" v-if="!isPlaying" @click="onPlayClicked"
                        class="bg-blue-400 text-white rounded p-2 mr-3 hover:bg-blue-500 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" height="30" viewBox="0 -960 960 960" width="30">
                        <path  fill="#F2F3F5" d="M381-239q-20 13-40.5 1.5T320-273v-414q0-24 20.5-35.5T381-721l326 207q18 12 18 34t-18 34L381-239Zm19-241Zm0 134 210-134-210-134v268Z"/>
                    </svg>

                </button>
                <button type="button" v-if="isPlaying" @click="onStopClicked"
                        class="bg-blue-400 text-white rounded p-2 mr-3 hover:bg-blue-500 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" height="30" viewBox="0 -960 960 960" width="30">
                        <path  fill="#F2F3F5" d="M600-200q-33 0-56.5-23.5T520-280v-400q0-33 23.5-56.5T600-760h80q33 0 56.5 23.5T760-680v400q0 33-23.5 56.5T680-200h-80Zm-320 0q-33 0-56.5-23.5T200-280v-400q0-33 23.5-56.5T280-760h80q33 0 56.5 23.5T440-680v400q0 33-23.5 56.5T360-200h-80Zm320-80h80v-400h-80v400Zm-320 0h80v-400h-80v400Zm0-400v400-400Zm320 0v400-400Z"/>
                    </svg>

                </button>
                <input type="range" min="0" max="1" step="0.01" class="slider" id="myRange" v-model="volumeValue">
                <div class="inline" v-if="allowSecondRegion">
                    <input id="regionCheckBox" type="checkbox" v-model="regionCheckboxValue"
                           class="ml-2 form-checkbox accent-blue-600 transition duration-150 ease-in-out focus:ring focus:ring-blue-400" />
                    <label class="ml-2" for="regionCheckBox">Second Region</label>
                </div>
            </div>

        </div>
    </div>
</template>

<style scoped>

</style>
