<?php
$pageTitle = 'View Study Plan - StudySmart';
$currentPage = 'study-plan';
include __DIR__ . '/../layouts/header.php';

// Determine if study plan is completed
$isCompleted = isset($studyPlan['is_completed']) && $studyPlan['is_completed'];
?>

<style>
.view-study-plan-page {
    max-width: 900px;
    margin: 0 auto;
    padding: 20px;
}

.study-plan-header {
    margin-bottom: 20px;
}

.study-plan-title {
    font-size: 24px;
    color: #1e293b;
    margin: 0 0 8px 0;
}

.study-plan-subtitle {
    color: #64748b;
    font-size: 14px;
    margin: 0;
}

/* Yellow Highlight Styles */
.highlight-word {
    background-color: #fef08a;
    border-radius: 4px;
    padding: 2px 4px;
    transition: background-color 0.1s ease;
}
.highlight-sentence {
    background-color: #fef08a;
    border-radius: 4px;
    padding: 2px;
    display: inline;
}
@media (max-width: 768px) {
    .highlight-word, .highlight-sentence {
        background-color: #fde047;
        padding: 3px 5px;
    }
}

/* Floating Recitation Control Bar */
.recitation-control-bar {
    position: fixed;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%) translateY(100px);
    background: rgba(30, 41, 59, 0.85);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border-radius: 16px;
    padding: 12px 20px;
    display: flex;
    align-items: center;
    gap: 12px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
    border: 1px solid rgba(255, 255, 255, 0.1);
    z-index: 1000;
    transition: transform 0.3s ease, opacity 0.3s ease;
    opacity: 0;
    min-width: 320px;
    max-width: 90%;
}

.recitation-control-bar.visible {
    transform: translateX(-50%) translateY(0);
    opacity: 1;
}

.recitation-control-bar.hidden {
    transform: translateX(-50%) translateY(100px);
    opacity: 0;
}

.recitation-progress-container {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.recitation-progress-bar {
    width: 100%;
    height: 6px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 3px;
    overflow: hidden;
    cursor: pointer;
    position: relative;
}

.recitation-progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #667eea, #764ba2);
    border-radius: 3px;
    transition: width 0.1s linear;
    width: 0%;
}

.recitation-time {
    display: flex;
    justify-content: space-between;
    font-size: 11px;
    color: rgba(255, 255, 255, 0.7);
    font-family: monospace;
}

.recitation-controls {
    display: flex;
    align-items: center;
    gap: 8px;
}

.recitation-btn {
    background: rgba(255, 255, 255, 0.15);
    border: none;
    color: white;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
    font-size: 14px;
}

.recitation-btn:hover {
    background: rgba(255, 255, 255, 0.25);
    transform: scale(1.1);
}

.recitation-btn:active {
    transform: scale(0.95);
}

.recitation-sentence-count {
    font-size: 11px;
    color: rgba(255, 255, 255, 0.7);
    min-width: 50px;
    text-align: center;
}

.back-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    text-decoration: none;
    border-radius: 8px;
    font-weight: 600;
    font-size: 14px;
    transition: transform 0.2s;
    margin-bottom: 20px;
}

.back-btn:hover {
    transform: translateY(-2px);
}

/* Study Plan Card */
.study-plan-card {
    background: white;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.study-plan-card h3 {
    margin: 0 0 20px 0;
    color: #1e293b;
    font-size: 18px;
    display: flex;
    align-items: center;
    gap: 10px;
}

/* Study Plan Info List */
.study-plan-info {
    list-style: none;
    padding: 0;
    margin: 0 0 20px 0;
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
}

.study-plan-info li {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    background: #f8fafc;
    border-radius: 8px;
    font-size: 14px;
    color: #64748b;
}

.study-plan-info li strong {
    color: #1e293b;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
}

.status-badge.in-progress {
    background: #fef3c7;
    color: #92400e;
}

.status-badge.completed {
    background: #d1fae5;
    color: #065f46;
}

/* Action Buttons */
.study-plan-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    margin-bottom: 20px;
}

.btn-primary,
.btn-secondary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 12px 20px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.2s;
    text-decoration: none;
    border: none;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.btn-primary:hover {
    transform: translateY(-2px);
}

.btn-secondary {
    background: white;
    border: 2px solid #e2e8f0;
    color: #64748b;
}

.btn-secondary:hover {
    background: #f8fafc;
}

.btn-success {
    background: linear-gradient(135deg, #16a34a, #059669);
    color: white;
}

/* Plan Details */
.plan-details {
    background: #f9fafb;
    padding: 20px;
    border-radius: 8px;
    white-space: pre-wrap;
    line-height: 1.8;
    color: #374151;
}

.plan-details h4 {
    margin: 0 0 15px 0;
    color: #1e293b;
    font-size: 16px;
    display: flex;
    align-items: center;
    gap: 10px;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .view-study-plan-page {
        padding: 15px;
    }

    .study-plan-title {
        font-size: 20px;
    }

    .study-plan-card {
        padding: 20px;
    }

    /* Convert info to vertical list on mobile */
    .study-plan-info {
        flex-direction: column;
        gap: 10px;
    }

    .study-plan-info li {
        width: 100%;
        justify-content: space-between;
        padding: 12px 15px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
    }

    .study-plan-actions {
        flex-direction: column;
    }

    .study-plan-actions button,
    .study-plan-actions a {
        width: 100%;
        justify-content: center;
    }

    .plan-details {
        padding: 15px;
        font-size: 14px;
    }
}

@media (max-width: 480px) {
    .study-plan-card {
        padding: 15px;
    }

    .study-plan-info li {
        flex-direction: column;
        align-items: flex-start;
        gap: 5px;
    }

    .status-badge {
        width: 100%;
        justify-content: center;
    }
}
</style>

<div class="view-study-plan-page">
    <div class="study-plan-header">
        <a href="/study-plan" class="back-btn">
            <i class="fas fa-arrow-left"></i> Back to Study Plans
        </a>
        <h1 class="study-plan-title">Study Plan</h1>
        <p class="study-plan-subtitle"><?php echo htmlspecialchars($studyPlan['title']); ?></p>
    </div>

    <div class="study-plan-card">
        <h3>
            <i class="fas fa-calendar-alt"></i> <?php echo htmlspecialchars($studyPlan['title']); ?>
        </h3>

        <ul class="study-plan-info">
            <li>
                <strong><i class="fas fa-calendar"></i> Created:</strong>
                <span><?php echo date('M d, Y', strtotime($studyPlan['created_at'])); ?></span>
            </li>
            <li>
                <strong><i class="fas fa-flag"></i> Status:</strong>
                <span class="status-badge <?php echo $isCompleted ? 'completed' : 'in-progress'; ?>">
                    <i class="fas fa-<?php echo $isCompleted ? 'check-circle' : 'clock'; ?>"></i>
                    <?php echo $isCompleted ? 'Completed' : 'In Progress'; ?>
                </span>
            </li>
        </ul>

        <div class="study-plan-actions">
            <button id="reciteBtn" onclick="reciteStudyPlan()" class="btn-primary">
                <i class="fas fa-volume-up"></i> AI Recite Study Plan
            </button>
            <button id="stopReciteBtn" onclick="stopRecitation()" class="btn-secondary" style="display: none;">
                <i class="fas fa-stop"></i> Stop Recitation
            </button>
            <?php if (!$isCompleted): ?>
                <button onclick="markStudyPlanComplete()" class="btn-primary btn-success">
                    <i class="fas fa-check-circle"></i> Mark as Complete
                </button>
            <?php else: ?>
                <span class="status-badge completed" style="padding: 12px 20px;">
                    <i class="fas fa-check-circle"></i> Completed
                </span>
            <?php endif; ?>
        </div>

        <div class="plan-details" id="plan-content">
            <h4><i class="fas fa-clipboard-list"></i> Plan Details</h4>
            <?php
            // Remove markdown formatting from study plan content
            $content = $studyPlan['content'];
            // Remove bold (**text**)
            $content = preg_replace('/\*\*(.*?)\*\*/', '$1', $content);
            // Remove italic (*text*)
            $content = preg_replace('/\*(.*?)\*/', '$1', $content);
            // Remove headers (###, ##, #)
            $content = preg_replace('/^#+\s*/m', '', $content);
            // Remove horizontal rules (---)
            $content = preg_replace('/^---\s*$/m', '', $content);
            // Remove markdown links [text](url)
            $content = preg_replace('/\[(.*?)\]\(.*?\)/', '$1', $content);
            // Remove escaped characters
            $content = str_replace(['\\'], '', $content);
            // Clean up multiple spaces/newlines
            $content = preg_replace('/\n{3,}/', "\n\n", $content);
            echo htmlspecialchars(trim($content));
            ?>
        </div>
    </div>
</div>

<!-- Floating Recitation Control Bar -->
<div id="recitation-control-bar" class="recitation-control-bar hidden">
    <div class="recitation-controls">
        <button id="pause-btn" class="recitation-btn" onclick="togglePause()" title="Pause">
            <i class="fas fa-pause"></i>
        </button>
        <button class="recitation-btn" onclick="stopRecitation()" title="Stop">
            <i class="fas fa-stop"></i>
        </button>
    </div>
    <div class="recitation-progress-container">
        <div class="recitation-progress-bar" id="recitation-progress-bar">
            <div class="recitation-progress-fill" id="recitation-progress-fill"></div>
        </div>
        <div class="recitation-time">
            <span id="current-time">00:00</span>
            <span id="sentence-count">0/0</span>
        </div>
    </div>
</div>

<script>
let isReciting = false;
let isPaused = false;
let synthesis = window.speechSynthesis;
let availableVoices = [];
let currentUtterance = null;
let currentSentenceIndex = 0;
let totalSentences = 0;
let startTime = 0;
let elapsedTime = 0;
let timerInterval = null;
let sentences = [];
const PLAN_TITLE = "<?php echo addslashes($studyPlan['title']); ?>";

// Load available voices
function loadVoices() {
    availableVoices = window.speechSynthesis.getVoices();
}

if ('speechSynthesis' in window) {
    loadVoices();
    // Chrome loads voices asynchronously
    if (speechSynthesis.onvoiceschanged !== undefined) {
        speechSynthesis.onvoiceschanged = loadVoices;
    }
}

function togglePause() {
    if (isPaused) {
        resumeRecitation();
    } else {
        pauseRecitation();
    }
}

function pauseRecitation() {
    isPaused = true;
    synthesis.pause();
    stopTimer();
    updatePauseButton();
}

function resumeRecitation() {
    isPaused = false;
    synthesis.resume();
    startTimer();
    updatePauseButton();
}

function updatePauseButton() {
    const pauseBtn = document.getElementById("pause-btn");
    if (pauseBtn) {
        if (isPaused) {
            pauseBtn.innerHTML = "<i class=\"fas fa-play\"></i>";
            pauseBtn.title = "Resume";
        } else {
            pauseBtn.innerHTML = "<i class=\"fas fa-pause\"></i>";
            pauseBtn.title = "Pause";
        }
    }
}

function showControlBar() {
    const controlBar = document.getElementById("recitation-control-bar");
    if (controlBar) {
        controlBar.classList.remove("hidden");
        controlBar.classList.add("visible");
    }
}

function hideControlBar() {
    const controlBar = document.getElementById("recitation-control-bar");
    if (controlBar) {
        controlBar.classList.remove("visible");
        controlBar.classList.add("hidden");
    }
}

function updateProgress(index) {
    const progressFill = document.getElementById("recitation-progress-fill");
    const currentTime = document.getElementById("current-time");
    const sentenceCount = document.getElementById("sentence-count");
    
    if (progressFill && totalSentences > 0) {
        const progress = ((index + 1) / totalSentences) * 100;
        progressFill.style.width = progress + "%";
    }
    
    if (sentenceCount) {
        sentenceCount.textContent = (index + 1) + "/" + totalSentences;
    }
}

function startTimer() {
    startTime = Date.now() - elapsedTime;
    timerInterval = setInterval(() => {
        elapsedTime = Date.now() - startTime;
        updateTimeDisplay();
    }, 1000);
}

function stopTimer() {
    if (timerInterval) {
        clearInterval(timerInterval);
        timerInterval = null;
    }
}

function updateTimeDisplay() {
    const currentTimeEl = document.getElementById("current-time");
    if (currentTimeEl) {
        currentTimeEl.textContent = formatTime(elapsedTime);
    }
}

function formatTime(ms) {
    const totalSeconds = Math.floor(ms / 1000);
    const minutes = Math.floor(totalSeconds / 60);
    const seconds = totalSeconds % 60;
    return (minutes < 10 ? "0" + minutes : minutes) + ":" + (seconds < 10 ? "0" + seconds : seconds);
}
async function markStudyPlanAsViewed() {
    try {
        console.log('Marking study plan <?php echo $studyPlan['id']; ?> as viewed...');
        const response = await fetch('/study-plan/<?php echo $studyPlan['id']; ?>/mark-viewed', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            credentials: 'include', // Send cookies for session
        });

        console.log('Response status:', response.status);
        const data = await response.json();
        console.log('Response data:', data);
        
        if (data.success) {
            console.log('Study plan marked as viewed successfully');
            // Update notification badge in header
            updateNotificationBadge();
        } else {
            console.error('Failed to mark as viewed:', data.error);
        }
    } catch (error) {
        console.error('Error marking study plan as viewed:', error);
    }
}

// Update notification badge count
async function updateNotificationBadge() {
    try {
        console.log('Fetching pending count from API...');
        
        // Fetch the actual count from the server
        const response = await fetch('/api/study-plan/pending-count', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
            },
            credentials: 'include',
        });

        console.log('Response status:', response.status);
        
        if (!response.ok) {
            console.error('API returned error status:', response.status);
            return;
        }

        const data = await response.json();
        console.log('API response data:', data);
        
        // Find and update the badge in the sidebar
        const badge = document.querySelector('.sidebar .menu a[href="/study-plan"] .notification-badge');
        console.log('Badge element found:', badge ? 'yes' : 'no');
        
        if (badge) {
            if (data.success) {
                const count = data.count;
                console.log('Current pending count from server:', count);
                
                if (count <= 0) {
                    badge.remove();
                    console.log('Badge removed (count is 0)');
                } else {
                    badge.textContent = count > 99 ? '99+' : count;
                    console.log('Badge updated to:', count);
                }
            } else {
                console.error('API returned success=false:', data.error);
            }
        } else {
            console.log('No badge element found on page (might already be hidden)');
        }
    } catch (error) {
        console.error('Error updating badge:', error);
    }
}

// Mark study plan as viewed when page loads
markStudyPlanAsViewed();

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

// Load available voices
function loadVoices() {
    availableVoices = window.speechSynthesis.getVoices();
}

if ('speechSynthesis' in window) {
    loadVoices();
    // Chrome loads voices asynchronously
    if (speechSynthesis.onvoiceschanged !== undefined) {
        speechSynthesis.onvoiceschanged = loadVoices;
    }
}

async function reciteStudyPlan() {
    const btn = document.getElementById("reciteBtn");
    const stopBtn = document.getElementById("stopReciteBtn");
    const contentDiv = document.getElementById("plan-content");

    if (isReciting) {
        stopRecitation();
        return;
    }

    isReciting = true;
    isPaused = false;
    prepareContentForSentences(contentDiv);
    speakContent(contentDiv);
    showControlBar();
    
    btn.classList.remove("btn-primary");
    btn.classList.add("btn-danger");
    btn.innerHTML = '<i class="fas fa-stop"></i> Stop Recitation';
    stopBtn.style.display = 'inline-block';
}

function stopRecitation() {
    synthesis.cancel();
    isReciting = false;
    isPaused = false;
    clearHighlights();
    restoreOriginalContent();
    hideControlBar();
    stopTimer();
    
    const btn = document.getElementById("reciteBtn");
    const stopBtn = document.getElementById("stopReciteBtn");
    btn.classList.remove("btn-danger");
    btn.classList.add("btn-primary");
    btn.innerHTML = '<i class="fas fa-volume-up"></i> AI Recite Study Plan';
    stopBtn.style.display = 'none';
}

function restoreOriginalContent() {
    const contentDiv = document.getElementById("plan-content");
    if (contentDiv && contentDiv.dataset.originalHtml) {
        contentDiv.innerHTML = contentDiv.dataset.originalHtml;
        contentDiv.dataset.originalHtml = "";
    }
}

function speakContent(contentDiv) {
    if (!synthesis) return;
    synthesis.cancel();
    clearHighlights();

    sentences = contentDiv.querySelectorAll(".sentence-span");
    totalSentences = sentences.length;
    currentSentenceIndex = 0;

    startTimer();
    speakNextSentence();
}

function speakNextSentence() {
    if (!isReciting) return;
    
    if (isPaused) {
        setTimeout(speakNextSentence, 100);
        return;
    }

    if (currentSentenceIndex >= totalSentences) {
        stopRecitation();
        return;
    }

    const sentenceSpan = sentences[currentSentenceIndex];
    const sentence = sentenceSpan.textContent;

    // Highlight current sentence
    clearHighlights();
    sentenceSpan.classList.add("highlight-sentence");
    sentenceSpan.scrollIntoView({ behavior: "smooth", block: "center" });
    
    // Update progress
    updateProgress(currentSentenceIndex);

    // Preprocess the sentence for math symbols
    const processedSentence = preprocessMathForSpeech(sentence);
    const utterance = new SpeechSynthesisUtterance(processedSentence);
    utterance.lang = "en-US";
    utterance.rate = 0.95;
    utterance.pitch = 1;
    utterance.volume = 1;

    // Select best available voice
    const preferredVoice = availableVoices.find(v =>
        v.name.includes('Google') || v.name.includes('Microsoft') || v.name.includes('Natural')
    ) || availableVoices.find(v => v.lang.startsWith('en'));

    if (preferredVoice) {
        utterance.voice = preferredVoice;
    }

    utterance.onend = function() {
        currentSentenceIndex++;
        setTimeout(function() {
            speakNextSentence();
        }, 100);
    };

    utterance.onerror = function() {
        stopRecitation();
    };

    synthesis.speak(utterance);
}

function prepareContentForSentences(contentDiv) {
    if (!contentDiv.dataset.originalHtml) {
        contentDiv.dataset.originalHtml = contentDiv.innerHTML;
    }
    const text = contentDiv.textContent;

    // Split by sentences and wrap each in a span
    const sentences = text.split(/([.!?]\s+)/);
    let html = "";

    for (let i = 0; i < sentences.length; i++) {
        const sentence = sentences[i];
        if (sentence.trim()) {
            html += "<span class=\"sentence-span\">" + escapeHtml(sentence) + "</span>";
        } else if (sentence) {
            html += sentence;
        }
    }

    contentDiv.innerHTML = html;
}

function clearHighlights() {
    const highlights = document.querySelectorAll(".highlight-word, .highlight-sentence");
    highlights.forEach(function(span) { span.classList.remove("highlight-word", "highlight-sentence"); });
}

function escapeHtml(text) {
    const div = document.createElement("div");
    div.textContent = text;
    return div.innerHTML;
}

window.addEventListener("beforeunload", function() {
    if (synthesis) {
        synthesis.cancel();
    }
    stopTimer();
    hideControlBar();
});

// Progress bar click to seek
document.addEventListener("DOMContentLoaded", function() {
    const progressBar = document.getElementById("recitation-progress-bar");
    if (progressBar) {
        progressBar.addEventListener("click", function(e) {
            if (!isReciting || totalSentences === 0) return;
            
            const rect = progressBar.getBoundingClientRect();
            const clickX = e.clientX - rect.left;
            const percentage = clickX / rect.width;
            const targetIndex = Math.floor(percentage * totalSentences);
            
            if (targetIndex !== currentSentenceIndex) {
                synthesis.cancel();
                clearHighlights();
                currentSentenceIndex = targetIndex;
                speakNextSentence();
            }
        });
    }
});

async function markStudyPlanComplete() {
    if (!confirm('Mark this study plan as complete?')) {
        return;
    }

    console.log('Marking study plan <?php echo $studyPlan['id']; ?> as complete...');

    try {
        const response = await fetch('/study-plan/complete/<?php echo $studyPlan['id']; ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            credentials: 'include',
        });

        console.log('Complete API response status:', response.status);

        const data = await response.json();
        console.log('Complete API response data:', data);

        if (data.success) {
            console.log('Study plan marked as complete successfully');
            
            // Show success message
            alert('Study plan marked as complete!');
            
            // Update notification badge by fetching fresh count from server
            console.log('Updating notification badge...');
            await updateNotificationBadge();
            console.log('Notification badge update complete');
            
            // Redirect to study plan page (not reload) to show updated count
            console.log('Redirecting to /study-plan');
            window.location.href = '/study-plan';
        } else {
            console.error('API returned error:', data.error);
            alert('Failed to mark study plan as complete. Please try again.');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    }
}
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
