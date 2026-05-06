let activeSim = 'pendulum';
let physicsP5;

const physicsLab = (p) => {
    // Shared State
    p.gravity = 9.8;
    
    // Pendulum State
    p.pendulum = {
        mode: 'single', // 'single' or 'double'
        bobs: [
            { angle: 45, aVel: 0, aAcc: 0, len: 150, mass: 5, dragging: false },
            { angle: 0, aVel: 0, aAcc: 0, len: 150, mass: 5, dragging: false }
        ],
        origin: null
    };

    // Free Fall State
    p.freefall = {
        y: 50,
        v: 0,
        mass: 2,
        height: 0,
        initialHeight: 40,
        maxHeight: 40,
        isFalling: false,
        floor: 450,
        landed: false,
        vFinal: 0,
        topMargin: 60
    };

    // Projectile State
    p.projectile = {
        x: 50,
        y: 450,
        v0: 50,
        angle: 45,
        t: 0,
        path: [],
        isFlying: false,
        peak: {x: 0, y: 0, h: 0},
        landed: false
    };

    // Doppler State
    p.doppler = {
        sourceX: 100,
        sourceV: 1,
        observerX: 400,
        vSound: 2,
        waves: [],
        f: 1,
        observedF: 1
    };

    // Newton 1 State
    p.newton1 = {
        x: 50,
        v: 0,
        friction: 0.01,
        force: 5,
        dragging: false,
        pushFrames: 0
    };

    // Newton 2 State
    p.newton2 = {
        mode: 'incline', // 'incline' or 'pulley'
        x: 0,
        v: 0,
        mass: 10,
        hangingMass: 5,
        force: 50,
        angle: 15,
        mu: 0.05,
        dragging: false,
        isPulling: false
    };

    // Newton 3 State
    p.newton3 = {
        obj1: { x: 200, v: 2, mass: 10 },
        obj2: { x: 400, v: -2, mass: 10 },
        collided: false,
        collisionFrames: 0,
        isRunning: false
    };

    // Gravity State
    p.gravitySim = {
        m1: 50,
        m2: 50,
        r: 200
    };

    // Coulomb State
    p.coulomb = {
        q1: 10,
        q2: -10,
        r: 200,
        dragging1: false,
        dragging2: false,
        x1: 0,
        x2: 0
    };

    p.setup = () => {
        physicsP5 = p;
        let container = document.getElementById('physics-canvas-container');
        let w = container.offsetWidth;
        // Calculate height proportionally - height scales with width to maintain aspect ratio
        let h = p.max(250, p.min(500, w * 0.65));
        let canvas = p.createCanvas(w, h);
        canvas.parent('physics-canvas-container');
        // Calculate scale factor based on canvas width (reference: 760px = scale 1.0, max 1.1)
        p.canvasScale = p.min(1.1, w / 760);
        p.pendulum.origin = p.createVector(p.width / 2, 50);
        
        // Dynamic floor levels
        p.freefall.floor = p.height - 50;
        p.projectile.y = p.height - 50;

        // Initialize Newton 3 positions based on canvas width
        p.newton3.obj1.x = p.width * 0.25;
        p.newton3.obj2.x = p.width * 0.75;
        p.newton3.obj1.v = 2 * p.canvasScale;
        p.newton3.obj2.v = -2 * p.canvasScale;

        // Initialize Coulomb positions
        p.coulomb.x1 = p.width / 2 - 100 * p.canvasScale;
        p.coulomb.x2 = p.width / 2 + 100 * p.canvasScale;

        // Calculate max safe height for Free Fall
        p.freefall.maxHeight = Math.floor((p.freefall.floor - p.freefall.topMargin) / 10);
        p.freefall.initialHeight = p.min(p.freefall.initialHeight, p.freefall.maxHeight);
        p.freefall.y = p.freefall.floor - (p.freefall.initialHeight * 10);
        
        updateUI();
    };

    p.windowResized = () => {
        let container = document.getElementById('physics-canvas-container');
        if (container) {
            let w = container.offsetWidth;
            // Calculate height proportionally - height scales with width to maintain aspect ratio
            let h = p.max(250, p.min(500, w * 0.65));
            p.resizeCanvas(w, h);
            // Calculate scale factor based on canvas width (reference: 760px = scale 1.0, max 1.1)
            p.canvasScale = p.min(1.1, w / 760);
            p.pendulum.origin = p.createVector(p.width / 2, 50);
            p.freefall.floor = p.height - 50;
            p.projectile.y = p.height - 50;

            if (!p.newton3.isRunning) {
                p.newton3.obj1.x = p.width * 0.25;
                p.newton3.obj2.x = p.width * 0.75;
                p.newton3.obj1.v = 2 * p.canvasScale;
                p.newton3.obj2.v = -2 * p.canvasScale;
            }

            p.freefall.maxHeight = Math.floor((p.freefall.floor - p.freefall.topMargin) / 10);
            p.freefall.initialHeight = p.min(p.freefall.initialHeight, p.freefall.maxHeight);
            if (!p.freefall.isFalling) {
                p.freefall.y = p.freefall.floor - (p.freefall.initialHeight * 10);
            }
            updateUI();
        }
    };

    p.draw = () => {
        p.background(255);
        
        if (activeSim === 'pendulum') {
            drawPendulum();
        } else if (activeSim === 'freefall') {
            drawFreeFall();
        } else if (activeSim === 'projectile') {
            drawProjectile();
        } else if (activeSim === 'doppler') {
            drawDoppler();
        } else if (activeSim === 'newton1') {
            drawNewton1();
        } else if (activeSim === 'newton2') {
            drawNewton2();
        } else if (activeSim === 'newton3') {
            drawNewton3();
        } else if (activeSim === 'gravity') {
            drawGravity();
        } else if (activeSim === 'coulomb') {
            drawCoulomb();
        }
    };

    function drawPendulum() {
        let bobs = p.pendulum.bobs;
        let isDouble = p.pendulum.mode === 'double';

        for (let i = 0; i < (isDouble ? 2 : 1); i++) {
            let bob = bobs[i];
            if (!bob.dragging) {
                let force = -1 * (p.gravity / (bob.len / 10)) * p.sin(p.radians(bob.angle));
                bob.aAcc = force;
                bob.aVel += bob.aAcc * 0.1;
                bob.angle += bob.aVel;
                bob.aVel *= 0.995;
            }
        }

        if (isDouble) {
            let bob1 = bobs[0];
            let bob2 = bobs[1];
            if (bob1.angle > bob2.angle - 10) {
                let v1 = bob1.aVel;
                let v2 = bob2.aVel;
                let m1 = bob1.mass;
                let m2 = bob2.mass;
                bob1.aVel = (v1 * (m1 - m2) + 2 * m2 * v2) / (m1 + m2);
                bob2.aVel = (v2 * (m2 - m1) + 2 * m1 * v1) / (m1 + m2);
                bob1.angle = bob2.angle - 10.1;
            }
        }

        p.fill(200);
        p.noStroke();
        p.textAlign(p.CENTER);
        p.textSize(30);
        p.text("‹", 40, p.height / 2);
        p.text("›", p.width - 40, p.height / 2);
        p.textSize(12);
        p.text(p.pendulum.mode === 'single' ? "Mode: Single Pendulum" : "Mode: Newton's Cradle (Double)", p.width / 2, p.height - 20);

        for (let i = 0; i < (isDouble ? 2 : 1); i++) {
            let bob = bobs[i];
            let x = bob.len * p.sin(p.radians(bob.angle)) + p.pendulum.origin.x;
            let y = bob.len * p.cos(p.radians(bob.angle)) + p.pendulum.origin.y;
            p.stroke(100);
            p.strokeWeight(2);
            p.line(p.pendulum.origin.x, p.pendulum.origin.y, x, y);
            p.fill(i === 0 ? p.color(0, 123, 255) : p.color(108, 117, 125));
            p.noStroke();
            p.circle(x, y, 30 + bob.mass * 2);
            p.fill(0);
            p.textAlign(p.LEFT);
            p.text(`${bob.angle.toFixed(1)}°`, x + 20, y);
        }

        let period = (2 * Math.PI * Math.sqrt((bobs[0].len / 100) / p.gravity)).toFixed(2);
        p.fill(0);
        p.textAlign(p.CENTER);
        p.textSize(14);
        p.text(`Angle 1: ${bobs[0].angle.toFixed(1)}° ${isDouble ? ` | Angle 2: ${bobs[1].angle.toFixed(1)}°` : ''} | T: ${period}s`, p.width / 2, 30);
    }

    function drawFreeFall() {
        // Draw solid floor
        let floorHeight = 40;
        p.noStroke();
        p.fill(100, 100, 100); // Dark gray floor
        p.rect(0, p.freefall.floor, p.width, floorHeight);
        p.stroke(60);
        p.strokeWeight(2);
        p.line(0, p.freefall.floor, p.width, p.freefall.floor); // Floor surface line

        let currentHeight = (p.freefall.floor - p.freefall.y) / 10;
        if (p.freefall.isFalling) {
            p.freefall.v += (p.gravity * 0.05);
            p.freefall.y += p.freefall.v;
        }

        // Constraints: Stop exactly at the floor
        if (p.freefall.y > p.freefall.floor - 15) {
            p.freefall.y = p.freefall.floor - 15;
            if (p.freefall.isFalling) {
                p.freefall.landed = true;
                p.freefall.vFinal = p.freefall.v * 10;
            }
            p.freefall.isFalling = false;
        }

        p.fill(0);
        p.noStroke();
        p.textAlign(p.CENTER);
        p.textSize(14);
        p.text(`Height: ${p.max(0, currentHeight).toFixed(2)}m  |  Gravity: ${p.gravity} m/s²`, p.width / 2, 30);
        
        // Draw the falling object
        p.fill(220, 53, 69);
        p.stroke(0);
        p.strokeWeight(1);
        p.circle(p.width / 2, p.freefall.y, 30);

        if (p.freefall.landed) {
            p.fill(0);
            p.noStroke();
            p.textSize(12);
            p.textAlign(p.LEFT);
            let initialY = p.freefall.floor - (p.freefall.initialHeight * 10);
            p.text(`v₀: 0 m/s`, p.width / 2 + 25, initialY + 5);
            p.text(`v_final: ${p.freefall.vFinal.toFixed(2)} m/s`, p.width / 2 + 25, p.freefall.floor - 10);
        }
    }

    function drawProjectile() {
        p.stroke(220);
        p.line(0, p.projectile.y, p.width, p.projectile.y);
        let currentHeight = 0;
        if (p.projectile.isFlying) {
            let vx = p.projectile.v0 * p.cos(p.radians(p.projectile.angle));
            let vy = p.projectile.v0 * p.sin(p.radians(p.projectile.angle));
            p.projectile.t += 0.1;
            let curX = p.projectile.x + vx * p.projectile.t;
            let curY = p.projectile.y - (vy * p.projectile.t - 0.5 * p.gravity * p.projectile.t * p.projectile.t);
            currentHeight = (p.projectile.y - curY) / 10;
            if (curY <= p.projectile.y) {
                p.projectile.path.push({x: curX, y: curY});
                if (currentHeight > p.projectile.peak.h) {
                    p.projectile.peak = {x: curX, y: curY, h: currentHeight};
                }
                p.fill(40, 167, 69);
                p.noStroke();
                p.circle(curX, curY, 15);
            } else {
                p.projectile.isFlying = false;
                p.projectile.landed = true;
            }
        }
        p.fill(0);
        p.noStroke();
        p.textAlign(p.CENTER);
        p.textSize(14);
        p.text(`Height: ${currentHeight.toFixed(2)}m  |  Gravity: ${p.gravity} m/s²`, p.width / 2, 30);
        p.noFill();
        p.stroke(40, 167, 69, 150);
        p.strokeWeight(2);
        p.beginShape();
        p.projectile.path.forEach(pt => p.vertex(pt.x, pt.y));
        p.endShape();
        if (p.projectile.landed || (!p.projectile.isFlying && p.projectile.path.length > 0)) {
            p.fill(0);
            p.noStroke();
            p.textSize(12);
            p.textAlign(p.LEFT);
            p.text(`v₀: ${p.projectile.v0} m/s`, p.projectile.x, p.projectile.y + 20);
            p.textAlign(p.CENTER);
            p.stroke(0, 100);
            p.line(p.projectile.peak.x, p.projectile.peak.y, p.projectile.peak.x, p.projectile.y);
            p.noStroke();
            p.text(`Max Height: ${p.projectile.peak.h.toFixed(2)}m`, p.projectile.peak.x, p.projectile.peak.y - 10);
            if (p.projectile.path.length > 0) {
                let last = p.projectile.path[p.projectile.path.length - 1];
                p.textAlign(p.RIGHT);
                p.text(`v_final: ${p.projectile.v0} m/s`, last.x, p.projectile.y + 20);
            }
        }
        if (!p.projectile.isFlying && p.projectile.path.length === 0) {
            p.fill(40, 167, 69);
            p.circle(p.projectile.x, p.projectile.y, 15);
        }
    }

    function drawDoppler() {
        p.doppler.sourceX += p.doppler.sourceV;
        if (p.doppler.sourceX > p.width) p.doppler.sourceX = 0;
        if (p.frameCount % 20 === 0) {
            p.doppler.waves.push({ x: p.doppler.sourceX, r: 0 });
        }
        p.noFill();
        p.stroke(0, 100);
        for (let i = p.doppler.waves.length - 1; i >= 0; i--) {
            let w = p.doppler.waves[i];
            w.r += p.doppler.vSound;
            p.circle(w.x, p.height/2, w.r * 2);
            if (w.r > p.width) p.doppler.waves.splice(i, 1);
        }

        // Calculate Observed Frequency
        // f' = f * (v_sound / (v_sound - v_source))
        // v_source is positive if moving towards observer, negative if away
        let distBefore = p.doppler.observerX - (p.doppler.sourceX - p.doppler.sourceV);
        let distAfter = p.doppler.observerX - p.doppler.sourceX;
        let relativeV = p.doppler.sourceV;
        
        // If source is moving towards observer from left, or away to right
        // Actually simpler: if sourceX < observerX and sourceV > 0 -> towards
        // if sourceX > observerX and sourceV > 0 -> away
        let movingTowards = (p.doppler.sourceX < p.doppler.observerX);
        
        if (movingTowards) {
            p.doppler.observedF = p.doppler.f * (p.doppler.vSound / (p.doppler.vSound - p.doppler.sourceV));
        } else {
            p.doppler.observedF = p.doppler.f * (p.doppler.vSound / (p.doppler.vSound + p.doppler.sourceV));
        }

        // Draw Car Sketch
        p.push();
        p.translate(p.doppler.sourceX, p.height / 2);
        
        // Wheels
        p.fill(50);
        p.circle(-10, 10, 10);
        p.circle(10, 10, 10);
        
        // Body
        p.fill(220, 53, 69); // Red car
        p.stroke(0);
        p.strokeWeight(1);
        p.rect(-20, -10, 40, 15, 2); // Main body
        p.rect(-10, -20, 20, 10, 5); // Cabin
        
        p.pop();

        // Draw Observer
        p.fill(0, 123, 255);
        p.stroke(0);
        p.rect(p.doppler.observerX - 10, p.height/2 - 20, 20, 40, 5);
        p.fill(255, 200, 150);
        p.circle(p.doppler.observerX, p.height/2 - 25, 15);
        
        p.fill(0);
        p.noStroke();
        p.textAlign(p.CENTER);
        p.text("Source (v_s)", p.doppler.sourceX, p.height/2 - 35);
        p.text("Observer", p.doppler.observerX, p.height/2 + 35);
        
        // Display Frequencies
        p.textAlign(p.LEFT);
        p.textSize(16);
        p.textStyle(p.BOLD);
        p.fill(220, 53, 69);
        p.text(`Source Freq: ${p.doppler.f.toFixed(2)} Hz`, 20, 30);
        p.fill(0, 100, 200);
        p.text(`Observed Freq: ${p.doppler.observedF.toFixed(2)} Hz`, 20, 55);
        p.textStyle(p.NORMAL);
        p.textSize(12);
    }

    function drawNewton1() {
        p.stroke(200);
        p.line(0, p.height - 50, p.width, p.height - 50);
        
        let frictionDecel = 0;
        if (!p.newton1.dragging) {
            p.newton1.x += p.newton1.v;
            frictionDecel = p.newton1.v * p.newton1.friction;
            p.newton1.v -= frictionDecel;
        } else {
            p.newton1.x = p.mouseX;
            p.newton1.v = 0;
        }

        if (p.newton1.x > p.width) p.newton1.x = 0;
        if (p.newton1.x < 0) p.newton1.x = p.width;
        
        p.fill(p.newton1.dragging ? 150 : 120);
        p.rect(p.newton1.x - 25, p.height - 100, 50, 50);

        let yBase = p.height - 75;

        // Show "Push" Force Arrow (Applied Force)
        if (p.newton1.pushFrames > 0) {
            let fApplied = p.newton1.force;
            // Draw arrow from side of box
            let startX = fApplied > 0 ? p.newton1.x - 25 : p.newton1.x + 25;
            drawArrow(startX, yBase, fApplied * 5, 0, 'red');
            p.fill('red');
            p.text(`Applied: ${p.abs(fApplied).toFixed(1)}N`, p.newton1.x, p.height - 130);
            p.newton1.pushFrames--;
        }

        p.fill(0);
        p.textAlign(p.CENTER);
        p.text(`Velocity: ${p.newton1.v.toFixed(2)}`, p.newton1.x, p.height - 110);

        // Show Friction Force Arrow (at ground level)
        if (p.abs(p.newton1.v) > 0.05) {
            let fFric = p.newton1.v * p.newton1.friction * 50; 
            let dir = p.newton1.v > 0 ? -1 : 1;
            let groundY = p.height - 48; // Just below the box bottom
            let startX = p.newton1.x + (p.newton1.v > 0 ? 25 : -25);
            drawArrow(startX, groundY, dir * 40, 0, 'orange');
            p.fill('orange');
            p.text(`Friction: ${p.abs(fFric).toFixed(2)}N`, p.newton1.x, groundY + 15);
        }
    }

    function drawNewton2() {
        let s = p.canvasScale || 1;
        // --- Navigation Arrows ---
        p.fill(200);
        p.noStroke();
        p.textAlign(p.CENTER);
        p.textSize(30 * s);
        p.text("‹", 40, p.height / 2);
        p.text("›", p.width - 40, p.height / 2);
        p.textSize(12 * s);
        p.text(p.newton2.mode === 'incline' ? "Example: Object on Incline" : "Example: Pulley System", p.width / 2, p.height - 20 * s);

        if (p.newton2.mode === 'incline') {
            drawNewton2Incline();
        } else {
            drawNewton2Pulley();
        }
    }

    function drawNewton2Incline() {
        let s = p.canvasScale || 1;
        let rad = p.radians(p.newton2.angle);
        let m = p.newton2.mass;
        let Fapp = (p.newton2.isPulling || p.newton2.dragging) ? p.newton2.force : 0;
        let g = p.gravity;
        let mu = p.newton2.mu;

        let x1 = 100 * s;
        let y1 = p.height - 100 * s;
        let maxLen = (p.width - 200 * s);

        // Draw solid floor
        p.noStroke();
        p.fill(120);
        p.rect(0, y1, p.width, 100 * s);
        p.stroke(60);
        p.strokeWeight(2);
        p.line(0, y1, p.width, y1); 
        
        // Draw Incline Geometry
        p.fill(240);
        p.noStroke();
        p.triangle(x1, y1, x1 + (maxLen + 50 * s) * p.cos(-rad), y1, x1 + (maxLen + 50 * s) * p.cos(-rad), y1 + (maxLen + 50 * s) * p.sin(-rad));

        p.stroke(80);
        p.strokeWeight(4);
        let surfaceEndX = x1 + (maxLen + 50 * s) * p.cos(-rad);
        let surfaceEndY = y1 + (maxLen + 50 * s) * p.sin(-rad);
        p.line(x1, y1, surfaceEndX, surfaceEndY);

        // Object Position
        let curX = x1 + p.newton2.x * p.cos(-rad);
        let curY = y1 + p.newton2.x * p.sin(-rad);

        // Handle Rope Interaction
        if (p.newton2.dragging) {
            let dx = p.mouseX - curX;
            let dy = p.mouseY - curY;
            let pullMag = p.dist(p.mouseX, p.mouseY, curX, curY);
            let angleToMouse = p.atan2(dy, dx);
            let relativeAngle = angleToMouse - (-rad);
            p.newton2.force = Math.round(p.cos(relativeAngle) * pullMag);
            Fapp = p.newton2.force;
            
            p.stroke(101, 67, 33);
            p.strokeWeight(3);
            p.line(curX, curY - 30 * s, p.mouseX, p.mouseY);
            
            let slider = document.getElementById('n2-f');
            if (slider) {
                slider.value = p.newton2.force;
                document.getElementById('n2f-val').innerText = p.newton2.force;
            }
        } else if (p.newton2.isPulling) {
            p.stroke(101, 67, 33);
            p.strokeWeight(3);
            p.line(curX, curY - 30 * s, curX + Fapp * 0.8 * p.cos(-rad), curY - 30 * s + Fapp * 0.8 * p.sin(-rad));
        }

        let Fg_parallel = m * g * p.sin(rad);
        let Fn = m * g * p.cos(rad);
        let Ff = 0;
        
        if (p.abs(p.newton2.v) > 0.1) {
            Ff = mu * Fn * (p.newton2.v > 0 ? 1 : -1);
        } else {
            let netExt = Fapp - Fg_parallel;
            if (p.abs(netExt) < mu * Fn) {
                Ff = netExt;
            } else {
                Ff = mu * Fn * (netExt > 0 ? 1 : -1);
            }
        }

        let Fnet = Fapp - Fg_parallel - Ff;
        let a = Fnet / m;
        
        if (!p.newton2.dragging) {
            p.newton2.v += a * 0.1;
            p.newton2.x += p.newton2.v;
        } else {
            p.newton2.v = 0;
        }

        if (p.newton2.x < 0) {
            p.newton2.x = 0;
            p.newton2.v = 0;
        }
        if (p.newton2.x > maxLen) {
            p.newton2.x = maxLen;
            p.newton2.v = 0;
        }
        
        p.push();
        p.translate(curX, curY);
        p.rotate(-rad);
        
        p.fill(220, 53, 69);
        p.stroke(0);
        p.strokeWeight(1);
        p.rect(-30 * s, -60 * s, 60 * s, 60 * s, 5);
        p.fill(255);
        p.textAlign(p.CENTER);
        p.textSize(14 * s);
        p.text(`${m}kg`, 0, -35 * s);

        if (p.abs(Fapp) > 1) {
            drawArrow(30 * s, -30 * s, Fapp * 0.8, 0, 'red');
            p.fill('red'); p.noStroke();
            p.textSize(12 * s);
            p.text(`F_app: ${p.abs(Fapp).toFixed(0)}N`, Fapp * 0.8 + (Fapp > 0 ? 40 * s : -40 * s), -45 * s);
        }
        
        drawArrow(0, -30 * s, 0, -Fn * 0.5, 'purple');
        p.fill('purple'); p.noStroke();
        p.textSize(12 * s);
        p.text(`F_N`, 0, -Fn * 0.5 - 40 * s);

        drawArrow(0, -30 * s, 0, Fn * 0.5, 'green');
        p.fill('green'); p.noStroke();
        p.textSize(12 * s);
        p.text(`F_g⊥`, 15 * s, Fn * 0.5 - 15 * s);

        drawArrow(-30 * s, -30 * s, -Fg_parallel * 0.8, 0, 'blue');
        p.fill('blue'); p.noStroke();
        p.textSize(12 * s);
        p.text(`F_g||`, -Fg_parallel * 0.8 - 40 * s, -45 * s);

        if (p.abs(Ff) > 1) {
            drawArrow(0, -5 * s, -Ff * 0.8, 0, 'orange');
            p.fill('orange'); p.noStroke();
            p.textSize(12 * s);
            p.text(`F_f`, -Ff * 0.8 - 15 * s, 10 * s);
        }
        
        p.pop();

        drawNewton2Dashboard(a, Fnet);
    }

    function drawNewton2Pulley() {
        let s = p.canvasScale || 1;
        let m1 = p.newton2.mass;
        let m2 = p.newton2.hangingMass;
        let g = p.gravity;
        let mu = p.newton2.mu;
        let Fapp = (p.newton2.isPulling || p.newton2.dragging) ? p.newton2.force : 0;

        let tableY = p.height - 250 * s;
        let floorY = p.height - 50;
        let tableXStart = 100 * s;
        let tableXEnd = p.width - 200 * s;
        let pulleyX = tableXEnd;
        let pulleyY = tableY;

        // Draw Ground/Floor (Solid and visible)
        p.noStroke();
        p.fill(120, 120, 120); // Solid gray floor
        p.rect(0, floorY, p.width, 50 * s);
        p.stroke(60);
        p.strokeWeight(3);
        p.line(0, floorY, p.width, floorY); // Floor surface line

        // Draw Table
        p.stroke(101, 67, 33);
        p.strokeWeight(4);
        p.line(tableXStart, tableY, tableXEnd, tableY); // Top
        p.line(tableXStart + 20 * s, tableY, tableXStart + 20 * s, floorY); // Leg 1
        p.line(tableXEnd - 40 * s, tableY, tableXEnd - 40 * s, floorY);  // Leg 2

        // Dynamics update for p.newton2.x
        let Fg2 = m2 * g;
        let Fn1 = m1 * g;
        let Ff = 0;
        
        if (p.abs(p.newton2.v) > 0.1) {
            Ff = mu * Fn1 * (p.newton2.v > 0 ? 1 : -1);
        } else {
            let netExt = Fg2 - Fapp;
            if (p.abs(netExt) < mu * Fn1) {
                Ff = netExt;
            } else {
                Ff = mu * Fn1 * (netExt > 0 ? 1 : -1);
            }
        }

        let Fnet = Fg2 - Fapp - Ff;
        let a = Fnet / (m1 + m2);

        if (!p.newton2.dragging) {
            p.newton2.v += a * 0.1;
            p.newton2.x += p.newton2.v;
        } else {
            p.newton2.v = 0;
            // Handle dragging logic later in the function to use correct positions
        }

        // Constraints
        let maxLift = -50 * s;
        let maxFall = floorY - tableY - 65 * s; // Stop when bottom hits floor
        
        if (p.newton2.x < maxLift) {
            p.newton2.x = maxLift;
            p.newton2.v = 0;
        }
        if (p.newton2.x > maxFall) {
            p.newton2.x = maxFall;
            p.newton2.v = 0;
        }

        // Calculate final positions for drawing
        let initialDist = 300 * s;
        let currentDist = initialDist - p.newton2.x; 
        let obj1X = pulleyX - currentDist;
        let obj1Y = tableY;
        let obj2X = pulleyX + 15 * s; 
        let obj2Y = tableY + p.newton2.x + 50 * s;

        if (p.newton2.dragging) {
            let dx = obj1X - p.mouseX;
            p.newton2.force = p.max(0, dx - 30 * s); 
            let slider = document.getElementById('n2-f');
            if (slider) {
                slider.value = p.newton2.force;
                document.getElementById('n2f-val').innerText = p.newton2.force;
            }
        }

        // Draw Pulley
        p.fill(150);
        p.noStroke();
        p.circle(pulleyX, pulleyY, 30 * s);
        p.fill(255);
        p.circle(pulleyX, pulleyY, 10 * s);

        // Draw Ropes
        p.stroke(120, 90, 60);
        p.strokeWeight(3);
        p.line(obj1X, obj1Y - 15 * s, pulleyX, pulleyY - 15 * s);
        p.line(pulleyX + 15 * s, pulleyY, pulleyX + 15 * s, obj2Y - 15 * s);

        // Extra Pull Rope
        p.line(obj1X - 30 * s, obj1Y - 15 * s, obj1X - 80 * s - (Fapp > 0 ? Fapp * 0.3 : 0), obj1Y - 15 * s);
        if (p.newton2.dragging || p.newton2.isPulling) {
            p.fill('red');
            p.noStroke();
            p.textAlign(p.RIGHT);
            p.textSize(12 * s);
            p.text("PULL", obj1X - 90 * s, obj1Y - 15 * s);
        }

        // Draw Objects
        p.fill(220, 53, 69); // Red m1
        p.stroke(0);
        p.strokeWeight(1);
        p.rect(obj1X - 30 * s, obj1Y - 30 * s, 60 * s, 30 * s, 5);
        p.fill(255);
        p.textAlign(p.CENTER);
        p.textSize(12 * s);
        p.text(`${m1}kg`, obj1X, obj1Y - 10 * s);

        p.fill(0, 123, 255); // Blue m2
        p.stroke(0);
        p.rect(obj2X - 15 * s, obj2Y - 15 * s, 30 * s, 30 * s, 5);
        p.fill(255);
        p.textSize(12 * s);
        p.text(`${m2}kg`, obj2X, obj2Y + 5 * s);

        // Arrows for Free Body Diagram
        drawArrow(obj1X + 30 * s, obj1Y - 15 * s, 40 * s, 0, 'blue'); 
        if (Fapp > 1) drawArrow(obj1X - 30 * s, obj1Y - 15 * s, -Fapp * 0.3, 0, 'red'); 
        if (p.abs(Ff) > 1) drawArrow(obj1X, obj1Y, -Ff * 0.5, 0, 'orange'); 

        drawArrow(obj2X, obj2Y + 15, 0, 40, 'green'); 
        drawArrow(obj2X, obj2Y - 15, 0, -40, 'blue'); 

        drawNewton2Dashboard(a, Fnet);
    }

    function drawNewton2Dashboard(a, Fnet) {
        let s = p.canvasScale || 1;
        p.fill(255, 240);
        p.stroke(200);
        p.rect(10 * s, 10 * s, 200 * s, 100 * s, 10);
        p.noStroke();
        p.fill(0);
        p.textAlign(p.LEFT);
        p.textSize(12 * s);
        p.textStyle(p.BOLD);
        p.text(`Acceleration: ${a.toFixed(2)} m/s²`, 25 * s, 35 * s);
        p.text(`Velocity: ${p.newton2.v.toFixed(2)} m/s`, 25 * s, 55 * s);
        p.text(`Net Force: ${Fnet.toFixed(1)} N`, 25 * s, 75 * s);
        if (p.newton2.mode === 'incline') {
            p.text(`Incline: ${p.newton2.angle}°`, 25 * s, 95 * s);
        } else {
            p.text(`Hanging m: ${p.newton2.hangingMass} kg`, 25 * s, 95 * s);
        }
        p.textStyle(p.NORMAL);
    }

    function drawNewton3() {
        let s = p.canvasScale || 1;
        let o1 = p.newton3.obj1;
        let o2 = p.newton3.obj2;
        let cy = p.height / 2;
        
        if (p.newton3.isRunning) {
            o1.x += o1.v;
            o2.x += o2.v;

            // Elastic Collision Logic
            let boxSize = 50 * s;
            if (p.abs(o1.x - o2.x) < boxSize && !p.newton3.collided) {
                let v1 = o1.v;
                let v2 = o2.v;
                let m1 = o1.mass;
                let m2 = o2.mass;
                
                // Final velocities after elastic collision
                o1.v = (v1 * (m1 - m2) + 2 * m2 * v2) / (m1 + m2);
                o2.v = (v2 * (m2 - m1) + 2 * m1 * v1) / (m1 + m2);
                
                p.newton3.collided = true;
                p.newton3.collisionFrames = 30; // Show force for 30 frames
            }
        }

        let boxSize = 50 * s;
        p.fill(0, 123, 255);
        p.rect(o1.x - boxSize/2, cy - boxSize/2, boxSize, boxSize);
        p.fill(108, 117, 125);
        p.rect(o2.x - boxSize/2, cy - boxSize/2, boxSize, boxSize);

        p.fill(0);
        p.textAlign(p.CENTER);
        p.textSize(12 * s);
        p.text(`m1: ${o1.mass}kg`, o1.x, cy - boxSize/2 - 10 * s);
        p.text(`m2: ${o2.mass}kg`, o2.x, cy - boxSize/2 - 10 * s);
        p.text(`v1: ${o1.v.toFixed(2)}`, o1.x, cy + boxSize/2 + 20 * s);
        p.text(`v2: ${o2.v.toFixed(2)}`, o2.x, cy + boxSize/2 + 20 * s);

        if (p.newton3.collisionFrames > 0) {
            // Show Action-Reaction Force Arrows
            drawArrow(o1.x - boxSize/2, cy, -60 * s, 0, 'red'); // Force on Obj 1
            drawArrow(o2.x + boxSize/2, cy, 60 * s, 0, 'red');  // Force on Obj 2
            p.fill('red');
            p.text("Force", o1.x - boxSize/2 - 30 * s, cy - 10 * s);
            p.text("Force", o2.x + boxSize/2 + 30 * s, cy - 10 * s);
            p.newton3.collisionFrames--;
        }
    }

    function drawGravity() {
        let cx = p.width/2;
        let cy = p.height/2;
        let r = p.gravitySim.r;
        p.fill(50, 100, 255);
        p.circle(cx - r/2, cy, p.gravitySim.m1);
        p.fill(200, 100, 50);
        p.circle(cx + r/2, cy, p.gravitySim.m2);
        p.stroke(0, 50);
        p.line(cx - r/2, cy, cx + r/2, cy);
        let f = (p.gravitySim.m1 * p.gravitySim.m2) / (r * r) * 100;
        p.noStroke();
        p.fill(0);
        p.textAlign(p.CENTER);
        p.text(`Force: ${f.toFixed(4)} N`, cx, cy - 20);
    }

    function drawCoulomb() {
        let cy = p.height / 2;
        let x1 = p.coulomb.x1;
        let x2 = p.coulomb.x2;
        let r = p.abs(x1 - x2);
        p.coulomb.r = r;

        // Draw Field Waves
        p.noFill();
        for (let i = 0; i < 4; i++) {
            let offset = (p.frameCount + i * 20) % 80;
            // Waves for Charge 1
            p.stroke(p.coulomb.q1 > 0 ? 255 : 0, 0, p.coulomb.q1 > 0 ? 0 : 255, 150 - offset * 1.8);
            p.circle(x1, cy, 30 + offset);
            // Waves for Charge 2
            p.stroke(p.coulomb.q2 > 0 ? 255 : 0, 0, p.coulomb.q2 > 0 ? 0 : 255, 150 - offset * 1.8);
            p.circle(x2, cy, 30 + offset);
        }

        p.noStroke();
        p.fill(p.coulomb.q1 > 0 ? 'red' : 'blue');
        p.circle(x1, cy, 30);
        p.fill(p.coulomb.q2 > 0 ? 'red' : 'blue');
        p.circle(x2, cy, 30);

        let f = (p.coulomb.q1 * p.coulomb.q2) / (r * r) * 1000;
        
        // Draw force arrows
        if (p.abs(f) > 0.01) {
            let arrowLen = p.constrain(p.abs(f) * 50, 20, 100);
            let forceDir1 = (x1 < x2 ? -1 : 1) * (f > 0 ? 1 : -1);
            let forceDir2 = (x2 < x1 ? -1 : 1) * (f > 0 ? 1 : -1);
            
            drawArrow(x1, cy, forceDir1 * arrowLen, 0, 'green');
            drawArrow(x2, cy, forceDir2 * arrowLen, 0, 'green');

            p.fill('green');
            p.textSize(10);
            p.text(`${p.abs(f).toFixed(2)}N`, x1 + (forceDir1 * arrowLen / 2), cy - 20);
            p.text(`${p.abs(f).toFixed(2)}N`, x2 + (forceDir2 * arrowLen / 2), cy - 20);
            p.textSize(12);
        }

        p.fill(0);
        p.textAlign(p.CENTER);
        p.text(`Force: ${f.toFixed(2)} N`, p.width/2, cy - 80);
        p.text(`Distance: ${r.toFixed(0)} units`, p.width/2, cy - 60);
        p.text(f < 0 ? "ATTRACTIVE" : "REPULSIVE", p.width/2, cy + 80);
    }

    function drawArrow(x, y, dx, dy, col) {
        let s = p.canvasScale || 1;
        p.stroke(col);
        p.strokeWeight(3);
        p.line(x, y, x + dx, y + dy);
        let angle = p.atan2(dy, dx);
        p.push();
        p.translate(x + dx, y + dy);
        p.rotate(angle);
        p.line(0, 0, -10 * s, -5 * s);
        p.line(0, 0, -10 * s, 5 * s);
        p.pop();
        p.noStroke();
    }

    p.mousePressed = () => {
        if (activeSim === 'pendulum') {
            if (p.mouseX < 60 || p.mouseX > p.width - 60) {
                p.pendulum.mode = p.pendulum.mode === 'single' ? 'double' : 'single';
                updateUI();
                return;
            }
            let count = p.pendulum.mode === 'double' ? 2 : 1;
            for (let i = 0; i < count; i++) {
                let bob = p.pendulum.bobs[i];
                let x = bob.len * p.sin(p.radians(bob.angle)) + p.pendulum.origin.x;
                let y = bob.len * p.cos(p.radians(bob.angle)) + p.pendulum.origin.y;
                if (p.dist(p.mouseX, p.mouseY, x, y) < 40) bob.dragging = true;
            }
        } else if (activeSim === 'newton1') {
            if (p.dist(p.mouseX, p.mouseY, p.newton1.x, p.height - 75) < 30) {
                p.newton1.dragging = true;
            }
        } else if (activeSim === 'newton2') {
            // Navigation Arrows
            if (p.mouseX < 60) {
                p.newton2.mode = p.newton2.mode === 'incline' ? 'pulley' : 'incline';
                p.newton2.x = 0; p.newton2.v = 0;
                updateUI();
                return;
            }
            if (p.mouseX > p.width - 60) {
                p.newton2.mode = p.newton2.mode === 'incline' ? 'pulley' : 'incline';
                p.newton2.x = 0; p.newton2.v = 0;
                updateUI();
                return;
            }

            if (p.newton2.mode === 'incline') {
                let rad = p.radians(p.newton2.angle);
                let x1 = 100;
                let y1 = p.height - 100;
                let curX = x1 + p.newton2.x * p.cos(-rad);
                let curY = y1 + p.newton2.x * p.sin(-rad);
                if (p.dist(p.mouseX, p.mouseY, curX, curY - 30) < 40) {
                    p.newton2.dragging = true;
                }
            } else {
                let tableY = p.height - 250;
                let tableXEnd = p.width - 200;
                let pulleyX = tableXEnd;
                let initialDist = 300;
                let currentDist = initialDist - p.newton2.x; 
                let obj1X = pulleyX - currentDist;
                if (p.dist(p.mouseX, p.mouseY, obj1X, tableY - 15) < 40) {
                    p.newton2.dragging = true;
                }
            }
        } else if (activeSim === 'coulomb') {
            if (p.dist(p.mouseX, p.mouseY, p.coulomb.x1, p.height/2) < 30) p.coulomb.dragging1 = true;
            if (p.dist(p.mouseX, p.mouseY, p.coulomb.x2, p.height/2) < 30) p.coulomb.dragging2 = true;
        }
    };

    p.mouseDragged = () => {
        if (activeSim === 'pendulum') {
            for (let bob of p.pendulum.bobs) {
                if (bob.dragging) {
                    let dx = p.mouseX - p.pendulum.origin.x;
                    let dy = p.mouseY - p.pendulum.origin.y;
                    bob.angle = p.degrees(p.atan2(dx, dy));
                    bob.aVel = 0;
                }
            }
        } else if (activeSim === 'newton1') {
            if (p.newton1.dragging) {
                p.newton1.x = p.mouseX;
            }
        } else if (activeSim === 'coulomb') {
            if (p.coulomb.dragging1) p.coulomb.x1 = p.mouseX;
            if (p.coulomb.dragging2) p.coulomb.x2 = p.mouseX;
        }
    };

    p.mouseReleased = () => { 
        if (activeSim === 'pendulum') {
            p.pendulum.bobs.forEach(b => b.dragging = false);
        } else if (activeSim === 'newton1') {
            p.newton1.dragging = false;
        } else if (activeSim === 'newton2') {
            p.newton2.dragging = false;
        } else if (activeSim === 'coulomb') {
            p.coulomb.dragging1 = false;
            p.coulomb.dragging2 = false;
        }
    };

    p.touchStarted = () => {
        p.mousePressed();
        if (activeSim === 'pendulum' && p.mouseX >= 0 && p.mouseX <= p.width && p.mouseY >= 0 && p.mouseY <= p.height) {
            return false; // Prevent scrolling ONLY for pendulum
        }
    };

    p.touchMoved = () => {
        p.mouseDragged();
        if (activeSim === 'pendulum' && p.mouseX >= 0 && p.mouseX <= p.width && p.mouseY >= 0 && p.mouseY <= p.height) {
            return false; // Prevent scrolling ONLY for pendulum
        }
    };

    p.touchEnded = () => {
        p.mouseReleased();
    };
};

const lawData = {
    'pendulum': {
        title: "Simple Pendulum",
        def: "A simple pendulum consists of a mass hanging from a fixed point that swings back and forth under the influence of gravity.",
        symbols: [
            { s: "T", d: "Period (s) - Time taken for one complete swing." },
            { s: "L", d: "Length (m) - Distance from the pivot to the center of mass." },
            { s: "g", d: "Gravity (9.8 m/s²) - Acceleration due to gravity." }
        ]
    },
    'freefall': {
        title: "Free Fall",
        def: "Motion of an object where gravity is the only force acting upon it.",
        symbols: [
            { s: "s", d: "Displacement (m) - Vertical distance fallen." },
            { s: "u", d: "Initial Velocity (m/s) - Usually 0 when dropped." },
            { s: "g", d: "Gravity (9.8 m/s²) - Constant downward acceleration." },
            { s: "t", d: "Time (s) - Duration of the fall." }
        ]
    },
    'projectile': {
        title: "Projectile Motion",
        def: "Motion of an object thrown into the air, subject to only the acceleration of gravity.",
        symbols: [
            { s: "R", d: "Horizontal Range (m) - Total distance covered on x-axis." },
            { s: "v", d: "Launch Velocity (m/s) - Initial speed of projection." },
            { s: "θ", d: "Launch Angle (°) - Angle relative to horizontal." },
            { s: "g", d: "Gravity (9.8 m/s²) - Affects vertical component." }
        ]
    },
    'doppler': {
        title: "Doppler Effect",
        def: "The change in frequency or wavelength of a wave in relation to an observer who is moving relative to the wave source.",
        symbols: [
            { s: "f'", d: "Observed Frequency - The frequency heard by the observer." },
            { s: "f", d: "Source Frequency - The frequency emitted by the source." },
            { s: "v", d: "Wave Velocity - Speed of sound/light in the medium." },
            { s: "v_s", d: "Source Velocity - Speed at which source is moving." }
        ]
    },
    'newton1': {
        title: "Newton's 1st Law",
        def: "An object at rest stays at rest and an object in motion stays in motion with the same speed and in the same direction unless acted upon by an unbalanced force.",
        symbols: [
            { s: "ΣF = 0", d: "Net Force - When all forces are balanced, velocity is constant." },
            { s: "v", d: "Velocity - Remains constant (speed and direction)." }
        ]
    },
    'newton2': {
        title: "Newton's 2nd Law",
        def: "The acceleration of an object is directly proportional to the net force acting on it and inversely proportional to its mass.",
        symbols: [
            { s: "F", d: "Net Force (N) - Total force applied to the object." },
            { s: "m", d: "Mass (kg) - Quantity of matter in the object." },
            { s: "a", d: "Acceleration (m/s²) - Rate of change of velocity." }
        ]
    },
    'newton3': {
        title: "Newton's 3rd Law",
        def: "For every action, there is an equal and opposite reaction. Forces always exist in pairs.",
        symbols: [
            { s: "F₁₂", d: "Action Force - Force exerted by object 1 on object 2." },
            { s: "F₂₁", d: "Reaction Force - Force exerted by object 2 on object 1." },
            { s: "F₁₂ = -F₂₁", d: "Equal & Opposite - The forces are identical in magnitude but opposite in direction." }
        ]
    },
    'gravity': {
        title: "Universal Gravitation",
        def: "Every mass attracts every other mass in the universe with a force proportional to the product of their masses and inversely proportional to the square of the distance between them.",
        symbols: [
            { s: "F", d: "Gravitational Force (N) - Attractive force between masses." },
            { s: "G", d: "Gravitational Constant - 6.674 × 10⁻¹¹ m³kg⁻¹s⁻²." },
            { s: "m1, m2", d: "Masses (kg) - Mass of the two interacting bodies." },
            { s: "r", d: "Distance (m) - Separation between centers of mass." }
        ]
    },
    'coulomb': {
        title: "Coulomb's Law",
        def: "The magnitude of the electrostatic force of attraction or repulsion between two point charges is directly proportional to the product of the magnitudes of charges and inversely proportional to the square of the distance between them.",
        symbols: [
            { s: "F", d: "Electric Force (N) - Repulsive or attractive force." },
            { s: "k", d: "Coulomb Constant - 8.987 × 10⁹ N⋅m²/C²." },
            { s: "q1, q2", d: "Charges (C) - Quantity of electric charge." },
            { s: "r", d: "Distance (m) - Separation between charges." }
        ]
    }
};

function updateUI() {
    if (!physicsP5) return;
    const p = physicsP5;
    const formulaDisplay = document.getElementById('active-formula');
    const formulaDesc = document.getElementById('formula-desc');
    const controls = document.getElementById('physics-controls');
    const topButtons = document.getElementById('physics-top-buttons');

    const configs = {
        'pendulum': {
            f: "T = 2π√(L/g)", d: "Period of a Pendulum",
            c: () => {
                let isDouble = p.pendulum.mode === 'double';
                return `
                <div class="mb-2">
                    <label class="small fw-bold">Length (L): <span id="len-val">${p.pendulum.bobs[0].len}</span></label>
                    <input type="range" class="form-range" id="pen-len" min="50" max="350" value="${p.pendulum.bobs[0].len}">
                </div>
                <div class="mb-2">
                    <label class="small fw-bold">Mass 1 (m1): <span id="mass1-val">${p.pendulum.bobs[0].mass}</span>kg</label>
                    <input type="range" class="form-range" id="pen-mass1" min="1" max="15" value="${p.pendulum.bobs[0].mass}">
                </div>
                ${isDouble ? `
                <div class="mb-2">
                    <label class="small fw-bold">Mass 2 (m2): <span id="mass2-val">${p.pendulum.bobs[1].mass}</span>kg</label>
                    <input type="range" class="form-range" id="pen-mass2" min="1" max="15" value="${p.pendulum.bobs[1].mass}">
                </div>` : ''}`;
            }
        },
        'freefall': {
            f: "s = ut + ½gt²", d: "Distance in Free Fall",
            c: () => `
                <button class="btn btn-danger btn-sm w-100 mb-2" id="drop-btn">DROP OBJECT</button>
                <button class="btn btn-secondary btn-sm w-100 mb-3" id="reset-freefall">RESET</button>
                <div class="mb-2">
                    <label class="small fw-bold">Mass (m): <span id="ff-mass-val">${p.freefall.mass}</span>kg</label>
                    <input type="range" class="form-range" id="ff-mass" min="1" max="50" value="${p.freefall.mass}">
                </div>
                <div class="mb-2">
                    <label class="small fw-bold">Height (h): <span id="ff-height-val">${p.freefall.initialHeight}</span>m</label>
                    <input type="range" class="form-range" id="ff-height" min="10" max="${p.freefall.maxHeight}" value="${p.freefall.initialHeight}">
                </div>`
        },
        'projectile': {
            f: "R = (v² sin 2θ) / g", d: "Horizontal Range",
            c: () => `
                <div class="mb-2">
                    <label class="small fw-bold">Velocity (v): <span id="v-val">${p.projectile.v0}</span></label>
                    <input type="range" class="form-range" id="proj-v" min="10" max="100" value="${p.projectile.v0}">
                </div>
                <div class="mb-2">
                    <label class="small fw-bold">Angle (θ): <span id="ang-val">${p.projectile.angle}</span></label>
                    <input type="range" class="form-range" id="proj-ang" min="0" max="90" value="${p.projectile.angle}">
                </div>
                <button class="btn btn-success btn-sm w-100" id="launch-btn">LAUNCH PROJECTILE</button>`
        },
        'doppler': {
            f: "f' = f [v / (v ∓ v_s)]", d: "Doppler Frequency Shift",
            c: () => `
                <div class="mb-2">
                    <label class="small fw-bold">Source Velocity: <span id="dv-val">${p.doppler.sourceV}</span></label>
                    <input type="range" class="form-range" id="doppler-v" min="0" max="1.5" step="0.1" value="${p.doppler.sourceV}">
                </div>
                <div class="mb-2">
                    <label class="small fw-bold">Observer Position: <span id="do-val">${p.doppler.observerX}</span></label>
                    <input type="range" class="form-range" id="doppler-obs" min="50" max="${p.width - 50}" step="10" value="${p.doppler.observerX}">
                </div>`
        },
        'newton1': {
            f: "v = constant (ΣF = 0)", d: "Inertia",
            c: () => `
                <div class="mb-2">
                    <label class="small fw-bold">Push Force: <span id="n1f-val">${p.newton1.force}</span></label>
                    <input type="range" class="form-range" id="n1-f" min="-20" max="20" value="${p.newton1.force}">
                </div>
                <button class="btn btn-primary btn-sm w-100 mb-2" id="n1-push">GIVE A PUSH</button>
                <div class="mb-2">
                    <label class="small fw-bold">Friction: <span id="fric-val">${p.newton1.friction}</span></label>
                    <input type="range" class="form-range" id="n1-fric" min="0" max="0.1" step="0.01" value="${p.newton1.friction}">
                </div>`
        },
        'newton2': {
            f: "ΣF = m × a", d: p.newton2.mode === 'incline' ? "Force on an Incline" : "Pulley System (Atwood Machine)",
            b: () => `
                <div class="d-flex gap-2 mb-3">
                    <button class="btn btn-primary btn-sm w-100" id="n2-pull">${p.newton2.isPulling ? "STOP" : "PULL"}</button>
                    <button class="btn btn-secondary btn-sm w-100" id="n2-reset">RESET</button>
                </div>`,
            c: () => `
                <div class="mb-2">
                    <label class="small fw-bold">User Pull Force (F): <span id="n2f-val">${p.newton2.force}</span> N</label>
                    <input type="range" class="form-range" id="n2-f" min="0" max="200" value="${p.newton2.force}">
                </div>
                <div class="mb-2">
                    <label class="small fw-bold">Table Mass (m1): <span id="n2m-val">${p.newton2.mass}</span> kg</label>
                    <input type="range" class="form-range" id="n2-m" min="1" max="50" value="${p.newton2.mass}">
                </div>
                ${p.newton2.mode === 'incline' ? `
                <div class="mb-2">
                    <label class="small fw-bold">Incline Angle (θ): <span id="n2a-val">${p.newton2.angle}</span>°</label>
                    <input type="range" class="form-range" id="n2-a" min="0" max="45" value="${p.newton2.angle}">
                </div>` : `
                <div class="mb-2">
                    <label class="small fw-bold">Hanging Mass (m2): <span id="n2hm-val">${p.newton2.hangingMass}</span> kg</label>
                    <input type="range" class="form-range" id="n2-hm" min="1" max="50" value="${p.newton2.hangingMass}">
                </div>`}
                <div class="mb-2">
                    <label class="small fw-bold">Friction (μ): <span id="n2mu-val">${p.newton2.mu}</span></label>
                    <input type="range" class="form-range" id="n2-mu" min="0" max="0.5" step="0.01" value="${p.newton2.mu}">
                </div>`
        },
        'newton3': {
            f: "F₁₂ = -F₂₁", d: "Action & Reaction",
            c: () => `
                <div class="mb-2">
                    <label class="small fw-bold">Mass 1: <span id="n3m1-val">${p.newton3.obj1.mass}</span>kg</label>
                    <input type="range" class="form-range" id="n3-m1" min="1" max="50" value="${p.newton3.obj1.mass}">
                </div>
                <div class="mb-2">
                    <label class="small fw-bold">Mass 2: <span id="n3m2-val">${p.newton3.obj2.mass}</span>kg</label>
                    <input type="range" class="form-range" id="n3-m2" min="1" max="50" value="${p.newton3.obj2.mass}">
                </div>
                <button class="btn btn-info btn-sm w-100" id="n3-reset">COLLIDE</button>`
        },
        'gravity': {
            f: "F = G(m₁m₂)/r²", d: "Newton's Gravitational Law",
            c: () => `
                <div class="mb-2">
                    <label class="small fw-bold">Mass 1: <span id="gm1-val">${p.gravitySim.m1}</span></label>
                    <input type="range" class="form-range" id="g-m1" min="10" max="100" value="${p.gravitySim.m1}">
                </div>
                <div class="mb-2">
                    <label class="small fw-bold">Mass 2: <span id="gm2-val">${p.gravitySim.m2}</span></label>
                    <input type="range" class="form-range" id="g-m2" min="10" max="100" value="${p.gravitySim.m2}">
                </div>
                <div class="mb-2">
                    <label class="small fw-bold">Distance (r): <span id="gr-val">${p.gravitySim.r}</span></label>
                    <input type="range" class="form-range" id="g-r" min="100" max="400" value="${p.gravitySim.r}">
                </div>`
        },
        'coulomb': {
            f: "F = k(q₁q₂)/r²", d: "Coulomb's Law",
            c: () => `
                <div class="mb-2">
                    <label class="small fw-bold">Charge 1 (q1): <span id="cq1-val">${p.coulomb.q1}</span></label>
                    <input type="range" class="form-range" id="c-q1" min="-20" max="20" value="${p.coulomb.q1}">
                </div>
                <div class="mb-2">
                    <label class="small fw-bold">Charge 2 (q2): <span id="cq2-val">${p.coulomb.q2}</span></label>
                    <input type="range" class="form-range" id="c-q2" min="-20" max="20" value="${p.coulomb.q2}">
                </div>`
        }
    };

    const config = configs[activeSim];
    if (config) {
        formulaDisplay.innerText = config.f;
        formulaDesc.innerText = config.d;
        controls.innerHTML = config.c();
        topButtons.innerHTML = config.b ? config.b() : '';
        bindEvents();
    }
}

function bindEvents() {
    if (!physicsP5) return;
    const p = physicsP5;

    // Formula Click for Pop-up
    document.getElementById('physics-formula-display').onclick = () => {
        const data = lawData[activeSim];
        if (!data) return;
        document.getElementById('lawTitle').innerText = data.title;
        document.getElementById('lawDefinition').innerText = data.def;
        const symList = document.getElementById('lawSymbols');
        symList.innerHTML = '';
        data.symbols.forEach(s => {
            const li = document.createElement('li');
            li.className = 'list-group-item';
            li.innerHTML = `<strong>${s.s}</strong>: ${s.d}`;
            symList.appendChild(li);
        });
        const modal = new bootstrap.Modal(document.getElementById('lawModal'));
        modal.show();
    };

    if (activeSim === 'pendulum') {
        document.getElementById('pen-len').oninput = (e) => {
            let val = parseInt(e.target.value);
            p.pendulum.bobs.forEach(b => b.len = val);
            document.getElementById('len-val').innerText = val;
        };
        document.getElementById('pen-mass1').oninput = (e) => {
            p.pendulum.bobs[0].mass = parseInt(e.target.value);
            document.getElementById('mass1-val').innerText = e.target.value;
        };
        if (p.pendulum.mode === 'double') {
            document.getElementById('pen-mass2').oninput = (e) => {
                p.pendulum.bobs[1].mass = parseInt(e.target.value);
                document.getElementById('mass2-val').innerText = e.target.value;
            };
        }
    } else if (activeSim === 'freefall') {
        document.getElementById('drop-btn').onclick = () => {
            if (p.freefall.landed || !p.freefall.isFalling) {
                p.freefall.y = p.freefall.floor - (p.freefall.initialHeight * 10);
                p.freefall.v = 0;
                p.freefall.landed = false;
                p.freefall.isFalling = true;
                p.freefall.height = p.freefall.initialHeight;
            }
        };
        document.getElementById('reset-freefall').onclick = () => {
            p.freefall.initialHeight = 40;
            p.freefall.y = p.freefall.floor - (p.freefall.initialHeight * 10);
            p.freefall.v = 0;
            p.freefall.isFalling = false;
            p.freefall.landed = false;
            updateUI();
        };
        document.getElementById('ff-mass').oninput = (e) => {
            p.freefall.mass = parseInt(e.target.value);
            document.getElementById('ff-mass-val').innerText = e.target.value;
        };
        document.getElementById('ff-height').oninput = (e) => {
            p.freefall.initialHeight = parseInt(e.target.value);
            document.getElementById('ff-height-val').innerText = e.target.value;
            if (!p.freefall.isFalling) {
                p.freefall.y = p.freefall.floor - (p.freefall.initialHeight * 10);
                p.freefall.landed = false;
            }
        };
    } else if (activeSim === 'projectile') {
        document.getElementById('proj-v').oninput = (e) => {
            p.projectile.v0 = parseInt(e.target.value);
            document.getElementById('v-val').innerText = e.target.value;
        };
        document.getElementById('proj-ang').oninput = (e) => {
            p.projectile.angle = parseInt(e.target.value);
            document.getElementById('ang-val').innerText = e.target.value;
        };
        document.getElementById('launch-btn').onclick = () => {
            p.projectile.t = 0;
            p.projectile.path = [];
            p.projectile.isFlying = true;
            p.projectile.landed = false;
            p.projectile.peak = {x: 0, y: 0, h: 0};
        };
    } else if (activeSim === 'doppler') {
        document.getElementById('doppler-v').oninput = (e) => {
            p.doppler.sourceV = parseFloat(e.target.value);
            document.getElementById('dv-val').innerText = e.target.value;
        };
        document.getElementById('doppler-obs').oninput = (e) => {
            p.doppler.observerX = parseInt(e.target.value);
            document.getElementById('do-val').innerText = e.target.value;
        };
    } else if (activeSim === 'newton1') {
        document.getElementById('n1-f').oninput = (e) => {
            p.newton1.force = parseInt(e.target.value);
            document.getElementById('n1f-val').innerText = e.target.value;
        };
        document.getElementById('n1-push').onclick = () => { 
            p.newton1.v = p.newton1.force;
            p.newton1.pushFrames = 10;
        };
        document.getElementById('n1-fric').oninput = (e) => {
            p.newton1.friction = parseFloat(e.target.value);
            document.getElementById('fric-val').innerText = e.target.value;
        };
    } else if (activeSim === 'newton2') {
        document.getElementById('n2-f').oninput = (e) => {
            p.newton2.force = parseInt(e.target.value);
            document.getElementById('n2f-val').innerText = e.target.value;
        };
        document.getElementById('n2-m').oninput = (e) => {
            p.newton2.mass = parseInt(e.target.value);
            document.getElementById('n2m-val').innerText = e.target.value;
        };
        if (document.getElementById('n2-a')) {
            document.getElementById('n2-a').oninput = (e) => {
                p.newton2.angle = parseInt(e.target.value);
                document.getElementById('n2a-val').innerText = e.target.value;
            };
        }
        if (document.getElementById('n2-hm')) {
            document.getElementById('n2-hm').oninput = (e) => {
                p.newton2.hangingMass = parseInt(e.target.value);
                document.getElementById('n2hm-val').innerText = e.target.value;
            };
        }
        document.getElementById('n2-mu').oninput = (e) => {
            p.newton2.mu = parseFloat(e.target.value);
            document.getElementById('n2mu-val').innerText = e.target.value;
        };
        document.getElementById('n2-pull').onclick = () => {
            p.newton2.isPulling = !p.newton2.isPulling;
            document.getElementById('n2-pull').innerText = p.newton2.isPulling ? "STOP" : "PULL";
            document.getElementById('n2-pull').className = p.newton2.isPulling ? "btn btn-danger btn-sm w-100" : "btn btn-primary btn-sm w-100";
        };
        document.getElementById('n2-reset').onclick = () => {
            p.newton2.x = 0;
            p.newton2.v = 0;
            p.newton2.isPulling = false;
            updateUI();
        };
    } else if (activeSim === 'newton3') {
        document.getElementById('n3-m1').oninput = (e) => {
            p.newton3.obj1.mass = parseInt(e.target.value);
            document.getElementById('n3m1-val').innerText = e.target.value;
        };
        document.getElementById('n3-m2').oninput = (e) => {
            p.newton3.obj2.mass = parseInt(e.target.value);
            document.getElementById('n3m2-val').innerText = e.target.value;
        };
        document.getElementById('n3-reset').onclick = () => {
            if (!p.newton3.isRunning) {
                p.newton3.isRunning = true;
                document.getElementById('n3-reset').innerText = "RESET";
            } else {
                p.newton3.obj1.x = p.width * 0.25;
                p.newton3.obj1.v = 2 * p.canvasScale;
                p.newton3.obj2.x = p.width * 0.75;
                p.newton3.obj2.v = -2 * p.canvasScale;
                p.newton3.collided = false;
                p.newton3.collisionFrames = 0;
                p.newton3.isRunning = false;
                document.getElementById('n3-reset').innerText = "COLLIDE";
            }
        };
    }
 else if (activeSim === 'gravity') {
        document.getElementById('g-m1').oninput = (e) => {
            p.gravitySim.m1 = parseInt(e.target.value);
            document.getElementById('gm1-val').innerText = e.target.value;
        };
        document.getElementById('g-m2').oninput = (e) => {
            p.gravitySim.m2 = parseInt(e.target.value);
            document.getElementById('gm2-val').innerText = e.target.value;
        };
        document.getElementById('g-r').oninput = (e) => {
            p.gravitySim.r = parseInt(e.target.value);
            document.getElementById('gr-val').innerText = e.target.value;
        };
    } else if (activeSim === 'coulomb') {
        document.getElementById('c-q1').oninput = (e) => {
            p.coulomb.q1 = parseInt(e.target.value);
            document.getElementById('cq1-val').innerText = e.target.value;
        };
        document.getElementById('c-q2').oninput = (e) => {
            p.coulomb.q2 = parseInt(e.target.value);
            document.getElementById('cq2-val').innerText = e.target.value;
        };
    }
}

    // === CUSTOM DROPDOWN LOGIC ===
    const dropdown = document.getElementById('physics-dropdown');
    const menu = document.getElementById('physics-menu');
    const options = document.querySelectorAll('.option');

    if (dropdown && menu) {
        dropdown.onclick = (e) => {
            e.stopPropagation();
            menu.classList.toggle('show');
            dropdown.classList.toggle('active');
        };

        options.forEach(option => {
            option.onclick = () => {
                activeSim = option.dataset.sim;
                
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
                
                updateUI();
            };
        });

        document.addEventListener('click', () => {
            menu.classList.remove('show');
            dropdown.classList.remove('active');
        });
    }

    // Keep the old button logic just in case there are any other .physics-btn in the footer
    document.querySelectorAll('.physics-btn').forEach(btn => {
        btn.onclick = (e) => {
            activeSim = e.target.closest('.physics-btn').dataset.sim;
            // Update custom dropdown if needed
            const matchingOption = document.querySelector(`.option[data-sim="${activeSim}"]`);
            if (matchingOption) matchingOption.click();
            else updateUI();
        };
    });

physicsP5 = new p5(physicsLab, 'physics-canvas-container');
