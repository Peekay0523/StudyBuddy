const dopplerSketch = (p) => {
    let carX = 0;
    let waves = [];

    p.setup = () => {
        p.createCanvas(400, 200);
    };

    p.draw = () => {
        p.background(255);
        
        // Road
        p.stroke(200);
        p.line(0, 150, p.width, 150);

        // Update car position
        carX += 2;
        if (carX > p.width + 100) carX = -100;

        // Emit waves
        if (p.frameCount % 20 === 0) {
            waves.push({ x: carX + 30, r: 0 });
        }

        // Draw and update waves
        p.noFill();
        p.strokeWeight(1);
        for (let i = waves.length - 1; i >= 0; i--) {
            let w = waves[i];
            w.r += 2;
            
            // Color based on position relative to car
            if (w.x > carX + 30) {
                p.stroke(255, 0, 0, 255 - w.r * 2); // Redshift (Towards)
            } else {
                p.stroke(0, 0, 255, 255 - w.r * 2); // Blueshift (Away)
            }
            
            p.circle(w.x, 100, w.r * 2);
            
            if (w.r > 200) waves.splice(i, 1);
        }

        // Draw Ambulance
        p.fill(255);
        p.stroke(0);
        p.rect(carX, 85, 60, 30);
        p.fill(255, 0, 0);
        if (p.frameCount % 10 < 5) p.rect(carX + 40, 75, 10, 10);
        p.fill(0);
        p.textAlign(p.CENTER);
        p.text("AMBULANCE", carX + 30, 105);
    };
};

const projectileSketch = (p) => {
    let x = 0, y = 0;
    let v0 = 60, angle = 45;
    let t = 0;
    let points = [];
    let isFlying = false;

    p.setup = () => {
        p.createCanvas(400, 200);
        p.select('#launchBtn').mousePressed(() => {
            x = 0; y = 0; t = 0; points = [];
            v0 = p.select('#velocityInput').value();
            angle = p.select('#angleInput').value();
            isFlying = true;
        });
    };

    p.draw = () => {
        p.background(245);
        p.translate(20, 180);
        
        // Axis
        p.stroke(0);
        p.line(0, 0, 360, 0);
        p.line(0, 0, 0, -160);

        if (isFlying) {
            let vx = v0 * p.cos(p.radians(angle));
            let vy = v0 * p.sin(p.radians(angle));
            let g = 9.8;

            x = vx * t;
            y = vy * t - 0.5 * g * t * t;

            if (y >= 0) {
                points.push({ x: x, y: -y });
                t += 0.1;
            } else {
                isFlying = false;
            }
        }

        // Draw Path
        p.noFill();
        p.stroke(255, 0, 0);
        p.beginShape();
        points.forEach(pt => p.vertex(pt.x, pt.y));
        p.endShape();

        // Draw Projectile
        if (points.length > 0) {
            let last = points[points.length - 1];
            p.fill(0);
            p.circle(last.x, last.y, 10);
        }
    };
};

new p5(dopplerSketch, 'doppler-container');
new p5(projectileSketch, 'projectile-container');
