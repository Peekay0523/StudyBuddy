<?php
$pageTitle = 'Career Recommendations - StudySmart';
$currentPage = 'careers';
$reportCardIdJs = htmlspecialchars($reportCard['id'] ?? '');

// Admission Probability Prediction Logic
function calculateAdmissionProbability($requiredAps, $studentAps, $grade, $term, $subjectRequirements = [], $studentGrades = []) {
    if (!$requiredAps) return ['percent' => 50, 'label' => 'Moderate Chance', 'color' => 'orange'];
    
    $grade = (int)filter_var($grade, FILTER_SANITIZE_NUMBER_INT);
    $term = (int)filter_var($term, FILTER_SANITIZE_NUMBER_INT);
    
    // Base probability based on APS
    $apsDiff = $studentAps - $requiredAps;
    $baseProb = 50 + ($apsDiff * 5); // 5% per APS point difference
    
    // Subject requirement check
    $subjectGaps = 0;
    if (!empty($subjectRequirements)) {
        foreach ($subjectRequirements as $req) {
            $subjName = strtolower($req['subject'] ?? '');
            $reqLevel = (int)($req['min_level'] ?? 0);
            
            $met = false;
            foreach ($studentGrades as $sSubj => $sGrade) {
                if (strpos(strtolower($sSubj), $subjName) !== false || strpos($subjName, strtolower($sSubj)) !== false) {
                    $sLevel = 0;
                    if (preg_match('/(\d+)/', $sGrade, $m)) {
                        $pct = (int)$m[1];
                        if ($pct >= 80) $sLevel = 7;
                        elseif ($pct >= 70) $sLevel = 6;
                        elseif ($pct >= 60) $sLevel = 5;
                        elseif ($pct >= 50) $sLevel = 4;
                        elseif ($pct >= 40) $sLevel = 3;
                        elseif ($pct >= 30) $sLevel = 2;
                        else $sLevel = 1;
                    }
                    if ($sLevel >= $reqLevel) {
                        $met = true;
                    } else {
                        $subjectGaps += ($reqLevel - $sLevel);
                    }
                    break;
                }
            }
            if (!$met && $subjectGaps === 0) $subjectGaps += 1;
        }
    }
    
    $baseProb -= ($subjectGaps * 15);
    
    // Adjustment based on Grade and Term (Realistic trajectory)
    $adjustment = 0;
    if ($grade == 12) {
        if ($term == 4) {
            // Very strict in final term
            $adjustment = -5;
        } elseif ($term == 1) {
            // More optimistic in term 1 (time to improve)
            $adjustment = 5;
        }
    } elseif ($grade == 11) {
        $adjustment = 10; // High potential for growth
    } elseif ($grade <= 10) {
        $adjustment = 15; // Plenty of time
    }
    
    $finalProb = max(5, min(98, $baseProb + $adjustment));
    
    if ($finalProb >= 75) return ['percent' => $finalProb, 'label' => 'High Chance', 'color' => '#22c55e'];
    if ($finalProb >= 50) return ['percent' => $finalProb, 'label' => 'Moderate Chance', 'color' => '#f59e0b'];
    return ['percent' => $finalProb, 'label' => 'Low Chance', 'color' => '#ef4444'];
}

$extraHead = <<<EOT
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

/* Global Responsive Base */
* {
    box-sizing: border-box;
    max-width: 100%;
}

html, body {
    overflow-x: hidden;
    width: 100%;
    margin: 0;
    padding: 0;
    position: relative;
    max-width: 100vw;
}

.career-recommendations-container {
    width: 100%;
    max-width: 100%;
    overflow-x: hidden;
    padding-bottom: 120px; /* Extra space for bottom nav */
    margin: 0 auto;
}

.title {
    font-size: 1.8rem;
    word-wrap: break-word;
    margin-top: 20px;
    padding: 0 15px;
}

.subtitle {
    font-size: 0.95rem;
    margin-bottom: 25px;
    padding: 0 15px;
}

@media (max-width: 640px) {
    .title {
        font-size: 1.5rem;
    }
    .subtitle {
        font-size: 0.9rem;
    }
    .btn-back {
        width: 100%;
        text-align: center;
    }
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
    .institution-card a,
.institution-card button {
    width: 100% !important;
    max-width: 240px !important;
    border: none !important;
    border-radius: 14px !important;
    padding: 15px 18px !important;
    font-size: 1rem !important;
    font-weight: 700 !important;
    cursor: pointer !important;
    transition: all 0.3s ease !important;
    text-decoration: none !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 10px !important;
}

.aps-badge-inline {
    font-size: 0.75rem;
    background: #f3f4f6;
    color: #374151;
    padding: 2px 8px;
    border-radius: 6px;
    font-weight: 700;
    border: 1px solid #e5e7eb;
}

.institution-type {
    margin-bottom: 12px;
    font-size: 0.75rem;
    color: #6b7280;
}

.inst-reason {
    font-size: 0.8rem;
    color: #4b5563;
    margin-bottom: 15px;
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
    min-height: 3.4em;
}

.likelihood-indicator {
    margin-bottom: 15px !important;
    width: 100% !important;
    justify-content: center !important;
    border: none !important;
    font-weight: 800 !important;
    padding: 8px !important;
}

.inst-actions {
    display: flex;
    flex-direction: row;
    gap: 8px;
    width: 100%;
    justify-content: center;
    margin-top: auto;
}

/* Prediction Badge Styles */
.prob-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 700;
    margin-top: 10px;
    border: 1px solid rgba(0,0,0,0.05);
}

.prob-badge i {
    font-size: 0.9rem;
}

/* Improve Button Styles */
.btn-improve {
    background: rgba(99, 102, 241, 0.1);
    color: #4f46e5;
    border: 1px solid rgba(99, 102, 241, 0.2) !important;
    font-size: 0.85rem !important;
    padding: 8px 16px !important;
    margin-top: 12px;
    border-radius: 12px !important;
    width: auto !important;
    display: inline-flex !important;
}

.btn-improve:hover {
    background: #4f46e5;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2);
}

/* Institution Card Button Overrides */
.institution-card a,
.institution-card button {
    width: 100% !important;
    max-width: none !important;
    border: none !important;
    border-radius: 12px !important;
    padding: 10px 12px !important;
    font-size: 0.9rem !important;
    font-weight: 700 !important;
    cursor: pointer !important;
    transition: all 0.2s ease !important;
    text-decoration: none !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 6px !important;
    min-height: 40px;
}

/* Mobile Specific Dashboard Layout */
@media (max-width: 768px) {
    .institutions-grid {
        display: flex !important;
        flex-wrap: wrap !important;
        gap: 10px !important;
        width: 100% !important;
        margin: 0 !important;
        padding: 5px !important;
        box-sizing: border-box !important;
    }

    .institution-card {
        flex: 1 1 calc(50% - 10px) !important;
        max-width: calc(50% - 5px) !important;
        min-width: 0 !important;
        padding: 0 !important;
        border-radius: 16px !important;
        margin: 0 !important;
        box-sizing: border-box !important;
        display: flex !important;
        flex-direction: column !important;
        justify-content: space-between !important;
    }

    .inst-actions {
        display: flex !important;
        flex-direction: row !important;
        flex-wrap: wrap !important;
        gap: 6px !important;
        width: 100% !important;
        margin-top: 10px !important;
    }

    .inst-actions .btn-visit,
    .inst-actions .btn-improve {
        flex: 1 1 calc(50% - 3px) !important;
        width: auto !important;
        min-width: 0 !important;
        padding: 8px 4px !important;
        font-size: 0.7rem !important;
        min-height: 38px !important;
        line-height: 1.1 !important;
        text-align: center !important;
    }

    .inst-actions .btn-applied {
        width: 100% !important;
        flex: 1 1 100% !important;
        padding: 8px 4px !important;
        font-size: 0.75rem !important;
        margin-top: 0 !important;
        min-height: 38px !important;
    }

    .institution-card h4 {
        font-size: 0.85rem !important;
        margin-bottom: 5px !important;
        min-height: 2.8em !important;
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
        justify-content: center !important;
    }

    .aps-badge-inline {
        font-size: 0.65rem !important;
        padding: 1px 6px !important;
        margin-top: 4px !important;
        margin-left: 0 !important;
    }

    .institution-type {
        font-size: 0.65rem !important;
        margin-bottom: 6px !important;
    }

    .inst-reason {
        font-size: 0.75rem !important;
        margin-bottom: 8px !important;
        -webkit-line-clamp: 2 !important;
        min-height: 2.5em !important;
        line-height: 1.3 !important;
    }

    .likelihood-indicator {
        font-size: 0.7rem !important;
        padding: 4px 6px !important;
        margin-bottom: 8px !important;
        width: 100% !important;
    }

    .institution-card a,
    .institution-card button {
        padding: 6px 4px !important;
        font-size: 0.75rem !important;
        min-height: 36px !important;
        border-radius: 10px !important;
    }
    
    .btn-improve {
        margin-top: 0 !important;
    }
}

@media (max-width: 480px) {
    .institution-card {
        flex: 1 1 100% !important;
        max-width: 100% !important;
    }
}

/* Glassmorphism Modal Styles */
.glass-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.3);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    z-index: 10000;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.glass-modal.active {
    display: flex;
    opacity: 1;
}

.glass-modal-content {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border-radius: 24px;
    width: 95%;
    max-width: 550px;
    max-height: 90vh;
    overflow-y: auto;
    padding: 24px;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    border: 1px solid rgba(255, 255, 255, 0.4);
    transform: scale(0.9);
    transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    position: relative;
}

@media (max-width: 640px) {
    .glass-modal-content {
        padding: 20px 16px;
        border-radius: 20px;
    }
}

.glass-modal.active .glass-modal-content {
    transform: scale(1);
}

.modal-close {
    position: absolute;
    top: 20px;
    right: 20px;
    background: rgba(0,0,0,0.05);
    border: none;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
}

.modal-close:hover {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
}

.improvement-advice-section {
    margin-bottom: 25px;
}

.advice-card {
    background: white;
    border-radius: 16px;
    padding: 15px;
    margin-bottom: 12px;
    border: 1px solid #f1f5f9;
}

.advice-card h4 {
    margin: 0 0 8px 0;
    font-size: 0.95rem;
    color: #1e293b;
    display: flex;
    align-items: center;
    gap: 8px;
}

.target-badge {
    background: #ecfdf5;
    color: #059669;
    padding: 2px 8px;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 700;
}

.alt-option {
    background: #f8fafc;
    border-left: 4px solid #6366f1;
    padding: 12px;
    border-radius: 0 12px 12px 0;
    margin-top: 10px;
}

/* Institution Card Landscape Themes */
.institution-card-landscape {
    position: relative;
    width: 100%;
    height: 80px;
    overflow: hidden;
    margin-bottom: 0;
    border-radius: 16px 16px 0 0;
}

.institution-card-landscape * { position: absolute; }

.institution-card-landscape .sky { width: 100%; height: 100%; }
.institution-card-landscape .sun { border-radius: 50%; width: 20px; height: 20px; }
.institution-card-landscape .ocean { bottom: 0; width: 100%; height: 25%; overflow: hidden; }
.institution-card-landscape .hill { border-radius: 50%; }
.institution-card-landscape .reflection { background: white; opacity: 0.3; }
.institution-card-landscape .tree { z-index: 3; bottom: 5%; }
.institution-card-landscape .filter { height: 100%; width: 100%; z-index: 5; opacity: 0.2; background: linear-gradient(0deg, rgba(255,255,255,1) 0%, rgba(255,255,255,0) 40%); }

/* Theme Variants */
.theme-summer .sky { background: linear-gradient(0deg, #f7e157 0%, #e96594 100%); }
.theme-summer .sun { background: white; bottom: 35%; left: 15%; filter: drop-shadow(0px 0px 8px white); }
.theme-summer .ocean { background: linear-gradient(0deg, #f1c07d 0%, #f7da96 100%); }
.theme-summer .hill-1 { right: -15%; bottom: 20%; width: 60px; height: 20px; background-color: #e6b29d; }
.theme-summer .hill-2 { right: -20%; bottom: 10%; width: 80px; height: 30px; background-color: #c29182; }
.theme-summer .hill-3 { left: -10%; bottom: -15%; width: 100px; height: 50px; background-color: #b77873; z-index: 3; }
.theme-summer .hill-4 { right: -10%; bottom: -20%; width: 100px; height: 50px; background-color: #a16773; z-index: 3; }
.theme-summer .tree svg { fill: #b77873; }

.theme-night .sky { background: linear-gradient(0deg, #0f172a 0%, #1e1b4b 100%); }
.theme-night .sun { background: #f1f5f9; bottom: 50%; left: 70%; filter: drop-shadow(0px 0px 8px #fff); width: 15px; height: 15px; }
.theme-night .sun::after { content: ""; position: absolute; width: 12px; height: 12px; background: #1e1b4b; border-radius: 50%; left: 5px; top: -2px; }
.theme-night .ocean { background: linear-gradient(0deg, #020617 0%, #1e293b 100%); opacity: 0.8; }
.theme-night .hill-1 { right: -15%; bottom: 20%; width: 60px; height: 20px; background-color: #334155; }
.theme-night .hill-2 { right: -20%; bottom: 10%; width: 80px; height: 30px; background-color: #1e293b; }
.theme-night .hill-3 { left: -10%; bottom: -15%; width: 100px; height: 50px; background-color: #0f172a; z-index: 3; }
.theme-night .hill-4 { right: -10%; bottom: -20%; width: 100px; height: 50px; background-color: #020617; z-index: 3; }
.theme-night .tree svg { fill: #0f172a; }
.theme-night .filter { background: linear-gradient(0deg, rgba(15,23,42,0.5) 0%, rgba(15,23,42,0) 60%); }

.theme-winter .sky { background: linear-gradient(0deg, #e2e8f0 0%, #94a3b8 100%); }
.theme-winter .sun { background: #fff; bottom: 60%; left: 20%; opacity: 0.5; filter: blur(4px); }
.theme-winter .ocean { background: linear-gradient(0deg, #f1f5f9 0%, #cbd5e1 100%); }
.theme-winter .hill-1 { right: -15%; bottom: 20%; width: 60px; height: 20px; background-color: #cbd5e1; }
.theme-winter .hill-2 { right: -20%; bottom: 10%; width: 80px; height: 30px; background-color: #94a3b8; }
.theme-winter .hill-3 { left: -10%; bottom: -15%; width: 100px; height: 50px; background-color: #f8fafc; z-index: 3; border-top: 1px solid #e2e8f0; }
.theme-winter .hill-4 { right: -10%; bottom: -20%; width: 100px; height: 50px; background-color: #ffffff; z-index: 3; border-top: 1px solid #f1f5f9; }
.theme-winter .tree svg { fill: #94a3b8; }
.theme-winter .filter { background: linear-gradient(0deg, rgba(255,255,255,0.8) 0%, rgba(255,255,255,0) 50%); }

.theme-autumn .sky { background: linear-gradient(0deg, #fed7aa 0%, #ea580c 100%); }
.theme-autumn .sun { background: #fef3c7; bottom: 40%; left: 75%; opacity: 0.4; }
.theme-autumn .ocean { background: linear-gradient(0deg, #7c2d12 0%, #9a3412 100%); opacity: 0.4; }
.theme-autumn .hill-1 { right: -15%; bottom: 20%; width: 60px; height: 20px; background-color: #9a3412; }
.theme-autumn .hill-2 { right: -20%; bottom: 10%; width: 80px; height: 30px; background-color: #7c2d12; }
.theme-autumn .hill-3 { left: -10%; bottom: -15%; width: 100px; height: 50px; background-color: #431407; z-index: 3; }
.theme-autumn .hill-4 { right: -10%; bottom: -20%; width: 100px; height: 50px; background-color: #451a03; z-index: 3; }
.theme-autumn .tree svg { fill: #7c2d12; }

.theme-forest .sky { background: linear-gradient(0deg, #bbf7d0 0%, #22c55e 100%); }
.theme-forest .sun { background: #fef9c3; bottom: 65%; left: 10%; opacity: 0.3; }
.theme-forest .ocean { background: linear-gradient(0deg, #064e3b 0%, #065f46 100%); opacity: 0.3; }
.theme-forest .hill-1 { right: -15%; bottom: 20%; width: 60px; height: 20px; background-color: #166534; }
.theme-forest .hill-2 { right: -20%; bottom: 10%; width: 80px; height: 30px; background-color: #14532d; }
.theme-forest .hill-3 { left: -10%; bottom: -15%; width: 100px; height: 50px; background-color: #064e3b; z-index: 3; }
.theme-forest .hill-4 { right: -10%; bottom: -20%; width: 100px; height: 50px; background-color: #022c22; z-index: 3; }
.theme-forest .tree svg { fill: #064e3b; }
.theme-forest .filter { background: linear-gradient(0deg, rgba(20,83,45,0.2) 0%, rgba(20,83,45,0) 60%); }

/* Tree Positions */
.tree-1 { bottom: 15%; left: 5%; width: 18px; height: 26px; }
.tree-2 { bottom: 10%; left: 20%; width: 15px; height: 22px; }
.tree-3 { bottom: 5%; right: 5%; width: 22px; height: 30px; }
</style>
EOT;

$extraHead .= <<<EOT
<script>
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

function reprocessReportCard() {
    const reportCardId = "{$reportCardIdJs}";
    if (!reportCardId) {
        alert("Report card ID not found");
        return;
    }

    if (confirm("This will reprocess your report card using AI-powered grade extraction. This may take a few seconds. Continue?")) {
        showLoading('Generating recommendations...');
        const form = document.createElement("form");
        form.method = "POST";
        form.action = "/reprocess-report-card/" + reportCardId;
        document.body.appendChild(form);
        form.submit();
    }
}

document.addEventListener("DOMContentLoaded", function() {
    console.log("DOM loaded, initializing speech...");
    
    let synthesis = window.speechSynthesis;
    let isSpeaking = false;
    let currentBtn = null;
    let voicesLoaded = false;

    // Force voice loading
    setTimeout(function() {
        if (synthesis) {
            synthesis.getVoices();
            voicesLoaded = true;
            console.log("Voices initialized, count:", synthesis.getVoices().length);
        }
    }, 100);

    // Make function globally accessible
    window.toggleSpeech = function(sectionId) {
        console.log("Toggle speech called for:", sectionId);
        const btn = document.getElementById("speech-btn-" + sectionId);
        const contentDiv = document.getElementById(sectionId);

        console.log("Button element:", btn);
        console.log("Content div:", contentDiv);

        if (!btn) {
            console.error("Button not found for ID: speech-btn-" + sectionId);
        }
        if (!contentDiv) {
            console.error("Content div not found for ID:", sectionId);
        }

        if (!btn || !contentDiv) {
            alert("Could not find speech controls. Check console for details.");
            return;
        }

        if (isSpeaking) {
            synthesis.cancel();
            isSpeaking = false;
            currentBtn = null;
            btn.innerHTML = "<i class=\"fas fa-volume-high\"></i> Recite";
            btn.classList.remove("btn-danger");
            btn.classList.add("btn-primary");
        } else {
            // Get readable content
            let content = "";
            const paragraphs = contentDiv.querySelectorAll("p, li, h4, .career-tag");
            paragraphs.forEach(el => {
                content += el.textContent.trim() + " ";
            });

            if (!content.trim()) {
                content = contentDiv.textContent.replace(/\s+/g, " ").trim();
            }

            if (!content) {
                alert("No content to read");
                return;
            }

            console.log("Content length:", content.length);
            // Preprocess math symbols before speaking
            const processedContent = preprocessMathForSpeech(content);
            speakContent(processedContent, btn);
        }
    };

    // Preprocess mathematical symbols for speech
    function preprocessMathForSpeech(text) {
        let processed = text;
        
        // Remove bullet point dashes first (before processing math symbols)
        // This handles: "   - Understanding" or "- Understanding"
        processed = processed.replace(/^\s*-\s+/gm, " ");

        // Replace division symbols
        processed = processed.replace(/\s*÷\s*/g, " divided by ");
        processed = processed.replace(/\s*\/\s*/g, " divided by ");
        
        // Replace multiplication symbols
        processed = processed.replace(/\s*×\s*/g, " times ");
        processed = processed.replace(/\s*\*\s*/g, " times ");
        processed = processed.replace(/\s*·\s*/g, " times ");
        
        // Replace addition and subtraction (only when surrounded by numbers/variables, not bullet points)
        processed = processed.replace(/(\d)\s*\+\s*(\d)/g, "$1 plus $2");
        processed = processed.replace(/(\d)\s*-\s*(\d)/g, "$1 minus $2");
        processed = processed.replace(/([a-zA-Z])\s*\+\s*([a-zA-Z])/g, "$1 plus $2");
        processed = processed.replace(/([a-zA-Z])\s*-\s*([a-zA-Z])/g, "$1 minus $2");
        
        // Replace equals
        processed = processed.replace(/\s*=\s*/g, " equals ");
        
        // Replace inequality symbols
        processed = processed.replace(/\s*≤\s*/g, " less than or equal to ");
        processed = processed.replace(/\s*≥\s*/g, " greater than or equal to ");
        processed = processed.replace(/\s*≠\s*/g, " not equal to ");
        
        // Remove parentheses silently (don't announce them for regular text)
        processed = processed.split("(").join("");
        processed = processed.split(")").join("");
        
        // Remove square brackets silently
        processed = processed.split("[").join("");
        processed = processed.split("]").join("");
        
        // Replace exponents
        processed = processed.replace(/\^(\d+)/g, " to the power of $1 ");
        
        // Replace square root
        processed = processed.replace(/√/g, " square root of ");
        
        // Replace pi
        processed = processed.replace(/π/g, " pi ");
        
        // Replace percentage
        processed = processed.replace(/%/g, " percent ");
        
        // Replace degree symbol
        processed = processed.replace(/°/g, " degrees ");
        
        // Replace angle symbol
        processed = processed.replace(/∠/g, " angle ");
        
        // Replace therefore symbol
        processed = processed.replace(/∴/g, " therefore ");
        
        // Replace because symbol
        processed = processed.replace(/∵/g, " because ");
        
        // Replace infinity
        processed = processed.replace(/∞/g, " infinity ");
        
        // Replace less than and greater than (only in math context with numbers)
        processed = processed.replace(/(\d)\s*</g, "$1 less than ");
        processed = processed.replace(/>/g, " greater than ");
        
        // Replace "m =" with "m equals" for gradient context
        processed = processed.replace(/\b([a-zA-Z])\s*=\s*/g, "$1 equals ");
        
        // Clean up multiple spaces
        processed = processed.replace(/\s+/g, " ").trim();
        
        return processed;
    }

    function speakContent(text, btn) {
        if (!synthesis) {
            alert("Text-to-speech is not supported in your browser. Please use Chrome, Edge, or Safari.");
            return;
        }

        synthesis.cancel();

        const utterance = new SpeechSynthesisUtterance(text);
        utterance.lang = "en-US";
        utterance.rate = 0.9;
        utterance.pitch = 1;
        utterance.volume = 1;

        const voices = synthesis.getVoices();
        console.log("Available voices:", voices.length);
        const preferredVoice = voices.find(voice => 
            voice.lang.includes("en-US") && voice.name.includes("Natural")
        ) || voices.find(voice => voice.lang.includes("en") && voice.name.includes("Google")) || voices.find(voice => voice.lang.includes("en")) || voices[0];

        if (preferredVoice) {
            console.log("Using voice:", preferredVoice.name);
            utterance.voice = preferredVoice;
        }

        currentBtn = btn;
        
        utterance.onend = () => {
            console.log("Speech ended");
            isSpeaking = false;
            currentBtn = null;
            btn.innerHTML = "<i class=\"fas fa-volume-high\"></i> Recite";
            btn.classList.remove("btn-danger");
            btn.classList.add("btn-primary");
        };

        utterance.onerror = (event) => {
            console.error("Speech synthesis error:", event);
            isSpeaking = false;
            currentBtn = null;
            btn.innerHTML = "<i class=\"fas fa-volume-high\"></i> Recite";
            btn.classList.remove("btn-danger");
            btn.classList.add("btn-primary");
        };

        isSpeaking = true;
        btn.innerHTML = "<i class=\"fas fa-stop\"></i> Stop";
        btn.classList.remove("btn-primary");
        btn.classList.add("btn-danger");

        console.log("Starting speech...");
        synthesis.speak(utterance);
    }

    window.addEventListener("beforeunload", () => {
        if (synthesis) {
            synthesis.cancel();
        }
    });
});
</script>
EOT;
include __DIR__ . '/../layouts/header.php';
?>

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="loading-spinner"></div>
    <div class="loading-text">Generating...</div>
</div>

<h1 class="title">Career Recommendations</h1>
<p class="subtitle">AI-powered career guidance based on your academic performance</p>

<a href="/dashboard" class="btn-primary btn-back" style="text-decoration: none; display: inline-block; margin-bottom: 20px;">
    <i class="fas fa-arrow-left"></i> Back to Dashboard
</a>

<?php 
// Debug: Show what data we have
echo "<!-- Debug: careers=" . count($careerRec['recommended_careers'] ?? []) . ", courses=" . count($careerRec['courses'] ?? []) . ", bursaries=" . count($careerRec['bursaries'] ?? []) . " -->";

if (empty($careerRec['recommended_careers']) && empty($careerRec['courses']) && empty($careerRec['bursaries'])): 
?>
    <div class="no-recommendations-card">
        <div class="no-recommendations-content">
            <i class="fas fa-compass"></i>
            <h3>No Career Recommendations Yet</h3>
            <p>Your report card is still being processed or no recommendations were generated.</p>
            <div class="no-recommendations-actions">
                <a href="/upload-report-card" class="btn-primary">
                    <i class="fas fa-upload"></i> Upload Another Report Card
                </a>
                <button onclick="location.reload()" class="btn-primary" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                    <i class="fas fa-redo"></i> Refresh Page
                </button>
            </div>
        </div>
    </div>
<?php else: ?>

<div class="career-recommendations-container">
    <!-- APS Score -->
    <div class="recommendation-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
        <div class="card-header">
            <h3><i class="fas fa-graduation-cap"></i> Your APS Score</h3>
            <?php if (($careerRec['aps'] ?? 0) === 0): ?>
                <form method="POST" action="/reprocess-report-card/<?php echo $reportCard['id']; ?>" style="display: inline;">
                    <button type="submit" class="btn-sm" style="background: rgba(255,255,255,0.2); color: white; border: 1px solid rgba(255,255,255,0.3); padding: 6px 12px; border-radius: 6px; cursor: pointer;">
                        <i class="fas fa-sync"></i> Reprocess with AI
                    </button>
                </form>
            <?php endif; ?>
        </div>
        <div class="card-content" style="text-align: center; padding: 20px;">
            <?php
            $aps = $careerRec['aps'] ?? 0;
            $apsClass = $aps >= 35 ? 'excellent' : ($aps >= 25 ? 'good' : 'average');
            ?>
            <div style="font-size: 2.5rem; font-weight: bold; margin: 15px 0;">
                <?php echo $aps > 0 ? $aps : 'N/A'; ?>
            </div>
            <p style="font-size: 0.95rem; opacity: 0.9; margin: 0;">
                <?php if ($aps >= 35): ?>
                    Excellent! You qualify for most Bachelor's degree programs
                <?php elseif ($aps >= 25): ?>
                    Good! You qualify for many Bachelor's degree programs
                <?php elseif ($aps > 0): ?>
                    Consider Diploma or Higher Certificate programs
                <?php else: ?>
                    <button onclick="reprocessReportCard()" style="background: rgba(255,255,255,0.2); color: white; border: 1px solid rgba(255,255,255,0.3); padding: 8px 16px; border-radius: 6px; cursor: pointer; margin-top: 10px;">
                        <i class="fas fa-sync"></i> Reprocess with AI to Calculate APS
                    </button>
                <?php endif; ?>
            </p>
        </div>
    </div>

    <!-- Recommended Careers -->
    <div class="recommendation-card">
        <div class="card-header">
            <h3><i class="fas fa-compass"></i> Recommended Careers</h3>
            <div style="display: flex; gap: 8px;">
                <?php if (!($reportCard['career_recommendations_regenerated'] ?? 0)): ?>
                <button onclick="reprocessReportCard()" class="btn-primary btn-sm" style="background: linear-gradient(135deg, #10b981, #059669); border: none;">
                    <i class="fas fa-wand-magic-sparkles"></i> Generate New
                </button>
                <?php endif; ?>
                <button id="speech-btn-careers" class="btn-primary btn-sm" onclick="toggleSpeech('careers')">
                    <i class="fas fa-volume-high"></i> Recite
                </button>
            </div>
        </div>
        <div id="careers" class="card-content">
            <?php if (!empty($careerRec['recommended_careers'])): ?>
                <div class="career-tags">
                    <?php foreach ($careerRec['recommended_careers'] as $career): ?>
                        <span class="career-tag"><?php echo htmlspecialchars(is_array($career) ? implode(', ', $career) : $career); ?></span>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="no-data">Upload your report card to get personalized career recommendations.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Strengths -->
    <div class="recommendation-card">
        <div class="card-header">
            <h3><i class="fas fa-star"></i> Your Strengths</h3>
        </div>
        <div class="card-content">
            <?php if (!empty($careerRec['strengths'])): ?>
                <div class="strengths-grid">
                    <?php foreach ($careerRec['strengths'] as $strength): ?>
                        <div class="strength-item">
                            <i class="fas fa-check-circle"></i>
                            <span><?php echo htmlspecialchars(is_array($strength) ? implode(', ', $strength) : $strength); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="no-data">No strengths identified yet.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Courses and Requirements -->
    <div class="recommendation-card">
        <div class="card-header">
            <h3><i class="fas fa-graduation-cap"></i> Recommended Courses & Requirements</h3>
            <button id="speech-btn-courses" class="btn-primary btn-sm" onclick="toggleSpeech('courses')">
                <i class="fas fa-volume-high"></i> Recite
            </button>
        </div>
        <div id="courses" class="card-content">
            <?php if (!empty($careerRec['courses'])): ?>
                <?php
                $courseIcons = [
                    'software' => 'fas fa-laptop-code',
                    'computer' => 'fas fa-desktop',
                    'engineering' => 'fas fa-gears',
                    'medicine' => 'fas fa-stethoscope',
                    'health' => 'fas fa-heart-pulse',
                    'doctor' => 'fas fa-user-md',
                    'nurse' => 'fas fa-user-nurse',
                    'law' => 'fas fa-gavel',
                    'business' => 'fas fa-briefcase',
                    'accounting' => 'fas fa-calculator',
                    'commerce' => 'fas fa-chart-line',
                    'science' => 'fas fa-flask',
                    'art' => 'fas fa-palette',
                    'design' => 'fas fa-pen-nib',
                    'education' => 'fas fa-chalkboard-user',
                    'teaching' => 'fas fa-apple-whole',
                    'agriculture' => 'fas fa-seedling',
                    'music' => 'fas fa-music',
                    'psychology' => 'fas fa-brain',
                ];

                function getCourseIcon($courseName, $icons) {
                    $courseName = strtolower($courseName);
                    foreach ($icons as $keyword => $icon) {
                        if (strpos($courseName, $keyword) !== false) {
                            return $icon;
                        }
                    }
                    return 'fas fa-graduation-cap';
                }
                ?>
                <div class="courses-grid">
                    <?php foreach ($careerRec['courses'] as $course): ?>
                        <div class="course-card">
                            <div class="course-top">
                                <div class="course-icon">
                                    <i class="<?php echo getCourseIcon($course['name'], $courseIcons); ?>"></i>
                                </div>
                                <div style="flex: 1;">
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                                        <h3 style="margin-right: 10px;"><?php echo htmlspecialchars($course['name']); ?></h3>
                                        <?php if (isset($course['suitability_score'])): ?>
                                            <div class="suitability-badge" style="background: <?php echo $course['suitability_score'] >= 80 ? '#ecfdf5' : '#fef3c7'; ?>; color: <?php echo $course['suitability_score'] >= 80 ? '#059669' : '#d97706'; ?>; padding: 4px 8px; border-radius: 8px; font-size: 0.75rem; font-weight: 700; white-space: nowrap;">
                                                <?php echo $course['suitability_score']; ?>% Match
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <span class="duration">
                                        <i class="fas fa-clock"></i> <?php echo htmlspecialchars($course['duration'] ?? '3-4 years'); ?>
                                    </span>
                                </div>
                            </div>

                            <?php if (isset($course['why_it_matches'])): ?>
                                <p style="font-size: 0.85rem; color: #4b5563; margin: 10px 0; line-height: 1.4; border-left: 3px solid #667eea; padding-left: 10px;">
                                    <strong>Why it matches:</strong> <?php echo htmlspecialchars($course['why_it_matches']); ?>
                                </p>
                            <?php endif; ?>

                            <?php if (isset($course['career_outlook'])): ?>
                                <div style="display: flex; align-items: center; gap: 6px; margin-bottom: 12px;">
                                    <span style="background: #eef2ff; color: #4f46e5; font-size: 0.7rem; font-weight: 700; padding: 2px 6px; border-radius: 4px; text-transform: uppercase;">
                                        <i class="fas fa-arrow-trend-up"></i> Outlook
                                    </span>
                                    <span style="font-size: 0.8rem; color: #6b7280;"><?php echo htmlspecialchars($course['career_outlook']); ?></span>
                                </div>
                            <?php endif; ?>

                            <div class="divider"></div>
                            
                            <!-- Entry Requirements -->
                            <button type="button" class="details-toggle" onclick="toggleDetails(this)">
                                <span><i class="fas fa-list-check"></i> Entry Requirements</span>
                                <i class="fas fa-chevron-down"></i>
                            </button>
                            <div class="details-content">
                                <div class="requirements">
                                    <?php
                                    $apsRequired = $course['aps_required'] ?? null;
                                    $subjectReqs = $course['subject_requirements'] ?? [];
                                    $requirementsStr = $course['requirements'] ?? '';
                                    
                                    if ($apsRequired): ?>
                                        <div class="requirement-item">
                                            <i class="fas fa-circle-check"></i>
                                            <span>Min APS <?php echo $apsRequired; ?></span>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($subjectReqs) && is_array($subjectReqs)): ?>
                                        <?php foreach ($subjectReqs as $req): ?>
                                            <?php
                                            $subj = $req['subject'] ?? '';
                                            $level = $req['min_level'] ?? 0;
                                            if ($subj && $level): ?>
                                                <div class="requirement-item">
                                                    <i class="fas fa-circle-check"></i>
                                                    <span><?php echo htmlspecialchars($subj); ?> (Level <?php echo $level; ?>)</span>
                                                </div>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    <?php elseif (!empty($requirementsStr)): ?>
                                        <div class="requirement-item">
                                            <i class="fas fa-info-circle" style="color: #667eea;"></i>
                                            <span><?php echo htmlspecialchars($requirementsStr); ?></span>
                                        </div>
                                    <?php else: ?>
                                        <div class="requirement-item">
                                            <i class="fas fa-info-circle"></i>
                                            <span>Contact institution for detailed requirements</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <!-- Institutions -->
                            <?php if (!empty($course['institutions']) && is_array($course['institutions'])): ?>
                                <button type="button" class="details-toggle mt" onclick="toggleDetails(this)">
                                    <span><i class="fas fa-building-columns"></i> Potential Institutions</span>
                                    <i class="fas fa-chevron-down"></i>
                                </button>
                                <div class="details-content">
                                    <div class="institution-list">
                                        <?php foreach ($course['institutions'] as $inst): ?>
                                            <?php
                                            $instName = is_array($inst) ? ($inst['name'] ?? null) : $inst;
                                            $instAps = is_array($inst) ? ($inst['aps_required'] ?? null) : null;
                                            $instReason = is_array($inst) ? ($inst['reason'] ?? null) : null;
                                            $instLikelihood = is_array($inst) ? ($inst['admission_likelihood'] ?? null) : null;
                                            ?>
                                            <?php if ($instName): ?>
                                                <div class="institution-item" style="flex-direction: column; align-items: flex-start; gap: 5px; padding: 12px;">
                                                    <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                                                        <span style="font-weight: 700;">
                                                            <?php echo htmlspecialchars($instName); ?>
                                                            <?php if ($instAps): ?>
                                                                <span style="font-size: 0.7rem; color: #6b7280; font-weight: 400; margin-left: 5px;">(APS <?php echo $instAps; ?>)</span>
                                                            <?php endif; ?>
                                                        </span>
                                                        <?php if ($instLikelihood): ?>
                                                            <span style="font-size: 0.7rem; font-weight: 800; color: <?php echo strtolower($instLikelihood) === 'high' ? '#10b981' : '#f59e0b'; ?>; background: <?php echo strtolower($instLikelihood) === 'high' ? '#ecfdf5' : '#fffbeb'; ?>; padding: 2px 6px; border-radius: 4px;"><?php echo strtoupper($instLikelihood); ?> CHANCE</span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <?php if ($instReason): ?>
                                                        <p style="font-size: 0.75rem; color: #6b7280; margin: 0; font-style: italic;">
                                                            <?php echo htmlspecialchars($instReason); ?>
                                                        </p>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="no-data">Course information will appear here after analysis.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Bursaries and Scholarships -->
    <div class="recommendation-card">
        <div class="card-header">
            <h3><i class="fas fa-scholarship"></i> Bursaries & Scholarships You Qualify For</h3>
            <button id="speech-btn-bursaries" class="btn-primary btn-sm" onclick="toggleSpeech('bursaries')">
                <i class="fas fa-volume-high"></i> Recite
            </button>
        </div>
        <div id="bursaries" class="card-content">
            <?php if (!empty($careerRec['bursaries'])): ?>
                <div class="bursaries-list">
                    <?php foreach ($careerRec['bursaries'] as $bursary): ?>
                        <div class="bursary-card">
                            <div class="bursary-header">
                                <h4><?php echo htmlspecialchars($bursary['name']); ?></h4>
                                <span class="bursary-provider"><?php echo htmlspecialchars($bursary['provider']); ?></span>
                            </div>
                            <div class="bursary-details">
                                <p><strong><i class="fas fa-user-check"></i> Eligibility:</strong></p>
                                <p><?php echo htmlspecialchars($bursary['eligibility']); ?></p>
                                
                                <?php if (!empty($bursary['covers'])): ?>
                                    <p><strong><i class="fas fa-money-bill-wave"></i> Covers:</strong> <?php echo htmlspecialchars($bursary['covers']); ?></p>
                                <?php endif; ?>
                                
                                <p><strong><i class="fas fa-calendar"></i> Deadline:</strong> <?php echo htmlspecialchars($bursary['deadline']); ?></p>
                                
                                <?php if (!empty($bursary['contact'])): ?>
                                    <p><strong><i class="fas fa-phone"></i> Contact:</strong> <?php echo htmlspecialchars($bursary['contact']); ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="bursary-actions">
                                <?php if (!empty($bursary['apply_url'])): ?>
                                    <a href="<?php echo htmlspecialchars($bursary['apply_url']); ?>" target="_blank" class="btn-apply">
                                        <i class="fas fa-external-link-alt"></i> Apply Now
                                    </a>
                                <?php endif; ?>
                                <a href="https://www.google.com/search?q=<?php echo urlencode($bursary['name'] . ' application'); ?>" target="_blank" class="btn-search">
                                    <i class="fas fa-search"></i> Search
                                </a>
                                <button type="button" onclick="markBursaryAsApplied(<?php echo $reportCard['id']; ?>, '<?php echo htmlspecialchars($bursary['name']); ?>', '<?php echo htmlspecialchars($bursary['provider']); ?>')" class="btn-applied">
                                    <i class="fas fa-check-circle"></i> Mark Applied
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="no-data">Bursary recommendations will appear here after analysis.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Institutions -->
    <div class="recommendation-card">
        <div class="card-header">
            <h3><i class="fas fa-university"></i> Recommended Institutions</h3>
        </div>
        <div class="card-content">
            <div class="institutions-grid">
                <?php
                $themes = ['summer', 'night', 'winter', 'autumn', 'forest'];
                $institutions = !empty($careerRec['institutions']) ? $careerRec['institutions'] : [
                    ['name' => 'University of Cape Town', 'type' => 'Public', 'website' => 'https://www.uct.ac.za', 'aps_required' => 30, 'reason' => 'Top ranked university in Africa', 'admission_likelihood' => 'Moderate'],
                    ['name' => 'Wits University', 'type' => 'Public', 'website' => 'https://www.wits.ac.za', 'aps_required' => 28, 'reason' => 'Excellent research facilities', 'admission_likelihood' => 'Moderate'],
                    ['name' => 'University of Pretoria', 'type' => 'Public', 'website' => 'https://www.up.ac.za', 'aps_required' => 26, 'reason' => 'Strong industry connections', 'admission_likelihood' => 'High'],
                    ['name' => 'University of Johannesburg', 'type' => 'Public', 'website' => 'https://www.uj.ac.za', 'aps_required' => 24, 'reason' => 'Modern campuses and tech-focused', 'admission_likelihood' => 'High'],
                    ['name' => 'UNISA', 'type' => 'Distance Learning', 'website' => 'https://www.unisa.ac.za', 'aps_required' => 20, 'reason' => 'Flexible distance learning', 'admission_likelihood' => 'High'],
                    ['name' => 'Stellenbosch University', 'type' => 'Public', 'website' => 'https://www.sun.ac.za', 'aps_required' => 28, 'reason' => 'Prestigious academic reputation', 'admission_likelihood' => 'Moderate'],
                    ['name' => 'UKZN', 'type' => 'Public', 'website' => 'https://www.ukzn.ac.za', 'aps_required' => 26, 'reason' => 'Leading research in health and sciences', 'admission_likelihood' => 'High'],
                    ['name' => 'Tshwane University of Technology', 'type' => 'Public', 'website' => 'https://www.tut.ac.za', 'aps_required' => 20, 'reason' => 'Focus on vocational and tech training', 'admission_likelihood' => 'High'],
                    ['name' => 'Cape Peninsula University of Technology', 'type' => 'Public', 'website' => 'https://www.cput.ac.za', 'aps_required' => 20, 'reason' => 'Innovation-led technical university', 'admission_likelihood' => 'High'],
                    ['name' => 'North-West University', 'type' => 'Public', 'website' => 'https://www.nwu.ac.za', 'aps_required' => 24, 'reason' => 'Strong community and student life', 'admission_likelihood' => 'High'],
                ];
                foreach ($institutions as $idx => $inst):
                    $instName = is_array($inst) ? ($inst['name'] ?? 'Unknown') : $inst;
                    $instType = is_array($inst) ? ($inst['type'] ?? 'Public') : 'Public';
                    $instAps = is_array($inst) ? ($inst['aps_required'] ?? 24) : 24;
                    $instReason = is_array($inst) ? ($inst['reason'] ?? null) : null;
                    $instWebsite = is_array($inst) ? ($inst['website'] ?? null) : null;
                    $themeClass = 'theme-' . $themes[$idx % count($themes)];

                    // Prediction for general entry
                    $prediction = calculateAdmissionProbability(
                        $instAps,
                        $careerRec['aps'] ?? 0,
                        $reportCard['grade'] ?? '12',
                        $reportCard['term'] ?? '1',
                        [], // No specific subject reqs for general institution card
                        $reportCard['grades_data'] ?? []
                    );
                ?>
                    <div class="institution-card">
                        <div class="institution-card-landscape <?php echo $themeClass; ?>">
                            <div class="sky"></div>
                            <div class="sun"></div>
                            <div class="hill hill-1"></div>
                            <div class="hill hill-2"></div>
                            <div class="ocean">
                                <div class="reflection" style="width: 15px; height: 2px; top: 10%; left: 20%; clip-path: polygon(0% 0%, 100% 0%, 50% 100%);"></div>
                                <div class="reflection" style="width: 25px; height: 4px; top: 30%; left: 35%; clip-path: polygon(0% 0%, 100% 0%, 60% 100%, 40% 100%);"></div>
                            </div>
                            <div class="hill hill-3"></div>
                            <div class="hill hill-4"></div>
                            <div class="tree tree-1"><svg viewBox="0 0 64 64"><path d="M32,0C18.148,0,12,23.188,12,32c0,9.656,6.883,17.734,16,19.594V60c0,2.211,1.789,4,4,4s4-1.789,4-4v-8.406 C45.117,49.734,52,41.656,52,32C52,22.891,46.051,0,32,0z"></path></svg></div>
                            <div class="tree tree-2"><svg viewBox="0 0 64 64"><path d="M32,0C18.148,0,12,23.188,12,32c0,9.656,6.883,17.734,16,19.594V60c0,2.211,1.789,4,4,4s4-1.789,4-4v-8.406 C45.117,49.734,52,41.656,52,32C52,22.891,46.051,0,32,0z"></path></svg></div>
                            <div class="tree tree-3"><svg viewBox="0 0 64 64"><path d="M32,0C18.148,0,12,23.188,12,32c0,9.656,6.883,17.734,16,19.594V60c0,2.211,1.789,4,4,4s4-1.789,4-4v-8.406 C45.117,49.734,52,41.656,52,32C52,22.891,46.051,0,32,0z"></path></svg></div>
                            <div class="filter"></div>
                        </div>
                        <div class="institution-card-content" style="padding: 20px; display: flex; flex-direction: column; flex: 1;">
                            <h4>
                                <?php echo htmlspecialchars($instName); ?>
                                <span class="aps-badge-inline">
                                    APS <?php echo $instAps; ?>
                                </span>
                            </h4>
                            <span class="institution-type"><?php echo htmlspecialchars($instType); ?></span>
                            
                            <?php if ($instReason): ?>
                                <p class="inst-reason">
                                    <?php echo htmlspecialchars($instReason); ?>
                                </p>
                            <?php endif; ?>

                            <!-- Admission Probability Badge -->
                            <div class="prob-badge likelihood-indicator">
                                <i class="fas fa-chart-pie"></i>
                                <span><?php echo $prediction['percent']; ?>% Likelihood</span>
                            </div>

                            <div class="inst-actions">
                                <?php if ($instWebsite): ?>
                                    <a href="<?php echo htmlspecialchars($instWebsite); ?>" target="_blank" class="btn-visit" title="Visit Website">
                                        <i class="fas fa-external-link-alt"></i>
                                    </a>
                                <?php elseif (is_string($inst)): ?>
                                    <a href="https://www.google.com/search?q=<?php echo urlencode($instName); ?>" target="_blank" class="btn-visit" title="Search Info">
                                        <i class="fas fa-search"></i>
                                    </a>
                                <?php endif; ?>
                                
                                <button type="button" class="btn-improve" onclick='showImprovementAdvice(
                                    <?php echo htmlspecialchars(json_encode($instName), ENT_QUOTES); ?>, 
                                    "General Admission", 
                                    <?php echo $instAps; ?>, 
                                    "[]",
                                    <?php echo $careerRec['aps'] ?? 0; ?>,
                                    <?php echo htmlspecialchars(json_encode($reportCard['grade'] ?? "12"), ENT_QUOTES); ?>,
                                    <?php echo htmlspecialchars(json_encode($reportCard['term'] ?? "1"), ENT_QUOTES); ?>,
                                    <?php echo htmlspecialchars(json_encode($reportCard['grades_data'] ?? []), ENT_QUOTES); ?>
                                )' title="Improve Chances">
                                    <i class="fas fa-wand-magic-sparkles"></i>
                                </button>

                                <button type="button" onclick="markInstitutionAsApplied(<?php echo $reportCard['id']; ?>, '<?php echo htmlspecialchars($instName); ?>', '<?php echo htmlspecialchars($instType); ?>')" class="btn-applied" title="Applied">
                                    <i class="fas fa-check-circle"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<style>
.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    padding-bottom: 12px;
    border-bottom: 2px solid #f3f4f6;
    flex-wrap: wrap;
    gap: 10px;
}

h1, h2, h3, h4, h5, h6 {
    word-break: break-word;
    overflow-wrap: break-word;
}

.career-recommendations-container {
    max-width: 1200px;
    margin: 0 auto;
    padding-bottom: 120px; /* Space for floating bottom navigation */
    overflow-x: hidden;
    width: 100%;
}

.recommendation-card {
    background: white;
    border-radius: 16px;
    padding: 24px;
    margin-bottom: 24px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
    border: 1px solid #f3f4f6;
    overflow: hidden;
    width: 100%;
}

.card-header h3 {
    margin: 0;
    color: #1f2937;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 1.15rem;
    font-weight: 700;
}

@media (max-width: 640px) {
    .card-header h3 {
        font-size: 1rem;
    }
}

.card-header h3 i {
    color: #667eea;
}

.card-content {
    color: #374151;
}

.career-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.career-tag {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    padding: 8px 16px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 13px;
}

.strengths-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 10px;
}

@media (max-width: 400px) {
    .strengths-grid {
        grid-template-columns: 1fr;
    }
}

.strength-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 12px;
    background: #f9fafb;
    border-radius: 8px;
    font-size: 14px;
}

.strength-item i {
    color: #22c55e;
    font-size: 16px;
}

.strengths-list {
    list-style: none;
    padding: 0;
}

.strengths-list li {
    padding: 10px 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.strengths-list li i {
    color: #22c55e;
}

.courses-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 25px;
}

@media (max-width: 768px) {
    .courses-grid {
        grid-template-columns: 1fr;
        gap: 15px;
    }
}

.course-card {
    background: white;
    padding: 24px;
    border-radius: 16px;
    border: 1px solid #e5e7eb;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s, box-shadow 0.2s;
    display: flex;
    flex-direction: column;
}

@media (max-width: 768px) {
    .course-card {
        padding: 16px;
    }
    
    .course-icon {
        width: 40px;
        height: 40px;
        font-size: 16px;
    }
    
    .course-card h3 {
        font-size: 1rem !important;
    }
}

.course-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
}

.course-top {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 10px;
}

.course-icon {
    width: 50px;
    height: 50px;
    background: #eef2ff;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #4f46e5;
    font-size: 20px;
    flex-shrink: 0;
}

.course-card h3 {
    margin: 0;
    color: #111827;
    font-size: 18px;
    font-weight: 700;
    line-height: 1.3;
}

.duration {
    font-size: 13px;
    color: #6b7280;
    display: flex;
    align-items: center;
    gap: 5px;
    margin-top: 4px;
}

.divider {
    height: 1px;
    background: #f3f4f6;
    margin: 15px 0;
}

.course-card h4 {
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    margin: 0 0 12px 0;
    display: flex;
    align-items: center;
    gap: 8px;
}

.mt {
    margin-top: 15px !important;
}

.details-toggle {
    width: 100%;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #f3f4f6;
    border: none;
    padding: 10px 14px;
    border-radius: 10px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    transition: background 0.2s;
}

.details-toggle:hover {
    background: #e5e7eb;
}

.details-toggle i.fa-chevron-down {
    font-size: 12px;
    transition: transform 0.3s;
}

.details-toggle.active i.fa-chevron-down {
    transform: rotate(180deg);
}

.details-content {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s ease-out, padding 0.3s ease;
    padding: 0 14px;
}

.details-content.active {
    max-height: 500px; /* Large enough to fit content */
    padding: 12px 14px;
}

.requirements {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.requirement-item {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 14px;
    color: #4b5563;
}

.requirement-item i {
    color: #10b981;
}

.institution-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.institution-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 14px;
    background: #f9fafb;
    border-radius: 10px;
    font-weight: 500;
    color: #1f2937;
}

.aps-badge {
    background: #4f46e5;
    color: white;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
}

.bursaries-list {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.bursary-card {
    background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
    border: 1px solid #bae6fd;
    border-radius: 12px;
    padding: 20px;
}

@media (max-width: 768px) {
    .bursary-card {
        padding: 16px;
    }
    
    .bursary-header h4 {
        font-size: 1rem;
    }
    
    .bursary-details p {
        font-size: 0.85rem;
    }
}

.bursary-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.bursary-header h4 {
    margin: 0;
    color: #1f2937;
}

.bursary-provider {
    background: #667eea;
    color: white;
    padding: 5px 12px;
    border-radius: 15px;
    font-size: 12px;
    font-weight: 600;
}

.bursary-details p {
    margin: 10px 0;
    line-height: 1.6;
}

.bursary-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 15px;
}

.btn-apply, .btn-search, .btn-visit {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
    transition: opacity 0.2s;
}

.btn-apply {
    background: linear-gradient(135deg, #22c55e, #16a34a);
    color: white;
}

.btn-search {
    background: linear-gradient(135deg, #3b82f6, #2563eb);
    color: white;
}

.btn-visit {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    margin-top: 10px;
}

.btn-apply:hover, .btn-search:hover, .btn-visit:hover {
    opacity: 0.9;
}

.institutions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
}

.institution-card {
    background: white;
    padding: 0;
    border-radius: 20px;
    text-align: center;
    border: 1px solid #e5e7eb;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
    height: 100%;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.institution-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 20px -5px rgba(0, 0, 0, 0.1);
}

@media (max-width: 768px) {
    .institutions-grid {
        grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
        gap: 16px;
    }

    .institution-card, .bursary-card {
        padding: 16px;
        border-radius: 16px;
    }
    
    .institution-card h4 {
        font-size: 1rem !important;
    }
    
    .institution-card p {
        font-size: 0.8rem !important;
        margin-bottom: 12px !important;
        line-height: 1.4 !important;
    }
}

@media (max-width: 480px) {
    .institutions-grid {
        grid-template-columns: 1fr;
    }

    .bursary-actions {
        display: flex !important;
        flex-wrap: wrap !important;
        gap: 8px !important;
        width: 100% !important;
    }

    .bursary-actions .btn-apply, 
    .bursary-actions .btn-search {
        flex: 1 1 calc(50% - 4px) !important;
        justify-content: center !important;
        padding: 10px 5px !important;
        font-size: 0.8rem !important;
        width: auto !important;
        min-width: 0 !important;
    }

    .bursary-actions .btn-applied {
        flex: 1 1 100% !important;
        width: 100% !important;
        margin-top: 0 !important;
        padding: 10px 5px !important;
        font-size: 0.8rem !important;
    }
}

.no-recommendations-card {
    background: white;
    border-radius: 16px;
    padding: 60px 40px;
    text-align: center;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    max-width: 600px;
    margin: 0 auto;
}
.no-recommendations-content i {
    font-size: 64px;
    color: #667eea;
    margin-bottom: 20px;
    display: block;
}
.no-recommendations-content h3 {
    color: #1f2937;
    margin-bottom: 15px;
    font-size: 24px;
}
.no-recommendations-content p {
    color: #6b7280;
    margin-bottom: 30px;
    font-size: 16px;
}
.no-recommendations-actions {
    display: flex;
    gap: 15px;
    justify-content: center;
    flex-wrap: wrap;
}
/* Mark as Applied Button */
.btn-applied {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    border: none;
    padding: 10px 16px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all 0.2s;
    text-decoration: none;
    width: 100%;
}
.btn-applied:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
}
.btn-applied:active {
    transform: translateY(0);
}
.btn-applied.applied {
    background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
    cursor: not-allowed;
}
.btn-applied.applied:hover {
    transform: none;
    box-shadow: none;
}
</style>

<script>
// Toggle details dropdowns
function toggleDetails(button) {
    const content = button.nextElementSibling;
    button.classList.toggle('active');
    content.classList.toggle('active');
}

// Show Improvement Advice Modal
function showImprovementAdvice(institution, course, apsReq, subjectReqs, studentAps, grade, term, studentGrades) {
    const modal = document.getElementById('improvementModal');
    const content = document.getElementById('improvementModalContent');
    
    // Clear previous content
    content.innerHTML = '<div style="text-align:center; padding: 40px;"><i class="fas fa-spinner fa-spin" style="font-size: 2rem; color: #6366f1;"></i><p style="margin-top:15px; color: #64748b;">AI is analyzing your performance...</p></div>';
    modal.classList.add('active');
    
    // Parse data
    const sGrades = typeof studentGrades === 'string' ? JSON.parse(studentGrades) : studentGrades;
    const sReqs = typeof subjectReqs === 'string' ? JSON.parse(subjectReqs) : subjectReqs;
    
    // Simulate AI analysis delay
    setTimeout(() => {
        let html = `
            <div style="margin-bottom: 25px;">
                <h2 style="margin: 0 0 5px 0; color: #1e293b; font-size: 1.4rem;">Improve Your Chances</h2>
                <p style="margin: 0; color: #64748b; font-size: 0.95rem;">Analysis for <strong>${course}</strong> at <strong>${institution}</strong></p>
            </div>
            
            <div class="improvement-advice-section">
                <h3 style="font-size: 1.1rem; color: #1e293b; margin-bottom: 15px; display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-bullseye" style="color: #6366f1;"></i> Target Improvements
                </h3>
        `;
        
        let needsImprovement = false;
        
        // Check APS
        if (studentAps < apsReq) {
            needsImprovement = true;
            html += `
                <div class="advice-card">
                    <h4><i class="fas fa-chart-line" style="color: #6366f1;"></i> APS Score <span class="target-badge">Target: ${apsReq}</span></h4>
                    <p style="margin: 5px 0 0 0; font-size: 0.9rem; color: #475569;">You currently have <strong>${studentAps} APS</strong>. You need to increase this by <strong>${apsReq - studentAps} points</strong> to meet the minimum requirement.</p>
                </div>
            `;
        }
        
        // Check Subjects
        if (sReqs && sReqs.length > 0) {
            sReqs.forEach(req => {
                const subjName = req.subject || '';
                const reqLevel = req.min_level || 0;
                
                let sMatch = null;
                for (let sSubj in sGrades) {
                    if (sSubj.toLowerCase().includes(subjName.toLowerCase()) || subjName.toLowerCase().includes(sSubj.toLowerCase())) {
                        sMatch = sGrades[sSubj];
                        break;
                    }
                }
                
                let sLevel = 0;
                if (sMatch) {
                    const pctMatch = sMatch.match(/(\d+)/);
                    if (pctMatch) {
                        const pct = parseInt(pctMatch[1]);
                        if (pct >= 80) sLevel = 7;
                        else if (pct >= 70) sLevel = 6;
                        else if (pct >= 60) sLevel = 5;
                        else if (pct >= 50) sLevel = 4;
                        else if (pct >= 40) sLevel = 3;
                        else if (pct >= 30) sLevel = 2;
                        else sLevel = 1;
                    }
                }
                
                if (sLevel < reqLevel) {
                    needsImprovement = true;
                    const targetPct = reqLevel === 7 ? '80%+' : (reqLevel === 6 ? '70%+' : (reqLevel === 5 ? '60%+' : '50%+'));
                    html += `
                        <div class="advice-card">
                            <h4><i class="fas fa-book" style="color: #6366f1;"></i> ${subjName} <span class="target-badge">Target: Level ${reqLevel} (${targetPct})</span></h4>
                            <p style="margin: 5px 0 0 0; font-size: 0.9rem; color: #475569;">Your current performance is Level ${sLevel}. Focus on ${subjName} to reach the required level.</p>
                        </div>
                    `;
                }
            });
        }
        
        if (!needsImprovement) {
            html += `
                <div class="advice-card" style="border-color: #ecfdf5; background: #f0fdf4;">
                    <h4 style="color: #065f46;"><i class="fas fa-check-circle"></i> Requirements Met!</h4>
                    <p style="margin: 5px 0 0 0; font-size: 0.9rem; color: #065f46;">You already meet the minimum requirements. Focus on maintaining these marks to ensure admission.</p>
                </div>
            `;
        }
        
        html += `</div>`; // End section
        
        // Time & Strategy
        const gradeInt = parseInt(grade);
        const termInt = parseInt(term);
        let timeAdvice = "";
        if (gradeInt === 12) {
            if (termInt === 1) timeAdvice = "You are in Grade 12 Term 1. This is the perfect time to make a massive impact on your final results.";
            else if (termInt === 2) timeAdvice = "Grade 12 Term 2 marks are critical for provisional admission. Every mark counts now.";
            else if (termInt === 3) timeAdvice = "You are approaching final exams. Focus on past papers and intensive revision.";
            else timeAdvice = "Final exams are here. Stay focused and prioritize your weakest subjects.";
        } else {
            timeAdvice = `Being in Grade ${gradeInt}, you have a strategic advantage. You have enough time to significantly boost your academic profile before Matric.`;
        }
        
        html += `
            <div style="margin-bottom: 25px;">
                <h3 style="font-size: 1.1rem; color: #1e293b; margin-bottom: 15px; display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-lightbulb" style="color: #f59e0b;"></i> Strategy & Time
                </h3>
                <p style="font-size: 0.95rem; color: #475569; line-height: 1.6;">${timeAdvice}</p>
                <ul style="padding-left: 20px; color: #475569; font-size: 0.9rem; line-height: 1.8;">
                    <li>Set aside 2 extra hours weekly for your lowest subjects.</li>
                    <li>Use our AI study plan feature to organize your revision.</li>
                    <li>Download past papers from our "Resources" section.</li>
                </ul>
            </div>
        `;
        
        // Alternatives
        if (studentAps < apsReq - 2 || needsImprovement) {
            html += `
                <div>
                    <h3 style="font-size: 1.1rem; color: #1e293b; margin-bottom: 15px; display: flex; align-items: center; gap: 8px;">
                        <i class="fas fa-shuffle" style="color: #6366f1;"></i> Backup Options
                    </h3>
                    <div class="alt-option">
                        <p style="margin: 0; font-size: 0.9rem; color: #1e293b; font-weight: 600;">Consider a related Diploma</p>
                        <p style="margin: 5px 0 0 0; font-size: 0.85rem; color: #475569;">Diplomas typically require lower APS (18-22) and can lead to a Degree later via articulation.</p>
                    </div>
                    <div class="alt-option" style="border-color: #10b981;">
                        <p style="margin: 0; font-size: 0.9rem; color: #1e293b; font-weight: 600;">Extended/Foundation Programme</p>
                        <p style="margin: 5px 0 0 0; font-size: 0.85rem; color: #475569;">Many universities offer 4-year extended degrees for students who slightly miss the marks.</p>
                    </div>
                </div>
            `;
        }
        
        content.innerHTML = html;
    }, 800);
}

function closeImprovementModal() {
    document.getElementById('improvementModal').classList.remove('active');
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('improvementModal');
    if (event.target === modal) {
        closeImprovementModal();
    }
}

// Mark bursary as applied
async function markBursaryAsApplied(reportCardId, bursaryName, bursaryProvider) {
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
            button.classList.add('applied');
            button.innerHTML = '<i class="fas fa-check"></i> Applied';
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

// Mark institution as applied
async function markInstitutionAsApplied(reportCardId, institutionName, institutionType) {
    if (!confirm(`Mark "${institutionName}" as applied? This will add it to your applications on the dashboard.`)) {
        return;
    }

    const button = event.target.closest('button');
    const originalText = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

    try {
        const response = await fetch('/api/mark-institution-applied', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                'institution_name': institutionName,
                'institution_type': institutionType
            })
        });

        const result = await response.json();

        if (result.success) {
            alert('✓ Institution marked as applied! Check your dashboard to track your application.');
            button.classList.add('applied');
            button.innerHTML = '<i class="fas fa-check"></i> Applied';
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
</script>

<!-- Improvement Advice Modal -->
<div id="improvementModal" class="glass-modal">
    <div class="glass-modal-content">
        <button class="modal-close" onclick="closeImprovementModal()">
            <i class="fas fa-times"></i>
        </button>
        <div id="improvementModalContent">
            <!-- Content will be populated by JavaScript -->
        </div>
        <div style="margin-top: 30px; text-align: center;">
            <button onclick="closeImprovementModal()" class="btn-primary" style="background: linear-gradient(135deg, #6366f1, #a855f7); width: 100%; max-width: none; border-radius: 12px;">
                Got it, thanks!
            </button>
        </div>
    </div>
</div>

<?php endif; ?>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
