const pendulumSketch = (p) => {
  let angle;
  let aVel;
  let aAcc;
  let len;
  let origin;
  let gravity = 0.4;

  p.setup = () => {
    p.createCanvas(400, 300);
    angle = p.PI / 4;
    aVel = 0.0;
    aAcc = 0.0;
    len = 180;
    origin = p.createVector(200, 0);
  };

  p.draw = () => {
    p.background(255);
    aAcc = (-1 * gravity / len) * p.sin(angle);
    aVel += aAcc;
    angle += aVel;
    aVel *= 0.995;
    
    let x = len * p.sin(angle) + origin.x;
    let y = len * p.cos(angle) + origin.y;
    
    p.stroke(0);
    p.strokeWeight(2);
    p.line(origin.x, origin.y, x, y);
    p.fill(127);
    p.circle(x, y, 32);
  };
};

new p5(pendulumSketch, 'pendulum-container');
