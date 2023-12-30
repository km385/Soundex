<script setup>
    import {onMounted} from "vue";
    import {Chart} from "chart.js/auto";

    onMounted(() => {
        const ctx = document.getElementById('myChart');

        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Zajęte', 'Wolne'],
                datasets: [{
                    label: '# of space',
                    data: [65, 45],
                    borderWidth: 1
                }]
            },
            options: {

            }
        });
    })
</script>

<template>
    <div class="">
        <canvas id="myChart" class=""></canvas>
    </div>
</template>

<style scoped>

</style>
