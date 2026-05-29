<?php
$pageTitle = 'View Memorandum - StudySmart';
$currentPage = 'scripts';
$scriptId = $script['id'];
$scriptTitle = addslashes($script['title']);
$scriptSubject = strtolower($script['subject'] ?? '');
// Show calculator for math/science subjects (flexible matching)
$showCalculator = (
    strpos($scriptSubject, 'math') !== false ||
    strpos($scriptSubject, 'phys') !== false ||
    strpos($scriptSubject, 'science') !== false ||
    $scriptSubject === 'mathematics' ||
    $scriptSubject === 'physical sciences' ||
    $scriptSubject === 'maths' ||
    $scriptSubject === 'physics'
);

// DEBUG: Show calculator for ALL subjects temporarily - REMOVE THIS LINE after testing
$showCalculator = true;

$extraHead = '
<!-- KaTeX for Math Rendering -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.css">
<script src="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/contrib/auto-render.min.js"></script>

<style>
.math-container {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 15px;
    margin: 10px 0;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    display: block;
    overflow-x: auto;
}
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
    
    .memo-header {
        flex-direction: column;
        gap: 15px;
    }
    
    .memo-header h3 {
        font-size: 18px;
        text-align: center;
    }
    
    .memo-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        justify-content: center;
        width: 100%;
    }
    
    .memo-actions button {
        flex: 1;
        min-width: 120px;
        padding: 10px 15px;
        font-size: 13px;
    }
    
    .feature-card {
        padding: 15px;
        margin: 10px;
    }
    
    .title {
        font-size: 24px;
    }
    
    .subtitle {
        font-size: 14px;
        padding: 0 15px;
    }
    
    .btn-back {
        margin: 10px 15px;
        padding: 8px 16px;
        font-size: 14px;
    }
    
    #memo-content {
        padding: 15px !important;
        font-size: 14px;
        line-height: 1.6;
    }
    
    #quiz-section {
        padding: 20px !important;
    }
    
    #question-container {
        padding: 15px !important;
    }
    
    #current-question {
        font-size: 16px !important;
    }
    
    #mic-btn {
        padding: 12px 30px !important;
        font-size: 16px !important;
    }
    
    .recitation-control-bar {
        min-width: 280px;
        padding: 10px 15px;
        bottom: 10px;
    }
    
    .recitation-btn {
        width: 32px;
        height: 32px;
    }
}

@media (max-width: 480px) {
    .memo-actions {
        flex-direction: column;
    }
    
    .memo-actions button {
        width: 100%;
        min-width: 100%;
    }
    
    .title {
        font-size: 20px;
    }
    
    .subtitle {
        font-size: 13px;
    }
    
    .feature-card {
        padding: 12px;
        margin: 8px;
    }
    
    #memo-content {
        padding: 12px !important;
        font-size: 13px;
    }
    
    #quiz-section {
        padding: 15px !important;
    }
    
    .recitation-control-bar {
        min-width: 260px;
        max-width: 95%;
        padding: 8px 12px;
        gap: 8px;
    }
    
    .recitation-btn {
        width: 28px;
        height: 28px;
        font-size: 12px;
    }
    
    .recitation-sentence-count {
        font-size: 10px;
    }
    
    .recitation-time {
        font-size: 10px;
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

.section-title {
    display: flex;
    align-items: center;
    gap: 12px;
    margin: 30px 0 15px 0;
    color: #1e293b;
    font-weight: 700;
    font-size: 1.1rem;
}

.section-title i {
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    font-size: 18px;
}

.key-topics-icon { background: #e0f2fe; color: #0ea5e9; }
.challenging-topics-icon { background: #fee2e2; color: #ef4444; }
.memo-content-icon { background: #f0fdf4; color: #22c55e; }

.topics-list {
    list-style: none;
    padding: 0;
    margin-bottom: 25px;
}

.topics-list li {
    padding: 12px 16px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    margin-bottom: 10px;
    display: flex;
    align-items: flex-start;
    gap: 12px;
    transition: transform 0.2s, box-shadow 0.2s;
}

.topics-list li:hover {
    transform: translateX(5px);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.topics-list li::before {
    content: "\f00c";
    font-family: "Font Awesome 6 Free";
    font-weight: 900;
    color: #10b981;
    font-size: 14px;
    margin-top: 3px;
}

.challenging-topics-list li::before {
    content: "\f0e7";
    color: #f59e0b;
}

/* Meta Section Styling */
.memo-meta {
    font-family: \'Plus Jakarta Sans\', \'Inter\', system-ui, -apple-system, sans-serif;
    background: #f8fafc;
    padding: 12px 20px;
    border-radius: 10px;
    margin-bottom: 25px;
    border-left: 4px solid #7c3aed;
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    font-size: 0.95rem;
    color: #475569;
    align-items: center;
}

.memo-meta span {
    display: flex;
    align-items: center;
    gap: 6px;
}

.memo-meta strong {
    color: #7c3aed;
    font-weight: 800;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.5px;
}

/* Accordion Styling */
.topic-accordion {
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    margin-bottom: 15px;
    overflow: hidden;
    background: #fff;
    transition: all 0.3s ease;
}

.topic-accordion[open] {
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    border-color: #cbd5e1;
}

.topic-accordion summary {
    padding: 12px 20px;
    cursor: pointer;
    list-style: none;
    display: flex;
    align-items: center;
    justify-content: space-between;
    outline: none;
    background: #ffffff;
    user-select: none;
}

.topic-accordion summary::-webkit-details-marker {
    display: none;
}

.summary-content {
    display: flex;
    align-items: center;
    gap: 12px;
    color: #1e293b;
    font-weight: 700;
    font-size: 1rem;
}

.summary-content i {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    font-size: 16px;
}

.accordion-chevron {
    transition: transform 0.3s ease;
    color: #94a3b8;
    font-size: 14px;
}

.topic-accordion[open] .accordion-chevron {
    transform: rotate(180deg);
}

.accordion-body {
    padding: 5px 20px 15px 64px;
}

.accordion-body .topics-list {
    margin-bottom: 0;
}
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
#feedback-text .katex {
    font-size: 1.05em;
}
     @media screen and (max-width: 770px) {

    * {
        max-width: 100% !important;
        box-sizing: border-box !important;
    }
} 
</style>
<script>
let synthesis = window.speechSynthesis;
let isSpeaking = false;
let isPaused = false;
let currentUtterance = null;
let currentSentenceIndex = 0;
let totalSentences = 0;
let startTime = 0;
let elapsedTime = 0;
let timerInterval = null;
let sentences = [];
const SCRIPT_ID = ' . $scriptId . ';
const SCRIPT_TITLE = "' . $scriptTitle . '";

function toggleSpeech() {
    const btn = document.getElementById("speech-btn");
    const icon = document.getElementById("speech-icon");

    if (isSpeaking) {
        stopRecitation();
    } else {
        const contentDiv = document.getElementById("memo-content");
        const content = contentDiv.textContent;
        isSpeaking = true;
        isPaused = false;
        prepareContentForSentences(contentDiv);
        speakContent(contentDiv);
        showControlBar();
        btn.classList.remove("btn-primary");
        btn.classList.add("btn-danger");
        icon.classList.remove("fa-volume-high");
        icon.classList.add("fa-stop");
        btn.innerHTML = "<i class=\"fas fa-stop\" id=\"speech-icon\"></i> Stop Recitation";
    }
}

function stopRecitation() {
    synthesis.cancel();
    isSpeaking = false;
    isPaused = false;
    clearHighlights();
    restoreOriginalContent();
    hideControlBar();
    stopTimer();
    
    const btn = document.getElementById("speech-btn");
    const icon = document.getElementById("speech-icon");
    if (btn) {
        btn.classList.remove("btn-danger");
        btn.classList.add("btn-primary");
        icon.classList.remove("fa-stop");
        icon.classList.add("fa-volume-high");
        btn.innerHTML = "<i class=\"fas fa-volume-high\" id=\"speech-icon\"></i> Recite Memorandum";
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
    const totalTime = document.getElementById("total-time");
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

function restoreOriginalContent() {
    const contentDiv = document.getElementById("memo-content");
    if (contentDiv && contentDiv.dataset.originalHtml) {
        contentDiv.innerHTML = contentDiv.dataset.originalHtml;
        // Re-render math after restoring
        renderMathInContent(contentDiv);
    }
}

function renderMathInContent(contentDiv) {
    let content = contentDiv.innerHTML;
    content = content.replace(/\[math\]([\s\S]*?)\[\/math\]|\\\\\(([\s\S]*?)\\\\\)|\\\\\[([\s\S]*?)\\\\\]/g, function(match, p1, p2, p3) {
        const tex = p1 || p2 || p3;
        const isDisplay = match.startsWith("[math]") || match.startsWith("\\\\[");
        return `<span class="math-render-wrapper ${isDisplay ? "math-container" : ""}" data-tex="${escapeHtml(tex)}" data-display="${isDisplay}"></span>`;
    });
    contentDiv.innerHTML = content;
    contentDiv.querySelectorAll(".math-render-wrapper").forEach(el => {
        katex.render(el.dataset.tex, el, { 
            throwOnError: false, 
            displayMode: el.dataset.display === "true" || el.dataset.tex.length > 50 
        });
    });
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
    if (!isSpeaking) return;

    if (isPaused) {
        setTimeout(speakNextSentence, 100);
        return;
    }

    if (currentSentenceIndex >= totalSentences) {
        stopRecitation();
        return;
    }

    const sentenceSpan = sentences[currentSentenceIndex];
    
    // For speech, we want the text content but we need to handle the math specifically
    // We clone the span and remove the math rendered elements, keeping only the sr-only ones
    const clone = sentenceSpan.cloneNode(true);
    clone.querySelectorAll(".math-render-wrapper").forEach(el => el.remove());
    let sentenceText = clone.textContent;

    // Clean up any extra whitespace
    sentenceText = sentenceText.trim();

    // Highlight current sentence
    clearHighlights();
    sentenceSpan.classList.add("highlight-sentence");
    sentenceSpan.scrollIntoView({ behavior: "smooth", block: "center" });

    // Update progress
    updateProgress(currentSentenceIndex);

    const utterance = new SpeechSynthesisUtterance(sentenceText);
    utterance.lang = "en-US";
    utterance.rate = 0.95;
    utterance.pitch = 1;
    utterance.volume = 1;

    utterance.onend = function() {
        currentSentenceIndex++;
        // Add a small pause between sentences (200ms for natural break)
        setTimeout(function() {
            speakNextSentence();
        }, 200);
    };

    utterance.onerror = function() {
        stopRecitation();
    };

    synthesis.speak(utterance);
}

function prepareContentForSentences(contentDiv) {
    // ALWAYS use the original pristine HTML if we have it
    // This prevents trying to process already-rendered math spans
    let content = contentDiv.dataset.originalHtml || contentDiv.innerHTML;
    
    // If we are setting originalHtml for the first time, do it now
    if (!contentDiv.dataset.originalHtml) {
        contentDiv.dataset.originalHtml = content;
    }
    
    // First, we need to extract the raw text but preserve our [math] tags
    // so we can render them later but still have clean text for speech
    
    // Convert [math]...[/math], \(...\), and \[...\] to a special format that KaTeX can render but speech can skip/handle
    // We use a temporary placeholder to avoid splitting sentences inside math tags
    const mathBlocks = [];
    content = content.replace(/\[math\]([\s\S]*?)\[\/math\]|\\\\\(([\s\S]*?)\\\\\)|\\\\\[([\s\S]*?)\\\\\]/g, function(match, p1, p2, p3) {
        const tex = p1 || p2 || p3;
        const id = "MATH_BLOCK_" + mathBlocks.length;
        const isDisplay = match.startsWith("[math]") || match.startsWith("\\\\[");
        mathBlocks.push({ id: id, tex: tex, original: match, isDisplay: isDisplay });
        return id;
    });

    // Clean up HTML tags for sentence splitting
    const tempDiv = document.createElement("div");
    tempDiv.innerHTML = content;
    const text = tempDiv.textContent;

    // Split by sentences
    const sentenceRegex = /[^.!?]*[.!?]+[\s]*/g;
    const rawSentences = text.match(sentenceRegex) || [text];
    let html = "";

    for (let i = 0; i < rawSentences.length; i++) {
        let sentence = rawSentences[i];
        if (sentence.trim()) {
            // Restore math blocks for this sentence
            mathBlocks.forEach(block => {
                // Replace placeholder with a span that KaTeX will target
                // and a hidden span for speech synthesis to read naturally
                const speechFriendlyMath = block.tex
                    .replace(/\\\\frac\{([^}]*)\}\{([^}]*)\}/g, "$1 over $2")
                    .replace(/\\\\sqrt\{([^}]*)\}/g, "square root of $1")
                    .replace(/\\\\tan\^\{-1\}/g, "inverse tangent")
                    .replace(/\\\\sin\^\{-1\}/g, "inverse sine")
                    .replace(/\\\\cos\^\{-1\}/g, "inverse cosine")
                    .replace(/\\\\theta/g, "theta")
                    .replace(/\\\\approx/g, "is approximately")
                    .replace(/\\\\circ/g, " degrees")
                    .replace(/\\\\pm/g, "plus or minus")
                    .replace(/\\\\times/g, "times")
                    .replace(/\^/g, " to the power of ")
                    .replace(/_/g, " sub ");
                
                const mathHtml = `<span class="math-render-wrapper ${block.isDisplay ? "math-container" : ""}" data-tex="${escapeHtml(block.tex)}" data-display="${block.isDisplay}"></span><span class="sr-only" style="display:none">${escapeHtml(speechFriendlyMath)}</span>`;
                sentence = sentence.replace(block.id, mathHtml);
            });
            
            html += "<span class=\"sentence-span\">" + sentence + "</span>";
        }
    }

    contentDiv.innerHTML = html;
    
    // Render the math blocks using KaTeX
    contentDiv.querySelectorAll(".math-render-wrapper").forEach(el => {
        try {
            katex.render(el.dataset.tex, el, {
                throwOnError: false,
                displayMode: el.dataset.display === "true" || el.dataset.tex.length > 50
            });
        } catch (e) {
            console.error("KaTeX error:", e);
            el.textContent = el.dataset.tex;
        }
    });
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
            if (!isSpeaking || totalSentences === 0) return;
            
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
    
    // Initial render for everything (Math + Sentences)
    const memoContent = document.getElementById("memo-content");
    if (memoContent) {
        prepareContentForSentences(memoContent);
    }
});
</script>';

$topics = json_decode($script['processed_topics'], true) ?? [];
$extraHead .= '<script>window.QUIZ_TOPICS = ' . json_encode($topics) . ';</script>';
$extraHead .= '<script src="/js/quiz.js"></script>';

include __DIR__ . '/../layouts/header.php';
?>

<h1 class="title">Memorandum</h1>
<p class="subtitle">AI-generated summary for: <?php echo htmlspecialchars($script['title']); ?></p>

<a href="/dashboard" class="btn-back">
    <i class="fas fa-arrow-left"></i> Back to Dashboard
</a>

<div class="feature-card" style="max-width: 800px;">
    <div class="memo-header">
        <h3>
            <i class="fas fa-file-alt"></i> <?php echo htmlspecialchars($script['title']); ?>
        </h3>
        <div class="memo-actions">
            <button id="speech-btn" class="btn-primary" onclick="toggleSpeech()">
                <i class="fas fa-volume-high" id="speech-icon"></i> Recite Memorandum
            </button>
            <?php if ($showCalculator): ?>
            <button onclick="toggleCalculator()" class="btn-secondary" style="background: #1e5799; color: white; border: none; cursor: pointer;" data-testid="calculator-button">
                <i class="fas fa-calculator"></i> Calculator
            </button>
            <?php endif; ?>
        </div>
    </div>

    <div class="memo-meta">
        <span><i class="fas fa-book-open" style="color: #a78bfa; font-size: 14px;"></i> <strong>Subject:</strong> <?php echo htmlspecialchars($script['subject'] ?? 'N/A'); ?></span>
        <span><i class="fas fa-graduation-cap" style="color: #a78bfa; font-size: 14px;"></i> <strong>Grade Level:</strong> <?php echo htmlspecialchars($script['grade_level'] ?? 'N/A'); ?></span>
        <span><i class="fas fa-calendar-day" style="color: #a78bfa; font-size: 14px;"></i> <strong>Uploaded:</strong> <?php echo date('M d, Y', strtotime($script['uploaded_at'])); ?></span>
    </div>

    <!-- Key Topics Accordion -->
    <details class="topic-accordion">
        <summary>
            <div class="summary-content">
                <i class="fas fa-star key-topics-icon"></i>
                <span>Key Topics</span>
            </div>
            <i class="fas fa-chevron-down accordion-chevron"></i>
        </summary>
        <div class="accordion-body">
            <?php
            $topics = json_decode($script['processed_topics'], true) ?? [];
            if (!empty($topics)):
            ?>
                <ul class="topics-list">
                    <?php foreach ($topics as $topic): ?>
                        <li><?php echo htmlspecialchars($topic); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p style="color: #6b7280;">No topics extracted yet.</p>
            <?php endif; ?>
        </div>
    </details>

    <!-- Challenging Topics Accordion -->
    <details class="topic-accordion">
        <summary>
            <div class="summary-content">
                <i class="fas fa-bolt-lightning challenging-topics-icon"></i>
                <span>Challenging Topics</span>
            </div>
            <i class="fas fa-chevron-down accordion-chevron"></i>
        </summary>
        <div class="accordion-body">
            <?php
            $challengingTopics = json_decode($script['challenging_topics'], true) ?? [];
            if (!empty($challengingTopics)):
            ?>
                <ul class="topics-list challenging-topics-list">
                    <?php foreach ($challengingTopics as $topic): ?>
                        <li><?php echo htmlspecialchars($topic); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p style="color: #6b7280;">No challenging topics identified.</p>
            <?php endif; ?>
        </div>
    </details>

    <div class="section-title">
        <i class="fas fa-file-signature memo-content-icon"></i>
        <span>Memorandum Content</span>
    </div>
    <div id="memo-content" style="background: #ffffff; padding: 25px; border-radius: 12px; border: 1px solid #e2e8f0; white-space: pre-wrap; line-height: 1.8; color: #334155; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <?php echo htmlspecialchars($memorandum['content'] ?? 'No memorandum available.'); ?>
    </div>

    <!-- Take Quiz Button -->
    <div style="margin-top: 30px; text-align: center;">
        <button id="take-quiz-btn" class="btn-primary" onclick="toggleQuiz()" style="padding: 14px 32px; font-size: 16px;">
            <i class="fas fa-question-circle"></i> Take Quiz
        </button>
    </div>

    <!-- Quiz Section -->
    <div id="quiz-section" style="display: none; margin-top: 30px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; border-radius: 12px; color: white;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="margin: 0; color: white;"><i class="fas fa-brain"></i> Voice Quiz - Test Your Knowledge</h3>
            <button onclick="toggleQuiz()" style="background: rgba(255,255,255,0.2); border: none; color: white; padding: 8px 12px; border-radius: 6px; cursor: pointer;">
                <i class="fas fa-times"></i> Close
            </button>
        </div>

        <!-- Quiz Progress -->
        <div id="quiz-progress" style="background: rgba(255,255,255,0.15); padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                <span id="quiz-progress-text">Question 1 of 5</span>
                <span id="quiz-score">Score: 0/0</span>
            </div>
            <div style="background: rgba(255,255,255,0.2); height: 8px; border-radius: 4px; overflow: hidden;">
                <div id="quiz-progress-bar" style="background: white; height: 100%; width: 0%; transition: width 0.3s ease;"></div>
            </div>
        </div>

        <!-- Quiz Options -->
        <div style="text-align: right; margin-bottom: 15px;">
            <button id="input-mode-toggle" onclick="toggleInputMode()" style="background: rgba(255,255,255,0.2); border: 1px solid white; color: white; padding: 6px 12px; border-radius: 20px; cursor: pointer; font-size: 12px;">
                Switch to Type Answer
            </button>
        </div>

        <!-- Question Display -->
        <div id="question-container" style="background: white; color: #1e293b; padding: 25px; border-radius: 10px; margin-bottom: 20px; min-height: 120px;">
            <h4 style="margin-top: 0; color: #667eea;"><i class="fas fa-question"></i> Question:</h4>
            <p id="current-question" style="font-size: 18px; line-height: 1.6; margin-bottom: 0;"></p>
        </div>

        <!-- Answer UI Area -->
        <div id="voice-answer-ui">
            <!-- Voice Answer Controls -->
            <div id="voice-controls" style="text-align: center; margin-bottom: 20px;">
                <button id="mic-btn" onclick="toggleVoiceInput()" style="background: white; color: #667eea; border: none; padding: 15px 40px; border-radius: 50px; font-size: 18px; font-weight: bold; cursor: pointer; box-shadow: 0 4px 15px rgba(0,0,0,0.2); transition: all 0.3s ease;">
                    <i class="fas fa-microphone"></i> Click to Answer (Voice)
                </button>
                <p id="voice-status" style="margin-top: 10px; font-size: 14px; opacity: 0.9;"></p>
            </div>

            <!-- Answer Display -->
            <div id="answer-display" style="background: rgba(255,255,255,0.15); padding: 15px; border-radius: 8px; margin-bottom: 15px; min-height: 50px;">
                <strong>Your Answer:</strong>
                <p id="user-answer" style="margin: 5px 0 0 0; font-size: 16px;"></p>
            </div>
        </div>

        <!-- Text Answer UI -->
        <div id="text-answer-ui" style="display: none; margin-bottom: 20px;">
            <textarea id="user-answer-text" placeholder="Type your answer here..." style="width: 100%; height: 100px; padding: 15px; border-radius: 10px; border: none; font-family: inherit; font-size: 16px; margin-bottom: 10px;"></textarea>
            <div style="text-align: center;">
                <button onclick="submitTextAnswer()" style="background: white; color: #667eea; border: none; padding: 12px 30px; border-radius: 8px; font-size: 16px; font-weight: bold; cursor: pointer;">
                    Submit Answer
                </button>
            </div>
        </div>

        <!-- Feedback Display -->
        <div id="feedback-display" style="display: none; padding: 20px; border-radius: 8px; margin-bottom: 15px;">
            <h4 id="feedback-title" style="margin-top: 0;"></h4>
            <p id="feedback-text" style="margin-bottom: 0;"></p>
        </div>

        <!-- Next Question Button -->
        <div id="next-btn-container" style="display: none; text-align: center;">
            <button onclick="nextQuestion()" style="background: white; color: #667eea; border: none; padding: 12px 30px; border-radius: 8px; font-size: 16px; font-weight: bold; cursor: pointer;">
                Next Question <i class="fas fa-arrow-right"></i>
            </button>
        </div>

        <!-- Quiz Results -->
        <div id="quiz-results" style="display: none; background: white; color: #1e293b; padding: 30px; border-radius: 10px; text-align: center;">
            <h3 style="color: #667eea;"><i class="fas fa-trophy"></i> Quiz Complete!</h3>
            <div style="font-size: 48px; font-weight: bold; color: #667eea; margin: 20px 0;">
                <span id="final-score">0</span>/<span id="total-questions">0</span>
            </div>
            <p id="score-percentage" style="font-size: 24px; margin-bottom: 20px;"></p>
            <p id="score-message" style="font-size: 18px; margin-bottom: 20px;"></p>
            <button onclick="restartQuiz()" style="background: #667eea; color: white; border: none; padding: 12px 30px; border-radius: 8px; font-size: 16px; cursor: pointer;">
                <i class="fas fa-redo"></i> Retake Quiz
            </button>
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

<?php include __DIR__ . '/../layouts/footer.php'; ?>
