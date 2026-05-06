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
                    position: 'bottom',
                    title: { display: true, text: 'x-axis', font: { weight: 'bold', size: 14 } }, 
                    grid: { 
                        color: (context) => context.tick.value === 0 ? '#000' : '#e0e0e0',
                        lineWidth: (context) => context.tick.value === 0 ? 2 : 1
                    },
                    border: { display: false },
                    min: -5,
                    max: 5,
                    ticks: { stepSize: 1 }
                },
                y: { 
                    position: 'left',
                    title: { display: true, text: 'y-axis', font: { weight: 'bold', size: 14 } }, 
                    grid: { 
                        color: (context) => context.tick.value === 0 ? '#000' : '#e0e0e0',
                        lineWidth: (context) => context.tick.value === 0 ? 2 : 1
                    }, 
                    border: { display: false },
                    min: -5, 
                    max: 5,
                    ticks: { stepSize: 1 }
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

    // === CUSTOM DROPDOWN LOGIC ===
    const dropdown = document.getElementById('alg-dropdown');
    const menu = document.getElementById('alg-menu');
    const options = document.querySelectorAll('#alg-menu .option');

    if (dropdown && menu) {
        dropdown.onclick = (e) => {
            e.stopPropagation();
            menu.classList.toggle('show');
            dropdown.classList.toggle('active');
        };

        options.forEach(option => {
            option.onclick = () => {
                currentAlgType = option.dataset.type;
                
                // Update UI parts
                options.forEach(opt => opt.classList.remove('active'));
                option.classList.add('active');
                
                const selectedIcon = dropdown.querySelector('.selected i');
                const selectedText = dropdown.querySelector('.selected span');
                const optionIcon = option.querySelector('i');
                const optionText = option.querySelector('strong');
                
                selectedIcon.className = optionIcon.className;
                selectedText.textContent = optionText.textContent;
                
                menu.classList.remove('show');
                dropdown.classList.remove('active');
                
                updateAlgVisibility();
                updateAlgGraph();
            };
        });

        document.addEventListener('click', () => {
            menu.classList.remove('show');
            dropdown.classList.remove('active');
        });
    }

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

    // Plot within the visible range (-5 to 5)
    for (let x = -5; x <= 5; x += 0.05) {
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
