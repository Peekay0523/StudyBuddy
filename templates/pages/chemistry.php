<!DOCTYPE html>
<?php
include __DIR__ . '/../layouts/header.php';
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chemistry - Science & Maths App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/css/style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/p5.js/1.4.0/p5.js"></script>
    <style>
        /* Global Responsive Adjustments */
:root {
    --primary-color: #6366f1;
    --secondary-color: #9333ea;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    color: #333;
    overflow-x: hidden; /* Prevent horizontal scroll */
}

.container {
    max-width: 1200px;
    padding-left: 15px;
    padding-right: 15px;
}

@media (max-width: 768px) {
    .container {
        padding-left: 10px;
        padding-right: 10px;
    }
}

.card {
    transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, 1);
    border: none;
    border-radius: 12px;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
}

/* Touch-friendly buttons */
.btn {
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: 600;
}

.thin-btn {
    font-size: 0.9rem;
    border-radius: 8px !important;
    margin-bottom: 8px;
    border: 1px solid #e9ecef !important;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    background-color: #fff;
    padding: 12px 15px !important;
    display: flex;
    align-items: center;
    width: 100%;
}

.thin-btn:hover {
    background-color: #f1f3f5;
    transform: translateX(5px);
}

.thin-btn.active {
    background-color: #007bff !important;
    color: white !important;
    border-color: #007bff !important;
    font-weight: 600;
    box-shadow: 0 4px 6px rgba(0, 123, 255, 0.2);
}

/* Custom Range Styling */
.form-range {
    height: 1.5rem;
}

.form-range::-webkit-slider-runnable-track {
    background: #e9ecef;
    height: 8px;
    border-radius: 4px;
}

.form-range::-webkit-slider-thumb {
    background: #007bff;
    margin-top: -6px;
    height: 20px;
    width: 20px;
    transition: transform 0.1s;
}

.form-range::-webkit-slider-thumb:active {
    transform: scale(1.3);
}

/* Canvas Responsiveness */
canvas {
    max-width: 100% !important;
    height: auto !important;
    display: block;
}

#geometry-canvas-container, #pendulum-container, #freefall-container, #projectile-container, 
#physics-canvas-container, #chemistry-canvas-container, #organic-canvas-container,
.simulation-box {
    margin: 0 auto;
    background-color: #fff;
    border-radius: 8px;
    overflow: hidden;
    width: 100% !important;
    position: relative;
}

.navbar-brand {
    font-weight: 700;
    letter-spacing: 0.5px;
}

section h2 {
    border-left: 4px solid #007bff;
    padding-left: 15px;
    margin-bottom: 25px;
    font-weight: 700;
}

@media (max-width: 576px) {
    section h2 {
        font-size: 1.25rem;
        margin-bottom: 15px;
    }
    section {
        margin-bottom: 2rem !important;
    }
}

/* Element Buttons - Scrollable on Mobile */
.element-scroll-container {
    display: flex;
    flex-wrap: nowrap;
    overflow-x: auto;
    overflow-y: hidden;
    gap: 8px;
    padding: 6px 0 10px;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none; /* Hide scrollbar */
    justify-content: center; /* Center buttons */
    scroll-behavior: smooth;
    scroll-snap-type: x mandatory;
    width: 100%;
    white-space: nowrap;
}

.element-scroll-container::-webkit-scrollbar {
    display: none;
}

@media (max-width: 768px) {
    .element-scroll-container {
        justify-content: flex-start; /* Start from left to allow scrolling */
        padding-left: 70px; /* Space for arrows */
        padding-right: 70px;
        gap: 10px;
    }

    .element-btn {
        flex: 0 0 auto !important;
        min-width: 58px;
        max-width: 90px !important;
        width: auto !important;
        padding: 8px 12px !important;
        font-size: 0.85rem;
        white-space: nowrap !important;
    }
}

.element-nav-wrapper {
    display: flex;
    align-items: center;
    position: relative;
    overflow: hidden;
    padding: 0 8px;
}

.element-btn {
    flex: 0 0 auto !important;
    min-width: 45px;
    max-width: 90px !important;
    width: auto !important; /* Override .thin-btn width: 100% */
    display: inline-flex;
    justify-content: center;
    align-items: center;
    scroll-snap-align: start;
    white-space: nowrap !important;
}

.scroll-arrow {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    z-index: 10;
    background: rgba(255,255,255,0.85);
    border: 1px solid rgba(221,221,221,0.8);
    border-radius: 50%;
    width: 35px;
    height: 35px;
    padding: 0;
    display: none; /* Hidden by default, show on mobile */
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 5px rgba(0,0,0,0.12);
    transition: background-color 0.2s ease, opacity 0.2s ease;
    opacity: 0.92;
}

@media (max-width: 768px) {
    .scroll-arrow {
        display: flex;
    }
}

.left-arrow { left: 5px; }
.right-arrow { right: 5px; }

.element-btn {
    flex: 0 0 auto;
    min-width: 45px;
    width: auto !important; /* Override .thin-btn width: 100% */
    scroll-snap-align: start;
}

/* Simulation Buttons - Scrollable on Mobile */
@media (max-width: 768px) {
    .simulation-scroll-container {
        display: flex;
        flex-wrap: nowrap;
        overflow-x: auto;
        gap: 8px;
        padding-bottom: 10px;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none; /* Hide scrollbar for cleaner look */
    }
    .simulation-scroll-container::-webkit-scrollbar {
        display: none;
    }
    .simulation-scroll-container .list-group-item {
        flex: 0 0 auto;
        width: auto !important;
        white-space: nowrap;
        border: 1px solid #dee2e6 !important;
        border-radius: 8px !important;
        margin-bottom: 0;
    }
    .simulation-scroll-container .thin-btn {
        padding: 8px 15px !important;
    }
}

/* REACTION SIMULATOR RESPONSIVE GRID */
.reaction-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 40px;
    border-bottom: 1px solid #eee;
    background: white;
    border-radius: 12px 12px 0 0;
}

.reaction-title {
    font-size: 22px;
    font-weight: bold;
    color: #1e293b;
}

.reaction-actions button {
    margin-left: 10px;
}

.btn-gradient {
    background: linear-gradient(135deg, #6366f1, #9333ea);
    color: white;
    border: none;
}

.btn-gradient:hover {
    background: linear-gradient(135deg, #5558e3, #8224db);
    color: white;
}

.reaction-container {
    display: grid;
    grid-template-columns: 1fr; /* Default to stack for mobile */
    gap: 20px;
    padding: 15px;
    background: white;
    border-radius: 0 0 12px 12px;
}

@media (min-width: 992px) {
    .reaction-container {
        grid-template-columns: 320px 1fr;
    }
}

@media (min-width: 768px) and (max-width: 991px) {
    .reaction-container {
        grid-template-columns: 1fr 1fr;
    }
    .simulation-box {
        grid-column: span 2;
        order: -1;
    }
}

.reactant-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
    padding: 12px;
    border-radius: 10px;
    background: #f8fafc;
}

.reactant-item input {
    width: 60px;
    padding: 5px;
    border: 1px solid #ddd;
    border-radius: 5px;
}

.simulation-box {
    height: 400px;
    border-radius: 16px;
    background: radial-gradient(circle, #f1f5f9, #e2e8f0);
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 400px;
}

.controls-slider {
    margin-bottom: 20px;
}

.controls-slider label {
    display: block;
    font-weight: 600;
    margin-bottom: 8px;
    font-size: 0.95rem;
}

.reaction-progress {
    height: 10px;
    background: #eee;
    border-radius: 10px;
    overflow: hidden;
    margin-top: 10px;
}

.reaction-progress-bar {
    height: 100%;
    background: linear-gradient(90deg, #22c55e, #16a34a);
    width: 0%;
    transition: width 0.3s ease;
}

/* Info Overlay Styles */
.info-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 2000;
    padding: 20px;
}

.info-overlay.hidden {
    display: none !important;
}

.info-dialog {
    background: white;
    border-radius: 16px;
    max-width: 800px;
    width: 100%;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
}

.info-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 25px;
    border-bottom: 1px solid #eee;
}

.info-header h5 {
    margin: 0;
    font-weight: 700;
    color: #1e293b;
}

.info-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    color: #64748b;
    cursor: pointer;
}

.info-content {
    padding: 25px;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-bottom: 25px;
}

.info-card {
    background: #f8fafc;
    padding: 15px;
    border-radius: 12px;
    border-left: 4px solid #6366f1;
}

.info-card h6 {
    color: #4338ca;
    font-weight: 700;
    margin-bottom: 8px;
}

.info-card p {
    font-size: 0.9rem;
    color: #475569;
    margin: 0;
}

.info-tips {
    background: #f0fdf4;
    padding: 15px;
    border-radius: 12px;
    border: 1px solid #bbf7d0;
}

.info-tips h6 {
    color: #166534;
    font-weight: 700;
}

.info-tips ul {
    margin: 10px 0 0 0;
    padding-left: 20px;
    font-size: 0.9rem;
    color: #166534;
}

.info-footer {
    padding: 15px 25px;
    border-top: 1px solid #eee;
    text-align: right;
}


/* Mobile Specific Tweaks */
@media (max-width: 576px) {
    h1 { font-size: 1.5rem; }
    h2 { font-size: 1.25rem; }
    
    .navbar-brand {
        font-size: 1rem;
    }

    .reaction-header {
        padding: 15px;
        flex-direction: column;
        text-align: center;
    }
    
    .reaction-actions {
        margin-top: 10px;
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
        justify-content: center;
    }
    
    .reaction-actions button {
        margin: 0;
        flex: 1 1 auto;
        font-size: 0.75rem;
    }

    .simulation-box {
        height: 250px !important;
        min-height: 250px !important;
    }

    .card-body {
        padding: 0.75rem;
    }

    .nav-btn {
        width: 35px;
        height: 35px;
        font-size: 0.8rem;
    }

    .list-group-item {
        padding: 10px 12px;
    }
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
        <h1 class="text-center mb-4">Chemistry Laboratory</h1>

        <!-- 1. Element Viewer -->
        <section class="mb-5">
            <h2 class="h4">1. Element Viewer</h2>
            <p class="text-muted small text-center mb-3">Explore atomic structures with interactive Bohr models.</p>
            
            <div class="element-nav-wrapper position-relative mb-4">
                <button class="btn scroll-arrow left-arrow" id="el-scroll-left"><i class="fas fa-chevron-left"></i></button>
                <div class="element-scroll-container bg-white p-3 rounded shadow-sm" id="element-container">
                    <button class="btn btn-outline-primary thin-btn element-btn active" data-element="Hydrogen">H</button>
                    <button class="btn btn-outline-primary thin-btn element-btn" data-element="Helium">He</button>
                    <button class="btn btn-outline-danger thin-btn element-btn" data-element="Lithium">Li</button>
                    <button class="btn btn-outline-danger thin-btn element-btn" data-element="Beryllium">Be</button>
                    <button class="btn btn-outline-success thin-btn element-btn" data-element="Carbon">C</button>
                    <button class="btn btn-outline-success thin-btn element-btn" data-element="Nitrogen">N</button>
                    <button class="btn btn-outline-success thin-btn element-btn" data-element="Oxygen">O</button>
                    <button class="btn btn-outline-info thin-btn element-btn" data-element="Neon">Ne</button>
                    <button class="btn btn-outline-warning thin-btn element-btn" data-element="Sodium">Na</button>
                    <button class="btn btn-outline-warning thin-btn element-btn" data-element="Magnesium">Mg</button>
                    <button class="btn btn-outline-warning thin-btn element-btn" data-element="Aluminium">Al</button>
                    <button class="btn btn-outline-secondary thin-btn element-btn" data-element="Chlorine">Cl</button>
                    <button class="btn btn-outline-dark thin-btn element-btn" data-element="Iron">Fe</button>
                    <button class="btn btn-outline-dark thin-btn element-btn" data-element="Silver">Ag</button>
                    <button class="btn btn-outline-dark thin-btn element-btn" data-element="Gold">Au</button>
                </div>
                <button class="btn scroll-arrow right-arrow" id="el-scroll-right"><i class="fas fa-chevron-right"></i></button>
            </div>
            <div class="row justify-content-center">
                <div class="col-md-8 text-center">
                    <div class="card shadow-sm border-0">
                        <div id="chemistry-canvas-container" class="bg-white rounded" style="min-height: 400px;"></div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 2. Organic Chemistry Lab -->
        <section class="mb-5">
            <h2 class="h4">2. Organic Chemistry Lab</h2>
            <div class="row">
                <div class="col-md-4">
                    <p class="text-muted small">Learn about Carbon bonding and Alcohols.</p>
                    <div class="list-group list-group-flush mb-4 shadow-sm rounded">
                        <button class="list-group-item list-group-item-action thin-btn active" id="alkane-btn">
                            <i class="fas fa-minus me-2 text-dark"></i>Alkanes (Single)
                        </button>
                        <button class="list-group-item list-group-item-action thin-btn" id="alkene-btn">
                            <i class="fas fa-equals me-2 text-primary"></i>Alkenes (Double)
                        </button>
                        <button class="list-group-item list-group-item-action thin-btn" id="alkyne-btn">
                            <i class="fas fa-bars me-2 text-danger"></i>Alkynes (Triple)
                        </button>
                    </div>

                    <div id="alkane-controls" class="card shadow-sm p-3 bg-white border-0">
                        <h6 class="fw-bold mb-3">Hydrocarbon: <span id="org-name" class="text-primary">Methane</span></h6>
                        <p class="small text-muted">Formula: <span id="org-formula" class="fw-bold text-dark">CH<sub>4</sub></span></p>
                        <div class="d-flex gap-2">
                            <button id="add-carbon" class="btn btn-success btn-sm w-100">+ Add Carbon</button>
                            <button id="remove-carbon" class="btn btn-danger btn-sm w-100">- Remove</button>
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="card shadow-sm border-0">
                        <div id="organic-canvas-container" class="bg-white rounded" style="min-height: 300px;"></div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 3. Common Reactions Section (With Arrows) -->
        <section class="mb-5">
            <h2 class="h4">3. Common Exam Reactions</h2>
            <div class="row justify-content-center">
                <div class="col-md-10">
                    <div class="card shadow-sm border-0 reaction-card">
                        <!-- Navigation Arrows -->
                        <button class="btn nav-btn nav-left" id="prev-reaction"><i class="fas fa-chevron-left"></i></button>
                        <button class="btn nav-btn nav-right" id="next-reaction"><i class="fas fa-chevron-right"></i></button>
                        
                        <div class="card-body py-5" id="reaction-display-area">
                            <!-- JS will inject content here -->
                            <div class="text-center">
                                <div class="spinner-border text-primary" role="status"></div>
                                <p class="mt-2 text-muted">Loading Reactions...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 4. Molecular Reaction Simulator -->
        <section class="mb-5">
            <h2 class="h4">4. Molecular Reaction Simulator</h2>
            
            <div class="row justify-content-center">
                <div class="col-lg-12">
                    <!-- Main Simulator Card -->
                    <div class="card shadow-sm border-0">
                        <!-- Header -->
                        <div class="reaction-header">
                            <div class="reaction-title">
                                <i class="fas fa-flask-vial"></i> Reaction Simulator
                            </div>
                            <div class="reaction-actions">
                                <button class="btn btn-outline-secondary btn-sm" id="learnmore-btn">Learn More</button>
                                <button class="btn btn-outline-secondary btn-sm" id="reaction-reset">Reset</button>
                                <button class="btn btn-gradient btn-sm" id="reaction-save">Save Experiment</button>
                            </div>
                        </div>

                        <!-- Custom Info Popup -->
                        <div class="info-overlay hidden" id="infoOverlay">
                            <div class="info-dialog">
                                <div class="info-header">
                                    <h5><i class="fas fa-flask-vial me-2"></i>How Reactions Work</h5>
                                    <button class="info-close" id="infoCloseBtn">&times;</button>
                                </div>
                                <div class="info-content">
                                    <div class="info-grid">
                                        <div class="info-card">
                                            <h6><i class="fas fa-temperature-high me-2"></i>Temperature Effect</h6>
                                            <p>Higher temperatures increase molecular kinetic energy, causing faster movement and more energetic collisions between reactant molecules.</p>
                                        </div>
                                        <div class="info-card">
                                            <h6><i class="fas fa-compress me-2"></i>Pressure Effect</h6>
                                            <p>Increasing pressure forces molecules into a smaller volume, raising concentration and collision frequency, leading to higher reaction rates.</p>
                                        </div>
                                        <div class="info-card">
                                            <h6><i class="fas fa-flask me-2"></i>Reactant Concentration</h6>
                                            <p>Adding more reactants increases the number of available molecules to react. According to Le Chatelier's Principle, this shifts equilibrium toward products.</p>
                                        </div>
                                        <div class="info-card">
                                            <h6><i class="fas fa-bolt me-2"></i>Activation Energy</h6>
                                            <p>Reactants must have sufficient energy to overcome the activation energy barrier. Only high-energy collisions result in bond breaking.</p>
                                        </div>
                                    </div>
                                    <div class="info-tips">
                                        <h6><i class="fas fa-lightbulb me-2"></i>Experiment Tips</h6>
                                        <ul>
                                            <li>Increase temperature to see faster molecular movement</li>
                                            <li>Increase pressure to compress molecules</li>
                                            <li>Adjust reactant quantities to explore stoichiometry</li>
                                            <li>Watch how yield changes with different conditions</li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="info-footer">
                                    <button class="btn btn-primary btn-sm" id="infoGotItBtn">Got It!</button>
                                </div>
                            </div>
                        </div>

                        <!-- Main Content Container -->
                        <div class="reaction-container">
                            <!-- LEFT PANEL: Controls & Results -->
                            <div class="card border-0 bg-light">
                                <div class="card-body">
                                    <h5 class="card-title mb-3">Controls</h5>

                                    <label class="small text-muted"><strong>Reaction Type:</strong></label>
                                    <select id="reaction-type" class="form-select form-select-sm mt-2 mb-3">
                                        <option value="h2o">2H₂ + O₂ → 2H₂O</option>
                                        <option value="hcl">H₂ + Cl₂ → 2HCl</option>
                                        <option value="nh3">N₂ + 3H₂ → 2NH₃</option>
                                        <option value="co2_comb">CH₄ + 2O₂ → CO₂ + 2H₂O</option>
                                        <option value="nacl">2Na + Cl₂ → 2NaCl</option>
                                        <option value="co2">C + O₂ → CO₂</option>
                                    </select>

                                    <!-- Temperature Control -->
                                    <div class="controls-slider mb-3">
                                        <label for="temp-slider">Temperature</label>
                                        <input type="range" id="temp-slider" class="form-range" min="0" max="500" value="298">
                                        <small class="text-muted"><span id="temp-value">298</span>K</small>
                                    </div>

                                    <!-- Pressure Control -->
                                    <div class="controls-slider mb-3">
                                        <label for="pressure-slider">Pressure</label>
                                        <input type="range" id="pressure-slider" class="form-range" min="0" max="10" step="0.1" value="1">
                                        <small class="text-muted"><span id="pressure-value">1.0</span> atm</small>
                                    </div>

                                    <hr class="my-3">

                                    <!-- Start Button -->
                                    <button class="btn btn-gradient w-100 btn-sm mb-3" id="reaction-start">
                                        <i class="fas fa-play-circle"></i> Start Reaction
                                    </button>

                                    <hr class="my-3">

                                    <h5 class="card-title mb-3">Results</h5>

                                    <p class="mb-2"><strong>Yield:</strong> <span id="reaction-yield">0</span>%</p>

                                    <div class="reaction-progress">
                                        <div class="reaction-progress-bar" id="reaction-yield-bar"></div>
                                    </div>

                                    <p class="small text-muted mt-2 mb-0">
                                        <strong>ΔH:</strong> <span id="reaction-enthalpy">-483.6</span> kJ/mol
                                    </p>
                                </div>
                            </div>

                            <!-- CENTER PANEL: Simulation -->
                            <div class="card border-0">
                                <div class="card-body">
                                    <h5 class="card-title mb-3">Reaction</h5>

                                    <div class="simulation-box" id="reaction-sim"></div>

                                    <div class="text-center mt-3">
                                        <strong id="reaction-equation">2H₂ + O₂ → 2H₂O</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <script src="/js/chemistry.js"></script>
    <script src="/js/organic_chem.js"></script>
    <script src="/js/reactions.js"></script>
    <script src="/js/simple_reaction_sim.js"></script>
    <script src="/js/reaction_simulator.js"></script>
</body>
</html>
