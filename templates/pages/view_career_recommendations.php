<?php
$pageTitle = 'Career Recommendations - StudySmart';
$currentPage = 'careers';
$reportCardIdJs = htmlspecialchars($reportCard['id'] ?? '');
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

<a href="/dashboard" class="btn-primary" style="text-decoration: none; display: inline-block; margin-bottom: 20px;">
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
            <button id="speech-btn-careers" class="btn-primary btn-sm" onclick="toggleSpeech('careers')">
                <i class="fas fa-volume-high"></i> Recite
            </button>
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
                    <?php foreach (array_slice($careerRec['courses'], 0, 5) as $course): ?>
                        <div class="course-card">
                            <div class="course-top">
                                <div class="course-icon">
                                    <i class="<?php echo getCourseIcon($course['name'], $courseIcons); ?>"></i>
                                </div>
                                <div>
                                    <h3><?php echo htmlspecialchars($course['name']); ?></h3>
                                    <span class="duration">
                                        <i class="fas fa-clock"></i> <?php echo htmlspecialchars($course['duration'] ?? '3-4 years'); ?>
                                    </span>
                                </div>
                            </div>

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
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <!-- Institutions -->
                            <?php if (!empty($course['institutions']) && is_array($course['institutions'])): ?>
                                <button type="button" class="details-toggle mt" onclick="toggleDetails(this)">
                                    <span><i class="fas fa-building-columns"></i> Institutions</span>
                                    <i class="fas fa-chevron-down"></i>
                                </button>
                                <div class="details-content">
                                    <div class="institution-list">
                                        <?php foreach ($course['institutions'] as $inst): ?>
                                            <?php
                                            $instName = is_array($inst) ? ($inst['name'] ?? null) : $inst;
                                            $instAps = is_array($inst) ? ($inst['aps_required'] ?? null) : null;
                                            ?>
                                            <?php if ($instName): ?>
                                                <div class="institution-item">
                                                    <span><?php echo htmlspecialchars($instName); ?></span>
                                                    <?php if ($instAps): ?>
                                                        <div class="aps-badge">APS <?php echo $instAps; ?></div>
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
                $institutions = [
                    ['name' => 'University of Cape Town', 'type' => 'Public', 'website' => 'https://www.uct.ac.za'],
                    ['name' => 'Wits University', 'type' => 'Public', 'website' => 'https://www.wits.ac.za'],
                    ['name' => 'Stellenbosch University', 'type' => 'Public', 'website' => 'https://www.sun.ac.za'],
                    ['name' => 'University of Pretoria', 'type' => 'Public', 'website' => 'https://www.up.ac.za'],
                    ['name' => 'University of Johannesburg', 'type' => 'Public', 'website' => 'https://www.uj.ac.za'],
                    ['name' => 'UKZN', 'type' => 'Public', 'website' => 'https://www.ukzn.ac.za'],
                    ['name' => 'UNISA', 'type' => 'Distance Learning', 'website' => 'https://www.unisa.ac.za'],
                    ['name' => 'Tshwane University of Technology (TUT)', 'type' => 'Public', 'website' => 'https://www.tut.ac.za'],
                    ['name' => 'Cape Peninsula University of Technology', 'type' => 'Public', 'website' => 'https://www.cput.ac.za'],
                    ['name' => 'Durban University of Technology', 'type' => 'Public', 'website' => 'https://www.dut.ac.za'],
                ];
                foreach ($institutions as $inst):
                ?>
                    <div class="institution-card">
                        <h4><?php echo htmlspecialchars($inst['name']); ?></h4>
                        <span class="institution-type"><?php echo htmlspecialchars($inst['type']); ?></span>
                        <a href="<?php echo htmlspecialchars($inst['website']); ?>" target="_blank" class="btn-visit">
                            <i class="fas fa-external-link-alt"></i> Visit Website
                        </a>
                        <button type="button" onclick="markInstitutionAsApplied(<?php echo $reportCard['id']; ?>, '<?php echo htmlspecialchars($inst['name']); ?>', '<?php echo htmlspecialchars($inst['type']); ?>')" class="btn-applied">
                            <i class="fas fa-check-circle"></i> Mark Applied
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<style>
.career-recommendations-container {
    max-width: 1200px;
    margin: 0 auto;
}

.recommendation-card {
    background: white;
    border-radius: 16px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    padding-bottom: 12px;
    border-bottom: 2px solid #f3f4f6;
}

.card-header h3 {
    margin: 0;
    color: #1f2937;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 1.1rem;
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
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 10px;
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
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 25px;
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
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
}

.institution-card {
    background: #f9fafb;
    padding: 20px;
    border-radius: 12px;
    text-align: center;
}

.institution-card h4 {
    margin: 0 0 10px;
    color: #1f2937;
    font-size: 15px;
}

.institution-type {
    background: #e5e7eb;
    color: #6b7280;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
}

.btn-sm {
    padding: 8px 16px;
    font-size: 13px;
    min-height: 36px;
}

.no-data {
    color: #6b7280;
    font-style: italic;
}

@media (max-width: 768px) {
    .card-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
    
    .bursary-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
    
    .bursary-actions {
        flex-direction: column;
    }
    
    .courses-grid {
        grid-template-columns: 1fr;
    }

    .institutions-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
    }

    .institution-card {
        padding: 15px 10px;
    }

    .institution-card h4 {
        font-size: 13px;
        min-height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .institution-type {
        font-size: 10px;
        padding: 2px 8px;
    }

    .btn-visit, .btn-applied {
        padding: 8px 10px;
        font-size: 11px;
        width: 100%;
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
    width: 100px;
    margin-top: 10px;
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

<?php endif; ?>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
