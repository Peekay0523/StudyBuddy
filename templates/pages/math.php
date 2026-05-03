<!DOCTYPE html>
<?php
include __DIR__ . '/../layouts/header.php';
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mathematics - Science & Maths App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/p5.js/1.4.0/p5.js"></script>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif !important;
            color: #333 !important;
        }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="simulate"><i class="fas fa-arrow-left me-2"></i>Back to Simulations</a>
        </div>
    </nav>

    <div class="container mt-4 pb-5">
        <h1 class="text-center mb-4">Mathematics Lab</h1>

        <!-- Euclidean Geometry Section -->
        <section class="mb-5">
            <h2 class="h4">1. Euclidean Geometry</h2>
            <div class="row">
                <div class="col-md-4">
                    <p class="text-muted small">Select a theorem and drag points on the circle!</p>
                    <div class="list-group list-group-flush mb-4 shadow-sm rounded">
                        <button class="list-group-item list-group-item-action theorem-btn active thin-btn" data-theorem="same_segment">
                            <i class="fas fa-circle-notch me-2 text-primary"></i>Angles in same segment
                        </button>
                        <button class="list-group-item list-group-item-action theorem-btn thin-btn" data-theorem="center_circumference">
                            <i class="fas fa-dot-circle me-2 text-primary"></i>Angle at center vs circumference
                        </button>
                        <button class="list-group-item list-group-item-action theorem-btn thin-btn" data-theorem="semicircle">
                            <i class="fas fa-adjust me-2 text-primary"></i>Angle in semi-circle
                        </button>
                        <button class="list-group-item list-group-item-action theorem-btn thin-btn" data-theorem="cyclic_quad">
                            <i class="fas fa-draw-polygon me-2 text-primary"></i>Cyclic Quadrilateral
                        </button>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="card shadow-sm border-0">
                        <div id="geometry-canvas-container" class="text-center bg-white" style="min-height: 400px;"></div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Trigonometry Section -->
        <section class="mb-5">
            <h2 class="h4">2. Trigonometry Lab</h2>
            <div class="row">
                <div class="col-md-4">
                    <p class="text-muted small">Explore waves and their properties.</p>
                    <div class="list-group list-group-flush mb-4 shadow-sm rounded">
                        <button class="list-group-item list-group-item-action trig-btn active thin-btn" data-type="sin">
                            <i class="fas fa-wave-square me-2 text-info"></i>Sine: y = a sin(bx) + q
                        </button>
                        <button class="list-group-item list-group-item-action trig-btn thin-btn" data-type="cos">
                            <i class="fas fa-wave-square me-2 text-info" style="transform: rotate(90deg);"></i>Cosine: y = a cos(bx) + q
                        </button>
                        <button class="list-group-item list-group-item-action trig-btn thin-btn" data-type="log">
                            <i class="fas fa-stream me-2 text-secondary"></i>Logarithmic: y = log<sub>b</sub>(x)
                        </button>
                    </div>

                    <div class="card shadow-sm border-0 bg-white p-3 mb-4">
                        <div class="text-center p-2 bg-light rounded mb-3">
                            <span class="text-muted small d-block mb-1">Generated Formula</span>
                            <div id="trig-formula" class="fw-bold text-primary h5 mb-0">y = 1 sin(1x) + 0</div>
                        </div>
                        <div id="trig-sliders">
                            <div class="mb-3 trig-param" data-for="sin cos">
                                <label class="form-label small fw-bold">Amplitude (a)</label>
                                <input type="range" class="form-range" id="trig-a" min="1" max="5" step="1" value="1">
                            </div>
                            <div class="mb-3 trig-param" data-for="sin cos">
                                <label class="form-label small fw-bold">Frequency (b)</label>
                                <input type="range" class="form-range" id="trig-b" min="1" max="5" step="1" value="1">
                            </div>
                            <div class="mb-3 trig-param" data-for="sin cos">
                                <label class="form-label small fw-bold">Vertical Shift (q)</label>
                                <input type="range" class="form-range" id="trig-q" min="-3" max="3" step="1" value="0">
                            </div>
                            <div class="mb-3 trig-param d-none" data-for="log">
                                <label class="form-label small fw-bold">Base (b)</label>
                                <input type="range" class="form-range" id="trig-base" min="2" max="10" step="1" value="10">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="card shadow-sm border-0" style="height: 400px;">
                        <div class="card-body">
                            <canvas id="trigChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Algebra & Graphs Section -->
        <section class="mb-5">
            <h2 class="h4">3. Algebra & Graphs Lab</h2>
            <div class="row">
                <div class="col-md-4">
                    <p class="text-muted small">Explore linear, quadratic, and other functions.</p>
                    <div class="list-group list-group-flush mb-4 shadow-sm rounded">
                        <button class="list-group-item list-group-item-action alg-btn active thin-btn" data-type="linear">
                            <i class="fas fa-slash me-2 text-success"></i>Straight Line: y = mx + c
                        </button>
                        <button class="list-group-item list-group-item-action alg-btn thin-btn" data-type="parabola">
                            <i class="fas fa-chart-area me-2 text-warning"></i>Parabola: y = a(x-p)² + q
                        </button>
                        <button class="list-group-item list-group-item-action alg-btn thin-btn" data-type="hyperbola">
                            <i class="fas fa-bezier-curve me-2 text-danger"></i>Hyperbola: y = a/(x-p) + q
                        </button>
                        <button class="list-group-item list-group-item-action alg-btn thin-btn" data-type="exponential">
                            <i class="fas fa-chart-line me-2 text-primary"></i>Exponential: y = a·b^(x-p) + q
                        </button>
                    </div>

                    <div class="card shadow-sm border-0 bg-white p-3 mb-4">
                        <div class="text-center p-2 bg-light rounded mb-3">
                            <span class="text-muted small d-block mb-1">Generated Formula</span>
                            <div id="alg-formula" class="fw-bold text-success h5 mb-0">y = 1x + 0</div>
                        </div>
                        <div id="alg-sliders">
                            <div class="mb-3 alg-param d-none" data-for="parabola hyperbola exponential">
                                <label class="form-label small fw-bold">Stretch/Amplitude (a)</label>
                                <input type="range" class="form-range" id="alg-a" min="-4" max="4" step="1" value="1">
                            </div>
                            <div class="mb-3 alg-param" data-for="linear">
                                <label class="form-label small fw-bold">Gradient (m)</label>
                                <input type="range" class="form-range" id="alg-m" min="-5" max="5" step="1" value="1">
                            </div>
                            <div class="mb-3 alg-param d-none" data-for="parabola hyperbola exponential">
                                <label class="form-label small fw-bold">Horizontal Shift (p)</label>
                                <input type="range" class="form-range" id="alg-p" min="-5" max="5" step="1" value="0">
                            </div>
                            <div class="mb-3 alg-param" data-for="linear parabola hyperbola exponential">
                                <label class="form-label small fw-bold">Vertical Shift (q/c)</label>
                                <input type="range" class="form-range" id="alg-q" min="-5" max="5" step="1" value="0">
                            </div>
                            <div class="mb-3 alg-param d-none" data-for="exponential">
                                <label class="form-label small fw-bold">Base (b)</label>
                                <input type="range" class="form-range" id="alg-b" min="2" max="5" step="1" value="2">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="card shadow-sm border-0" style="height: 400px;">
                        <div class="card-body">
                            <canvas id="algChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <script src="/js/geometry.js"></script>
    <script src="/js/trig_lab.js"></script>
    <script src="/js/algebra_lab.js"></script>
</body>
</html>
