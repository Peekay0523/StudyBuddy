// Molecular Reaction Simulator using p5.js
const ReactionSimulator = {
    state: {
        isRunning: false,
        reactionType: 'h2o',
        temperature: 298,
        pressure: 1,
        yield: 0,
        molecules: [],
        p5Instance: null,
        reactionProgress: 0,
        maxTime: 10000,
        reactionCount: 0,
        totalReactants: 0
    },

    reactions: {
        h2o: {
            name: 'Water Synthesis',
            equation: '2H₂ + O₂ → 2H₂O',
            reactant1: { symbol: 'H₂', atoms: ['H', 'H'], color: '#ddd', inputId: 'h2-amount' },
            reactant2: { symbol: 'O₂', atoms: ['O', 'O'], color: '#ff6b6b', inputId: 'o2-amount' },
            product: { symbol: 'H₂O', atoms: ['O', 'H', 'H'], color: '#87ceeb' },
            ratio: [2, 1, 2],
            enthalpy: -483.6
        },
        hcl: {
            name: 'HCl Synthesis',
            equation: 'H₂ + Cl₂ → 2HCl',
            reactant1: { symbol: 'H₂', atoms: ['H', 'H'], color: '#ddd', inputId: 'h2-amount' },
            reactant2: { symbol: 'Cl₂', atoms: ['Cl', 'Cl'], color: '#90EE90', inputId: 'cl2-amount' },
            product: { symbol: 'HCl', atoms: ['H', 'Cl'], color: '#FF6B9D' },
            ratio: [1, 1, 2],
            enthalpy: -92.3
        },
        nh3: {
            name: 'Haber Process',
            equation: 'N₂ + 3H₂ → 2NH₃',
            reactant1: { symbol: 'N₂', atoms: ['N', 'N'], color: '#9370DB', inputId: 'n2-amount' },
            reactant2: { symbol: 'H₂', atoms: ['H', 'H'], color: '#ddd', inputId: 'h2-amount' },
            product: { symbol: 'NH₃', atoms: ['N', 'H', 'H', 'H'], color: '#FF6B9D' },
            ratio: [1, 3, 2],
            enthalpy: -92.4
        },
        co2_comb: {
            name: 'Combustion',
            equation: 'CH₄ + 2O₂ → CO₂ + 2H₂O',
            reactant1: { symbol: 'CH₄', atoms: ['C', 'H', 'H', 'H', 'H'], color: '#2f3542', inputId: 'ch4-amount' },
            reactant2: { symbol: 'O₂', atoms: ['O', 'O'], color: '#ff6b6b', inputId: 'o2-amount' },
            product: { symbol: 'CO₂', atoms: ['C', 'O', 'O'], color: '#ff4757' },
            product2: { symbol: 'H₂O', atoms: ['O', 'H', 'H'], color: '#87ceeb' },
            ratio: [1, 2, 1, 2],
            enthalpy: -890.3
        },
        nacl: {
            name: 'Salt Formation',
            equation: '2Na + Cl₂ → 2NaCl',
            reactant1: { symbol: 'Na', atoms: ['Na'], color: '#ced6e0', inputId: 'na-amount' },
            reactant2: { symbol: 'Cl₂', atoms: ['Cl', 'Cl'], color: '#90EE90', inputId: 'cl2-amount' },
            product: { symbol: 'NaCl', atoms: ['Na', 'Cl'], color: '#FF6B9D' },
            ratio: [2, 1, 2],
            enthalpy: -411.1
        },
        co2: {
            name: 'CO₂ Formation',
            equation: 'C + O₂ → CO₂',
            reactant1: { symbol: 'C', atoms: ['C'], color: '#2f3542', inputId: 'c-amount' },
            reactant2: { symbol: 'O₂', atoms: ['O', 'O'], color: '#ff6b6b', inputId: 'o2-amount' },
            product: { symbol: 'CO₂', atoms: ['C', 'O', 'O'], color: '#ff4757' },
            ratio: [1, 1, 1],
            enthalpy: -393.5
        }
    },

    colors: {
        'H': '#ddd',
        'O': '#ff6b6b',
        'Cl': '#90EE90',
        'N': '#9370DB',
        'C': '#2f3542',
        'Na': '#ced6e0'
    },

    init() {
        this.setupEventListeners();
        this.updateEquation();
        this.updateReactantUI();
        
        // Initialize p5
        this.state.p5Instance = new p5(this.sketch.bind(this), 'reaction-sim');
    },

    setupEventListeners() {
        // === CUSTOM DROPDOWN LOGIC ===
        const dropdown = document.getElementById('reaction-dropdown');
        const menu = document.getElementById('reaction-menu');
        const options = document.querySelectorAll('#reaction-menu .option');

        if (dropdown && menu) {
            dropdown.onclick = (e) => {
                e.stopPropagation();
                menu.classList.toggle('show');
                dropdown.classList.toggle('active');
            };

            options.forEach(option => {
                option.onclick = () => {
                    this.state.reactionType = option.dataset.reaction;
                    
                    // Update UI parts
                    options.forEach(opt => opt.classList.remove('active'));
                    option.classList.add('active');
                    
                    const selectedIcon = dropdown.querySelector('.selected i');
                    const selectedText = dropdown.querySelector('.selected span');
                    const optionIcon = option.querySelector('i');
                    const smallText = option.querySelector('small');
                    
                    selectedIcon.className = optionIcon.className;
                    selectedText.textContent = smallText.textContent;
                    
                    menu.classList.remove('show');
                    dropdown.classList.remove('active');

                    // Reset and update
                    this.updateEquation();
                    this.updateReactantUI();
                    this.reset();
                };
            });

            document.addEventListener('click', () => {
                menu.classList.remove('show');
                dropdown.classList.remove('active');
            });
        }

        const startBtns = document.querySelectorAll('#reaction-start, #reaction-start-mobile');
        startBtns.forEach(btn => {
            btn.addEventListener('click', () => this.startReaction());
        });

        const resetBtn = document.getElementById('reaction-reset');
        if (resetBtn) {
            resetBtn.addEventListener('click', () => this.reset());
        }

        const saveBtn = document.getElementById('reaction-save');
        if (saveBtn) {
            saveBtn.addEventListener('click', () => this.saveExperiment());
        }

        const learnMoreBtn = document.getElementById('learnmore-btn');
        const infoOverlay = document.getElementById('infoOverlay');
        const infoCloseBtn = document.getElementById('infoCloseBtn');
        const infoGotItBtn = document.getElementById('infoGotItBtn');

        if (learnMoreBtn && infoOverlay) {
            learnMoreBtn.addEventListener('click', () => {
                infoOverlay.classList.remove('hidden');
            });
        }

        if (infoCloseBtn && infoOverlay) {
            infoCloseBtn.addEventListener('click', () => {
                infoOverlay.classList.add('hidden');
            });
        }

        if (infoGotItBtn && infoOverlay) {
            infoGotItBtn.addEventListener('click', () => {
                infoOverlay.classList.add('hidden');
            });
        }

        if (infoOverlay) {
            infoOverlay.addEventListener('click', (e) => {
                if (e.target === infoOverlay) {
                    infoOverlay.classList.add('hidden');
                }
            });
        }

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && infoOverlay && !infoOverlay.classList.contains('hidden')) {
                infoOverlay.classList.add('hidden');
            }
        });

        const tempSlider = document.getElementById('temp-slider');
        if (tempSlider) {
            tempSlider.addEventListener('input', (e) => {
                this.state.temperature = e.target.value;
                document.getElementById('temp-value').textContent = e.target.value;
            });
        }

        const pressureSlider = document.getElementById('pressure-slider');
        if (pressureSlider) {
            pressureSlider.addEventListener('input', (e) => {
                this.state.pressure = e.target.value;
                document.getElementById('pressure-value').textContent = parseFloat(e.target.value).toFixed(1);
            });
        }
    },

    updateEquation() {
        const reaction = this.reactions[this.state.reactionType];
        const equationEl = document.getElementById('reaction-equation');
        if (equationEl) equationEl.textContent = reaction.equation;
        
        const enthalpyEl = document.getElementById('reaction-enthalpy');
        if (enthalpyEl) enthalpyEl.textContent = reaction.enthalpy;
    },

    updateReactantUI() {
        // No specific reactant amount inputs needed for new dropdown
    },

    startReaction() {
        if (this.state.isRunning) return;
        this.initializeMolecules();
        this.state.isRunning = true;
        this.state.reactionProgress = 0;
        this.state.reactionCount = 0;
    },

    initializeMolecules() {
        const reaction = this.reactions[this.state.reactionType];
        this.state.molecules = [];
        
        const r1Count = reaction.ratio[0];
        const r2Count = reaction.ratio[1];
        
        this.state.totalReactants = r1Count + r2Count;

        const p = this.state.p5Instance;
        if (!p) return;

        for (let i = 0; i < r1Count; i++) {
            this.state.molecules.push(this.createMolecule(p, 'reactant1', reaction.reactant1));
        }

        for (let i = 0; i < r2Count; i++) {
            this.state.molecules.push(this.createMolecule(p, 'reactant2', reaction.reactant2));
        }
    },

    createMolecule(p, type, data) {
        const isLeft = type === 'reactant1';
        return {
            type: type,
            atoms: data.atoms.slice(),
            name: data.symbol,
            color: data.color,
            x: isLeft ? p.random(20, p.width / 2 - 80) : p.random(p.width / 2 + 80, p.width - 20),
            y: p.random(40, p.height - 40),
            vx: p.random(-1.5, 1.5),
            vy: p.random(-1.5, 1.5),
            reacted: false
        };
    },

    reset() {
        this.state.isRunning = false;
        this.state.molecules = [];
        this.state.yield = 0;
        
        const yieldEl = document.getElementById('reaction-yield');
        if (yieldEl) yieldEl.textContent = '0';
        const yieldBar = document.getElementById('reaction-yield-bar');
        if (yieldBar) yieldBar.style.width = '0%';
    },

    sketch(p) {
        p.setup = () => {
            const container = document.getElementById('reaction-sim');
            const w = container.offsetWidth;
            const h = 300;
            const canvas = p.createCanvas(w, h);
            canvas.parent('reaction-sim');
        };

        p.draw = () => {
            p.background(255);
            
            if (!this.state.isRunning && this.state.molecules.length === 0) {
                p.fill(150);
                p.textAlign(p.CENTER, p.CENTER);
                p.textSize(14);
                p.text('Select a reaction and click "Start Simulation"', p.width / 2, p.height / 2);
                return;
            }

            p.stroke(240);
            p.line(p.width / 2, 20, p.width / 2, p.height - 20);

            this.updateAndDrawMolecules(p);
            this.updateYield();
        };

        p.windowResized = () => {
            const container = document.getElementById('reaction-sim');
            if (container) p.resizeCanvas(container.offsetWidth, 300);
        };
    },

    updateAndDrawMolecules(p) {
        const reaction = this.reactions[this.state.reactionType];
        const speedMult = (this.state.temperature / 298) * (1 + this.state.pressure / 10);
        const centerX = p.width / 2;

        for (let i = 0; i < this.state.molecules.length; i++) {
            let mol = this.state.molecules[i];
            if (mol.reacted) continue;

            const jitterStrength = 0.15 * speedMult;
            mol.vx += p.random(-jitterStrength, jitterStrength);
            mol.vy += p.random(-jitterStrength, jitterStrength);

            const driftStrength = 0.0003;
            mol.vx += (centerX - mol.x) * driftStrength;

            mol.x += mol.vx * speedMult;
            mol.y += mol.vy * speedMult;

            if (mol.x < 25 || mol.x > p.width - 25) {
                mol.vx *= -0.9;
                mol.x = p.constrain(mol.x, 25, p.width - 25);
            }
            if (mol.y < 25 || mol.y > p.height - 25) {
                mol.vy *= -0.9;
                mol.y = p.constrain(mol.y, 25, p.height - 25);
            }

            const maxSpeed = 3.5;
            mol.vx = p.constrain(mol.vx, -maxSpeed, maxSpeed);
            mol.vy = p.constrain(mol.vy, -maxSpeed, maxSpeed);

            if (mol.type !== 'product' && mol.type !== 'product2') {
                for (let j = i + 1; j < this.state.molecules.length; j++) {
                    let other = this.state.molecules[j];
                    if (!other.reacted && mol.type !== other.type && other.type !== 'product' && other.type !== 'product2') {
                        let d = p.dist(mol.x, mol.y, other.x, other.y);
                        if (d < 55) {
                            this.checkAndExecuteReaction(p, mol, other, reaction);
                        }
                    }
                }
            }

            this.drawMolecule(p, mol);
        }
    },

    checkAndExecuteReaction(p, m1, m2, reaction) {
        const r1Needed = reaction.ratio[0];
        const r2Needed = reaction.ratio[1];
        
        const availableR1 = this.state.molecules.filter(m => m.type === 'reactant1' && !m.reacted);
        const availableR2 = this.state.molecules.filter(m => m.type === 'reactant2' && !m.reacted);

        if (availableR1.length >= r1Needed && availableR2.length >= r2Needed) {
            m1.reacted = true;
            m2.reacted = true;

            let r1ToMark = r1Needed - (m1.type === 'reactant1' ? 1 : 0) - (m2.type === 'reactant1' ? 1 : 0);
            let r2ToMark = r2Needed - (m1.type === 'reactant2' ? 1 : 0) - (m2.type === 'reactant2' ? 1 : 0);

            for (let m of this.state.molecules) {
                if (m.reacted) continue;
                if (r1ToMark > 0 && m.type === 'reactant1') {
                    m.reacted = true;
                    r1ToMark--;
                } else if (r2ToMark > 0 && m.type === 'reactant2') {
                    m.reacted = true;
                    r2ToMark--;
                }
            }

            const midX = (m1.x + m2.x) / 2;
            const midY = (m1.y + m2.y) / 2;

            const p1Count = reaction.ratio[2];
            for (let k = 0; k < p1Count; k++) {
                this.state.molecules.push({
                    type: 'product',
                    atoms: [...reaction.product.atoms],
                    name: reaction.product.symbol,
                    color: reaction.product.color,
                    x: midX + p.random(-40, 40),
                    y: midY + p.random(-40, 40),
                    vx: p.random(-1.5, 1.5),
                    vy: p.random(-1.5, 1.5),
                    reacted: false
                });
            }

            if (reaction.product2) {
                const p2Count = reaction.ratio[3];
                for (let k = 0; k < p2Count; k++) {
                    this.state.molecules.push({
                        type: 'product2',
                        atoms: [...reaction.product2.atoms],
                        name: reaction.product2.symbol,
                        color: reaction.product2.color,
                        x: midX + p.random(-40, 40),
                        y: midY + p.random(-40, 40),
                        vx: p.random(-1.5, 1.5),
                        vy: p.random(-1.5, 1.5),
                        reacted: false
                    });
                }
            }
            
            this.state.reactionCount++;
        }
    },

    drawMolecule(p, mol) {
        p.push();
        p.translate(mol.x, mol.y);
        const atomRad = 28;
        const offset = 22;

        if (mol.atoms.length === 1) {
            this.drawAtom(p, 0, 0, mol.atoms[0], mol.color, atomRad);
        } else if (mol.atoms.length === 2) {
            p.stroke(100);
            p.strokeWeight(3);
            p.line(-offset, 0, offset, 0);
            this.drawAtom(p, -offset, 0, mol.atoms[0], this.colors[mol.atoms[0]] || mol.color, atomRad);
            this.drawAtom(p, offset, 0, mol.atoms[1], this.colors[mol.atoms[1]] || mol.color, atomRad);
        } else if (mol.atoms.length === 3) {
            p.stroke(100);
            p.strokeWeight(3);
            p.line(0, 0, -offset, offset);
            p.line(0, 0, offset, offset);
            this.drawAtom(p, 0, 0, mol.atoms[0], this.colors[mol.atoms[0]], atomRad);
            this.drawAtom(p, -offset, offset, mol.atoms[1], this.colors[mol.atoms[1]], atomRad);
            this.drawAtom(p, offset, offset, mol.atoms[2], this.colors[mol.atoms[2]], atomRad);
        } else if (mol.atoms.length === 5) {
            p.stroke(100);
            p.strokeWeight(3);
            for (let i = 0; i < 4; i++) {
                let a = i * p.TWO_PI / 4;
                p.line(0, 0, offset * 1.4 * p.cos(a), offset * 1.4 * p.sin(a));
            }
            this.drawAtom(p, 0, 0, mol.atoms[0], this.colors[mol.atoms[0]], atomRad);
            for (let i = 0; i < 4; i++) {
                let a = i * p.TWO_PI / 4;
                this.drawAtom(p, offset * 1.4 * p.cos(a), offset * 1.4 * p.sin(a), mol.atoms[i+1], this.colors[mol.atoms[i+1]], atomRad);
            }
        }
        p.pop();
    },

    drawAtom(p, x, y, symbol, color, rad) {
        p.fill(color);
        p.stroke(30);
        p.strokeWeight(2);
        p.circle(x, y, rad);
        p.fill(p.brightness(color) > 60 ? 0 : 255);
        p.noStroke();
        p.textSize(rad * 0.6);
        p.textStyle(p.BOLD);
        p.textAlign(p.CENTER, p.CENTER);
        p.text(symbol, x, y);
    },

    updateYield() {
        const totalPossible = this.state.totalReactants;
        if (totalPossible === 0) return;
        const products = this.state.molecules.filter(m => m.type === 'product' || m.type === 'product2').length;
        const reaction = this.reactions[this.state.reactionType];
        const productRatio = reaction.ratio[2] + (reaction.ratio[3] || 0);
        const reactantRatio = reaction.ratio[0] + reaction.ratio[1];
        const maxProducts = (this.state.totalReactants / reactantRatio) * productRatio;
        this.state.yield = Math.min(Math.round((products / maxProducts) * 100), 100);
        const yieldEl = document.getElementById('reaction-yield');
        if (yieldEl) yieldEl.textContent = this.state.yield;
        const yieldBar = document.getElementById('reaction-yield-bar');
        if (yieldBar) yieldBar.style.width = this.state.yield + '%';
    },

    saveExperiment() {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();
        const reaction = this.reactions[this.state.reactionType];
        const timestamp = new Date().toLocaleString();
        
        doc.setFillColor(0, 123, 255);
        doc.rect(0, 0, 210, 40, 'F');
        doc.setTextColor(255, 255, 255);
        doc.setFontSize(22);
        doc.text("CHEMISTRY EXPERIMENT REPORT", 105, 25, { align: "center" });
        
        doc.setTextColor(50, 50, 50);
        doc.setFontSize(12);
        doc.text(`Generated on: ${timestamp}`, 20, 50);
        doc.text(`Reaction Name: ${reaction.name}`, 20, 60);
        doc.text(`Equation: ${reaction.equation}`, 20, 70);
        doc.text(`Temperature: ${this.state.temperature} K`, 20, 80);
        doc.text(`Pressure: ${this.state.pressure} atm`, 20, 90);
        doc.text(`FINAL YIELD: ${this.state.yield}%`, 20, 100);
        
        doc.save(`Chemistry_Report_${Date.now()}.pdf`);
        alert("Experiment report saved!");
    }
};

document.addEventListener('DOMContentLoaded', () => {
    ReactionSimulator.init();
});
