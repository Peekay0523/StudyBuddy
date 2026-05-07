<?php
$pageTitle = 'Science & Maths Simulations - StudySmart';
$currentPage = 'simulate';

// Extra styles for this page
$extraHead = '
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .simulation-container .card {
            border-radius: 16px;
            transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, 1);
            border: none;
            overflow: hidden;
        }
        .simulation-container .card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1) !important;
        }
        .simulation-container .icon-circle {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 32px;
            color: white;
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }
        .bg-math { background: linear-gradient(135deg, #4e73df, #224abe); }
        .bg-physics { background: linear-gradient(135deg, #1cc88a, #13855c); }
        .bg-chemistry { background: linear-gradient(135deg, #e74a3b, #c0392b); }
        
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
        
    </div>

    <div class="row g-4 justify-content-center">
        <!-- Mathematics Card -->
        <div class="col-lg-4 col-md-6">
            <div class="card h-100 shadow-sm text-center p-4">
                <div class="card-body d-flex flex-column">
                    <div class="icon-circle bg-math">
                        <i class="fas fa-square-root-variable"></i>
                    </div>
                    <h3 class="card-title fw-bold mb-3">Mathematics</h3>
                    <p class="card-text text-muted mb-4">
                        Explore Euclidean Geometry theorems, Trigonometric waves, and Algebraic functions with real-time interactive graphs.
                    </p>
                    <div class="mt-auto">
                        <a href="/math" class="btn btn-primary w-100 py-2 fw-bold">Open Maths Lab</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Physics Card -->
        <div class="col-lg-4 col-md-6">
            <div class="card h-100 shadow-sm text-center p-4">
                <div class="card-body d-flex flex-column">
                    <div class="icon-circle bg-physics">
                        <i class="fas fa-atom"></i>
                    </div>
                    <h3 class="card-title fw-bold mb-3">Physics</h3>
                    <p class="card-text text-muted mb-4">
                        Simulate pendulums, free fall, projectile motion, and Doppler effect with dynamic calculations and visualisations.
                    </p>
                    <div class="mt-auto">
                        <a href="/physics" class="btn btn-success w-100 py-2 fw-bold">Open Physics Lab</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chemistry Card -->
        <div class="col-lg-4 col-md-6">
            <div class="card h-100 shadow-sm text-center p-4">
                <div class="card-body d-flex flex-column">
                    <div class="icon-circle bg-chemistry">
                        <i class="fas fa-vial"></i>
                    </div>
                    <h3 class="card-title fw-bold mb-3">Chemistry</h3>
                    <p class="card-text text-muted mb-4">
                        Visualize Bohr atomic models, organic hydrocarbon bonding (alkanes, alkenes, alkynes), and common exam reactions.
                    </p>
                    <div class="mt-auto">
                        <a href="/chemistry" class="btn btn-danger w-100 py-2 fw-bold">Open Chemistry Lab</a>
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
