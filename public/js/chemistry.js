const chemistrySketch = (p) => {
    let currentElement = 'Hydrogen';
    const elements = {
        'Hydrogen': { symbol: 'H', protons: 1, neutrons: 0, electrons: [1], color: '#add8e6' },
        'Helium': { symbol: 'He', protons: 2, neutrons: 2, electrons: [2], color: '#f9ca24' },
        'Lithium': { symbol: 'Li', protons: 3, neutrons: 4, electrons: [2, 1], color: '#ff4757' },
        'Beryllium': { symbol: 'Be', protons: 4, neutrons: 5, electrons: [2, 2], color: '#2ed573' },
        'Carbon': { symbol: 'C', protons: 6, neutrons: 6, electrons: [2, 4], color: '#2f3542' },
        'Nitrogen': { symbol: 'N', protons: 7, neutrons: 7, electrons: [2, 5], color: '#3498db' },
        'Oxygen': { symbol: 'O', protons: 8, neutrons: 8, electrons: [2, 6], color: '#ff4757' },
        'Neon': { symbol: 'Ne', protons: 10, neutrons: 10, electrons: [2, 8], color: '#fd79a8' },
        'Sodium': { symbol: 'Na', protons: 11, neutrons: 12, electrons: [2, 8, 1], color: '#ced6e0' },
        'Magnesium': { symbol: 'Mg', protons: 12, neutrons: 12, electrons: [2, 8, 2], color: '#27ae60' },
        'Aluminium': { symbol: 'Al', protons: 13, neutrons: 14, electrons: [2, 8, 3], color: '#bdc3c7' },
        'Chlorine': { symbol: 'Cl', protons: 17, neutrons: 18, electrons: [2, 8, 7], color: '#2ed573' },
        'Iron': { symbol: 'Fe', protons: 26, neutrons: 30, electrons: [2, 8, 14, 2], color: '#e67e22' },
        'Silver': { symbol: 'Ag', protons: 47, neutrons: 61, electrons: [2, 8, 18, 18, 1], color: '#dfe6e9' },
        'Gold': { symbol: 'Au', protons: 79, neutrons: 118, electrons: [2, 8, 18, 32, 18, 1], color: '#ffa502' }
    };

    p.setup = () => {
        let container = document.getElementById('chemistry-canvas-container');
        let w = container.offsetWidth;
        let h = p.min(400, p.windowHeight * 0.5);
        let canvas = p.createCanvas(w, h);
        canvas.parent('chemistry-canvas-container');
        
        // Ensure buttons work correctly
        const elBtns = document.querySelectorAll('.element-btn');
        elBtns.forEach(btn => {
            btn.onclick = (e) => {
                currentElement = e.target.dataset.element;
                elBtns.forEach(b => b.classList.remove('active'));
                e.target.classList.add('active');
            };
        });

        // Scroll Handling
        const scrollContainer = document.getElementById('element-container');
        const scrollLeftBtn = document.getElementById('el-scroll-left');
        const scrollRightBtn = document.getElementById('el-scroll-right');

        if (scrollLeftBtn && scrollRightBtn && scrollContainer) {
            const scrollStep = () => Math.max(120, Math.round(scrollContainer.offsetWidth * 0.75));

            scrollLeftBtn.onclick = () => {
                scrollContainer.scrollBy({ left: -scrollStep(), behavior: 'smooth' });
            };
            scrollRightBtn.onclick = () => {
                scrollContainer.scrollBy({ left: scrollStep(), behavior: 'smooth' });
            };

            // Hide/Show arrows based on scroll position
            const toggleArrows = () => {
                scrollLeftBtn.style.opacity = scrollContainer.scrollLeft <= 0 ? '0.3' : '1';
                scrollLeftBtn.style.pointerEvents = scrollContainer.scrollLeft <= 0 ? 'none' : 'auto';
                
                const isAtEnd = scrollContainer.scrollLeft + scrollContainer.offsetWidth >= scrollContainer.scrollWidth - 10;
                scrollRightBtn.style.opacity = isAtEnd ? '0.3' : '1';
                scrollRightBtn.style.pointerEvents = isAtEnd ? 'none' : 'auto';
            };

            scrollContainer.onscroll = toggleArrows;
            window.addEventListener('resize', toggleArrows);
            toggleArrows();
        }
    };

    p.windowResized = () => {
        let container = document.getElementById('chemistry-canvas-container');
        if (container) {
            let w = container.offsetWidth;
            let h = p.min(400, p.windowHeight * 0.5);
            p.resizeCanvas(w, h);
        }
    };

    p.draw = () => {
        p.background(255);
        let data = elements[currentElement];
        if (!data) return;
        
        let centerX = p.width / 2;
        let centerY = p.height / 2;

        // Dynamic spacing logic to ensure "rings" are clear
        let numShells = data.electrons.length;
        let nucleusSize = 45;
        let startRadius = 55;
        let maxRadius = 175;
        
        // Calculate gap so rings always fit but are spread out for fewer shells
        let gap = (maxRadius - startRadius) / Math.max(numShells, 1);
        if (numShells <= 2) gap = 45; // Spread out small atoms like Lithium

        // 1. Draw Nucleus
        p.stroke(0);
        p.strokeWeight(2);
        p.fill(data.color);
        p.circle(centerX, centerY, nucleusSize);
        
        // Symbol in nucleus
        p.noStroke();
        p.fill(p.brightness(data.color) > 60 ? 0 : 255);
        p.textAlign(p.CENTER, p.CENTER);
        p.textSize(18);
        p.textStyle(p.BOLD);
        p.text(data.symbol, centerX, centerY);

        // 2. Draw Shells and Electrons
        data.electrons.forEach((count, i) => {
            let shellRadius = startRadius + (i * gap);
            
            // Draw Shell Ring (Make it dark enough to see)
            p.noFill();
            p.stroke(180);
            p.strokeWeight(1.5);
            p.circle(centerX, centerY, shellRadius * 2);

            // Draw Electrons on this shell
            for (let j = 0; j < count; j++) {
                // Slower rotation for outer shells
                let speed = 0.02 / (i + 1);
                let angle = p.frameCount * speed + (p.TWO_PI / count) * j;
                
                let ex = centerX + shellRadius * p.cos(angle);
                let ey = centerY + shellRadius * p.sin(angle);
                
                // Electron appearance
                p.fill(50, 80, 255);
                p.noStroke();
                p.circle(ex, ey, 9); // Slightly larger electrons
                
                // Optional: add a tiny glow/stroke to electrons
                p.stroke(255);
                p.strokeWeight(1);
                p.noFill();
                p.circle(ex, ey, 9);
            }
        });

        // 3. Info Panel
        p.fill(0);
        p.noStroke();
        p.textAlign(p.LEFT);
        p.textSize(13);
        p.textStyle(p.BOLD);
        p.text(`${currentElement} (Atomic No: ${data.protons})`, 15, 25);
        p.textStyle(p.NORMAL);
        p.text(`Shells: ${numShells}`, 15, 45);
        p.text(`Total Electrons: ${data.electrons.reduce((a,b)=>a+b,0)}`, 15, 65);
    };

    p.touchStarted = () => {
        if (p.mouseX >= 0 && p.mouseX <= p.width && p.mouseY >= 0 && p.mouseY <= p.height) {
            // No specific touch logic needed for viewer, but we prevent default scroll if touching canvas
            return false;
        }
    };
};

// Initialize with the container ID
new p5(chemistrySketch, 'chemistry-canvas-container');
