

document.addEventListener('DOMContentLoaded', () => {

    if (typeof window.chartLabels === 'undefined') {
        return;
    }

    const data = {
        labels: ['janvier','fevrier','mars'],
        datasets: [
            {
                label: 'Postulations',
                data: [10, 20],
                backgroundColor: 'blue',
                barThickness: 'flex',
                maxBarThickness: 50,
                categoryPercentage: 0.6,
                borderWidth: 1
            },
            {
            label: 'Créations d offres',
            data: [15, 32],
            backgroundColor: 'green',
            barThickness: 'flex',
            maxBarThickness: 50,
            categoryPercentage: 0.6,
            borderWidth: 1
        },
        {
            label: ' Validations',
            data: [10, 20],
            backgroundColor: 'yellow',
            barThickness: 'flex',
            maxBarThickness: 50,
            categoryPercentage: 0.6,
            borderWidth: 1
        }
    ]
    };

    const config = {
        type: 'bar',
        data: data,
        options: {
            layout: {
                padding:{
                    left: 0,
                    right: 0
                    }
                },
            scales: {
                x: {
                    grid:{
                        display: false
                    }
                },
                y: {
                    beginAtZero:true
                }
            }
        }
    };

    const ctx = document.getElementById('myChart');
    if (ctx) {
        new Chart(ctx, config);
    }
});
