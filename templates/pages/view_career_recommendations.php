<?php
$pageTitle = 'Career Recommendations - StudySmart';
$currentPage = 'careers';
$extraHead = '<script>
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
            speakContent(content, btn);
        }
    };

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
</script>';
include __DIR__ . '/../layouts/header.php';
?>

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
                    Upload your results to calculate your APS score
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
                <div class="courses-grid">
                    <?php foreach (array_slice($careerRec['courses'], 0, 5) as $course): ?>
                        <div class="course-card">
                            <h4><?php echo htmlspecialchars($course['name']); ?></h4>
                            <p class="course-duration"><i class="fas fa-clock"></i> <?php echo htmlspecialchars($course['duration'] ?? '3-4 years'); ?></p>
                            <div class="course-requirements">
                                <strong><i class="fas fa-list-check"></i> Entry Requirements:</strong>
                                <p><?php echo htmlspecialchars($course['requirements'] ?? 'Varies by institution'); ?></p>
                            </div>
                            <?php if (!empty($course['institutions']) && is_array($course['institutions'])): ?>
                                <div class="course-institutions">
                                    <strong><i class="fas fa-university"></i> Institutions Offering This Course:</strong>
                                    <ul class="institution-list">
                                        <?php foreach ($course['institutions'] as $inst): ?>
                                            <?php 
                                            $instName = is_array($inst) ? ($inst['name'] ?? null) : $inst;
                                            $instReq = is_array($inst) ? ($inst['entry_requirements'] ?? null) : null;
                                            ?>
                                            <?php if ($instName): ?>
                                                <li>
                                                    <strong><?php echo htmlspecialchars($instName); ?></strong>
                                                    <?php if ($instReq): ?>
                                                        <br><small style="color: #666;"><i class="fas fa-info-circle"></i> Requires: <?php echo htmlspecialchars($instReq); ?></small>
                                                    <?php endif; ?>
                                                </li>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </ul>
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
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
}

.course-card {
    background: #f9fafb;
    padding: 20px;
    border-radius: 12px;
    border-left: 4px solid #667eea;
}

.course-card h4 {
    margin: 0 0 10px;
    color: #1f2937;
}

.course-duration {
    color: #6b7280;
    font-size: 14px;
    margin-bottom: 15px;
}

.course-requirements, .course-institutions {
    margin-top: 15px;
}

.course-requirements strong, .course-institutions strong {
    display: block;
    margin-bottom: 5px;
    color: #374151;
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
    
    .courses-grid, .institutions-grid {
        grid-template-columns: 1fr;
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
</style>

<?php endif; ?>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
