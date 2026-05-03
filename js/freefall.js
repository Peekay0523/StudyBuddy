const freeFallSketch = (p) => {
  let y = 0;
  let velocity = 0;
  let accel = 0.2;
  let isFalling = false;
  let startY = 20;

  p.setup = () => {
    p.createCanvas(200, 400);
    y = startY;
    
    // Attach buttons to functions
    document.getElementById('startFall').onclick = () => isFalling = true;
    document.getElementById('resetFall').onclick = () => {
      y = startY;
      velocity = 0;
      isFalling = false;
    };
  };

  p.draw = () => {
    p.background(240);
    if (isFalling) {
      velocity += accel;
      y += velocity;
    }
    if (y > p.height - 20) {
      y = p.height - 20;
      isFalling = false;
      velocity = 0;
    }
    p.fill(255, 0, 0);
    p.noStroke();
    p.circle(p.width/2, y, 20);
    p.fill(0);
    p.text("Velocity: " + velocity.toFixed(2), 10, 20);
    p.text("Height: " + (p.height - y - 20).toFixed(0), 10, 40);
  };
};

new p5(freeFallSketch, 'freefall-container');
