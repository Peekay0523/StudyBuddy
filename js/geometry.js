const geometrySketch = (p) => {
    let radius = 150;
    let centerX = 200;
    let centerY = 200;
    let points = [];
    let activePoint = null;
    let currentTheorem = 'same_segment';

    p.setup = () => {
        let container = document.getElementById('geometry-canvas-container');
        let w = container.offsetWidth;
        let h = p.min(400, p.windowHeight * 0.5);
        p.createCanvas(w, h);
        centerX = p.width / 2;
        centerY = p.height / 2;
        radius = p.min(w, h) * 0.4;
        
        // Initialize points on the circle
        points.push({ label: 'A', angle: p.PI * 0.8, color: '#007bff' });
        points.push({ label: 'B', angle: p.PI * 0.2, color: '#007bff' });
        points.push({ label: 'C', angle: p.PI * 1.5, color: '#ff4757' });
        points.push({ label: 'D', angle: p.PI * 1.2, color: '#2ed573' });
        points.push({ label: 'O', angle: 0, isCenter: true, color: '#ffa502' });

        // Bind buttons
        const btnGroup = document.querySelectorAll('.theorem-btn');
        btnGroup.forEach(btn => {
            btn.onclick = (e) => {
                currentTheorem = e.target.closest('.theorem-btn').dataset.theorem;
                btnGroup.forEach(b => b.classList.remove('active'));
                e.target.closest('.theorem-btn').classList.add('active');
            };
        });
    };

    p.windowResized = () => {
        let container = document.getElementById('geometry-canvas-container');
        if (container) {
            let w = container.offsetWidth;
            let h = p.min(400, p.windowHeight * 0.5);
            p.resizeCanvas(w, h);
            centerX = p.width / 2;
            centerY = p.height / 2;
            radius = p.min(w, h) * 0.4;
        }
    };

    p.draw = () => {
        p.background(255);
        p.noFill();
        p.stroke(200);
        p.circle(centerX, centerY, radius * 2);

        // Draw Center
        p.fill(0);
        p.circle(centerX, centerY, 5);
        p.text("O", centerX + 5, centerY - 5);

        // Update positions based on angles
        points.forEach(pt => {
            if (!pt.isCenter) {
                pt.x = centerX + radius * p.cos(pt.angle);
                pt.y = centerY + radius * p.sin(pt.angle);
            } else {
                pt.x = centerX;
                pt.y = centerY;
            }
        });

        p.strokeWeight(2);
        
        if (currentTheorem === 'same_segment') {
            drawSameSegment(p, points);
        } else if (currentTheorem === 'center_circumference') {
            drawCenterCircumference(p, points);
        } else if (currentTheorem === 'cyclic_quad') {
            drawCyclicQuad(p, points);
        } else if (currentTheorem === 'semicircle') {
            drawSemicircle(p, points);
        }

        // Draw points
        points.forEach(pt => {
            if (pt.isCenter && currentTheorem !== 'center_circumference') return;
            p.fill(pt.color);
            p.noStroke();
            p.circle(pt.x, pt.y, 12);
            p.fill(0);
            p.textAlign(p.CENTER);
            p.text(pt.label, pt.x, pt.y - 15);
        });
    };

    function drawSameSegment(p, pts) {
        let a = pts[0], b = pts[1], c = pts[2], d = pts[3];
        // Chord AB
        p.stroke(0);
        p.line(a.x, a.y, b.x, b.y);
        // Angle ACB
        p.stroke('#ff4757');
        p.line(a.x, a.y, c.x, c.y);
        p.line(b.x, b.y, c.x, c.y);
        // Angle ADB
        p.stroke('#2ed573');
        p.line(a.x, a.y, d.x, d.y);
        p.line(b.x, b.y, d.x, d.y);
        
        p.fill(0);
        p.noStroke();
        p.text("Angles at C and D are equal", 200, 380);
    }

    function drawCenterCircumference(p, pts) {
        let a = pts[0], b = pts[1], c = pts[2], o = pts[4];
        p.stroke(0);
        p.line(a.x, a.y, b.x, b.y);
        // Angle AOB (Center)
        p.stroke('#ffa502');
        p.line(a.x, a.y, o.x, o.y);
        p.line(b.x, b.y, o.x, o.y);
        // Angle ACB (Circumference)
        p.stroke('#ff4757');
        p.line(a.x, a.y, c.x, c.y);
        p.line(b.x, b.y, c.x, c.y);
        
        p.fill(0);
        p.noStroke();
        p.text("Angle at Center = 2 x Angle at Circumference", 200, 380);
    }

    function drawCyclicQuad(p, pts) {
        p.stroke(0);
        for(let i=0; i<4; i++) {
            let p1 = pts[i];
            let p2 = pts[(i+1)%4];
            p.line(p1.x, p1.y, p2.x, p2.y);
        }
        p.fill(0);
        p.noStroke();
        p.text("Opposite angles sum to 180°", 200, 380);
    }

    function drawSemicircle(p, pts) {
        let a = pts[0];
        // Diameter
        let bX = centerX - (a.x - centerX);
        let bY = centerY - (a.y - centerY);
        p.stroke(0);
        p.line(a.x, a.y, bX, bY);
        // Point C
        let c = pts[2];
        p.stroke('#ff4757');
        p.line(a.x, a.y, c.x, c.y);
        p.line(bX, bY, c.x, c.y);
        
        p.fill(0);
        p.noStroke();
        p.text("Angle in a semi-circle is 90°", 200, 380);
    }

    p.mousePressed = () => {
        points.forEach(pt => {
            if (pt.isCenter) return;
            let d = p.dist(p.mouseX, p.mouseY, pt.x, pt.y);
            if (d < 15) {
                activePoint = pt;
            }
        });
    };

    p.mouseDragged = () => {
        if (activePoint) {
            let dx = p.mouseX - centerX;
            let dy = p.mouseY - centerY;
            activePoint.angle = p.atan2(dy, dx);
        }
    };

    p.mouseReleased = () => {
        activePoint = null;
    };

    p.touchStarted = () => {
        p.mousePressed();
        if (p.mouseX >= 0 && p.mouseX <= p.width && p.mouseY >= 0 && p.mouseY <= p.height) {
            return false; // Prevent scrolling
        }
    };

    p.touchMoved = () => {
        p.mouseDragged();
        if (p.mouseX >= 0 && p.mouseX <= p.width && p.mouseY >= 0 && p.mouseY <= p.height) {
            return false; // Prevent scrolling
        }
    };

    p.touchEnded = () => {
        p.mouseReleased();
    };
};

new p5(geometrySketch, 'geometry-canvas-container');
