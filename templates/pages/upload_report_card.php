<?php
$pageTitle = 'Upload Report Card - StudySmart';
$currentPage = 'careers';

// Get subscription info
$user = getCurrentUser();
$isFreeTier = isFreeTierUser($user['id']);
$subscription = getUserSubscription($user['id']);

$isFreeTierJson = $isFreeTier ? 'true' : 'false';

$extraHead = <<<'SCRIPT'
<style>
/* Loading Overlay */
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.9);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.3s ease, visibility 0.3s ease;
}
.loading-overlay.active {
    opacity: 1;
    visibility: visible;
}
.loading-spinner {
    width: 60px;
    height: 60px;
    border: 4px solid #e9ecef;
    border-top: 4px solid #667eea;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
.loading-text {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, 50px);
    color: #6c757d;
    font-size: 1rem;
    font-weight: 500;
}

/* Bursaries Section */
.bursaries-list-container {
    display: grid;
    gap: 20px;
}

.bursary-item {
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    border-left: 4px solid #667eea;
    transition: transform 0.2s, box-shadow 0.2s;
}

.bursary-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
}

.bursary-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    flex-wrap: wrap;
    gap: 10px;
}

.bursary-header h4 {
    margin: 0;
    color: #1f2937;
    font-size: 18px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.bursary-deadline {
    font-size: 13px;
    color: #6b7280;
    padding: 6px 12px;
    background: #f3f4f6;
    border-radius: 20px;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.bursary-deadline.urgent {
    background: #fef3c7;
    color: #92400e;
}

.bursary-provider {
    font-size: 14px;
    color: #6b7280;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.bursary-details {
    margin-bottom: 15px;
}

.bursary-details p {
    margin: 8px 0;
    font-size: 14px;
    color: #374151;
    line-height: 1.6;
}

.bursary-details strong {
    color: #1f2937;
}

.bursary-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    padding-top: 15px;
    border-top: 1px solid #e5e7eb;
}

.no-bursaries {
    text-align: center;
    padding: 40px 20px;
    background: #f9fafb;
    border-radius: 12px;
    border: 2px dashed #e5e7eb;
}

/* Career Search Section */
.career-search-section {
    background: white;
    border-radius: 16px;
    padding: 25px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    margin-bottom: 30px;
}

.career-search-box {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
}

.career-search-input {
    flex: 1;
    padding: 12px 20px;
    border: 2px solid #e5e7eb;
    border-radius: 10px;
    font-size: 1rem;
    transition: border-color 0.2s;
}

.career-search-input:focus {
    outline: none;
    border-color: #667eea;
}

.career-search-btn {
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

.career-search-btn:hover {
    transform: translateY(-2px);
}

.career-search-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

.career-results {
    display: grid;
    gap: 15px;
    margin-top: 20px;
}

.career-result-item {
    background: #f9fafb;
    border-radius: 12px;
    padding: 20px;
    border-left: 4px solid #667eea;
    transition: all 0.2s;
    margin-bottom: 15px;
}

.career-result-item:hover {
    background: #eff6ff;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.career-result-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.career-result-name {
    font-size: 18px;
    font-weight: 700;
    color: #1f2937;
    margin: 0;
}

.career-result-aps {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.career-result-description {
    color: #6b7280;
    font-size: 14px;
    line-height: 1.6;
    margin-bottom: 15px;
}

.career-result-subjects {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 10px;
}

.subject-badge {
    background: #e0e7ff;
    color: #4f46e5;
    padding: 6px 12px;
    border-radius: 15px;
    font-size: 12px;
    font-weight: 500;
}

.career-no-results {
    text-align: center;
    padding: 40px 20px;
    background: #f9fafb;
    border-radius: 12px;
    border: 2px dashed #e5e7eb;
    color: #6b7280;
}

.career-loading {
    text-align: center;
    padding: 30px;
    color: #6b7280;
}

.career-feedback-section {
    margin-top: 20px;
    padding: 20px;
    background: #f0fdf4;
    border-radius: 12px;
    border-left: 4px solid #10b981;
}

.career-feedback-title {
    font-size: 16px;
    font-weight: 700;
    color: #059669;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.career-feedback-item {
    margin-bottom: 12px;
    font-size: 14px;
    color: #374151;
    line-height: 1.6;
}

.career-feedback-item strong {
    color: #1f2937;
}

.feedback-meets {
    color: #10b981;
    font-weight: 600;
}

.feedback-below {
    color: #f59e0b;
    font-weight: 600;
}

.feedback-excellent {
    color: #667eea;
    font-weight: 600;
}

/* Responsive layout for upload report card page */
@media (max-width: 1024px) {
    .upload-container {
        grid-template-columns: 1fr !important;
    }

    .career-search-card {
        position: static !important;
    }
}

@media (max-width: 768px) {
    .career-search-card {
        padding: 20px !important;
    }

    .career-search-box {
        flex-direction: column;
    }

    .career-search-btn {
        width: 100%;
    }
}
</style>
SCRIPT;

$extraHead .= <<<'SCRIPT'
<script>
let isFreeTierGlobal = false;

// Show/hide loading overlay
function showLoading(message) {
    const overlay = document.getElementById('loadingOverlay');
    const text = document.querySelector('.loading-text');
    if (overlay) {
        if (message) text.textContent = message;
        overlay.classList.add('active');
    }
}

function hideLoading() {
    const overlay = document.getElementById('loadingOverlay');
    if (overlay) {
        overlay.classList.remove('active');
    }
}

function initUploadHandlers() {
    const fileInput = document.getElementById("report_card_file");
    const previewSection = document.getElementById("preview-section");
    const fileName = document.getElementById("file-name");
    const fileSize = document.getElementById("file-size");
    const clearBtn = document.getElementById("clear-btn");
    const uploadArea = document.getElementById("upload-area");

    // Drag and drop
    if (uploadArea) {
        uploadArea.addEventListener("dragover", (e) => {
            e.preventDefault();
            uploadArea.style.borderColor = "#667eea";
            uploadArea.style.background = "#e0e7ff";
        });

        uploadArea.addEventListener("dragleave", () => {
            uploadArea.style.borderColor = "#cbd5e1";
            uploadArea.style.background = "#f8fafc";
        });

        uploadArea.addEventListener("drop", (e) => {
            e.preventDefault();
            uploadArea.style.borderColor = "#cbd5e1";
            uploadArea.style.background = "#f8fafc";

            const files = Array.from(e.dataTransfer.files);
            if (files.length > 0) {
                handleFile(files[0]);
            }
        });
    }

    // File input change
    if (fileInput) {
        fileInput.addEventListener("change", (e) => {
            if (e.target.files && e.target.files[0]) {
                handleFile(e.target.files[0]);
            }
        });
    }

    // Clear selection
    if (clearBtn && fileInput && previewSection && uploadArea) {
        clearBtn.addEventListener("click", () => {
            fileInput.value = "";
            previewSection.style.display = "none";
            uploadArea.style.display = "block";
        });
    }
}

function handleFile(file) {
    const allowedTypes = ["application/pdf", "application/vnd.openxmlformats-officedocument.wordprocessingml.document", "image/jpeg", "image/jpg", "image/png"];

    if (!allowedTypes.includes(file.type)) {
        alert("Invalid file type. Please upload PDF, DOCX, JPG, or PNG.");
        return;
    }

    if (file.size > 10 * 1024 * 1024) {
        alert("File size must be less than 10MB.");
        return;
    }

    const fileName = document.getElementById("file-name");
    const fileSize = document.getElementById("file-size");
    const previewSection = document.getElementById("preview-section");
    const uploadArea = document.getElementById("upload-area");

    if (fileName && fileSize && previewSection && uploadArea) {
        fileName.textContent = file.name;
        fileSize.textContent = (file.size / 1024 / 1024).toFixed(2) + " MB";
        previewSection.style.display = "block";
        uploadArea.style.display = "none";
    }
}

async function loadUploadedReportCards() {
    try {
        const response = await fetch("/api/get-user-report-cards");
        const data = await response.json();

        const reportCardsList = document.getElementById("report-cards-list");
        if (reportCardsList) {
            if (data.report_cards && data.report_cards.length > 0) {
                reportCardsList.innerHTML = data.report_cards.map(rc => `
                    <div class="report-card-item">
                        <div class="report-card-info">
                            <h4>Report Card - ${rc.grade ? "Grade " + rc.grade : ""} ${rc.term ? "Term " + rc.term : ""}</h4>
                            <p class="report-card-meta">
                                <span class="badge blue">${rc.file_path}</span>
                                <span class="badge green">${new Date(rc.uploaded_at).toLocaleDateString()}</span>
                            </p>
                        </div>
                        <div class="report-card-actions">
                            ${isFreeTierGlobal
                                ? `<a href="/subscription" class="btn-primary btn-sm" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                                    <i class="fas fa-lock"></i> Upgrade to View Recommendations
                                </a>`
                                : `<a href="/view-career-recommendations/${rc.id}" class="btn-primary btn-sm">
                                    <i class="fas fa-compass"></i> View Career Recommendations
                                </a>`
                            }
                            <form method="POST" action="/delete-report-card/${rc.id}" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this report card?');">
                                <button type="submit" class="btn-sm btn-sm-danger" style="cursor: pointer;">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </form>
                        </div>
                    </div>
                `).join("");
            } else {
                reportCardsList.innerHTML = "<p class=\"no-data\">No report cards uploaded yet. Upload your first report card to get career recommendations!</p>";
            }
        }
    } catch (error) {
        console.error("Failed to load report cards:", error);
    }
}

async function loadAvailableBursaries() {
    try {
        const response = await fetch("/api/get-available-bursaries");
        const data = await response.json();

        const bursariesList = document.getElementById("bursaries-list");
        if (bursariesList) {
            if (data.success && data.bursaries && data.bursaries.length > 0) {
                bursariesList.innerHTML = data.bursaries.map(bursary => `
                    <div class="bursary-item">
                        <div class="bursary-header">
                            <h4><i class="fas fa-scholarship"></i> ${escapeHtml(bursary.name)}</h4>
                            <span class="bursary-deadline ${bursary.days_left < 30 ? 'urgent' : ''}">
                                <i class="fas fa-calendar-alt"></i> 
                                ${new Date(bursary.deadline).toLocaleDateString()} 
                                (${bursary.days_left} days left)
                            </span>
                        </div>
                        <div class="bursary-provider">
                            <i class="fas fa-building"></i> ${escapeHtml(bursary.provider)}
                        </div>
                        <div class="bursary-details">
                            <p><strong>Eligibility:</strong> ${escapeHtml(bursary.eligibility)}</p>
                            ${bursary.covers ? `<p><strong>Covers:</strong> ${escapeHtml(bursary.covers)}</p>` : ''}
                            ${bursary.required_subjects && bursary.required_subjects.length > 0 ? `
                                <p><strong>Required Subjects:</strong> 
                                    <span class="badge blue">${bursary.required_subjects.join('</span> <span class="badge blue">')}</span>
                                </p>
                            ` : ''}
                            <p><strong>Grade Range:</strong> ${bursary.min_grade_average}% - ${bursary.max_grade_average}%</p>
                            ${bursary.contact ? `<p><strong>Contact:</strong> ${escapeHtml(bursary.contact)}</p>` : ''}
                        </div>
                        <div class="bursary-actions">
                            ${bursary.apply_url ? `
                                <a href="${escapeHtml(bursary.apply_url)}" target="_blank" class="btn-primary btn-sm" style="text-decoration: none; display: inline-flex; align-items: center; gap: 5px;">
                                    <i class="fas fa-external-link-alt"></i> Apply Now
                                </a>
                            ` : ''}
                            <a href="https://www.google.com/search?q=${encodeURIComponent(bursary.name + ' application')}" target="_blank" class="btn-secondary btn-sm" style="text-decoration: none; display: inline-flex; align-items: center; gap: 5px;">
                                <i class="fas fa-search"></i> Search
                            </a>
                            <button type="button" onclick="markBursaryAsApplied('${escapeHtml(bursary.name)}', '${escapeHtml(bursary.provider)}')" class="btn-applied-mark" style="padding: 8px 16px; font-size: 13px; background: linear-gradient(135deg, #10b981, #059669); color: white; border: none; border-radius: 6px; cursor: pointer; display: inline-flex; align-items: center; gap: 5px;">
                                <i class="fas fa-check-circle"></i> Mark Applied
                            </button>
                        </div>
                    </div>
                `).join("");
            } else {
                bursariesList.innerHTML = `
                    <div class="no-bursaries">
                        <i class="fas fa-inbox" style="font-size: 48px; color: #cbd5e1; margin-bottom: 15px; display: block;"></i>
                        <p style="color: #6b7280; margin: 0;">No bursaries available at the moment. Check back later!</p>
                    </div>
                `;
            }
        }
    } catch (error) {
        console.error("Failed to load bursaries:", error);
        const bursariesList = document.getElementById("bursaries-list");
        if (bursariesList) {
            bursariesList.innerHTML = '<p class="error">Failed to load bursaries. Please try again later.</p>';
        }
    }
}

// Mark bursary as applied
async function markBursaryAsApplied(bursaryName, bursaryProvider) {
    if (!confirm(`Mark "${bursaryName}" as applied? This will add it to your applications on the dashboard.`)) {
        return;
    }

    const button = event.target.closest('button');
    const originalText = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

    try {
        const response = await fetch('/api/mark-bursary-applied', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                'bursary_name': bursaryName,
                'bursary_provider': bursaryProvider
            })
        });

        const result = await response.json();

        if (result.success) {
            alert('✓ Bursary marked as applied! Check your dashboard to track your application.');
            button.innerHTML = '<i class="fas fa-check"></i> Applied';
            button.disabled = true;
            button.style.background = 'linear-gradient(135deg, #6b7280, #4b5563)';
            button.style.cursor = 'not-allowed';
        } else {
            alert('Error: ' + (result.error || 'Failed to mark as applied'));
            button.disabled = false;
            button.innerHTML = originalText;
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
        button.disabled = false;
        button.innerHTML = originalText;
    }
}

// Career Search Functions
let userSubjects = [];
let userGrades = {};
let calculatedAPS = 0;

async function searchCareers() {
    const searchInput = document.getElementById('career-search-input');
    const resultsContainer = document.getElementById('career-results-container');
    const searchBtn = document.getElementById('career-search-btn');

    const query = searchInput.value.trim();

    if (!query) {
        resultsContainer.innerHTML = '<div class="career-no-results"><i class="fas fa-search" style="font-size: 32px; margin-bottom: 10px; display: block;"></i><p>Please enter a career name to search</p></div>';
        return;
    }

    searchBtn.disabled = true;
    searchBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Searching...';
    resultsContainer.innerHTML = '<div class="career-loading"><i class="fas fa-spinner fa-spin" style="font-size: 32px; margin-bottom: 10px; display: block;"></i><p>Searching careers...</p></div>';

    try {
        const response = await fetch(`/api/search-careers?q=${encodeURIComponent(query)}`);
        const data = await response.json();

        if (data.success && data.careers && data.careers.length > 0) {
            resultsContainer.innerHTML = data.careers.map(career => {
                const institutions = career.institutions || [];
                
                // Build institutions HTML
                let institutionsHtml = '';
                if (institutions.length > 0) {
                    institutionsHtml = institutions.map(inst => {
                        const subjects = inst.subject_requirements || [];
                        const qualifications = inst.qualifications || [];
                        
                        // Subjects with levels
                        const subjectsHtml = subjects.length > 0 ? 
                            subjects.map(s => `<span class="subject-badge">${escapeHtml(s.subject)} - Level ${s.level}</span>`).join('') : 
                            '<span style="color: #6b7280; font-size: 12px;">No specific subjects required</span>';
                        
                        // Qualifications
                        const qualificationsHtml = qualifications.length > 0 ?
                            qualifications.map(q => `<div style="font-size: 13px; color: #374151; margin: 4px 0;"><i class="fas fa-certificate" style="color: #667eea; margin-right: 5px;"></i>${escapeHtml(q.name)} (${escapeHtml(q.type)}, ${escapeHtml(q.duration || 'N/A')})</div>`).join('') :
                            '<span style="color: #6b7280; font-size: 12px;">No qualifications listed</span>';
                        
                        return `
                            <div style="background: #f9fafb; padding: 12px; border-radius: 8px; margin-bottom: 10px; border-left: 3px solid #667eea;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                    <h5 style="margin: 0; color: #1f2937; font-size: 14px; font-weight: 600;">
                                        <i class="fas fa-university"></i> ${escapeHtml(inst.name)}
                                    </h5>
                                    ${inst.required_aps ? `<span class="career-result-aps" style="font-size: 11px; padding: 3px 8px;">APS: ${inst.required_aps}</span>` : ''}
                                </div>
                                <div style="margin-bottom: 8px;">
                                    <div style="font-size: 12px; color: #6b7280; margin-bottom: 6px;"><strong>Required Subjects:</strong></div>
                                    <div class="career-result-subjects" style="display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 8px;">
                                        ${subjectsHtml}
                                    </div>
                                </div>
                                <div>
                                    <div style="font-size: 12px; color: #6b7280; margin-bottom: 6px;"><strong>Qualifications:</strong></div>
                                    ${qualificationsHtml}
                                </div>
                                ${inst.website ? `
                                    <a href="${escapeHtml(inst.website)}" target="_blank" class="btn-primary btn-sm" style="margin-top: 8px; display: inline-flex; align-items: center; gap: 5px; text-decoration: none; font-size: 12px; padding: 6px 12px;">
                                        <i class="fas fa-external-link-alt"></i> Visit Website
                                    </a>
                                ` : ''}
                            </div>
                        `;
                    }).join('');
                }
                
                return `
                    <div class="career-result-item" style="cursor: default;" onclick="event.stopPropagation();">
                        <div class="career-result-header" style="margin-bottom: 15px;">
                            <h4 class="career-result-name" style="font-size: 20px;"><i class="fas fa-briefcase"></i> ${escapeHtml(career.name)}</h4>
                            <span class="career-result-aps">
                                <i class="fas fa-award"></i> Min APS: ${career.min_aps_score || 'N/A'}
                            </span>
                        </div>
                        <p class="career-result-description" style="margin-bottom: 20px;">${escapeHtml(career.description || 'No description available')}</p>
                        <div style="margin-bottom: 15px;">
                            <h5 style="margin: 0 0 10px 0; color: #1f2937; font-size: 15px; display: flex; align-items: center; gap: 8px;">
                                <i class="fas fa-graduation-cap" style="color: #667eea;"></i> Institutions & Requirements
                            </h5>
                            ${institutionsHtml || '<p style="color: #6b7280; font-size: 13px;">No institutions listed for this career.</p>'}
                        </div>
                        <div style="text-align: center; margin-top: 15px; padding-top: 15px; border-top: 1px solid #e5e7eb;">
                            <button onclick="showCareerDetails(${career.id}, '${escapeHtml(career.name).replace(/'/g, "\\'")}') " class="btn-secondary btn-sm" style="padding: 8px 20px; font-size: 13px;">
                                <i class="fas fa-info-circle"></i> View More Details
                            </button>
                        </div>
                    </div>
                `;
            }).join('');
        } else {
            resultsContainer.innerHTML = '<div class="career-no-results"><i class="fas fa-inbox" style="font-size: 32px; margin-bottom: 10px; display: block;"></i><p>No careers found. Try a different search term.</p></div>';
        }
    } catch (error) {
        console.error('Career search error:', error);
        resultsContainer.innerHTML = '<div class="career-no-results"><i class="fas fa-exclamation-triangle" style="font-size: 32px; margin-bottom: 10px; display: block; color: #ef4444;"></i><p>Error searching careers. Please try again.</p></div>';
    } finally {
        searchBtn.disabled = false;
        searchBtn.innerHTML = '<i class="fas fa-search"></i> Search';
    }
}

async function showCareerDetails(careerId, careerName) {
    const modal = document.getElementById('career-details-modal');
    const modalTitle = document.getElementById('modal-career-title');
    const modalContent = document.getElementById('modal-career-content');
    
    modalTitle.textContent = careerName;
    modalContent.innerHTML = '<div class="career-loading"><i class="fas fa-spinner fa-spin" style="font-size: 32px; margin-bottom: 10px; display: block;"></i><p>Loading career details...</p></div>';
    modal.style.display = 'flex';
    
    try {
        const response = await fetch(`/api/show-career/${careerId}`);
        const data = await response.json();
        
        if (data.success && data.career) {
            const career = data.career;
            
            let institutionsHtml = '';
            if (career.institutions && career.institutions.length > 0) {
                institutionsHtml = career.institutions.map(inst => `
                    <div style="background: #f9fafb; padding: 15px; border-radius: 8px; margin-bottom: 15px; border-left: 3px solid #667eea;">
                        <h5 style="margin: 0 0 10px 0; color: #1f2937; font-size: 16px;">
                            <i class="fas fa-university"></i> ${escapeHtml(inst.name)}
                        </h5>
                        <p style="margin: 5px 0; font-size: 14px; color: #6b7280;">
                            <i class="fas fa-map-marker-alt"></i> ${escapeHtml(inst.city || '')}, ${escapeHtml(inst.province || '')}
                        </p>
                        ${inst.min_aps_score ? `
                            <p style="margin: 5px 0; font-size: 14px;">
                                <strong>Required APS:</strong> 
                                <span class="career-result-aps" style="font-size: 12px; padding: 4px 10px;">${inst.min_aps_score}</span>
                            </p>
                        ` : ''}
                        ${inst.subject_requirements && inst.subject_requirements.length > 0 ? `
                            <div style="margin-top: 10px;">
                                <strong style="font-size: 13px;">Required Subjects:</strong>
                                <div class="career-result-subjects" style="margin-top: 8px;">
                                    ${inst.subject_requirements.map(subject => `
                                        <span class="subject-badge">${escapeHtml(subject.subject || subject)} - Level ${subject.level || 'N/A'}</span>
                                    `).join('')}
                                </div>
                            </div>
                        ` : ''}
                        ${inst.qualifications && inst.qualifications.length > 0 ? `
                            <div style="margin-top: 10px;">
                                <strong style="font-size: 13px;">Qualifications Offered:</strong>
                                <ul style="margin: 8px 0 0 20px; font-size: 14px; color: #374151;">
                                    ${inst.qualifications.map(qual => `
                                        <li>${escapeHtml(qual.name)} (${escapeHtml(qual.type)}, ${escapeHtml(qual.duration || 'N/A')})</li>
                                    `).join('')}
                                </ul>
                            </div>
                        ` : ''}
                        ${inst.website ? `
                            <a href="${escapeHtml(inst.website)}" target="_blank" class="btn-primary btn-sm" style="margin-top: 10px; display: inline-flex; align-items: center; gap: 5px; text-decoration: none;">
                                <i class="fas fa-external-link-alt"></i> Visit Website
                            </a>
                        ` : ''}
                    </div>
                `).join('');
            }
            
            modalContent.innerHTML = `
                <div style="margin-bottom: 20px;">
                    <p style="color: #4b5563; line-height: 1.6;">${escapeHtml(career.description || 'No description available')}</p>
                </div>
                <div style="margin-bottom: 20px;">
                    <h4 style="color: #1f2937; font-size: 18px; margin-bottom: 15px;">
                        <i class="fas fa-graduation-cap"></i> Institutions Offering This Career
                    </h4>
                    ${institutionsHtml || '<p class="career-no-results">No institutions found.</p>'}
                </div>
                <div style="text-align: center;">
                    <button onclick="document.getElementById('career-details-modal').style.display = 'none'" class="btn-secondary" style="padding: 10px 30px;">
                        <i class="fas fa-times"></i> Close
                    </button>
                </div>
            `;
        } else {
            modalContent.innerHTML = '<div class="career-no-results"><p>Error loading career details.</p></div>';
        }
    } catch (error) {
        console.error('Error loading career details:', error);
        modalContent.innerHTML = '<div class="career-no-results"><p>Error loading career details. Please try again.</p></div>';
    }
}

function enterPressedForSearch(event) {
    if (event.key === 'Enter') {
        searchCareers();
    }
}

document.addEventListener("DOMContentLoaded", function() {
    initUploadHandlers();
    loadUploadedReportCards();
    loadAvailableBursaries();
});
</script>
SCRIPT;

// Add script to set the isFreeTierGlobal value
$extraHead .= '<script>isFreeTierGlobal = ' . $isFreeTierJson . ';</script>';

include __DIR__ . '/../layouts/header.php';
?>

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="loading-spinner"></div>
    <div class="loading-text">Uploading...</div>
</div>

<h1 class="title">Upload Report Card</h1>
<p class="subtitle">Upload your report card for AI-powered career recommendations.</p>

<?php if ($error): ?>
    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<div class="upload-container" style="display: grid; grid-template-columns: 1fr 400px; gap: 30px; align-items: start;">
    <!-- Left Column: Upload Form -->
    <div class="auth-box" style="max-width: 600px;">
        <form method="post" action="/upload-report-card" enctype="multipart/form-data">
            <!-- Drag & Drop File Input -->
            <div class="form-group">
                <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #1e293b;">Report Card File (PDF, DOCX, JPG, PNG)</label>
                <label for="report_card_file" class="upload-area" id="upload-area" style="border: 3px dashed #cbd5e1; border-radius: 12px; padding: 30px; text-align: center; background: #f8fafc; cursor: pointer; transition: all 0.3s ease; display: block;">
                    <input type="file" id="report_card_file" name="report_card_file" accept=".pdf,.docx,.jpg,.jpeg,.png" style="display: none;">
                    <i class="fas fa-cloud-upload-alt" style="font-size: 40px; color: #667eea; margin-bottom: 15px; display: block;"></i>
                    <h4 style="margin: 0 0 8px 0; color: #1e293b; font-size: 15px;">Click or drag file to upload</h4>
                    <p style="margin: 0; color: #64748b; font-size: 13px;">PDF, DOCX, JPG, PNG (Max 10MB)</p>
                </label>
                <div style="margin-top: 15px; text-align: center;">
                    <button type="button" id="select-from-scans-btn" class="btn-secondary" style="padding: 10px 20px; font-size: 14px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 8px; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; transition: all 0.2s;" onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 4px 12px rgba(102,126,234,0.4)'" onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='none'">
                        <i class="fas fa-folder-open"></i> Select from My Scans
                    </button>
                </div>
                <div id="preview-section" style="display: none; margin-top: 15px; padding: 15px; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <i class="fas fa-file-alt" style="font-size: 28px; color: #667eea;"></i>
                        <div style="flex: 1;">
                            <div style="font-weight: 600; color: #1e293b; font-size: 14px;" id="file-name"></div>
                            <div style="font-size: 12px; color: #64748b;" id="file-size"></div>
                        </div>
                        <button type="button" id="clear-btn" style="background: #fee2e2; color: #ef4444; border: none; padding: 6px 12px; border-radius: 6px; cursor: pointer; font-size: 13px;">
                            <i class="fas fa-trash"></i> Remove
                        </button>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="grade">Grade (optional)</label>
                <select id="grade" name="grade">
                    <option value="">Select Grade</option>
                    <option value="8">Grade 8</option>
                    <option value="9">Grade 9</option>
                    <option value="10">Grade 10</option>
                    <option value="11">Grade 11</option>
                    <option value="12">Grade 12</option>
                </select>
            </div>

            <div class="form-group">
                <label for="term">Term (optional)</label>
                <select id="term" name="term">
                    <option value="">Select Term</option>
                    <option value="1">Term 1</option>
                    <option value="2">Term 2</option>
                    <option value="3">Term 3</option>
                    <option value="4">Term 4</option>
                </select>
            </div>

            <button type="submit" class="btn-primary">Upload Report Card</button>
        </form>
    </div>

    <!-- Right Column: Career Search Card -->
    <div class="career-search-card" style="background: white; border-radius: 12px; padding: 24px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border: 1px solid #e5e7eb; position: sticky; top: 20px;">
        <h3 style="margin: 0 0 10px 0; color: #1f2937; display: flex; align-items: center; gap: 10px; font-size: 18px;">
            <i class="fas fa-compass" style="color: #667eea;"></i> Search Careers & Check APS
        </h3>
        <p style="color: #6b7280; font-size: 13px; margin-bottom: 20px; line-height: 1.5;">Search for careers and find out the required APS and subjects for South African universities.</p>
        
        <div class="career-search-box" style="display: flex; gap: 10px; margin-bottom: 15px;">
            <input type="text" id="career-search-input" class="career-search-input" placeholder="e.g., Doctor, Engineer..." onkeypress="enterPressedForSearch(event)" style="flex: 1; padding: 10px 14px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 14px;">
            <button type="button" id="career-search-btn" class="career-search-btn" onclick="searchCareers()" style="padding: 10px 16px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; white-space: nowrap;">
                <i class="fas fa-search"></i> Search
            </button>
        </div>
        
        <div id="career-results-container" class="career-results" style="max-height: 400px; overflow-y: auto;">
            <div class="career-no-results" style="text-align: center; padding: 20px 10px;">
                <i class="fas fa-search" style="font-size: 24px; margin-bottom: 8px; display: block; color: #cbd5e1;"></i>
                <p style="color: #6b7280; margin: 0; font-size: 13px;">Enter a career name to search</p>
            </div>
        </div>
    </div>
</div>

<!-- Your Uploaded Report Cards Section (Full Width Below) -->
<div class="report-cards-section" style="margin-top: 40px;">
    <h2 class="section-title"><i class="fas fa-file-alt"></i> Your Uploaded Report Cards</h2>
    <div id="report-cards-list" class="report-cards-list">
        <p class="loading">Loading report cards...</p>
    </div>
</div>

<div class="report-cards-section">
    <h2 class="section-title"><i class="fas fa-scholarship"></i> New Bursaries Available for You</h2>
    <div id="bursaries-list" class="bursaries-list-container">
        <p class="loading"><i class="fas fa-spinner fa-spin"></i> Loading bursaries...</p>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form validation - check if file is selected before submit
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const fileInput = document.getElementById('report_card_file');
            const selectedScanInput = document.getElementById('selected_scan_file');

            // Check if either a file is selected OR a scan is selected
            const hasFile = fileInput && fileInput.files && fileInput.files.length > 0;
            const hasScan = selectedScanInput && selectedScanInput.value && selectedScanInput.value.trim() !== '';

            if (!hasFile && !hasScan) {
                e.preventDefault();
                alert('Please select a file to upload. Click or drag a file into the upload area, or use "Select from My Scans" button.');
                const uploadArea = document.getElementById('upload-area');
                if (uploadArea) {
                    uploadArea.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    uploadArea.style.borderColor = '#ef4444';
                    uploadArea.style.background = '#fef2f2';
                    setTimeout(() => {
                        uploadArea.style.borderColor = '#cbd5e1';
                        uploadArea.style.background = '#f8fafc';
                    }, 2000);
                }
                return false;
            }
            
            // Show loading overlay
            showLoading('Uploading report card...');
        });
    }

    // Select from scans functionality
    const selectFromScansBtn = document.getElementById('select-from-scans-btn');
    const scansModalOverlay = document.getElementById('scans-modal-overlay');
    const closeScansModal = document.getElementById('close-scans-modal');
    const closeScansModalBottom = document.getElementById('close-scans-modal-bottom');
    const scansList = document.getElementById('scans-list');
    const noScans = document.getElementById('no-scans');

    if (selectFromScansBtn) {
        selectFromScansBtn.addEventListener('click', async () => {
            scansModalOverlay.style.display = 'flex';
            await loadScans();
        });
    }

    if (closeScansModal) {
        closeScansModal.addEventListener('click', () => {
            scansModalOverlay.style.display = 'none';
        });
    }

    if (closeScansModalBottom) {
        closeScansModalBottom.addEventListener('click', () => {
            scansModalOverlay.style.display = 'none';
        });
    }

    if (scansModalOverlay) {
        scansModalOverlay.addEventListener('click', (e) => {
            if (e.target === scansModalOverlay) {
                scansModalOverlay.style.display = 'none';
            }
        });
    }
});

async function loadScans() {
    try {
        const response = await fetch('/api/scan-saved-list');
        const data = await response.json();

        if (data.success && data.files && data.files.length > 0) {
            document.getElementById('no-scans').style.display = 'none';
            const scansList = document.getElementById('scans-list');
            if (scansList) {
                scansList.innerHTML = data.files.map(file => `
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 15px; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='#eff6ff'" onmouseout="this.style.background='#f8fafc'" onclick="selectScan('${file.name}', '${file.url}')">
                        <div style="display: flex; align-items: center; gap: 15px; flex: 1;">
                            <i class="fas fa-file-pdf" style="font-size: 32px; color: #dc2626;"></i>
                            <div>
                                <h4 style="margin: 0; color: #1f2937; font-size: 15px;">${escapeHtml(file.name)}</h4>
                                <small style="color: #6b7280;">${file.size} • ${file.date}</small>
                            </div>
                        </div>
                        <i class="fas fa-chevron-right" style="color: #94a3b8;"></i>
                    </div>
                `).join('');
            }
        } else {
            const noScans = document.getElementById('no-scans');
            if (noScans) noScans.style.display = 'block';
            const scansList = document.getElementById('scans-list');
            if (scansList) scansList.innerHTML = '';
        }
    } catch (error) {
        console.error('Error loading scans:', error);
        const scansList = document.getElementById('scans-list');
        if (scansList) scansList.innerHTML = '<p style="text-align: center; color: #dc2626;">Error loading scans</p>';
    }
}

function selectScan(filename, url) {
    // Create a hidden input to store the selected scan
    let hiddenInput = document.getElementById('selected_scan_file');
    if (!hiddenInput) {
        hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.id = 'selected_scan_file';
        hiddenInput.name = 'selected_scan_file';
        const form = document.querySelector('form');
        if (form) form.appendChild(hiddenInput);
    }
    hiddenInput.value = filename;

    // Update preview
    const fileName = document.getElementById('file-name');
    const fileSize = document.getElementById('file-size');
    const previewSection = document.getElementById('preview-section');
    const uploadArea = document.getElementById('upload-area');

    if (fileName && fileSize && previewSection && uploadArea) {
        fileName.textContent = filename;
        fileSize.textContent = 'From saved scans';
        previewSection.style.display = 'block';
        uploadArea.style.display = 'none';
    }

    const scansModalOverlay = document.getElementById('scans-modal-overlay');
    if (scansModalOverlay) scansModalOverlay.style.display = 'none';
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>

<!-- Career Details Modal -->
<div id="career-details-modal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: white; padding: 30px; border-radius: 12px; max-width: 800px; width: 90%; max-height: 80vh; overflow-y: auto; box-shadow: 0 20px 60px rgba(0,0,0,0.3);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #e5e7eb;">
            <h3 id="modal-career-title" style="margin: 0; color: #1f2937; display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-briefcase" style="color: #667eea;"></i> Career Details
            </h3>
            <button onclick="document.getElementById('career-details-modal').style.display = 'none'" style="background: none; border: none; font-size: 24px; color: #6b7280; cursor: pointer;">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div id="modal-career-content">
            <!-- Career details will be loaded here -->
        </div>
    </div>
</div>

<!-- Select from Scans Modal -->
<div id="scans-modal-overlay" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: white; padding: 30px; border-radius: 12px; max-width: 600px; width: 90%; max-height: 80vh; overflow-y: auto; box-shadow: 0 20px 60px rgba(0,0,0,0.3);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #e5e7eb;">
            <h3 style="margin: 0; color: #1f2937; display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-folder-open" style="color: #667eea;"></i> Select from My Scans
            </h3>
            <button id="close-scans-modal" style="background: none; border: none; font-size: 24px; color: #6b7280; cursor: pointer;">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div id="scans-list" style="display: grid; gap: 15px;">
            <p style="text-align: center; color: #6b7280; padding: 40px;">
                <i class="fas fa-spinner fa-spin" style="font-size: 24px;"></i><br>Loading scans...
            </p>
        </div>
        <div id="no-scans" style="display: none; text-align: center; padding: 40px; color: #6b7280;">
            <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 15px; display: block;"></i>
            <p>No saved scans yet. <a href="/scan" style="color: #667eea;">Create one first</a></p>
        </div>
        <div style="margin-top: 20px; padding-top: 15px; border-top: 1px solid #e5e7eb; text-align: center;">
            <button id="close-scans-modal-bottom" class="btn-secondary" style="padding: 10px 30px;">
                <i class="fas fa-times"></i> Cancel
            </button>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
