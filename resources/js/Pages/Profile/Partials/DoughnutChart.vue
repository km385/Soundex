<script setup>
import {computed, inject, onMounted, reactive, ref, watch} from "vue";
import {Chart} from "chart.js/auto";
import {usePage} from "@inertiajs/vue3";
import {useI18n} from "vue-i18n";
const v18n = useI18n()
const page = usePage()

let chart = reactive({})
const highContrast = inject('highContrast')

const title = computed(() => {
    return v18n.t('dashboard.storageSpace')
})

function initChart() {
    const ctx = document.getElementById('freeStorage');

    const percentage = ((page.props.auth.user.storage_used / 1024) / 200).toFixed(2) * 100;
    const remainingPercentage = 100 - percentage;

    const data = {
        labels: ['Filled', 'Remaining'],
        datasets: [{
            data: [percentage, remainingPercentage],
            backgroundColor: [highContrast.value ? 'yellow' : '#36A2EB', '#DDDDDD'],
            borderWidth: 0,
            hoverOffset: 4,
        }]
    };

    const doughnutLabel = {
        id: 'doughnutLabel',
        beforeDatasetsDraw(chart, args, options) {
            const {ctx, data} = chart

            ctx.save()

            const xCor = chart.getDatasetMeta(0).data[0].x
            const yCor = chart.getDatasetMeta(0).data[0].y

            ctx.font = 'bold 30px sans-serif'
            ctx.fillStyle = highContrast.value ? 'yellow' : 'rgba(54, 162, 235, 1)'
            ctx.textAlign = 'center'
            ctx.textBaseline = 'middle'
            ctx.fillText(`${percentage}%`, xCor, yCor)
        }
    }

    const options = {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '80%',
        plugins: {
            legend: {
                display: false,
            },
            tooltip: {
                enabled: false,
            },
            title: {
                display: true,
                text: title.value,
                color: highContrast.value ? 'yellow' : Chart.defaults.color,
                font: {
                    size: 20
                }
            }
        },
    };

    chart = new Chart(ctx, {
        type: 'doughnut',
        data: data,
        options: options,
        plugins: [doughnutLabel]
    });
}

onMounted(() => {
    initChart();
})

watch(highContrast, (newValue) => {
    chart.data.datasets[0].backgroundColor = newValue ? ['yellow', 'white'] : ['#36A2EB', '#DDDDDD']
    chart.options.plugins.title.color = newValue ? 'yellow' : Chart.defaults.color
    chart.update()
})

watch(v18n.locale, () => {
    chart.options.plugins.title.text = title.value
    chart.update()
})
</script>

<template>
    <div class="">
        <canvas id="freeStorage" class=""></canvas>
    </div>
</template>

<style scoped>

</style>
