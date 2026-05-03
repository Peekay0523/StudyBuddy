let trigChart;
let currentTrigType = 'sin';

function initTrigLab() {
    const ctx = document.getElementById('trigChart').getContext('2d');
    
    trigChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Function Graph',
                data: [],
                borderColor: '#0dcaf0',
                borderWidth: 3,
                fill: false,
                pointRadius: 0,
                tension: 0.2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: { 
                    type: 'linear',
                    title: { display: true, text: 'Degrees' }, 
                    grid: { color: '#e0e0e0', lineWidth: 1 },
                    border: { display: true, color: '#000', width: 3 },
                    min: 0,
                    max: 360
                },
                y: { 
                    title: { display: true, text: 'y' }, 
                    grid: { color: '#e0e0e0', lineWidth: 1 }, 
                    border: { display: true, color: '#000', width: 3 },
                    min: -5, 
                    max: 5 
                }
            },
            plugins: {
                legend: { display: false }
            }
        }
    });

    document.querySelectorAll('#trig-sliders .form-range').forEach(el => {
        el.addEventListener('input', updateTrigGraph);
    });

    document.querySelectorAll('.trig-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            currentTrigType = e.target.closest('.trig-btn').dataset.type;
            document.querySelectorAll('.trig-btn').forEach(b => b.classList.remove('active'));
            e.target.closest('.trig-btn').classList.add('active');
            
            updateTrigVisibility();
            updateTrigGraph();
        });
    });

    updateTrigVisibility();
    updateTrigGraph();
}

function updateTrigVisibility() {
    document.querySelectorAll('.trig-param').forEach(ctrl => {
        const allowed = ctrl.dataset.for.split(' ');
        if (allowed.includes(currentTrigType)) {
            ctrl.classList.remove('d-none');
        } else {
            ctrl.classList.add('d-none');
        }
    });
}

function updateTrigGraph() {
    const a = parseFloat(document.getElementById('trig-a').value);
    const b = parseFloat(document.getElementById('trig-b').value);
    const q = parseFloat(document.getElementById('trig-q').value);
    const base = parseFloat(document.getElementById('trig-base').value);
    
    const data = [];
    let formulaText = "";

    if (currentTrigType === 'sin' || currentTrigType === 'cos') {
        trigChart.options.scales.x.min = 0;
        trigChart.options.scales.x.max = 360;
        trigChart.options.scales.x.title.text = 'Degrees';
        
        for (let x = 0; x <= 360; x += 2) {
            let y = (currentTrigType === 'sin') 
                ? a * Math.sin(b * x * Math.PI / 180) + q
                : a * Math.cos(b * x * Math.PI / 180) + q;
            data.push({x: x, y: y});
        }
        formulaText = `y = ${a.toFixed(0)} ${currentTrigType}(${b.toFixed(0)}x) ${q >= 0 ? '+' : ''} ${q.toFixed(0)}`;
    } else if (currentTrigType === 'log') {
        trigChart.options.scales.x.min = 0.1;
        trigChart.options.scales.x.max = 10;
        trigChart.options.scales.x.title.text = 'x';
        for (let x = 0.1; x <= 10; x += 0.1) {
            data.push({x: x, y: Math.log(x) / Math.log(base)});
        }
        formulaText = `y = log<sub>${base.toFixed(0)}</sub>(x)`;
    }

    document.getElementById('trig-formula').innerHTML = formulaText;
    trigChart.data.datasets[0].data = data;
    trigChart.update('none');
}

document.addEventListener('DOMContentLoaded', initTrigLab);
