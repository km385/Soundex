<script setup>
import WaveSurfer from "wavesurfer.js";
import {nextTick, onMounted, reactive, ref, watch} from "vue";
import RegionsPlugin from "wavesurfer.js/dist/plugins/regions";

const ws = reactive({
    wsInstance: WaveSurfer,
    durationTime: 0,
    currentTime: 0,
})

let regions = reactive({})

const props = defineProps({
    file: File,
    id: {
        type: String,
        default: "waveform"
    },
    showRegion: Boolean,
    showControls: Boolean,
    secondRegion: Boolean
})
const emit = defineEmits(['regionCoords'])

onMounted( () => {
    console.log('on mounted')
    console.log(props.id)
    console.log(props.file)

    ws.wsInstance = WaveSurfer.create({
        container: `#${props.id}`,
        waveColor: '#4F4A85',
        progressColor: '#383351',
        cursorColor: 'black',
        normalize: false,
        cursorWidth: 5,
        url: URL.createObjectURL(props.file)
    })

    if(props.showRegion){
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
    ws.wsInstance.on("audioprocess", () => {
        // since the event is triggered a lot, it might be good to consider a global ref to the region to
        // avoid allocation

        ws.currentTime = ws.wsInstance.getCurrentTime()

        if(props.showRegion){
            // keep cursor in the region while dragging it
            const region = regions.getRegions()[0]
            if(ws.currentTime >= region.end || ws.currentTime <= region.start){
                ws.wsInstance.setTime(region.start)
            }
        }
    })

    ws.wsInstance.on("ready", (duration) => {
        console.log('ready event')
        ws.durationTime = duration

        if(props.showRegion){
            regions.getRegions()[0].setOptions({
                start: duration * 0.1,
                end: duration * 0.2
            })

            if(props.secondRegion){
                regions.getRegions()[1].setOptions({
                    start: duration * 0.3,
                    end: duration * 0.4
                })
            }
        }
    })

    if(props.showRegion){
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

function onPlayClicked() {
    ws.wsInstance.play()
}

function onStopClicked() {
    ws.wsInstance.pause()
}

watch(() => props.file, (value) => {
    const blob = new Blob([value])
    ws.wsInstance.load(URL.createObjectURL(blob))
})

watch(() => props.secondRegion, (value) => {
    if(value){
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
    } else {
        regions.getRegions()[1].remove()
    }
})

function changeHandleStyles(region){
    for(const child of region.element.children){
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

</script>

<template>
    <div :id="id"></div>
    <div v-if="showControls">
        <button type="button" @click="onPlayClicked"
                class="bg-blue-400 text-white rounded py-2 px-4 mt-5 mr-3 hover:bg-blue-500">Play
        </button>
        <button type="button" @click="onStopClicked"
                class="bg-blue-400 text-white rounded py-2 px-4 mt-5 mr-3 hover:bg-blue-500">Stop
        </button>

        <input type="text" v-model="ws.currentTime" class="border-none">
        <input type="text" v-model="ws.durationTime" class="border-none">
    </div>


</template>

<style scoped>

</style>
