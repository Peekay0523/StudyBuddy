<?php
$pageTitle = 'Science & Maths Practical App';
$currentPage = 'simulate';
include __DIR__ . '/../layouts/header.php';
?>
<style>
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif !important;
    background-color: #f5f7fb !important;
}

/* FORCE proper row layout */
.simulation-container .row {
    display: flex !important;
    flex-wrap: wrap !important;
    justify-content: center !important;
}

/* FORCE equal columns */
.simulation-container .col-md-4 {
    flex: 0 0 32% !important;
    max-width: 32% !important;
    display: flex !important;
}

/* Mobile responsiveness */
@media (max-width: 768px) {
    .simulation-container .col-md-4 {
        flex: 0 0 100% !important;
        max-width: 100% !important;
    }
}

/* CARD FIX */
.simulation-container .card {
    width: 100% !important;
    border-radius: 16px !important;
    background: #ffffff !important;
    box-shadow: 0 8px 20px rgba(0,0,0,0.08) !important;
    transition: all 0.3s ease !important;
    padding: 10px !important;
}

/* Hover effect */
.simulation-container .card:hover {
    transform: translateY(-6px) !important;
    box-shadow: 0 15px 30px rgba(0,0,0,0.12) !important;
}

/* CARD BODY */
.simulation-container .card-body {
    text-align: center !important;
    padding: 25px 15px !important;
}

/* ICON FIX */
.simulation-container .card i {
    font-size: 30px !important;
    color: white !important;
    background: linear-gradient(135deg, #4e73df, #224abe) !important;
    padding: 20px !important;
    border-radius: 50% !important;
    margin-bottom: 15px !important;
}

/* Different icon colors */
.simulation-container .col-md-4:nth-child(2) i {
    background: linear-gradient(135deg, #1cc88a, #13855c) !important;
}

.simulation-container .col-md-4:nth-child(3) i {
    background: linear-gradient(135deg, #e74a3b, #c0392b) !important;
}

/* TITLE */
.simulation-container .card-title {
    font-size: 18px !important;
    font-weight: 600 !important;
}

/* TEXT */
.simulation-container .card-text {
    font-size: 14px !important;
    color: #666 !important;
    min-height: 60px !important;
}

/* BUTTON */
.simulation-container .btn {
    border-radius: 10px !important;
    padding: 10px !important;
    font-weight: 500 !important;
}
.btn-success {
    background-color: #28a745 !important;
    border-color: #28a745 !important;
    color: #ffffff !important;
}

.btn-success:hover {
    background-color: #218838 !important;
    border-color: #1e7e34 !important;
}

/* Remove weird inherited spacing */
.simulation-container .mb-3,
.simulation-container .mb-md-4 {
    margin-bottom: 0 !important;
}
    </style>
<div class="container mt-4 mt-md-5 simulation-container">
    <div class="row text-center">
        <div class="col-12 col-md-4 mb-3 mb-md-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body p-4">
                    <i class="fas fa-square-root-variable fa-2x mb-3"></i>
                    <h3 class="card-title h4 fw-bold">Mathematics</h3>
                    <p class="card-text text-muted small">Explore Euclidean Geometry, Trigonometry, and algebraic functions with interactive graphs.</p>
                    <a href="math" class="btn btn-primary w-100 thin-btn mt-2">Open Maths Lab</a>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4 mb-3 mb-md-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body p-4">
                    <i class="fas fa-atom fa-2x mb-3"></i>

                    <h3 class="card-title h4 fw-bold">Physics</h3>
                    <p class="card-text text-muted small">Simulate pendulums, free fall, and projectile motion with real-time calculations.</p>
                    <a href="physics" class="btn btn-success w-100 thin-btn mt-2">Open Physics Lab</a>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4 mb-3 mb-md-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body p-4">
                    <i class="fas fa-vial fa-2x mb-3"></i>
                    <h3 class="card-title h4 fw-bold">Chemistry</h3>
                    <p class="card-text text-muted small">Visualize Bohr models, hydrocarbon bonding, and common chemical reactions.</p>
                    <a href="chemistry" class="btn btn-danger w-100 thin-btn mt-2">Open Chemistry Lab</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
