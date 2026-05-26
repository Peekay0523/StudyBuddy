<?php
$pageTitle = 'Mathematics Laboratory - StudySmart';
$currentPage = 'math';

// Extra styles for this page
$extraHead = '
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/p5.js/1.4.0/p5.js"></script>
    <link rel="stylesheet" href="/css/calculators-core.css">
    <!-- MathJax Configuration -->
    <script>
        window.MathJax = {
            tex: {
                inlineMath: [[\'$\', \'$\'], [\'\\\\(\', \'\\\\)\']],
                displayMath: [[\'$$\', \'$$\'], [\'\\\\[\', \'\\\\]\']]
            },
            svg: { fontCache: \'global\' }
        };
    </script>
    <script id="MathJax-script" async src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/mathjs/11.8.0/math.js"></script>
    <style>
        @media (max-width: 768px) {
            .container { padding-left: 10px; padding-right: 10px; }
            .top-card { padding: 15px; }
            .icon-box { width: 45px; height: 45px; font-size: 18px; }
            #geometry-canvas-container { min-height: 300px !important; }
            .card { height: auto !important; }
            #trigChart, #algChart { min-height: 300px; }
        }
    </style>
';

include __DIR__ . '/../layouts/header.php';
?>

    <div class="container mt-4 pb-5">
        <div class="d-flex align-items-center mb-4">
            <a href="/simulate" class="btn btn-outline-secondary btn-sm me-3"><i class="fas fa-arrow-left"></i></a>
            <h1 class="h3 mb-0">Mathematics Laboratory</h1>
        </div>

        <!-- 1. Euclidean Geometry Section -->
        <section class="mb-5">
            <div class="top-card">
                <div class="lab-info">
                    <div class="icon-box">
                        <i class="fas fa-shapes"></i>
                    </div>
                    <div>
                        <h3>Euclidean Geometry</h3>
                        <p class="d-none d-sm-block">Select a theorem and drag points on the circle!</p>
                    </div>
                </div>

                <div class="dropdown-container">
                    <div class="custom-select" id="geometry-dropdown">
                        <div class="selected">
                            <i class="fas fa-circle-notch"></i>
                            <span>Angles in same segment</span>
                        </div>
                        <i class="fas fa-chevron-down arrow"></i>
                    </div>

                    <div class="dropdown-menu" id="geometry-menu">
                        <div class="option active" data-theorem="same_segment">
                            <i class="fas fa-circle-notch"></i>
                            <div>
                                <strong>Same Segment</strong>
                                <small>Angles subtended by the same arc</small>
                            </div>
                        </div>
                        <div class="option" data-theorem="center_circumference">
                            <i class="fas fa-dot-circle"></i>
                            <div>
                                <strong>Center vs Circumference</strong>
                                <small>Angle at center is twice circumference</small>
                            </div>
                        </div>
                        <div class="option" data-theorem="semicircle">
                            <i class="fas fa-adjust"></i>
                            <div>
                                <strong>Semi-circle</strong>
                                <small>Angle in a semi-circle is 90°</small>
                            </div>
                        </div>
                        <div class="option" data-theorem="cyclic_quad">
                            <i class="fas fa-draw-polygon"></i>
                            <div>
                                <strong>Cyclic Quadrilateral</strong>
                                <small>Opposite angles are supplementary</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card shadow-sm border-0 overflow-hidden">
                        <div id="geometry-canvas-container" class="text-center bg-white" style="min-height: 400px;"></div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 2. Trigonometry Section -->
        <section class="mb-5">
            <div class="top-card">
                <div class="lab-info">
                    <div class="icon-box" style="background: linear-gradient(135deg, #17a2b8, #117a8b);">
                        <i class="fas fa-wave-square"></i>
                    </div>
                    <div>
                        <h3>Trigonometry Lab</h3>
                        <p class="d-none d-sm-block">Explore waves and their properties.</p>
                    </div>
                </div>

                <div class="dropdown-container">
                    <div class="custom-select" id="trig-dropdown">
                        <div class="selected">
                            <i class="fas fa-wave-square"></i>
                            <span>Sine: y = a sin(bx) + q</span>
                        </div>
                        <i class="fas fa-chevron-down arrow"></i>
                    </div>

                    <div class="dropdown-menu" id="trig-menu">
                        <div class="option active" data-type="sin">
                            <i class="fas fa-wave-square"></i>
                            <div>
                                <strong>Sine Function</strong>
                                <small>y = a sin(bx) + q</small>
                            </div>
                        </div>
                        <div class="option" data-type="cos">
                            <i class="fas fa-wave-square" style="transform: rotate(90deg);"></i>
                            <div>
                                <strong>Cosine Function</strong>
                                <small>y = a cos(bx) + q</small>
                            </div>
                        </div>
                        <div class="option" data-type="log">
                            <i class="fas fa-stream"></i>
                            <div>
                                <strong>Logarithmic Function</strong>
                                <small>y = log<sub>b</sub>(x)</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card shadow-sm border-0 bg-white p-3 h-100">
                        <div class="text-center p-2 bg-light rounded mb-3 border">
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
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body">
                            <canvas id="trigChart" style="max-height: 400px;"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 3. Algebra & Graphs Section -->
        <section class="mb-5">
            <div class="top-card">
                <div class="lab-info">
                    <div class="icon-box" style="background: linear-gradient(135deg, #28a745, #1e7e34);">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div>
                        <h3>Algebra & Graphs Lab</h3>
                        <p class="d-none d-sm-block">Explore linear, quadratic, and other functions.</p>
                    </div>
                </div>

                <div class="dropdown-container">
                    <div class="custom-select" id="alg-dropdown">
                        <div class="selected">
                            <i class="fas fa-slash"></i>
                            <span>Straight Line: y = mx + c</span>
                        </div>
                        <i class="fas fa-chevron-down arrow"></i>
                    </div>

                    <div class="dropdown-menu" id="alg-menu">
                        <div class="option active" data-type="linear">
                            <i class="fas fa-slash"></i>
                            <div>
                                <strong>Straight Line</strong>
                                <small>y = mx + c</small>
                            </div>
                        </div>
                        <div class="option" data-type="parabola">
                            <i class="fas fa-chart-area"></i>
                            <div>
                                <strong>Parabola</strong>
                                <small>y = a(x-p)² + q</small>
                            </div>
                        </div>
                        <div class="option" data-type="hyperbola">
                            <i class="fas fa-bezier-curve"></i>
                            <div>
                                <strong>Hyperbola</strong>
                                <small>y = a/(x-p) + q</small>
                            </div>
                        </div>
                        <div class="option" data-type="exponential">
                            <i class="fas fa-chart-line"></i>
                            <div>
                                <strong>Exponential</strong>
                                <small>y = a·b^(x-p) + q</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card shadow-sm border-0 bg-white p-3 h-100">
                        <div class="text-center p-2 bg-light rounded mb-3 border">
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
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body">
                            <canvas id="algChart" style="max-height: 400px;"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

<?php include __DIR__ . '/../components/math_calculator.php'; ?>

<?php
$extraScripts = '
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/js/calculators-core.js"></script>
    <script src="/js/math-calculator.js"></script>
    <script src="/js/geometry.js"></script>
    <script src="/js/trig_lab.js"></script>
    <script src="/js/algebra_lab.js"></script>
';

include __DIR__ . '/../layouts/footer.php';
?>
