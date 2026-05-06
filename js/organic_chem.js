const organicSketch = (p) => {
    let carbonCount = 1;
    let mode = 'alkane'; // alkane, alkene, alkyne

    const alkaneNames = ["", "Methane", "Ethane", "Propane", "Butane", "Pentane", "Hexane", "Heptane", "Octane", "Nonane", "Decane"];
    const alkeneNames = ["", "", "Ethene", "Propene", "Butene", "Pentene", "Hexene", "Heptene", "Octene", "Nonene", "Decene"];
    const alkyneNames = ["", "", "Ethyne", "Propyne", "Butyne", "Pentyne", "Hexyne", "Heptyne", "Octyne", "Nonyne", "Decyne"];
    
    p.setup = () => {
        let container = document.getElementById('organic-canvas-container');
        let w = container.offsetWidth;
        let h = p.min(300, p.windowHeight * 0.4);
        let canvas = p.createCanvas(w, h);
        canvas.parent('organic-canvas-container');
        
        document.getElementById('add-carbon').onclick = () => {
            if (carbonCount < 10) carbonCount++;
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

        // === CUSTOM DROPDOWN LOGIC ===
        const dropdown = document.getElementById('organic-dropdown');
        const menu = document.getElementById('organic-menu');
        const options = document.querySelectorAll('#organic-menu .option');

        if (dropdown && menu) {
            dropdown.onclick = (e) => {
                e.stopPropagation();
                menu.classList.toggle('show');
                dropdown.classList.toggle('active');
            };

            options.forEach(option => {
                option.onclick = () => {
                    const newMode = option.dataset.mode;
                    
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

                    // Trigger mode change
                    setMode(newMode);
                };
            });

            document.addEventListener('click', () => {
                menu.classList.remove('show');
                dropdown.classList.remove('active');
            });
        }
    };

    function setMode(newMode) {
        mode = newMode;
        
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
        // Dynamic Scaling Factor
        const baseSpacing = 70;
        const totalPadding = 120;
        const availableWidth = p.width * 0.85;
        
        let spacing = baseSpacing;
        if ((count - 1) * baseSpacing + totalPadding > availableWidth) {
            spacing = (availableWidth - totalPadding) / Math.max(count - 1, 1);
        }
        
        let scale = p.constrain(spacing / baseSpacing, 0.4, 1.0);
        spacing = baseSpacing * scale;

        let startX = (p.width - (count - 1) * spacing) / 2;
        let y = p.height / 2;

        for (let i = 0; i < count; i++) {
            let cx = startX + i * spacing;
            
            if (i < count - 1) {
                p.stroke(0);
                p.strokeWeight(3 * scale);
                if (i === 0 && type === 'alkene') {
                    p.line(cx, y - (5 * scale), cx + spacing, y - (5 * scale));
                    p.line(cx, y + (5 * scale), cx + spacing, y + (5 * scale));
                } else if (i === 0 && type === 'alkyne') {
                    p.line(cx, y - (8 * scale), cx + spacing, y - (8 * scale));
                    p.line(cx, y, cx + spacing, y);
                    p.line(cx, y + (8 * scale), cx + spacing, y + (8 * scale));
                } else {
                    p.line(cx, y, cx + spacing, y);
                }
            }

            drawAtom(p, cx, y, 'C', '#2f3542', 30 * scale);

            p.stroke(150);
            p.strokeWeight(2 * scale);

            let hPositions = [];
            let hDist = 45 * scale;

            if (type === 'alkane') {
                hPositions.push({dx: 0, dy: -hDist}); 
                hPositions.push({dx: 0, dy: hDist});  
                if (i === 0) hPositions.push({dx: -hDist, dy: 0}); 
                if (i === count - 1) hPositions.push({dx: hDist, dy: 0}); 
            } else if (type === 'alkene') {
                if (i === 0) {
                    hPositions.push({dx: -hDist*0.8, dy: -hDist*0.8});
                    hPositions.push({dx: -hDist*0.8, dy: hDist*0.8});
                } else if (i === 1) {
                    hPositions.push({dx: 0, dy: -hDist});
                    if (count === 2) {
                        hPositions.push({dx: hDist*0.8, dy: hDist*0.8});
                        hPositions.push({dx: hDist*0.8, dy: -hDist*0.8});
                    }
                } else {
                    hPositions.push({dx: 0, dy: -hDist});
                    hPositions.push({dx: 0, dy: hDist});
                    if (i === count - 1) hPositions.push({dx: hDist, dy: 0});
                }
            } else if (type === 'alkyne') {
                if (i === 0) {
                    hPositions.push({dx: -hDist, dy: 0});
                } else if (i === 1) {
                    if (count === 2) hPositions.push({dx: hDist, dy: 0});
                } else {
                    hPositions.push({dx: 0, dy: -hDist});
                    hPositions.push({dx: 0, dy: hDist});
                    if (i === count - 1) hPositions.push({dx: hDist, dy: 0});
                }
            }

            hPositions.forEach(pos => {
                p.stroke(150);
                p.strokeWeight(2 * scale);
                p.line(cx, y, cx + pos.dx, y + pos.dy);
                drawAtom(p, cx + pos.dx, y + pos.dy, 'H', '#add8e6', 20 * scale);
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
