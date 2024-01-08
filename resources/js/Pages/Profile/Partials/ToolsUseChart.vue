<script setup>

import {inject, onMounted, reactive, ref, watch} from "vue";
import {Chart} from "chart.js/auto";

const highContrast = inject('highContrast')
let chart = reactive({})


async function getDataForChart() {
    // todo map job names to tool names
    try {
        const res = await axios.get('/jobs')
        if(res.data && res.data.data) {
            const val = res.data.data
            if(val && val.length > 0) {
                const labelsArr = val.map(item => item ? item.tool_name : null);
                const dataArr = val.map(item => item ? item.count : null);
                return {labelsArr, dataArr}
            } else {
                console.error("empty array")
                return []
            }
        } else {
            console.error('unexpected response structure')
            return []
        }

    } catch (error) {
        console.error('an error occurred')
        return []
    }

}

function initChart(dataset) {
    const {labelsArr, dataArr} = dataset

    const ctx = document.getElementById('toolsUseChart');

    const data = {
        labels: labelsArr,
        datasets: [{
            data: dataArr,
            backgroundColor: highContrast.value ? ['yellow'] : ['#36A2EB', '#DDDDDD' , 'red' ,'yellow', 'green', 'blue'],
            borderWidth: 0,
            hoverOffset: 4,
        }]
    };


    const options = {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '80%',
        plugins: {
            legend: {
                display: false,
            },
            tooltip: {
                enabled: true,
            },
            title: {
                display: true,
                text: 'Tools You use the most',
                color: highContrast.value ? 'yellow' : Chart.defaults.color,
                font: {
                    size: 20
                }
            }
        },
        scales: {
            x: {
                ticks: {
                    color: highContrast.value ? 'yellow' : Chart.defaults.color, // Change the color of x-axis text
                },
            },
            y: {
                ticks: {
                    color: highContrast.value ? 'yellow' : Chart.defaults.color, // Change the color of y-axis text
                },
            },
        },
    };

    chart = new Chart(ctx, {
        type: 'bar',
        data: data,
        options: options,

    });
}

watch(highContrast, (newValue) => {

    chart.data.datasets[0].backgroundColor = newValue ? ['yellow'] : ['#36A2EB', '#DDDDDD' , 'red' ,'yellow', 'green', 'blue']
    chart.options.scales.x.ticks.color = newValue ? 'yellow' : Chart.defaults.color
    chart.options.scales.y.ticks.color = newValue ? 'yellow' : Chart.defaults.color
    chart.options.plugins.title.color = newValue ? 'yellow' : Chart.defaults.color
    chart.update()

})

const isDataPresent = ref(true)

onMounted(async () => {
    const dataset = await getDataForChart()
    if(dataset.length !== 0) {
        initChart(dataset)
    } else {
        isDataPresent.value = false
    }
})
</script>

<template>
    <div class="flex justify-center items-center">
        <canvas id="toolsUseChart" v-if="isDataPresent"></canvas>
        <p v-else class="text-3xl">tools  never used</p>
    </div>
</template>

<style scoped>

</style>
