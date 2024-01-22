<script setup>
import { Head } from '@inertiajs/vue3';
import {  inject, onMounted, ref } from "vue";
import ToolsUseChart from "@/Pages/Profile/Partials/ToolsUseChart.vue";
import "ag-grid-community/styles//ag-grid.css";
import "ag-grid-community/styles//ag-theme-quartz.css";
import { AgGridVue } from "ag-grid-vue3";
import {useI18n} from "vue-i18n";

const gridOptions = {
  paginationAutoPageSize: true,
  pagination: true,
};
const translate = useI18n()
const deleteUser = async (userId) => {
    try {
        await axios.delete(`/users/${userId}`);
        rdUsers.value = await fetchDataUsers();
        reloadData();
    } catch (error) {
        console.error(error);
    }
};

const cleanUser = async (userId) => {
    try {
        await axios.patch(`/users/${userId}`);
        rdUsers.value = await fetchDataUsers();
        reloadData();
    } catch (error) {
        console.error(error);
    }
};

const fetchDataUsers = async () => {
    try {
        const response = await axios.get('/users')
        return response.data.users;
    } catch (e) {
        console.log(e)
    }
};
const fetchDataTools = async () => {
    try {
        const response = await axios.get('/jobStatistics')
        return response.data.data;
    } catch (e) {
        console.log(e)
    }
};
const fetchDataToolsNew = async () => {
    try {
        const response = await axios.get('/jobsIndex')
        console.log(response.data.data)
        return response.data.data;
    } catch (e) {
        console.log(e)
    }
};
const delRender = (params) => {
    const button = document.createElement('button');
    button.innerHTML = translate.t('adminPanel.tables.deleteButton');
    button.addEventListener('click', () => params.onDeleteClick(params.data.id));
    return button;
};

const cleanRender = (params) => {
    const button = document.createElement('button');
    button.innerHTML = translate.t('adminPanel.tables.removeButton');
    button.addEventListener('click', () => params.onDeleteClick(params.data.id));
    return button;
};

const cdUsers = ref([
    { headerName: translate.t('adminPanel.tables.id'), field: 'id', width: 45 },
    { headerName: translate.t('adminPanel.tables.nickname'), field: 'nickname', width: 125 },
    { headerName: translate.t('adminPanel.tables.email'), field: 'email', width: 200 },
    {
        headerName:  translate.t('adminPanel.tables.toolsUsage'), field: 'tool_usage', width: 115, cellRenderer: (params) => {
            return params.value || 0;
        },
    },
    {
        headerName: translate.t('adminPanel.tables.storageUsed'), field: 'storage_used', width: 120, cellRenderer: (params) => {
            const storageInKB = params.value || 0;
            const storageInMB = (storageInKB / 1000).toFixed(2);
            return `${storageInMB} MB`;
        },
    },
    {
        headerName:  translate.t('adminPanel.tables.filesStored'), field: 'files_stored', width: 110, cellRenderer: (params) => {
            return params.value || 0;
        },
    },
    { headerName:  translate.t('adminPanel.tables.registrate'), field: 'created_at', width: 125 , cellRenderer: (params) => {
            const isoDate = params.value || '';
            const date = new Date(isoDate);
            const formattedDate = `${date.getFullYear()}-${(date.getMonth() + 1).toString().padStart(2, '0')}-${date.getDate().toString().padStart(2, '0')} ${date.getHours().toString().padStart(2, '0')}:${date.getMinutes().toString().padStart(2, '0')}:${date.getSeconds().toString().padStart(2, '0')}`;
            return formattedDate;
        }},
    { headerName: translate.t('adminPanel.tables.delete'), cellRenderer: 'deleteButtonRenderer', cellRendererParams: { onDeleteClick: deleteUser }, width: 110 },
    { headerName:translate.t('adminPanel.tables.remove'), cellRenderer: 'cleanButtonRenderer', cellRendererParams: { onDeleteClick: cleanUser }, width: 115 },
]);
const cdTools = ref([
    { headerName: translate.t('adminPanel.tables.toolsName'), field: 'tool_name', width: 125 },
    { headerName: translate.t('adminPanel.tables.avgProcess'), field: 'count', width: 275 },
    {headerName: translate.t('adminPanel.tables.avgProcess'), field: 'avg_time', width: 275, cellRenderer: (params) => {
            const time = params.value || 0;
            const timeSec = (time / 1000).toFixed(2);
            return `${timeSec} Sec`;
        }
    },
    {
        headerName: translate.t('adminPanel.tables.guestProc'), field: 'guest_percentage', width: 225, cellRenderer: (params) => {
            const procFormated = (params.value / 1).toFixed(2);
            return procFormated + "%";
        }
    }
]);

const cdToolsNew = ref([
    { headerName: translate.t('adminPanel.tables.id'), field: 'id', width: 55 },
    { headerName: translate.t('adminPanel.tables.toolsName'), field: 'tool_name', width: 125 },
    {
        headerName: translate.t('adminPanel.tables.process'), field: 'time', width: 225, cellRenderer: (params) => {
            const time = params.value || 0;
            const timeSec = (time / 1000).toFixed(2);
            return `${timeSec} Sec`;
        }
    },
    { headerName: translate.t('adminPanel.tables.userId'), field: 'user_id', width: 125 , cellRenderer: (params) => {
            return params.value || 'Guest';
        }},
    { headerName: translate.t('adminPanel.tables.done'), field: 'updated_at', width: 175 , cellRenderer: (params) => {
            const isoDate = params.value || '';
            const date = new Date(isoDate);
            const formattedDate = `${date.getFullYear()}-${(date.getMonth() + 1).toString().padStart(2, '0')}-${date.getDate().toString().padStart(2, '0')} ${date.getHours().toString().padStart(2, '0')}:${date.getMinutes().toString().padStart(2, '0')}:${date.getSeconds().toString().padStart(2, '0')}`;
            return formattedDate;
        }}
]);

const rdUsers = ref([]);
const rdTools = ref([]);
const rdToolsNew = ref([]);
const totalDataUsage = ref(0.0);
const totalFilesCount = ref(0);
const highContrast = inject('highContrast')

const comUsers = {
    deleteButtonRenderer: delRender,
    cleanButtonRenderer: cleanRender,
};
function reloadData() {
    const calculatedDataUsage = rdUsers.value.reduce((total, user) => total + user.storage_used, 0);
    const calculatedFilesCount = rdUsers.value.reduce((total, user) => total + user.files_stored, 0);
    totalDataUsage.value = (calculatedDataUsage / 1000).toFixed(2);
    totalFilesCount.value = calculatedFilesCount;
}
onMounted(async () => {
    rdUsers.value = await fetchDataUsers();
    rdTools.value = await fetchDataTools();
    rdToolsNew.value = await fetchDataToolsNew();
    reloadData();
});
</script>
<template>
    <Head title="Admin Panel" />

    <div :class="{ 'high-contrast-bg': highContrast }" class="py-10">

        <!-- PAGE HEADER -->
        <div :class="{ 'high-contrast-input': highContrast }"
            class="mx-auto  w-[23rem] text-center  py-5 bg-gray-800 rounded-lg items-center px-10 shadow-xl">
            <div class="text-white text-center lg:text-left">
                <h1 class="text-4xl font-bold text-center" :class="{ 'high-contrast-text': highContrast }">{{$t('adminPanel.adminPanel')}}:</h1>
            </div>
        </div>

        <!-- Users -->
        <div :class="{ 'high-contrast-input': highContrast }"
            class="mx-auto max-w-5xl w-[90%] pt-5 pb-8  mt-8 mb-10 lg:mt-8 bg-gray-800 rounded-lg items-center justify-around px-10 shadow-xl">
            <!-- header -->
            <div class="text-white text-center lg:text-left mb-5 w-[100%]">
                <h1 class="text-2xl font-bold" :class="{ 'high-contrast-text': highContrast }">
                    {{$t('adminPanel.userControl')}}:</h1>
            </div>

            <!-- users controls -->
            <div class="flex flex-col lg:flex-row mb-2">
                <div style="width: 100%; flex: 1 1 auto;">
                    <ag-grid-vue style="width: 100%; height: 30rem" :class="{ 'ag-theme-high-contrast': highContrast }"
                        class="ag-theme-quartz-dark" :gridOptions="gridOptions" :columnDefs="cdUsers" :rowData="rdUsers" :components="comUsers">
                    </ag-grid-vue>
                </div>
            </div>
            <!-- server usage totals -->
            <div class="text-white text-center lg:text-left mb-2 w-[100%] mt-5">
                <h1 class="text-xl font-bold" :class="{ 'high-contrast-text': highContrast }">
                    {{$t('adminPanel.serverRes')}}:</h1>
            </div>

            <div :class="{ 'high-contrast-text': highContrast }" class="text-white">
                <div v-if="totalDataUsage > 0">
                    <p>{{ $t('adminPanel.totalMemory') }}: {{ totalDataUsage }} MB</p>
                </div>
                <div v-if="totalFilesCount > 0">
                    <p>{{ $t('adminPanel.totalFiles') }}: {{ totalFilesCount }}</p>
                </div>
            </div>
        </div>

        <!-- Tools -->
        <div :class="{ 'high-contrast-input': highContrast }"
            class="mx-auto max-w-5xl w-[90%] py-5  mt-10 mb-10 lg:mt-8 bg-gray-800 rounded-lg items-center justify-around px-10 shadow-xl">
            <!-- header -->
            <div class="text-white text-center lg:text-left mb-5 w-[100%]">
                <h1 class="text-2xl font-bold" :class="{ 'high-contrast-text': highContrast }">
                    {{$t('adminPanel.toolsStats')}}:</h1>
            </div>
            <!-- Tools stats -->
            <div class="flex flex-col  mb-2 ">
                <div class="justify-center flex" style="width: 100%; flex: 1 1 auto;">
                    <ag-grid-vue style="width: 90%; height: 18rem" :class="{ 'ag-theme-high-contrast': highContrast }"
                        class="ag-theme-quartz-dark" :columnDefs="cdTools" :rowData="rdTools">
                    </ag-grid-vue>
                </div>
                <!-- Most Used Diagram -->
                <div class="text-white text-center lg:text-left mb-5  w-[100%] mt-8">
                    <h1 class="text-xl font-bold" :class="{ 'high-contrast-text': highContrast }">
                        {{$t('adminPanel.toolsChart')}}:</h1>
                </div>
                <div :class="{ 'high-contrast-input': highContrast }"
                    class="mx-auto max-w-3xl w-[90%] mb-10 flex  bg-gray-800 rounded-lg items-center px-10 justify-center shadow-xl border">
                    <div class="py-10 w-full my-10 ">
                        <ToolsUseChart :routePath="'/jobStatistics'" :title="' '" />
                    </div>
                </div>

                <!-- New Tools -->
                <div class="text-white text-center lg:text-left mb-5 w-[100%] mt-8">
                    <h1 class="text-xl font-bold" :class="{ 'high-contrast-text': highContrast }">
                        {{ $t("adminPanel.toolsReq") }}:</h1>
                </div>
                <div class="justify-center flex mb-5" style="width: 100%; flex: 1 1 auto;">
                    <ag-grid-vue style="width: 75%; height: 30rem"  :class="{ 'ag-theme-high-contrast': highContrast }"
                        class="ag-theme-quartz-dark"  :gridOptions="gridOptions" :columnDefs="cdToolsNew" :rowData="rdToolsNew">
                    </ag-grid-vue>
                </div>
            </div>


        </div>
    </div>
</template>

<style scoped>
.high-contrast-bg {
    @apply bg-black
}

.high-contrast-text {
    @apply text-[#FFFF00FF]
}

.high-contrast-border {
    @apply border-[#FFFF00FF]
}

.high-contrast-input {
    @apply text-xl border border-[#FFFF00FF] bg-black text-[#FFFF00FF]
}

.high-contrast-button {
    @apply text-xl border border-[#FFFF00FF] bg-black text-[#FFFF00FF] focus:border-[#FFFF00FF] focus:ring-[#FFFF00FF] hover:bg-yellow-300 hover:text-black
}

.high-contrast-button-selected {
    @apply bg-yellow-300 text-black
}

.ag-theme-high-contrast {
    --ag-background-color: black !important;
    --ag-foreground-color: #FFFF00FF !important;
    --ag-border-color: #FFFF00FF !important;
    --ag-alpine-active-color: #FFFF00FF !important;
    --ag-secondary-foreground-color: #FFFF00FF !important;
    --ag-header-background-color: black !important;
    --ag-range-selection-border-color: #FFFF00FF !important;
    --ag-range-selection-background-color: #FFFF00FF !important;
}</style>