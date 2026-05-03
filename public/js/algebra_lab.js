let algChart;
let currentAlgType = 'linear';

function initAlgLab() {
    const ctx = document.getElementById('algChart').getContext('2d');
    
    algChart = new Chart(ctx, {
        type: 'line',
        data: {
            datasets: [{
                label: 'Function Graph',
                data: [],
                borderColor: '#198754',
                borderWidth: 3,
                fill: false,
                pointRadius: 0,
                tension: 0.1,
                spanGaps: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: { 
                    type: 'linear',
                    position: 'center',
                    title: { display: true, text: 'x' }, 
                    grid: { color: '#e0e0e0', lineWidth: 1 },
                    border: { display: true, color: '#000', width: 3 },
                    min: -10,
                    max: 10
                },
                y: { 
                    position: 'center',
                    title: { display: true, text: 'y' }, 
                    grid: { color: '#e0e0e0', lineWidth: 1 }, 
                    border: { display: true, color: '#000', width: 3 },
                    min: -10, 
                    max: 10 
                }
            },
            plugins: {
                legend: { display: false }
            }
        }
    });

    document.querySelectorAll('#alg-sliders .form-range').forEach(el => {
        el.addEventListener('input', updateAlgGraph);
    });

    document.querySelectorAll('.alg-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            currentAlgType = e.target.closest('.alg-btn').dataset.type;
            document.querySelectorAll('.alg-btn').forEach(b => b.classList.remove('active'));
            e.target.closest('.alg-btn').classList.add('active');
            
            updateAlgVisibility();
            updateAlgGraph();
        });
    });

    updateAlgVisibility();
    updateAlgGraph();
}

function updateAlgVisibility() {
    document.querySelectorAll('.alg-param').forEach(ctrl => {
        const allowed = ctrl.dataset.for.split(' ');
        if (allowed.includes(currentAlgType)) {
            ctrl.classList.remove('d-none');
        } else {
            ctrl.classList.add('d-none');
        }
    });
}

function updateAlgGraph() {
    const a = parseFloat(document.getElementById('alg-a').value);
    const m = parseFloat(document.getElementById('alg-m').value);
    const p = parseFloat(document.getElementById('alg-p').value);
    const q = parseFloat(document.getElementById('alg-q').value);
    const b = parseFloat(document.getElementById('alg-b').value);
    
    const data = [];
    let formulaText = "";

    for (let x = -10; x <= 10; x += 0.1) {
        let y = null;
        if (currentAlgType === 'linear') {
            y = m * x + q;
        } else if (currentAlgType === 'parabola') {
            y = a * Math.pow(x - p, 2) + q;
        } else if (currentAlgType === 'hyperbola') {
            if (Math.abs(x - p) < 0.05) y = NaN;
            else y = a / (x - p) + q;
        } else if (currentAlgType === 'exponential') {
            y = a * Math.pow(b, x - p) + q;
        }
        data.push({x: x, y: y});
    }

    if (currentAlgType === 'linear') formulaText = `y = ${m.toFixed(0)}x ${q >= 0 ? '+' : ''} ${q.toFixed(0)}`;
    if (currentAlgType === 'parabola') formulaText = `y = ${a.toFixed(0)}(x ${p >= 0 ? '-' : '+'} ${Math.abs(p).toFixed(0)})² ${q >= 0 ? '+' : ''} ${q.toFixed(0)}`;
    if (currentAlgType === 'hyperbola') formulaText = `y = ${a.toFixed(0)} / (x ${p >= 0 ? '-' : '+'} ${Math.abs(p).toFixed(0)}) ${q >= 0 ? '+' : ''} ${q.toFixed(0)}`;
    if (currentAlgType === 'exponential') formulaText = `y = ${a.toFixed(0)} · ${b.toFixed(0)}^(x ${p >= 0 ? '-' : '+'} ${Math.abs(p).toFixed(0)}) ${q >= 0 ? '+' : ''} ${q.toFixed(0)}`;

    document.getElementById('alg-formula').innerHTML = formulaText;
    algChart.data.datasets[0].data = data;
    algChart.update('none');
}

document.addEventListener('DOMContentLoaded', initAlgLab);
