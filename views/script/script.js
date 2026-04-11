
// ===================================================
//  GRÁFICO DE INGRESOS Y EGRESOS (ECharts)
// ===================================================
(function () {
    const graphyc = document.getElementById('graphyc');

    if (graphyc && typeof echarts !== 'undefined') {
        const myChart = echarts.init(graphyc);

        const option = {
            title: { text: 'INGRESOS EGRESOS' },
            tooltip: {},
            legend: { data: ['egresos', 'ingresos'] },
            xAxis: {
                data: ['LUNES', 'MARTES', 'MIÉRCOLES', 'JUEVES', 'VIERNES', 'SÁBADO', 'DOMINGO']
            },
            yAxis: {},
            series: [
                {
                    name: 'egresos',
                    type: 'bar',
                    data: [5, 25, 36, 10, 10, 34, 1]
                },
                {
                    name: 'ingresos',
                    type: 'bar',
                    data: [1, 20, 56, 10, 13, 20, 1]
                }
            ]
        };

        myChart.setOption(option);
    }
})();


// ===================================================
// GRÁFICO DE INGRESOS Y EGRESOS (ECharts)
// ===================================================
(function () {
    const graphyc = document.getElementById('graphyc');

    if (graphyc && typeof echarts !== 'undefined') {
        const myChart = echarts.init(graphyc);

        const option = {
            title: { text: 'INGRESOS EGRESOS' },
            tooltip: {},
            legend: { data: ['egresos', 'ingresos'] },
            xAxis: {
                data: ['LUNES', 'MARTES', 'MIÉRCOLES', 'JUEVES', 'VIERNES', 'SÁBADO', 'DOMINGO']
            },
            yAxis: {},
            series: [
                {
                    name: 'egresos',
                    type: 'bar',
                    data: [5, 25, 36, 10, 10, 34, 1]
                },
                {
                    name: 'ingresos',
                    type: 'bar',
                    data: [1, 20, 56, 10, 13, 20, 1]
                }
            ]
        };

        myChart.setOption(option);
    }
})();
