<?php
$pageTitle = 'Science & Maths Simulations - StudySmart';
$currentPage = 'simulate';

// Extra styles for this page
$extraHead = '
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .simulation-container .card {
            border-radius: 20px;
            transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, 1);
            border: 1px solid #e2e8f0;
            overflow: hidden;
            padding: 0 !important;
            display: flex;
            flex-direction: column;
            background: white;
        }
        .simulation-container .card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.12) !important;
            border-color: #cbd5e1;
        }

        /* Simulation Card Landscape */
        .simulation-card-landscape {
            position: relative;
            width: 100%;
            height: 120px;
            overflow: hidden;
        }
        .simulation-card-landscape * { position: absolute; }
        .simulation-card-landscape .sky { width: 100%; height: 100%; }
        .simulation-card-landscape .floating-shape {
            color: rgba(255, 255, 255, 0.2);
            font-size: 24px;
            pointer-events: none;
            z-index: 2;
        }

        /* Math Theme */
        .theme-math .sky { background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%); }
        .theme-math .shape-1 { top: 20%; left: 10%; font-size: 30px; transform: rotate(-15deg); }
        .theme-math .shape-2 { top: 50%; right: 15%; font-size: 40px; transform: rotate(10deg); opacity: 0.15; }
        .theme-math .shape-3 { bottom: 10%; left: 30%; font-size: 20px; }

        /* Physics Theme */
        .theme-physics .sky { background: linear-gradient(135deg, #0ea5e9 0%, #0369a1 100%); }
        .theme-physics .shape-1 { top: 15%; right: 20%; font-size: 35px; transform: rotate(15deg); } /* Atom/Space */
        .theme-physics .shape-2 { top: 40%; left: 15%; font-size: 28px; transform: rotate(-20deg); } /* Apple */
        .theme-physics .shape-3 { bottom: 20%; right: 40%; font-size: 22px; opacity: 0.1; }

        /* Chemistry Theme */
        .theme-chemistry .sky { background: linear-gradient(135deg, #f43f5e 0%, #be123c 100%); }
        .theme-chemistry .shape-1 { top: 25%; left: 20%; font-size: 32px; transform: rotate(-10deg); }
        .theme-chemistry .shape-2 { bottom: 20%; right: 20%; font-size: 38px; transform: rotate(20deg); opacity: 0.15; }
        .theme-chemistry .shape-3 { top: 10%; left: 50%; font-size: 18px; }

        .simulation-card-content {
            padding: 24px;
            flex: 1;
            display: flex;
            flex-direction: column;
            text-align: center;
        }

        .simulation-container .icon-circle {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: -35px auto 20px;
            font-size: 28px;
            color: white;
            position: relative;
            z-index: 10;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            border: 4px solid white;
        }
        
        .bg-math { background: #4f46e5; }
        .bg-physics { background: #0ea5e9; }
        .bg-chemistry { background: #f43f5e; }
        
        @media (max-width: 768px) {
            .simulation-container .icon-circle { width: 60px; height: 60px; font-size: 24px; margin-bottom: 15px; }
            .simulation-container h3 { font-size: 1.25rem; }
        }
    </style>
';

include __DIR__ . '/../layouts/header.php';
?>

<div class="container mt-4 pb-5 simulation-container">
    <div class="text-center mb-5">
        <h1 class="display-5 fw-bold mb-3">Laboratory Simulators</h1>
        <p class="text-muted">Interactive visual experiments for STEM subjects</p>
    </div>

    <div class="row g-4 justify-content-center">
        <!-- Mathematics Card -->
        <div class="col-lg-4 col-md-6">
            <div class="card h-100 shadow-sm">
                <div class="simulation-card-landscape theme-math">
                    <div class="sky"></div>
                    <i class="fas fa-pi floating-shape shape-1"></i>
                    <i class="fas fa-infinity floating-shape shape-2"></i>
                    <i class="fas fa-calculator floating-shape shape-3"></i>
                </div>
                <div class="simulation-card-content">
                    <div class="icon-circle bg-math">
                        <i class="fas fa-square-root-variable"></i>
                    </div>
                    <h3 class="card-title fw-bold mb-3">Mathematics</h3>
                    <p class="card-text text-muted mb-4">
                        Explore Euclidean Geometry theorems, Trigonometric waves, and Algebraic functions with real-time interactive graphs.
                    </p>
                    <div class="mt-auto">
                        <a href="/math" class="btn btn-primary w-100 py-2 fw-bold" style="border-radius: 12px; background: #4f46e5; border: none;">Open Maths Lab</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Physics Card -->
        <div class="col-lg-4 col-md-6">
            <div class="card h-100 shadow-sm">
                <div class="simulation-card-landscape theme-physics">
                    <div class="sky"></div>
                    <i class="fas fa-apple-whole floating-shape shape-1"></i>
                    <i class="fas fa-atom floating-shape shape-2"></i>
                    <i class="fas fa-user-astronaut floating-shape shape-3"></i>
                </div>
                <div class="simulation-card-content">
                    <div class="icon-circle bg-physics">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <h3 class="card-title fw-bold mb-3">Physics</h3>
                    <p class="card-text text-muted mb-4">
                        Simulate pendulums, free fall, projectile motion, and Doppler effect with dynamic calculations and visualisations.
                    </p>
                    <div class="mt-auto">
                        <a href="/physics" class="btn btn-success w-100 py-2 fw-bold" style="border-radius: 12px; background: #0ea5e9; border: none;">Open Physics Lab</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chemistry Card -->
        <div class="col-lg-4 col-md-6">
            <div class="card h-100 shadow-sm">
                <div class="simulation-card-landscape theme-chemistry">
                    <div class="sky"></div>
                    <i class="fas fa-flask floating-shape shape-1"></i>
                    <i class="fas fa-bubbles floating-shape shape-2"></i>
                    <i class="fas fa-dna floating-shape shape-3"></i>
                </div>
                <div class="simulation-card-content">
                    <div class="icon-circle bg-chemistry">
                        <i class="fas fa-vial"></i>
                    </div>
                    <h3 class="card-title fw-bold mb-3">Chemistry</h3>
                    <p class="card-text text-muted mb-4">
                        Visualize Bohr atomic models, organic hydrocarbon bonding (alkanes, alkenes, alkynes), and common exam reactions.
                    </p>
                    <div class="mt-auto">
                        <a href="/chemistry" class="btn btn-danger w-100 py-2 fw-bold" style="border-radius: 12px; background: #f43f5e; border: none;">Open Chemistry Lab</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$extraScripts = '
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
';
include __DIR__ . '/../layouts/footer.php';
?>
