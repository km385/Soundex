<script setup>
import {subToPrivate, subToChannel, disconnectFromPrivate, disconnectFromPublic} from "@/Subscriptions/subs.js";
import {v4 as uuidv4} from "uuid";
import {usePage} from "@inertiajs/vue3";
import {inject, onMounted, onUnmounted, ref} from "vue";
import axios from "axios";
import SidebarLayout from "@/Layouts/SidebarLayout.vue";
import InputFieldWithLabel from "./Partials/InputFieldWithLabel.vue";
import LoadingScreen from "./Partials/LoadingScreen.vue";
import SelectExtension from "@/Pages/Tools/Partials/SelectExtension.vue";
import FileInfo from "@/Pages/Tools/Partials/FileInfo.vue";
import ResultOptionsScreen from "@/Pages/Tools/Partials/ResultOptionsScreen.vue";
import ToolsUploadScreen from "@/Pages/Tools/Partials/ToolsUploadScreen.vue";
import MainToolsWindow from "@/Pages/Tools/Partials/MainToolsWindow.vue";
import InputError from "@/Components/InputError.vue";
import {useI18n} from "vue-i18n";

defineOptions({
    layout: SidebarLayout
})

const page = usePage()
const v18n = useI18n()
const guestId = page.props.auth.user ? `${page.props.auth.user.id}-${uuidv4()}` : uuidv4()
const isLoading = ref(false);

const fileUploaded = ref({})
const coverUploaded = ref({})
const coverUrl = ref("")
const isFileUploaded = ref(false)

const isCoverUploaded = ref(false)

const downloadLink = ref("")

const isError = ref(false)
const error = ref("")

const highContrast = inject('highContrast')
const isMounted = ref(true)
onMounted(() => {
    isMounted.value = true
    console.log(guestId)
    if(page.props.auth.user){
        subToPrivate(guestId, handleSubToPrivate)
    } else {
        subToChannel(guestId, handleSubToPublic)
    }
})

onUnmounted(() => {
    console.log('unmounted')
    isMounted.value = false
    if(page.props.auth.user) {
        disconnectFromPrivate(guestId)
    } else {
        disconnectFromPublic(guestId)
    }

})
function handleSubToPublic(event) {
    if(!isMounted.value) return

    console.log("the event has been successfully captured")
    console.log(event)

    if(event.fileName === "ERROR") {
        error.value = v18n.t('error')
        isError.value = true
    } else {
        downloadLink.value = event.fileName
    }
    isLoading.value = false
}

function handleSubToPrivate(event) {
    if(!isMounted.value) return

    console.log("the event has been successfully captured")
    console.log(event)

    if(event.fileName === "ERROR") {
        error.value = v18n.t('error')
        isError.value = true
    } else {
        downloadLink.value = event.fileName
    }
    isLoading.value = false
}

const form = ref({
    artist: '',
    title: '',
    genre: '',
    year: '',
    album: '',
    composer: '',
    comment: '',
    copyrightMessage: '',
    publisher: '',
    trackNumber: '',
    lyrics: '',
    coverRef: File,
    fileRef: File,
    extension: ''
})

async function onSubmit() {
    if(!validateTrackNumber()) return;

    const isAllFieldsEmpty = Object.values(form.value).slice(0, -3).every(value => value === '');
    if(isAllFieldsEmpty && isCoverUploaded.value === false) {
        error.value = v18n.t('tagEditor.error1')
        isError.value = true
        console.log(form.value.coverRef)
        return
    }

    const formData = new FormData()
    Object.keys(form.value).forEach(key => {
        const value = form.value[key]
        formData.append(key, value)
        console.log(key, value)
    })

    formData.append('guestId', guestId)

    try {
        isLoading.value = true
        const res = await axios.post('/tools/tageditor', formData)
        console.log(res.data.message)
    } catch (err) {
        if(err.response.data.error){
            console.log('no file')
        } else {
            console.log(err)
        }
    }

}

function getFile(file) {
    console.log('file received')
    fileUploaded.value = file
    form.value.fileRef = file
    isFileUploaded.value = true
}

function onCoverUpload(event) {

    if(!event.target.files[0].type.startsWith("image/")) {
        error.value = v18n.t('tagEditor.error2')
        isError.value = true
        document.getElementById('cover').value = null
        return
    }

    if(event.target.files[0].type.includes('svg')) {
        error.value = v18n.t('tagEditor.error2')
        isError.value = true
        document.getElementById('cover').value = null
        return
    }
    isCoverUploaded.value = true
    coverUploaded.value = event.target.files[0]
    form.coverRef = event.target.files[0]
    coverUrl.value = URL.createObjectURL(event.target.files[0])
    form.value.coverRef = event.target.files[0]
}

function updateTitle(e) {
    form.value.title = e
}
function getExtension(ext) {
    form.value.extension = ext
}

const coverInput = ref()

const isSmallInt = (value) => {
    if(value === null) return true

    const intValue = parseInt(value)
    return Number.isInteger(intValue) && intValue >= 0 && intValue <= 65535
};

const validateTrackNumber = () => {
    if (form.value.trackNumber !== '' && !isSmallInt(form.value.trackNumber)) {
        error.value = v18n.t('tagEditor.error3')
        isError.value = true
        return false
    }
    return true
};



</script>
<template>
    <loading-screen v-if="isLoading" />
    <MainToolsWindow v-if="!isLoading">
        <ToolsUploadScreen v-if="!isFileUploaded" :title="$t('tagEditor.title')" :description="$t('tagEditor.description')"
                           @file="getFile"/>

        <div v-if="isFileUploaded && !downloadLink"
             :class="{'high-contrast-label': highContrast}"
             class="mt-20 grid grid-cols-1 lg:grid-cols-3 gap-4  sm:mx-10 lg:mx-0 p-6 bg-gray-800 rounded-lg shadow-lg" id="form">
        <FileInfo :file-size="fileUploaded.size" :file-name="fileUploaded.name"
                  class="lg:col-span-3" />

            <InputFieldWithLabel :label="$t('tagEditor.titleL')" @update:model-value="form.title = $event"/>
            <InputFieldWithLabel :label="$t('tagEditor.artist')" @update:model-value="form.artist = $event"/>
            <InputFieldWithLabel :label="$t('tagEditor.genre')"  @update:model-value="form.genre = $event"/>
            <InputFieldWithLabel :label="$t('tagEditor.year')" type="date" @update:model-value="form.year = $event"/>
            <InputFieldWithLabel :label="$t('tagEditor.album')" @update:model-value="form.album = $event"/>
            <InputFieldWithLabel :label="$t('tagEditor.composer')" @update:model-value="form.composer = $event"/>
            <InputFieldWithLabel :label="$t('tagEditor.comment')" @update:model-value="form.comment = $event"/>
            <InputFieldWithLabel :label="$t('tagEditor.copyrightMessage')" @update:model-value="form.copyrightMessage = $event"/>
            <InputFieldWithLabel :label="$t('tagEditor.publisher')" @update:model-value="form.publisher = $event"/>
            <InputFieldWithLabel :label="$t('tagEditor.trackNumber')" type="number" @update:model-value="form.trackNumber = $event"/>
            <InputFieldWithLabel :label="$t('tagEditor.lyrics')" @update:model-value="form.lyrics = $event"/>

            <div class="mb-6 lg:col-span-3">
                <label for="cover"
                       :class="{ 'high-contrast-button': highContrast }"
                       class="cursor-pointer border-2 border-gray-400 inline-block p-2 mb-2 uppercase font-bold text-xs text-white rounded-lg">
                    {{ $t("tagEditor.cover") }}
                </label>
                <input id="cover"
                       class="hidden border border-gray-400 p-2 w-full text-white border-none rounded-lg bg-gray-500"
                       name="cover"
                       type="file"
                       @change="onCoverUpload"
                >

                <div v-if="isCoverUploaded" class="w-[200px] h-[200px] ml-[10px] mt-2">
                    <img :src="coverUrl" alt="Cover Image" class="w-full h-full object-cover"/>
                </div>
                <div class="mt-2">
                    <select-extension @extension="getExtension"/>
                </div>
            </div>

            <div v-if="isFileUploaded && !downloadLink" class="">
                <button type="button"  @click="onSubmit"
                        :class="{'high-contrast-button': highContrast }"
                        class="bg-blue-400 text-white rounded py-2 px-4 mt-5 mr-3 hover:bg-blue-500">{{ $t("tools.submit") }}</button>
                <button type="button"  @click="isFileUploaded = false;isError = false"
                        :class="{'high-contrast-button': highContrast }"
                        class="bg-blue-400 text-white rounded py-2 px-4 mt-5 mr-3 hover:bg-blue-500">{{ $t("tools.changeFile") }}</button>
            </div>
        </div>

        <ResultOptionsScreen v-if="downloadLink" @go-back="downloadLink = ''"
                             :file-to-download-link="downloadLink" :file-to-download-name="fileUploaded.name"/>

        <div v-if="isError">
            <p>{{ error }}</p>
        </div>

    </MainToolsWindow>
</template>


<style scoped>

.high-contrast-label {
    background-color: black;
    border: 1px solid yellow;
    color: yellow;
    font-size: 1rem; /* 16px */
    line-height: 1.5rem; /* 24px */
}

.high-contrast-input {
    @apply text-xl border border-[#FFFF00FF] bg-black text-[#FFFF00FF]
}

.high-contrast-button {
    @apply text-xl border border-[#FFFF00FF] bg-black text-[#FFFF00FF] focus:border-[#FFFF00FF] focus:ring-[#FFFF00FF] hover:bg-yellow-300 hover:text-black
}

.high-contrast-input:focus {
    --tw-ring-color: yellow;
    border: 2px solid yellow;

}

.high-contrast-input:hover {
    //--tw-ring-color: yellow;
    border: 1px solid yellow;
    background-color: black;
}

</style>
