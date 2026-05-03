const organicSketch = (p) => {
    let carbonCount = 1;
    let mode = 'alkane'; // alkane, alkene, alkyne

    const alkaneNames = ["", "Methane", "Ethane", "Propane", "Butane", "Pentane", "Hexane", "Heptane", "Octane"];
    const alkeneNames = ["", "", "Ethene", "Propene", "Butene", "Pentene", "Hexene", "Heptene", "Octene"];
    const alkyneNames = ["", "", "Ethyne", "Propyne", "Butyne", "Pentyne", "Hexyne", "Heptyne", "Octyne"];
    
    p.setup = () => {
        let container = document.getElementById('organic-canvas-container');
        let w = container.offsetWidth;
        let h = p.min(300, p.windowHeight * 0.4);
        let canvas = p.createCanvas(w, h);
        canvas.parent('organic-canvas-container');
        
        document.getElementById('add-carbon').onclick = () => {
            if (carbonCount < 8) carbonCount++;
            updateInfo();
        };
        
        document.getElementById('remove-carbon').onclick = () => {
            if (carbonCount > 1) {
                carbonCount--;
                if ((mode === 'alkene' || mode === 'alkyne') && carbonCount < 2) {
                    carbonCount = 2;
                }
            }
            updateInfo();
        };

        document.getElementById('alkane-btn').onclick = function() {
            setMode('alkane', this);
        };

        document.getElementById('alkene-btn').onclick = function() {
            setMode('alkene', this);
        };

        document.getElementById('alkyne-btn').onclick = function() {
            setMode('alkyne', this);
        };
    };

    function setMode(newMode, btn) {
        mode = newMode;
        // Update active class on buttons
        document.querySelectorAll('.list-group-item').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        
        if ((mode === 'alkene' || mode === 'alkyne') && carbonCount < 2) {
            carbonCount = 2;
        }
        updateInfo();
    }

    p.windowResized = () => {
        let container = document.getElementById('organic-canvas-container');
        if (container) {
            let w = container.offsetWidth;
            let h = p.min(300, p.windowHeight * 0.4);
            p.resizeCanvas(w, h);
        }
    };

    function updateInfo() {
        let name = "";
        let formula = "";
        if (mode === 'alkane') {
            const hCount = carbonCount * 2 + 2;
            name = alkaneNames[carbonCount];
            formula = `C<sub>${carbonCount}</sub>H<sub>${hCount}</sub>`;
        } else if (mode === 'alkene') {
            const hCount = carbonCount * 2;
            name = alkeneNames[carbonCount];
            formula = `C<sub>${carbonCount}</sub>H<sub>${hCount}</sub>`;
        } else if (mode === 'alkyne') {
            const hCount = carbonCount * 2 - 2;
            name = alkyneNames[carbonCount];
            formula = `C<sub>${carbonCount}</sub>H<sub>${hCount}</sub>`;
        }
        document.getElementById('org-name').innerText = name;
        document.getElementById('org-formula').innerHTML = formula;
    }

    p.draw = () => {
        p.background(255);
        drawHydrocarbon(p, carbonCount, mode);
    };

    function drawHydrocarbon(p, count, type) {
        let spacing = 70;
        let startX = (p.width - (count - 1) * spacing) / 2;
        let y = p.height / 2;

        for (let i = 0; i < count; i++) {
            let cx = startX + i * spacing;
            
            // Draw C-C bonds
            if (i < count - 1) {
                p.stroke(0);
                p.strokeWeight(3);
                if (i === 0 && type === 'alkene') {
                    // Double bond between 1st and 2nd Carbon
                    p.line(cx, y - 5, cx + spacing, y - 5);
                    p.line(cx, y + 5, cx + spacing, y + 5);
                } else if (i === 0 && type === 'alkyne') {
                    // Triple bond between 1st and 2nd Carbon
                    p.line(cx, y - 8, cx + spacing, y - 8);
                    p.line(cx, y, cx + spacing, y);
                    p.line(cx, y + 8, cx + spacing, y + 8);
                } else {
                    // Single bond
                    p.line(cx, y, cx + spacing, y);
                }
            }

            // Carbon Atom
            drawAtom(p, cx, y, 'C', '#2f3542', 30);

            // Hydrogen logic
            p.stroke(150);
            p.strokeWeight(2);

            let hPositions = []; // {dx, dy}

            if (type === 'alkane') {
                hPositions.push({dx: 0, dy: -45}); // Top
                hPositions.push({dx: 0, dy: 45});  // Bottom
                if (i === 0) hPositions.push({dx: -45, dy: 0}); // Left
                if (i === count - 1) hPositions.push({dx: 45, dy: 0}); // Right
            } else if (type === 'alkene') {
                if (i === 0) {
                    hPositions.push({dx: -35, dy: -35});
                    hPositions.push({dx: -35, dy: 35});
                } else if (i === 1) {
                    hPositions.push({dx: 0, dy: -45});
                    if (count === 2) {
                        hPositions.push({dx: 35, dy: 35});
                        hPositions.push({dx: 35, dy: -35});
                    }
                } else {
                    hPositions.push({dx: 0, dy: -45});
                    hPositions.push({dx: 0, dy: 45});
                    if (i === count - 1) hPositions.push({dx: 45, dy: 0});
                }
            } else if (type === 'alkyne') {
                if (i === 0) {
                    hPositions.push({dx: -45, dy: 0});
                } else if (i === 1) {
                    if (count === 2) hPositions.push({dx: 45, dy: 0});
                } else {
                    hPositions.push({dx: 0, dy: -45});
                    hPositions.push({dx: 0, dy: 45});
                    if (i === count - 1) hPositions.push({dx: 45, dy: 0});
                }
            }

            hPositions.forEach(pos => {
                p.stroke(150);
                p.line(cx, y, cx + pos.dx, y + pos.dy);
                drawAtom(p, cx + pos.dx, y + pos.dy, 'H', '#add8e6', 20);
            });
        }
    }

    function drawAtom(p, x, y, sym, col, size) {
        p.stroke(0);
        p.strokeWeight(1);
        p.fill(col);
        p.circle(x, y, size);
        p.fill(p.brightness(col) > 50 ? 0 : 255);
        p.noStroke();
        p.textAlign(p.CENTER, p.CENTER);
        p.textSize(size * 0.5);
        p.text(sym, x, y);
    }
};

new p5(organicSketch, 'organic-canvas-container');
