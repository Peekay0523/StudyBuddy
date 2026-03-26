<?php
$pageTitle = 'Careers - StudySmart';
$currentPage = 'careers';
$cacheBuster = time();
include __DIR__ . '/../layouts/header.php';
?>

<style>
.careers-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.careers-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 25px;
    margin-top: 30px;
}

.career-card {
    background: white;
    border-radius: 16px;
    padding: 25px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    transition: transform 0.2s, box-shadow 0.2s;
    border-left: 5px solid #667eea;
}

.career-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 20px rgba(0, 0, 0, 0.1);
}

.career-icon {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 20px;
}

.career-icon i {
    font-size: 28px;
    color: white;
}

.career-title {
    margin: 0 0 15px 0;
    color: #1f2937;
    font-size: 1.3rem;
    font-weight: 700;
}

.career-description {
    color: #4b5563;
    font-size: 0.95rem;
    line-height: 1.6;
    margin-bottom: 20px;
}

.career-meta {
    display: flex;
    gap: 15px;
    align-items: center;
    padding-top: 15px;
    border-top: 1px solid #f3f4f6;
}

.aps-badge {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 6px 12px;
    border-radius: 15px;
    font-weight: 600;
    font-size: 0.85rem;
}

.institution-count {
    color: #6b7280;
    font-size: 0.85rem;
}

.view-details-btn {
    display: block;
    width: 100%;
    padding: 12px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    text-align: center;
    text-decoration: none;
    border-radius: 10px;
    font-weight: 600;
    margin-top: 15px;
    transition: transform 0.2s;
}

.view-details-btn:hover {
    transform: translateY(-2px);
}

.search-box {
    background: white;
    border-radius: 16px;
    padding: 25px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    margin-bottom: 30px;
}

.search-input-group {
    display: flex;
    gap: 10px;
}

.search-input-group input[type="text"] {
    flex: 1;
    padding: 12px 20px;
    border: 2px solid #e5e7eb;
    border-radius: 10px;
    font-size: 1rem;
}

.search-input-group input[type="text"]:focus {
    outline: none;
    border-color: #667eea;
}

.search-input-group button {
    padding: 12px 30px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 10px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: transform 0.2s;
}

.search-input-group button:hover {
    transform: translateY(-2px);
}

.filter-section {
    background: white;
    border-radius: 16px;
    padding: 25px;
    margin-bottom: 30px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
}

.filter-section h3 {
    margin: 0 0 20px 0;
    color: #1f2937;
    font-size: 1.2rem;
}

.filter-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.filter-tag {
    padding: 10px 20px;
    background: #f3f4f6;
    color: #374151;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    border: 2px solid transparent;
}

.filter-tag:hover {
    background: #e5e7eb;
}

.filter-tag.active {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.loading-state {
    text-align: center;
    padding: 60px 20px;
    color: #6b7280;
}

.loading-spinner {
    width: 50px;
    height: 50px;
    border: 4px solid #e5e7eb;
    border-top: 4px solid #667eea;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 20px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.no-results {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 16px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
}

.no-results i {
    font-size: 4rem;
    color: #d1d5db;
    margin-bottom: 20px;
}

.no-results h3 {
    color: #374151;
    margin-bottom: 10px;
}

.no-results p {
    color: #6b7280;
}

@media (max-width: 768px) {
    .careers-grid {
        grid-template-columns: 1fr;
    }

    .search-input-group {
        flex-direction: column;
    }

    .filter-tags {
        justify-content: center;
    }
}
</style>

<div class="careers-container">
    <h1 class="title" style="margin-bottom: 10px;">
        <i class="fas fa-compass"></i> Explore Careers
    </h1>
    <p class="subtitle" style="margin-bottom: 30px;">
        Discover exciting career opportunities and plan your future
    </p>

    <!-- Search Box -->
    <div class="search-box">
        <form id="searchForm" onsubmit="return false;">
            <div class="search-input-group">
                <input
                    type="text"
                    id="searchInput"
                    placeholder="Search for a career..."
                    autocomplete="off"
                >
                <button type="submit" id="searchBtn">
                    <i class="fas fa-search"></i> Search
                </button>
            </div>
        </form>
    </div>

    <!-- Filter Section -->
    <div class="filter-section">
        <h3><i class="fas fa-filter"></i> Browse by Category</h3>
        <div class="filter-tags" id="filterTags">
            <span class="filter-tag active" data-category="all">All Careers</span>
            <span class="filter-tag" data-category="Engineering">Engineering</span>
            <span class="filter-tag" data-category="Health">Health & Medicine</span>
            <span class="filter-tag" data-category="Business">Business & Commerce</span>
            <span class="filter-tag" data-category="Science">Science</span>
            <span class="filter-tag" data-category="Technology">Technology</span>
            <span class="filter-tag" data-category="Arts">Arts & Design</span>
            <span class="filter-tag" data-category="Education">Education</span>
            <span class="filter-tag" data-category="Law">Law & Justice</span>
        </div>
    </div>

    <!-- Results -->
    <div id="resultsContainer">
        <div class="loading-state">
            <div class="loading-spinner"></div>
            <p>Loading careers...</p>
        </div>
    </div>
</div>

<script>
let allCareers = [];
let currentCategory = 'all';

document.addEventListener('DOMContentLoaded', function() {
    loadAllCareers();
    
    document.getElementById('searchInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            searchCareers();
        }
    });

    document.getElementById('filterTags').addEventListener('click', function(e) {
        if (e.target.classList.contains('filter-tag')) {
            document.querySelectorAll('.filter-tag').forEach(tag => tag.classList.remove('active'));
            e.target.classList.add('active');
            currentCategory = e.target.dataset.category;
            filterCareers();
        }
    });
});

async function loadAllCareers() {
    try {
        const response = await fetch('/api/search-careers?q=&limit=100');
        const data = await response.json();
        
        if (data.success) {
            allCareers = data.careers;
            displayAllCareers(allCareers);
        } else {
            displayNoCareers();
        }
    } catch (error) {
        console.error('Error loading careers:', error);
        displayNoCareers();
    }
}

function displayAllCareers(careers) {
    const container = document.getElementById('resultsContainer');
    
    if (careers.length === 0) {
        displayNoCareers();
        return;
    }
    
    let html = `<div class="careers-grid">`;
    
    careers.forEach(career => {
        const iconClass = getCareerIcon(career.name);
        html += `
            <div class="career-card" data-category="${getCareerCategory(career.name)}">
                <div class="career-icon">
                    <i class="${iconClass}"></i>
                </div>
                <h3 class="career-title">${escapeHtml(career.name)}</h3>
                <p class="career-description">${escapeHtml(career.description || 'Explore this exciting career path')}</p>
                <div class="career-meta">
                    <span class="aps-badge">Min APS: ${career.min_aps_score || 'N/A'}</span>
                    <span class="institution-count">
                        <i class="fas fa-university"></i> ${career.institution_count || 0} institutions
                    </span>
                </div>
                <a href="/search-careers?career=${encodeURIComponent(career.name)}" class="view-details-btn">
                    <i class="fas fa-arrow-right"></i> View Details
                </a>
            </div>
        `;
    });
    
    html += `</div>`;
    container.innerHTML = html;
}

function displayNoCareers() {
    const container = document.getElementById('resultsContainer');
    container.innerHTML = `
        <div class="no-results">
            <i class="fas fa-briefcase"></i>
            <h3>No Careers Available</h3>
            <p>Check back later for new career opportunities</p>
        </div>
    `;
}

function searchCareers() {
    const searchTerm = document.getElementById('searchInput').value.trim();
    
    if (!searchTerm) {
        filterCareers();
        return;
    }
    
    const filtered = allCareers.filter(career => 
        career.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
        (career.description && career.description.toLowerCase().includes(searchTerm.toLowerCase()))
    );
    
    displayAllCareers(filtered);
}

function filterCareers() {
    if (currentCategory === 'all') {
        displayAllCareers(allCareers);
    } else {
        const filtered = allCareers.filter(career => 
            getCareerCategory(career.name) === currentCategory
        );
        displayAllCareers(filtered);
    }
}

function getCareerIcon(careerName) {
    const name = careerName.toLowerCase();
    if (name.includes('engineer')) return 'fas fa-cog';
    if (name.includes('doctor') || name.includes('nurse') || name.includes('health')) return 'fas fa-stethoscope';
    if (name.includes('teacher') || name.includes('educat')) return 'fas fa-chalkboard-teacher';
    if (name.includes('lawyer') || name.includes('legal') || name.includes('law')) return 'fas fa-gavel';
    if (name.includes('artist') || name.includes('design') || name.includes('art')) return 'fas fa-palette';
    if (name.includes('business') || name.includes('commerce') || name.includes('finance')) return 'fas fa-chart-line';
    if (name.includes('scientist') || name.includes('science')) return 'fas fa-flask';
    if (name.includes('tech') || name.includes('computer') || name.includes('software')) return 'fas fa-laptop-code';
    return 'fas fa-briefcase';
}

function getCareerCategory(careerName) {
    const name = careerName.toLowerCase();
    if (name.includes('engineer')) return 'Engineering';
    if (name.includes('doctor') || name.includes('nurse') || name.includes('health') || name.includes('medic')) return 'Health';
    if (name.includes('business') || name.includes('commerce') || name.includes('finance') || name.includes('account')) return 'Business';
    if (name.includes('scientist') || name.includes('science') || name.includes('biology') || name.includes('chemistry')) return 'Science';
    if (name.includes('tech') || name.includes('computer') || name.includes('software') || name.includes('program')) return 'Technology';
    if (name.includes('artist') || name.includes('design') || name.includes('art') || name.includes('music')) return 'Arts';
    if (name.includes('teacher') || name.includes('educat') || name.includes('professor')) return 'Education';
    if (name.includes('lawyer') || name.includes('legal') || name.includes('law') || name.includes('justice')) return 'Law';
    return 'Other';
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
