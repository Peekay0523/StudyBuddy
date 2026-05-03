// Simple Molecular Reaction Simulator
let simState = {
    isRunning: false,
    isPaused: false,
    reactionType: 'h2-cl2',
    speed: 1,
    moleculeCount: 3,
    molecules: [],
    p5Instance: null,
    reactionProgress: 0,
    maxTime: 8000,
    reactionCount: 0
};

const REACTIONS = {
    'h2-cl2': {
        name: 'H₂ + Cl₂ → 2HCl',
        reactant1: { name: 'H₂', atoms: ['H', 'H'], color: '#FFD700' },
        reactant2: { name: 'Cl₂', atoms: ['Cl', 'Cl'], color: '#90EE90' },
        product: { name: 'HCl', atoms: ['H', 'Cl'], color: '#FF6B9D' },
        ratio: [1, 1, 2]
    },
    'h2-o2': {
        name: '2H₂ + O₂ → 2H₂O',
        reactant1: { name: 'H₂', atoms: ['H', 'H'], color: '#FFD700' },
        reactant2: { name: 'O₂', atoms: ['O', 'O'], color: '#FF6347' },
        product: { name: 'H₂O', atoms: ['O', 'H', 'H'], color: '#87CEEB' },
        ratio: [2, 1, 2]
    },
    'n2-h2': {
        name: 'N₂ + 3H₂ → 2NH₃',
        reactant1: { name: 'N₂', atoms: ['N', 'N'], color: '#9370DB' },
        reactant2: { name: 'H₂', atoms: ['H', 'H'], color: '#FFD700' },
        product: { name: 'NH₃', atoms: ['N', 'H', 'H', 'H'], color: '#FF6B9D' },
        ratio: [1, 3, 2]
    }
};

const COLORS = {
    'H': '#FFD700',
    'Cl': '#90EE90',
    'O': '#FF6347',
    'N': '#9370DB',
    'HCl': '#FF6B9D',
    'H2O': '#87CEEB',
    'NH3': '#FF6B9D'
};

const SKETCH = (p) => {
    p.setup = function() {
        const container = document.getElementById('simple-sim-container');
        if (!container) return;
        
        const width = container.offsetWidth;
        const canvas = p.createCanvas(width, 450);
        canvas.parent('simple-sim-container');
        p.frameRate(60);
        initializeSimulation(p);
    };

    p.draw = function() {
        p.background(250);
        
        if (simState.isRunning && !simState.isPaused) {
            simState.reactionProgress += 1 * simState.speed;
        }

        // Draw separation line
        drawSeparation(p);

        // Update and draw molecules
        updateAndDrawMolecules(p);

        // Draw stats
        drawStats(p);

        if (simState.reactionProgress >= simState.maxTime && simState.isRunning) {
            simState.isRunning = false;
        }
    };

    p.windowResized = function() {
        const container = document.getElementById('simple-sim-container');
        if (container && p && p.width !== container.offsetWidth) {
            p.resizeCanvas(container.offsetWidth, 450);
        }
    };
};

function initializeSimulation(p) {
    const reaction = REACTIONS[simState.reactionType];
    simState.molecules = [];
    simState.reactionProgress = 0;
    simState.reactionCount = 0;

    // Create reactant 1 molecules (left side)
    for (let i = 0; i < reaction.ratio[0]; i++) {
        simState.molecules.push({
            type: 'reactant1',
            atoms: reaction.reactant1.atoms.slice(),
            name: reaction.reactant1.name,
            color: reaction.reactant1.color,
            x: p.random(40, 120),
            y: p.random(80, 350),
            vx: p.random(0.8, 2),
            vy: p.random(-1, 1),
            reacted: false
        });
    }

    // Create reactant 2 molecules (right side)
    for (let i = 0; i < reaction.ratio[1]; i++) {
        simState.molecules.push({
            type: 'reactant2',
            atoms: reaction.reactant2.atoms.slice(),
            name: reaction.reactant2.name,
            color: reaction.reactant2.color,
            x: p.random(p.width - 120, p.width - 40),
            y: p.random(80, 350),
            vx: p.random(-2, -0.8),
            vy: p.random(-1, 1),
            reacted: false
        });
    }
}

function drawSeparation(p) {
    p.stroke(200);
    p.strokeWeight(2);
    p.line(p.width / 2, 60, p.width / 2, 400);
    p.fill(100);
    p.textSize(10);
    p.textAlign(p.CENTER);
    p.text('Reaction Zone', p.width / 2, 45);
}

function updateAndDrawMolecules(p) {
    const reaction = REACTIONS[simState.reactionType];
    const progressRatio = simState.reactionProgress / simState.maxTime;

    for (let i = 0; i < simState.molecules.length; i++) {
        let mol = simState.molecules[i];

        if (!mol.reacted) {
            // Add attraction towards center as reaction progresses
            const centerX = p.width / 2;
            const attraction = progressRatio * 0.003 * simState.speed;
            
            mol.x += mol.vx + (centerX - mol.x) * attraction;
            mol.y += mol.vy;

            // Bounce off walls
            if (mol.x < 30) { mol.x = 30; mol.vx *= -0.8; }
            if (mol.x > p.width - 30) { mol.x = p.width - 30; mol.vx *= -0.8; }
            if (mol.y < 70) { mol.y = 70; mol.vy *= -0.8; }
            if (mol.y > 380) { mol.y = 380; mol.vy *= -0.8; }

            // Check collisions with other molecules
            for (let j = i + 1; j < simState.molecules.length; j++) {
                let other = simState.molecules[j];
                if (!other.reacted && mol.type !== other.type) {
                    let dist = p.dist(mol.x, mol.y, other.x, other.y);
                    if (dist < 35) {
                        // Reaction occurs if enough reactants are present
                        const r1Needed = reaction.ratio[0];
                        const r2Needed = reaction.ratio[1];
                        
                        const availableR1 = simState.molecules.filter(m => m.type === 'reactant1' && !m.reacted);
                        const availableR2 = simState.molecules.filter(m => m.type === 'reactant2' && !m.reacted);

                        if (availableR1.length >= r1Needed && availableR2.length >= r2Needed) {
                            // Consume exact ratio
                            for (let k = 0; k < r1Needed; k++) availableR1[k].reacted = true;
                            for (let k = 0; k < r2Needed; k++) availableR2[k].reacted = true;
                            
                            simState.reactionCount++;

                            // Create products based on ratio[2]
                            const midX = (mol.x + other.x) / 2;
                            const midY = (mol.y + other.y) / 2;

                            for (let k = 0; k < reaction.ratio[2]; k++) {
                                simState.molecules.push({
                                    type: 'product',
                                    atoms: reaction.product.atoms.slice(),
                                    name: reaction.product.name,
                                    color: reaction.product.color,
                                    x: midX + (k * 25 - 12),
                                    y: midY + p.random(-15, 15),
                                    vx: p.random(-1.5, 1.5),
                                    vy: p.random(-1.5, 1.5),
                                    reacted: false
                                });
                            }
                            break;
                        }
                    }
                }
            }
        }

        // Draw molecule
        drawMolecule(p, mol);
    }
}

function drawMolecule(p, mol) {
    const atomSize = mol.type === 'product' ? 18 : 20;
    const spacing = mol.atoms.length === 2 ? 32 : 18;

    // Draw atoms and bonds
    if (mol.atoms.length === 2) {
        // Diatomic molecule
        p.fill(COLORS[mol.atoms[0]] || mol.color);
        p.stroke(0);
        p.strokeWeight(3);
        p.circle(mol.x - spacing / 2, mol.y, atomSize);
        
        p.fill(COLORS[mol.atoms[1]] || mol.color);
        p.circle(mol.x + spacing / 2, mol.y, atomSize);

        // Bond line
        p.stroke(100);
        p.strokeWeight(2);
        p.line(mol.x - spacing / 2 + atomSize / 2, mol.y, mol.x + spacing / 2 - atomSize / 2, mol.y);

        // Label
        p.fill(0);
        p.textSize(10);
        p.textAlign(p.CENTER);
        p.text(mol.name, mol.x, mol.y + atomSize + 12);

    } else if (mol.atoms.length === 3) {
        // Triatomic molecule (H2O or NH3)
        const angle1 = -120 * p.PI / 180;
        const angle2 = 120 * p.PI / 180;
        const bondLen = 28;

        // Center atom
        p.fill(COLORS[mol.atoms[0]] || mol.color);
        p.stroke(0);
        p.strokeWeight(3);
        p.circle(mol.x, mol.y, atomSize);

        // Other atoms
        p.fill(COLORS[mol.atoms[1]] || mol.color);
        p.circle(mol.x + bondLen * p.cos(angle1), mol.y + bondLen * p.sin(angle1), atomSize);
        
        p.fill(COLORS[mol.atoms[2]] || mol.color);
        p.circle(mol.x + bondLen * p.cos(angle2), mol.y + bondLen * p.sin(angle2), atomSize);

        // Bonds
        p.stroke(100);
        p.strokeWeight(3);
        p.line(mol.x, mol.y, mol.x + bondLen * p.cos(angle1), mol.y + bondLen * p.sin(angle1));
        p.line(mol.x, mol.y, mol.x + bondLen * p.cos(angle2), mol.y + bondLen * p.sin(angle2));

        // Label
        p.fill(0);
        p.textSize(10);
        p.textAlign(p.CENTER);
        p.text(mol.name, mol.x, mol.y + atomSize + 18);

    } else if (mol.atoms.length === 4) {
        // Tetrahedral molecule (NH3)
        const angles = [0, 120 * p.PI / 180, 240 * p.PI / 180];
        const bondLen = 26;

        p.fill(COLORS[mol.atoms[0]] || mol.color);
        p.stroke(0);
        p.strokeWeight(3);
        p.circle(mol.x, mol.y, atomSize);

        p.fill(COLORS[mol.atoms[1]] || mol.color);
        p.circle(mol.x + bondLen * p.cos(angles[0]), mol.y + bondLen * p.sin(angles[0]), atomSize);

        p.fill(COLORS[mol.atoms[2]] || mol.color);
        p.circle(mol.x + bondLen * p.cos(angles[1]), mol.y + bondLen * p.sin(angles[1]), atomSize);

        p.fill(COLORS[mol.atoms[3]] || mol.color);
        p.circle(mol.x + bondLen * p.cos(angles[2]), mol.y + bondLen * p.sin(angles[2]), atomSize);

        p.stroke(100);
        p.strokeWeight(3);
        for (let i = 0; i < 3; i++) {
            p.line(mol.x, mol.y, 
                   mol.x + bondLen * p.cos(angles[i]), 
                   mol.y + bondLen * p.sin(angles[i]));
        }

        p.fill(0);
        p.textSize(10);
        p.textAlign(p.CENTER);
        p.text(mol.name, mol.x, mol.y + atomSize + 20);
    }
}

function drawStats(p) {
    const reaction = REACTIONS[simState.reactionType];
    const reactant1Remaining = simState.molecules.filter(m => m.type === 'reactant1' && !m.reacted).length;
    const reactant2Remaining = simState.molecules.filter(m => m.type === 'reactant2' && !m.reacted).length;
    const productCount = simState.molecules.filter(m => m.type === 'product').length;

    p.fill(0);
    p.textSize(11);
    p.textAlign(p.LEFT);

    p.text(`Reaction: ${reaction.name}`, 20, 420);
    p.text(`${reaction.reactant1.name} remaining: ${reactant1Remaining}`, 20, 435);
    p.text(`${reaction.reactant2.name} remaining: ${reactant2Remaining}`, 200, 435);
    p.text(`Products formed: ${productCount}`, 420, 435);
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    // Reaction type selector
    document.querySelectorAll('input[name="reaction-type"]').forEach(radio => {
        radio.addEventListener('change', function(e) {
            simState.reactionType = e.target.value;
            if (simState.p5Instance) {
                initializeSimulation(simState.p5Instance);
            }
        });
    });

    // Start button
    document.getElementById('simple-start').addEventListener('click', function() {
        if (!simState.p5Instance) {
            simState.p5Instance = new p5(SKETCH);
        }
        simState.isRunning = true;
        simState.isPaused = false;
    });

    // Pause button
    document.getElementById('simple-pause').addEventListener('click', function() {
        if (simState.isRunning) {
            simState.isPaused = !simState.isPaused;
            this.textContent = simState.isPaused ? '▶ Resume' : '⏸ Pause';
        }
    });

    // Reset button
    document.getElementById('simple-reset').addEventListener('click', function() {
        simState.isRunning = false;
        simState.isPaused = false;
        document.getElementById('simple-pause').textContent = '⏸ Pause';
        
        if (simState.p5Instance) {
            simState.p5Instance.remove();
            simState.p5Instance = null;
        }
    });

    // Initialize p5
    simState.p5Instance = new p5(SKETCH);
});
