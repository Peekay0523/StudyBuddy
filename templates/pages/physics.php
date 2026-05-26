<?php
$pageTitle = 'Physics Laboratory - StudySmart';
$currentPage = 'physics';

// Extra styles for this page
$extraHead = '
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/p5.js/1.4.0/p5.js"></script>
    <link rel="stylesheet" href="/css/calculators-core.css">
    <link rel="stylesheet" href="/css/physics-calculator.css">
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
    <style>
        .nav-btn { position: absolute; top: 50%; transform: translateY(-50%); z-index: 10; border-radius: 50%; width: 40px; height: 40px; padding: 0; line-height: 40px; background: rgba(255,255,255,0.8); border: 1px solid #ddd; transition: all 0.2s; }
        .nav-btn:hover { background: #007bff; color: white; border-color: #007bff; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        .nav-left { left: 10px; }
        .nav-right { right: 10px; }
        .nav-btn.hidden { display: none !important; }

        @media (max-width: 768px) {
            .container { padding-left: 10px; padding-right: 10px; }
            .top-card { padding: 15px; }
            .icon-box { width: 45px; height: 45px; font-size: 18px; }
            #physics-canvas-container { min-height: 350px !important; }
            .nav-btn { width: 35px; height: 35px; line-height: 35px; }
        }
    </style>
';

include __DIR__ . '/../layouts/header.php';
?>

    <div class="container mt-4 pb-5">
        <div class="d-flex align-items-center mb-4">
            <a href="/simulate" class="btn btn-outline-secondary btn-sm me-3"><i class="fas fa-arrow-left"></i></a>
            <h1 class="h3 mb-0">Physics Laboratory</h1>
        </div>

        <!-- Custom Styled Header & Dropdown -->
        <div class="top-card">
            <div class="lab-info">
                <div class="icon-box">
                    <i class="fas fa-atom"></i>
                </div>
                <div>
                    <h3>Physics Simulator</h3>
                    <p class="d-none d-sm-block">Select a topic to start experimenting.</p>
                </div>
            </div>

            <div class="dropdown-container">
                <div class="custom-select" id="physics-dropdown">
                    <div class="selected">
                        <i class="fas fa-dot-circle"></i>
                        <span>Simple Pendulum</span>
                    </div>
                    <i class="fas fa-chevron-down arrow"></i>
                </div>

                <div class="dropdown-menu" id="physics-menu">
                    <div class="option active" data-sim="pendulum">
                        <i class="fas fa-dot-circle"></i>
                        <div>
                            <strong>Simple Pendulum</strong>
                            <small>Oscillatory motion & gravity</small>
                        </div>
                    </div>
                    <div class="option" data-sim="freefall">
                        <i class="fas fa-arrow-down"></i>
                        <div>
                            <strong>Free Fall</strong>
                            <small>Gravity & acceleration</small>
                        </div>
                    </div>
                    <div class="option" data-sim="projectile">
                        <i class="fas fa-rocket"></i>
                        <div>
                            <strong>Projectile Motion</strong>
                            <small>2D kinematics & trajectory</small>
                        </div>
                    </div>
                    <div class="option" data-sim="doppler">
                        <i class="fas fa-wave-square"></i>
                        <div>
                            <strong>Doppler Effect</strong>
                            <small>Sound waves & frequency shift</small>
                        </div>
                    </div>
                    <div class="option" data-sim="newton1">
                        <i class="fas fa-square"></i>
                        <div>
                            <strong>Newton's 1st Law</strong>
                            <small>Inertia & friction</small>
                        </div>
                    </div>
                    <div class="option" data-sim="newton2">
                        <i class="fas fa-weight-hanging"></i>
                        <div>
                            <strong>Newton's 2nd Law</strong>
                            <small>Force, mass & acceleration</small>
                        </div>
                    </div>
                    <div class="option" data-sim="newton3">
                        <i class="fas fa-exchange-alt"></i>
                        <div>
                            <strong>Newton's 3rd Law</strong>
                            <small>Action & reaction (collisions)</small>
                        </div>
                    </div>
                    <div class="option" data-sim="gravity">
                        <i class="fas fa-globe"></i>
                        <div>
                            <strong>Gravitation</strong>
                            <small>Newton's Law of Universal Gravitation</small>
                        </div>
                    </div>
                    <div class="option" data-sim="coulomb">
                        <i class="fas fa-bolt"></i>
                        <div>
                            <strong>Coulomb's Law</strong>
                            <small>Electrostatic forces & charges</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Simulation Viewport -->
        <div class="row">
            <div class="col-md-12">
                <div class="card shadow-sm border-0 mb-4 overflow-hidden position-relative">
                    <button class="btn nav-btn nav-left hidden" id="prev-sim-mode" title="Previous Mode"><i class="fas fa-chevron-left"></i></button>
                    <button class="btn nav-btn nav-right hidden" id="next-sim-mode" title="Next Mode"><i class="fas fa-chevron-right"></i></button>
                    <div class="card-body p-0">
                        <div id="physics-canvas-container" class="text-center bg-white d-flex justify-content-center align-items-center" style="min-height: 500px; height: auto;">
                            <!-- p5.js canvas -->
                        </div>
                    </div>
                </div>

                <!-- Controls & Calculations Card -->
                <div class="card shadow-sm border-0 bg-white p-3 mb-4">
                    <div id="physics-top-buttons" class="mb-3"></div>

                    <div id="physics-formula-display" class="bg-light p-3 rounded mb-3 text-center border" style="cursor: pointer;" title="Click for details">
                        <!-- Formula injected here -->
                        <div class="fw-bold text-primary h5 mb-1" id="active-formula">T = 2π√(L/g)</div>
                        <div class="text-muted small" id="formula-desc">Period of a Pendulum</div>
                        <div class="text-primary mt-2" style="font-size: 0.7rem;"><i class="fas fa-info-circle"></i> Click for details</div>
                    </div>

                    <div id="physics-controls">
                        <!-- Dynamic sliders injected here -->
                    </div>
                </div>

                <div class="mt-3 text-muted small text-center" id="interaction-hint">
                    <i class="fas fa-mouse me-1"></i> Use your mouse or touch to interact with the simulation.
                </div>
            </div>
        </div>

    </div>

    <!-- Law Details Modal -->
    <div class="modal fade" id="lawModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="lawTitle">Law Definition</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="lawDefinition" class="mb-3 lead small"></div>
                    <h6 class="border-bottom pb-2">Symbols:</h6>
                    <ul id="lawSymbols" class="list-group list-group-flush small"></ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

<?php include __DIR__ . '/../components/physics_calculator.php'; ?>

<?php
$extraScripts = '
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/js/calculators-core.js"></script>
    <script src="/js/physics-calculator.js"></script>
    <script src="/js/physics_lab.js"></script>
';

include __DIR__ . '/../layouts/footer.php';
?>