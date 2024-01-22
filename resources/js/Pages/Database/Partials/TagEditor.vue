<script setup>
import { usePage } from "@inertiajs/vue3";
import { inject, ref } from "vue";
import InputFieldWithLabel from "@/Pages/Tools/Partials/InputFieldWithLabel.vue";
import { useI18n } from "vue-i18n";

const page = usePage()
const v18n = useI18n()
const emits = defineEmits(['discardEdit', 'saveEdit']);
const isError = ref(false)
const error = ref("")
const highContrast = inject('highContrast')

const props = defineProps({
    song: Object,
})

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
    extension: ''
})

function saveEdit() {
    const nonEmptyFormData = Object.fromEntries(
        Object.entries(form.value).filter(([key, value]) => value !== "")
    );

    if (nonEmptyFormData['id'] !== undefined) {
        nonEmptyFormData['id'] = getValueByKey('id');
    }

    const changedData = Object.entries(nonEmptyFormData).reduce((changes, [key, value]) => {
        const originalValue = getValueByKey(key);
        if (value !== originalValue) {
            changes[key] = value;
        }
        return changes;
    }, {});

    changedData['id'] = getValueByKey('id'); 

    if (Object.keys(changedData).length > 1) {
        emits('saveEdit', changedData);
    }
}


function getValueByKey(key) {
    return props.song.find(pair => pair[0] === key)?.[1] || '';
}

</script>
<template>
    <div class="w-[90%] max-w-3xl w mx-auto text-white flex flex-col">
        <div :class="{ 'high-contrast-label': highContrast }"
            class=" grid grid-cols-1 lg:grid-cols-3 gap-4  sm:mx-10 lg:mx-0 p-6 bg-gray-800 rounded-lg shadow-lg" id="form">

            <InputFieldWithLabel class="black-placeholder" :label="$t('tagEditor.titleL')"
                @update:model-value="form.title = $event" :placeholder="getValueByKey('title')" />
            <InputFieldWithLabel :label="$t('tagEditor.artist')" @update:model-value="form.artist = $event"
                :placeholder="getValueByKey('artist')" />
            <InputFieldWithLabel :label="$t('tagEditor.genre')" @update:model-value="form.genre = $event"
                :placeholder="getValueByKey('genre')" />
            <InputFieldWithLabel :label="$t('tagEditor.year')" type="date" @update:model-value="form.year = $event"
                :placeholder="getValueByKey('year')" />
            <InputFieldWithLabel :label="$t('tagEditor.album')" @update:model-value="form.album = $event"
                :placeholder="getValueByKey('album')" />
            <InputFieldWithLabel :label="$t('tagEditor.composer')" @update:model-value="form.composer = $event"
                :placeholder="getValueByKey('composer')" />
            <InputFieldWithLabel :label="$t('tagEditor.comment')" @update:model-value="form.comment = $event"
                :placeholder="getValueByKey('comment')" />
            <InputFieldWithLabel :label="$t('tagEditor.copyrightMessage')"
                @update:model-value="form.copyrightMessage = $event" :value="getValueByKey('copyright_message')" />
            <InputFieldWithLabel :label="$t('tagEditor.publisher')" @update:model-value="form.publisher = $event"
                :placeholder="getValueByKey('publisher')" />
            <InputFieldWithLabel :label="$t('tagEditor.trackNumber')" type="number"
                @update:model-value="form.trackNumber = $event" :value="getValueByKey('track_number')" />
            <InputFieldWithLabel :label="$t('tagEditor.lyrics')" @update:model-value="form.lyrics = $event"
                :placeholder="'asd'" />


            <div class="mb-6 lg:col-span-3">

            </div>

            <div>

                <button type="button" @click="emits('discardEdit'); isError = false"
                    :class="{ 'high-contrast-button': highContrast }"
                    class="bg-blue-400 text-white rounded py-2 px-4 mt-5 mr-3 hover:bg-blue-500"> Discard Changes</button>
                <button type="button" @click="saveEdit()" :class="{ 'high-contrast-button': highContrast }"
                    class="bg-blue-400 text-white rounded py-2 px-4 mt-5 mr-3 hover:bg-blue-500">Save Changes</button>
            </div>
        </div>

        <div v-if="isError">
            <p>{{ error }}</p>
        </div>

    </div>
</template>


<style>
input::placeholder,
textarea::placeholder {
    color: #3d3d3d !important;
}

.high-contrast-label {
    background-color: black;
    border: 1px solid yellow;
    color: yellow;
    font-size: 1rem;
    /* 16px */
    line-height: 1.5rem;
    /* 24px */
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
