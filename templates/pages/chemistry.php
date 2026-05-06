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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/p5.js/1.4.0/p5.js"></script>
    <style>
        .animate-fade { animation: fadeIn 0.5s; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .reaction-card { position: relative; overflow: hidden; }
        .nav-btn { position: absolute; top: 50%; transform: translateY(-50%); z-index: 10; border-radius: 50%; width: 40px; height: 40px; padding: 0; line-height: 40px; background: rgba(255,255,255,0.8); border: 1px solid #ddd; transition: all 0.2s; }
        .nav-btn:hover { background: #007bff; color: white; border-color: #007bff; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        .nav-left { left: 10px; }
        .nav-right { right: 10px; }
        .shadow-inner { box-shadow: inset 0 2px 4px rgba(0,0,0,0.06); }
        /* Fix for modal flickering/scrolling jump */
        body.modal-open { padding-right: 0 !important; overflow: hidden; }
        .modal { background: rgba(0,0,0,0.2); }
        /* Ensure modal appears above canvas */
        .modal-backdrop { z-index: 1040; }
        .modal { z-index: 1050 !important; pointer-events: auto; }
        .modal-dialog { pointer-events: auto; }
        .modal-content { pointer-events: auto; }
        /* Canvas Interactivity */
        canvas { touch-action: manipulation; }
    </style>
</head>
<body class="bg-light">
    <nav class="btn-secondary" style="padding: 8px 16px;">
        <div class="container">
            <a class="navbar-brand" href="index.php"><i class="fas fa-arrow-left me-2"></i>Back to Dashboard</a>
        </div>
    </nav>

    <div class="container mt-4 pb-5">
        <h1 class="text-center mb-4">Chemistry Laboratory</h1>

        <!-- 1. Element Viewer -->
        <section class="mb-5">
            <div class="top-card">
                <div class="lab-info">
                    <div class="icon-box">
                        <i class="fas fa-atom"></i>
                    </div>
                    <div>
                        <h3>Element Viewer</h3>
                        <p>Explore atomic structures with interactive Bohr models.</p>
                    </div>
                </div>
                
                <div class="dropdown-container">
                    <div class="custom-select" id="element-dropdown">
                        <div class="selected">
                            <i class="fas fa-dot-circle text-primary"></i>
                            <span>Hydrogen</span>
                        </div>
                        <i class="fas fa-chevron-down arrow"></i>
                    </div>
                    <div class="dropdown-menu" id="element-menu" style="max-height: 350px; overflow-y: auto;">
                        <div class="option active" data-element="Hydrogen">
                            <i class="fas fa-circle text-primary"></i>
                            <div><strong>Hydrogen</strong> <small>Atomic No: 1</small></div>
                        </div>
                        <div class="option" data-element="Helium">
                            <i class="fas fa-circle text-primary"></i>
                            <div><strong>Helium</strong> <small>Atomic No: 2</small></div>
                        </div>
                        <div class="option" data-element="Lithium">
                            <i class="fas fa-circle text-danger"></i>
                            <div><strong>Lithium</strong> <small>Atomic No: 3</small></div>
                        </div>
                        <div class="option" data-element="Beryllium">
                            <i class="fas fa-circle text-danger"></i>
                            <div><strong>Beryllium</strong> <small>Atomic No: 4</small></div>
                        </div>
                        <div class="option" data-element="Carbon">
                            <i class="fas fa-circle text-success"></i>
                            <div><strong>Carbon</strong> <small>Atomic No: 6</small></div>
                        </div>
                        <div class="option" data-element="Nitrogen">
                            <i class="fas fa-circle text-success"></i>
                            <div><strong>Nitrogen</strong> <small>Atomic No: 7</small></div>
                        </div>
                        <div class="option" data-element="Oxygen">
                            <i class="fas fa-circle text-success"></i>
                            <div><strong>Oxygen</strong> <small>Atomic No: 8</small></div>
                        </div>
                        <div class="option" data-element="Neon">
                            <i class="fas fa-circle text-info"></i>
                            <div><strong>Neon</strong> <small>Atomic No: 10</small></div>
                        </div>
                        <div class="option" data-element="Sodium">
                            <i class="fas fa-circle text-warning"></i>
                            <div><strong>Sodium</strong> <small>Atomic No: 11</small></div>
                        </div>
                        <div class="option" data-element="Magnesium">
                            <i class="fas fa-circle text-warning"></i>
                            <div><strong>Magnesium</strong> <small>Atomic No: 12</small></div>
                        </div>
                    </div>
                </div>
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
            <div class="top-card">
                <div class="lab-info">
                    <div class="icon-box" style="background: linear-gradient(135deg, #2ecc71, #27ae60);">
                        <i class="fas fa-dna"></i>
                    </div>
                    <div>
                        <h3>Organic Chemistry Lab</h3>
                        <p>Learn about Carbon bonding and Hydrocarbons.</p>
                    </div>
                </div>

                <div class="dropdown-container">
                    <div class="custom-select" id="organic-dropdown">
                        <div class="selected">
                            <i class="fas fa-minus"></i>
                            <span>Alkanes (Single)</span>
                        </div>
                        <i class="fas fa-chevron-down arrow"></i>
                    </div>
                    <div class="dropdown-menu" id="organic-menu">
                        <div class="option active" data-mode="alkane">
                            <i class="fas fa-minus"></i>
                            <div><strong>Alkanes</strong> <small>Single carbon bonds</small></div>
                        </div>
                        <div class="option" data-mode="alkene">
                            <i class="fas fa-equals"></i>
                            <div><strong>Alkenes</strong> <small>Double carbon bonds</small></div>
                        </div>
                        <div class="option" data-mode="alkyne">
                            <i class="fas fa-bars"></i>
                            <div><strong>Alkynes</strong> <small>Triple carbon bonds</small></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div id="alkane-controls" class="card shadow-sm p-4 bg-white border-0 mb-4">
                        <h6 class="fw-bold mb-3">Hydrocarbon: <span id="org-name" class="text-primary">Methane</span></h6>
                        <p class="small text-muted mb-4">Formula: <span id="org-formula" class="fw-bold text-dark">CH<sub>4</sub></span></p>
                        <div class="d-grid gap-2">
                            <button id="add-carbon" class="btn btn-success btn-sm">+ Add Carbon</button>
                            <button id="remove-carbon" class="btn btn-danger btn-sm">- Remove Carbon</button>
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

        <!-- 3. Common Reactions Section -->
        <section class="mb-5">
            <div class="top-card">
                <div class="lab-info">
                    <div class="icon-box" style="background: linear-gradient(135deg, #f1c40f, #f39c12);">
                        <i class="fas fa-vial"></i>
                    </div>
                    <div>
                        <h3>Common Exam Reactions</h3>
                        <p>Essential balanced equations for your studies.</p>
                    </div>
                </div>
            </div>

            <div class="row justify-content-center">
                <div class="col-md-10">
                    <div class="card shadow-sm border-0 reaction-card">
                        <button class="btn nav-btn nav-left" id="prev-reaction"><i class="fas fa-chevron-left"></i></button>
                        <button class="btn nav-btn nav-right" id="next-reaction"><i class="fas fa-chevron-right"></i></button>
                        <div class="card-body py-5" id="reaction-display-area">
                            <div class="text-center"><div class="spinner-border text-primary"></div></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 4. Molecular Reaction Simulator -->
        <section class="mb-5">
            <div class="top-card">
                <div class="lab-info">
                    <div class="icon-box" style="background: linear-gradient(135deg, #6366f1, #9333ea);">
                        <i class="fas fa-flask-vial"></i>
                    </div>
                    <div>
                        <h3>Molecular Simulator</h3>
                        <p>Visualize chemical reactions at the molecular level.</p>
                    </div>
                </div>

                <div class="dropdown-container">
                    <div class="custom-select" id="reaction-dropdown">
                        <div class="selected">
                            <i class="fas fa-burn"></i>
                            <span>2H₂ + O₂ → 2H₂O</span>
                        </div>
                        <i class="fas fa-chevron-down arrow"></i>
                    </div>
                    <div class="dropdown-menu" id="reaction-menu">
                        <div class="option active" data-reaction="h2o">
                            <i class="fas fa-burn"></i>
                            <div><strong>Water Synthesis</strong> <small>2H₂ + O₂ → 2H₂O</small></div>
                        </div>
                        <div class="option" data-reaction="hcl">
                            <i class="fas fa-gas-pump"></i>
                            <div><strong>HCl Synthesis</strong> <small>H₂ + Cl₂ → 2HCl</small></div>
                        </div>
                        <div class="option" data-reaction="nh3">
                            <i class="fas fa-wind"></i>
                            <div><strong>Haber Process</strong> <small>N₂ + 3H₂ → 2NH₃</small></div>
                        </div>
                        <div class="option" data-reaction="co2_comb">
                            <i class="fas fa-fire"></i>
                            <div><strong>Combustion</strong> <small>CH₄ + 2O₂ → CO₂ + 2H₂O</small></div>
                        </div>
                        <div class="option" data-reaction="nacl">
                            <i class="fas fa-cube"></i>
                            <div><strong>Salt Formation</strong> <small>2Na + Cl₂ → 2NaCl</small></div>
                        </div>
                        <div class="option" data-reaction="co2">
                            <i class="fas fa-cloud"></i>
                            <div><strong>CO₂ Formation</strong> <small>C + O₂ → CO₂</small></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row justify-content-center">
                <div class="col-lg-12">
                    <div class="card shadow-sm border-0">
                        <div class="reaction-header">
                            <div class="reaction-title">Simulation Environment</div>
                            <div class="reaction-actions">
                                <button class="btn btn-outline-secondary btn-sm" id="learnmore-btn">Learn More</button>
                                <button class="btn btn-outline-secondary btn-sm" id="reaction-reset">Reset</button>
                                <button class="btn btn-gradient btn-sm" id="reaction-save">Save Report</button>
                            </div>
                        </div>
                        
                        <div class="reaction-container">
                            <div class="card border-0 bg-light">
                                <div class="card-body">
                                    <h5 class="card-title mb-4">Conditions</h5>
                                    <div class="controls-slider mb-4">
                                        <label>Temperature</label>
                                        <input type="range" id="temp-slider" class="form-range" min="0" max="500" value="298">
                                        <small class="text-muted"><span id="temp-value">298</span>K</small>
                                    </div>
                                    <div class="controls-slider mb-4">
                                        <label>Pressure</label>
                                        <input type="range" id="pressure-slider" class="form-range" min="0" max="10" step="0.1" value="1">
                                        <small class="text-muted"><span id="pressure-value">1.0</span> atm</small>
                                    </div>
                                    <button class="btn btn-gradient w-100 btn-sm mb-4" id="reaction-start">
                                        <i class="fas fa-play-circle me-1"></i> Start Simulation
                                    </button>
                                    <hr>
                                    <h5 class="card-title mb-3">Metrics</h5>
                                    <p class="mb-1"><strong>Yield:</strong> <span id="reaction-yield">0</span>%</p>
                                    <div class="reaction-progress mb-3"><div class="reaction-progress-bar" id="reaction-yield-bar"></div></div>
                                    <p class="small text-muted"><strong>Enthalpy (ΔH):</strong> <span id="reaction-enthalpy">-483.6</span> kJ/mol</p>
                                </div>
                            </div>
                            <div class="card border-0">
                                <div class="card-body text-center">
                                    <h5 class="card-title text-start mb-3">Molecular View</h5>
                                    <div class="simulation-box" id="reaction-sim"></div>
                                    <div class="mt-4 p-3 bg-light rounded">
                                        <strong id="reaction-equation" class="h5">2H₂ + O₂ → 2H₂O</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Info Popup -->
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
                        <p>Higher temperatures increase molecular kinetic energy, causing faster movement and more energetic collisions.</p>
                    </div>
                    <div class="info-card">
                        <h6><i class="fas fa-compress me-2"></i>Pressure Effect</h6>
                        <p>Increasing pressure raises concentration and collision frequency, leading to higher reaction rates.</p>
                    </div>
                </div>
                <div class="info-tips">
                    <h6><i class="fas fa-lightbulb me-2"></i>Experiment Tips</h6>
                    <ul>
                        <li>Increase temperature to see faster molecular movement</li>
                        <li>Watch how yield changes with different conditions</li>
                    </ul>
                </div>
            </div>
            <div class="info-footer">
                <button class="btn btn-primary btn-sm" id="infoGotItBtn">Got It!</button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="js/chemistry.js"></script>
    <script src="js/organic_chem.js"></script>
    <script src="js/reactions.js"></script>
    <script src="js/simple_reaction_sim.js"></script>
    <script src="js/reaction_simulator.js"></script>
</body>
</html>
