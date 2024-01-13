<script setup>
import {computed, inject, onMounted, reactive, watch} from "vue";
import {Chart} from "chart.js/auto";
import {usePage} from "@inertiajs/vue3";
import {useI18n} from "vue-i18n";

const page = usePage()
const v18n = useI18n()

let chart = reactive({})
const highContrast = inject('highContrast')

const title = computed(() => {
    return v18n.t('dashboard.numberOfFiles')
})

function initChart() {
    const ctx = document.getElementById('numberOfFiles');

    const percentage = (page.props.auth.user.files_stored / 50) * 100
    const remainingPercentage = 50 - percentage;

    const data = {
        labels: ['Filled', 'Remaining'],
        datasets: [{
            data: [percentage, remainingPercentage],
            backgroundColor: [highContrast.value ? 'yellow' : '#FECEAB', '#DDDDDD'],
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
            ctx.fillStyle = highContrast.value ? 'yellow' : '#FECEAB'
            ctx.textAlign = 'center'
            ctx.textBaseline = 'middle'
            ctx.fillText(`${page.props.auth.user.files_stored} / 50`, xCor, yCor)
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
                color: highContrast.value ? 'yellow' : 'white',
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

watch(v18n.locale, () => {
    chart.options.plugins.title.text = title.value
    chart.update()
})

watch(highContrast, (newValue) => {
    chart.data.datasets[0].backgroundColor = newValue ? ['yellow', 'white'] : ['#FECEAB', '#DDDDDD']
    chart.options.plugins.title.color = newValue ? 'yellow' : 'white'
    chart.update()
})
</script>

<template>
    <div class="">
        <canvas id="numberOfFiles" class=""></canvas>
    </div>
</template>

<style scoped>

</style>
