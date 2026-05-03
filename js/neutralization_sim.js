// Neutralization Reaction Simulator
let p5Instance = null;
let simState = {
    isRunning: false,
    isPaused: false,
    naohConc: 0.25,
    hno3Conc: 0.25,
    speedMultiplier: 1,
    naohMolecules: [],
    hno3Molecules: [],
    productMolecules: [],
    reactionTime: 0,
    maxReactionTime: 5000,
    totalNaOH: 0,
    totalHNO3: 0,
    reactedCount: 0
};

const SKETCH = (p) => {
    p.setup = function() {
        const container = document.getElementById('sim-canvas-container');
        const width = container.offsetWidth;
        const canvas = p.createCanvas(width, 500);
        canvas.parent('sim-canvas-container');
        initializeSimulation();
    };

    p.draw = function() {
        p.background(245);
        p.fill(0);
        p.textSize(12);

        if (simState.isRunning && !simState.isPaused) {
            simState.reactionTime += 1 * simState.speedMultiplier;
        }

        // Draw containers
        drawContainers();

        // Draw and update molecules
        updateAndDrawMolecules();

        // Display stats
        displayStats();

        // Check if reaction is complete
        if (simState.reactionTime >= simState.maxReactionTime && !simState.isPaused) {
            simState.isRunning = false;
            document.getElementById('show-results').disabled = false;
            displayResults();
        }
    };

    p.windowResized = function() {
        const container = document.getElementById('sim-canvas-container');
        if (container && p.width !== container.offsetWidth) {
            p.resizeCanvas(container.offsetWidth, 500);
        }
    };
};

function initializeSimulation() {
    // Calculate number of molecules based on concentration
    const volume = 0.5; // dm³ (500 mL)
    simState.totalNaOH = Math.max(2, Math.round(simState.naohConc * volume * 20)); // Scale for visibility
    simState.totalHNO3 = Math.max(2, Math.round(simState.hno3Conc * volume * 20));
    
    simState.naohMolecules = [];
    simState.hno3Molecules = [];
    simState.productMolecules = [];
    simState.reactedCount = 0;
    simState.reactionTime = 0;

    // Create NaOH molecules (left side)
    for (let i = 0; i < simState.totalNaOH; i++) {
        simState.naohMolecules.push({
            x: p5Instance.random(50, 150),
            y: p5Instance.random(100, 400),
            vx: p5Instance.random(0.5, 2),
            vy: p5Instance.random(-1, 1),
            reacted: false,
            reactedTime: 0
        });
    }

    // Create HNO3 molecules (right side)
    for (let i = 0; i < simState.totalHNO3; i++) {
        simState.hno3Molecules.push({
            x: p5Instance.random(p5Instance.width - 150, p5Instance.width - 50),
            y: p5Instance.random(100, 400),
            vx: p5Instance.random(-2, -0.5),
            vy: p5Instance.random(-1, 1),
            reacted: false,
            reactedTime: 0
        });
    }
}

function drawContainers() {
    const p = p5Instance;
    
    // Left container (NaOH)
    p.stroke(50);
    p.strokeWeight(2);
    p.fill(255);
    p.rect(20, 80, 180, 340, 5);
    
    // Right container (HNO3)
    p.rect(p.width - 200, 80, 180, 340, 5);

    // Labels
    p.fill(0);
    p.textSize(14);
    p.textAlign(p.CENTER);
    p.text('NaOH', 110, 60);
    p.textSize(11);
    p.text(`[${simState.naohConc.toFixed(2)} M]`, 110, 440);

    p.textSize(14);
    p.text('HNO₃', p.width - 110, 60);
    p.textSize(11);
    p.text(`[${simState.hno3Conc.toFixed(2)} M]`, p.width - 110, 440);
}

function updateAndDrawMolecules() {
    const p = p5Instance;
    const progressRatio = simState.reactionTime / simState.maxReactionTime;

    // Update and draw NaOH molecules
    for (let i = 0; i < simState.naohMolecules.length; i++) {
        let mol = simState.naohMolecules[i];
        
        if (!mol.reacted) {
            // Move towards center as reaction progresses
            if (progressRatio > 0.1) {
                mol.x += mol.vx + (p.width / 2 - mol.x) * 0.002 * progressRatio * simState.speedMultiplier;
            } else {
                mol.x += mol.vx;
            }
            mol.y += mol.vy;

            // Bounce off walls
            if (mol.x < 30 || mol.x > p.width - 30) mol.vx *= -1;
            if (mol.y < 100 || mol.y > 400) mol.vy *= -1;

            // Check for collision with HNO3
            for (let j = 0; j < simState.hno3Molecules.length; j++) {
                let acid = simState.hno3Molecules[j];
                if (!acid.reacted) {
                    let dist = p.dist(mol.x, mol.y, acid.x, acid.y);
                    if (dist < 20) {
                        // Reaction occurs
                        mol.reacted = true;
                        mol.reactedTime = 0;
                        acid.reacted = true;
                        acid.reactedTime = 0;
                        simState.reactedCount++;
                        
                        // Create product
                        simState.productMolecules.push({
                            x: (mol.x + acid.x) / 2,
                            y: (mol.y + acid.y) / 2,
                            time: 0
                        });
                        break;
                    }
                }
            }
        }

        // Draw NaOH molecule
        if (mol.reacted) {
            mol.reactedTime++;
            // Fade out
            let alpha = 255 * (1 - (mol.reactedTime / 30));
            p.fill(100, 150, 255, alpha);
        } else {
            p.fill(100, 150, 255);
        }
        p.noStroke();
        drawMolecule(p, mol.x, mol.y, 'NaOH', mol.reacted);
    }

    // Update and draw HNO3 molecules
    for (let i = 0; i < simState.hno3Molecules.length; i++) {
        let mol = simState.hno3Molecules[i];
        
        if (!mol.reacted) {
            // Move towards center
            if (progressRatio > 0.1) {
                mol.x += mol.vx + (p.width / 2 - mol.x) * 0.002 * progressRatio * simState.speedMultiplier;
            } else {
                mol.x += mol.vx;
            }
            mol.y += mol.vy;

            // Bounce off walls
            if (mol.x < 30 || mol.x > p.width - 30) mol.vx *= -1;
            if (mol.y < 100 || mol.y > 400) mol.vy *= -1;
        }

        // Draw HNO3 molecule
        if (mol.reacted) {
            mol.reactedTime++;
            let alpha = 255 * (1 - (mol.reactedTime / 30));
            p.fill(255, 100, 100, alpha);
        } else {
            p.fill(255, 100, 100);
        }
        p.noStroke();
        drawMolecule(p, mol.x, mol.y, 'HNO3', mol.reacted);
    }

    // Draw products (NaNO3 and H2O)
    for (let i = 0; i < simState.productMolecules.length; i++) {
        let prod = simState.productMolecules[i];
        prod.time++;
        
        // Move down slightly
        prod.y += 0.5;

        p.fill(100, 200, 100);
        p.noStroke();
        drawMolecule(p, prod.x, prod.y, 'NaNO₃', false);
    }
}

function drawMolecule(p, x, y, type, fading) {
    p.push();
    p.translate(x, y);
    
    if (type === 'NaOH') {
        // Draw as three connected circles
        p.circle(-4, 0, 6);
        p.circle(0, 0, 5);
        p.circle(4, 0, 6);
        p.stroke(0);
        p.strokeWeight(1);
        p.line(-1, 0, 3, 0);
    } else if (type === 'HNO3') {
        // Draw as triangle of atoms
        p.circle(0, -3, 6);
        p.circle(-3, 2, 5);
        p.circle(3, 2, 5);
        p.stroke(0);
        p.strokeWeight(1);
        p.line(0, -1, -2, 1);
        p.line(0, -1, 2, 1);
    } else if (type === 'NaNO₃') {
        // Draw as four connected circles
        p.circle(-5, 0, 5);
        p.circle(0, -3, 4);
        p.circle(0, 3, 4);
        p.circle(5, 0, 5);
        p.stroke(0);
        p.strokeWeight(1);
        p.line(-2, 0, 0, -1);
        p.line(0, -1, 0, 1);
        p.line(0, 1, 2, 0);
    }
    
    p.pop();
}

function displayStats() {
    const p = p5Instance;
    const totalReacted = Math.min(simState.totalNaOH, simState.totalHNO3);
    const naohRemaining = simState.totalNaOH - simState.reactedCount;
    const hno3Remaining = simState.totalHNO3 - simState.reactedCount;

    p.fill(0);
    p.textSize(11);
    p.textAlign(p.LEFT);
    
    p.fill(100, 150, 255);
    p.text(`NaOH: ${naohRemaining}/${simState.totalNaOH}`, 220, 120);
    
    p.fill(255, 100, 100);
    p.text(`HNO₃: ${hno3Remaining}/${simState.totalHNO3}`, 220, 145);
    
    p.fill(100, 200, 100);
    p.text(`Products: ${simState.reactedCount}`, 220, 170);

    const progressPercent = (simState.reactionTime / simState.maxReactionTime * 100).toFixed(0);
    p.fill(0);
    p.text(`Progress: ${progressPercent}%`, 220, 195);
}

function displayResults() {
    // Show results card
    document.getElementById('results-card').style.display = 'block';
    
    // Calculate and display results
    const h3o = Math.pow(10, -2.8);
    const totalNaOH = simState.totalNaOH / 20; // Convert back to moles
    const naohConc = totalNaOH / 0.5;

    document.getElementById('result-h3o').textContent = h3o.toExponential(2);
    document.getElementById('result-naoh').textContent = naohConc.toFixed(4);

    // Update stats cards
    const hno3Remaining = (simState.totalHNO3 - simState.reactedCount) / 20;
    const naohRemaining = (simState.totalNaOH - simState.reactedCount) / 20;
    const products = simState.reactedCount / 20;

    document.getElementById('stat-hno3').textContent = hno3Remaining.toFixed(4) + ' mol';
    document.getElementById('stat-naoh').textContent = naohRemaining.toFixed(4) + ' mol';
    document.getElementById('stat-product').textContent = products.toFixed(4) + ' mol';
    document.getElementById('stat-ph').textContent = '2.8';
}

// Event listeners
document.getElementById('naoh-conc-slider').addEventListener('input', function(e) {
    simState.naohConc = parseFloat(e.target.value);
    document.getElementById('naoh-conc-display').textContent = simState.naohConc.toFixed(2);
});

document.getElementById('hno3-conc-slider').addEventListener('input', function(e) {
    simState.hno3Conc = parseFloat(e.target.value);
    document.getElementById('hno3-conc-display').textContent = simState.hno3Conc.toFixed(2);
});

document.getElementById('speed-slider').addEventListener('input', function(e) {
    simState.speedMultiplier = parseFloat(e.target.value);
    const speedText = simState.speedMultiplier === 0.5 ? '0.5x' : simState.speedMultiplier === 1 ? '1x' : '2x';
    document.getElementById('speed-display').textContent = speedText;
});

document.getElementById('start-sim').addEventListener('click', function() {
    if (!p5Instance) {
        p5Instance = new p5(SKETCH);
    }
    simState.isRunning = true;
    simState.isPaused = false;
    document.getElementById('show-results').disabled = true;
    document.getElementById('results-card').style.display = 'none';
    
    // Reset stats display
    document.getElementById('stat-hno3').textContent = '—';
    document.getElementById('stat-naoh').textContent = '—';
    document.getElementById('stat-product').textContent = '—';
    document.getElementById('stat-ph').textContent = '—';
});

document.getElementById('pause-sim').addEventListener('click', function() {
    simState.isPaused = !simState.isPaused;
});

document.getElementById('reset-sim').addEventListener('click', function() {
    simState.isRunning = false;
    simState.isPaused = false;
    document.getElementById('show-results').disabled = true;
    document.getElementById('results-card').style.display = 'none';
    
    // Reset stats display
    document.getElementById('stat-hno3').textContent = '—';
    document.getElementById('stat-naoh').textContent = '—';
    document.getElementById('stat-product').textContent = '—';
    document.getElementById('stat-ph').textContent = '—';
    
    if (p5Instance) {
        p5Instance.remove();
        p5Instance = null;
    }
});

document.getElementById('show-results').addEventListener('click', function() {
    displayResults();
});

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    p5Instance = new p5(SKETCH);
});
