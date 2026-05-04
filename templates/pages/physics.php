<!DOCTYPE html>
<?php
include __DIR__ . '/../layouts/header.php';
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Physics Lab - Science & Maths App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/css/style.css">
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
        <h1 class="text-center mb-4">Physics Simulator</h1>

        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="card shadow-sm border-0 bg-white p-3">
                    <div class="row align-items-center">
                        <div class="col-md-4">
                            <h2 class="h4 mb-0">Simulation Lab</h2>
                            <p class="text-muted small mb-0">Select a topic to start experimenting.</p>
                        </div>
                        <div class="col-md-8">
                            <select id="physics-topic-select" class="form-select form-select-lg shadow-sm border-primary">
                                <option value="pendulum">Simple Pendulum</option>
                                <option value="freefall">Free Fall</option>
                                <option value="projectile">Projectile Motion</option>
                                <option value="doppler">Doppler Effect</option>
                                <option value="newton1">Newton's 1st Law</option>
                                <option value="newton2">Newton's 2nd Law</option>
                                <option value="newton3">Newton's 3rd Law</option>
                                <option value="gravity">Gravitation</option>
                                <option value="coulomb">Coulomb's Law</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Simulation Viewport -->
            <div class="col-md-12">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body p-0">
                        <div id="physics-canvas-container" class="text-center bg-white d-flex justify-content-center align-items-center" style="min-height: 500px; height: auto;">
                            <!-- p5.js canvas -->
                        </div>
                    </div>
                </div>

                <!-- Controls & Calculations Card -->
                <div class="card shadow-sm border-0 bg-white p-3 mb-4">
                    <div id="physics-top-buttons"></div>

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
                    <i class="fas fa-mouse me-1"></i> Use your mouse to interact with the simulation.
                </div>
            </div>
        </div>
    </div>

    <!-- Law Details Modal -->
    <div class="modal fade" id="lawModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
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
            </div>
        </div>
    </div>

    <script src="/js/physics_lab.js"></script>
</body>
</html>
