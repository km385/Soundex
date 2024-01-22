<script setup>

import {computed, inject, onMounted, reactive, ref, watch} from "vue";
import {Chart} from "chart.js/auto";
import {useI18n} from "vue-i18n";



const v18n = useI18n()
const highContrast = inject('highContrast')
let chart = reactive({})

const props = defineProps({
  title: {
    type: String,
    default: () => ''
  },

  routePath: {
    type: String,
    default: '/jobs'
  }
});


const title = computed(() => {
  return props.title === '' ? v18n.t('dashboard.toolsUsed') : props.title;
});


const labelRef = ref(null)

async function getDataForChart() {
    try {
        const res = await axios.get(props.routePath)
        if(res.data && res.data.data) {
            const val = res.data.data
            if(val && val.length > 0) {
                let labelsArr = val.map(item => item ? item.tool_name : null);
                labelRef.value = labelsArr
                labelsArr = labelsArr.map(label => v18n.t(`dashboard.tools.${label}`))
                const dataArr = val.map(item => item ? item.count : null);
                return {labelsArr, dataArr}
            } else {

                return []
            }
        } else {

            return []
        }

    } catch (error) {

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
            backgroundColor: highContrast.value ? ['yellow'] : ['#FECEAB', '#DDDDDD' , 'red' ,'yellow', 'green', 'blue'],
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
                text: title.value,
                color: highContrast.value ? 'yellow' : 'white',
                font: {
                    size: 20
                }
            }
        },
        scales: {
            x: {
                ticks: {
                    color: highContrast.value ? 'yellow' : 'white',
                },
            },
            y: {
                ticks: {
                    color: highContrast.value ? 'yellow' : 'white',
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

watch(v18n.locale,() => {
    if(!isDataPresent.value) return
    chart.options.plugins.title.text = title.value
    chart.data.labels = labelRef.value.map(label => v18n.t(`dashboard.tools.${label}`))
    chart.update()
})

watch(highContrast, (newValue) => {
    if(!isDataPresent.value) return

    chart.data.datasets[0].backgroundColor = newValue ? ['yellow'] : ['#FECEAB', '#DDDDDD' , 'red' ,'yellow', 'green', 'blue']
    chart.options.scales.x.ticks.color = newValue ? 'yellow' : 'white'
    chart.options.scales.y.ticks.color = newValue ? 'yellow' : 'white'
    chart.options.plugins.title.color = newValue ? 'yellow' : 'white'
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
        <p v-else :class="{'text-yellow-300':highContrast}" class="text-white text-3xl">tools  never used</p>
    </div>
</template>

<style scoped>

</style>
