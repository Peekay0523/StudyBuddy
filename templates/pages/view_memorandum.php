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

async function regenerateMemorandum() {
    const btn = document.getElementById("regenerate-btn");
    const memoContent = document.getElementById("memo-content");
    
    if (!confirm("Are you sure you want to regenerate this memorandum? The current content will be replaced with a new version.")) {
        return;
    }
    
    const originalBtnHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = "<i class=\"fas fa-spinner fa-spin\"></i> Regenerating...";
    
    // Add loading overlay to content
    memoContent.style.opacity = "0.5";
    memoContent.style.pointerEvents = "none";
    
    try {
        const response = await fetch("/generate-memorandum", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded",
            },
            body: new URLSearchParams({
                "script_id": SCRIPT_ID
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            memoContent.textContent = result.memorandum;
            alert("Memorandum successfully regenerated with improved accuracy!");
            // Refresh the page to update other sections if needed
            window.location.reload();
        } else {
            alert("Error: " + (result.error || "Failed to regenerate memorandum"));
        }
    } catch (error) {
        console.error("Error:", error);
        alert("An error occurred. Please try again.");
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalBtnHtml;
        memoContent.style.opacity = "1";
        memoContent.style.pointerEvents = "auto";
    }
}

function downloadMemorandum(format) {
    const scriptId = SCRIPT_ID;
    const scriptTitle = SCRIPT_TITLE;
    const modal = document.getElementById("download-format-modal");
    modal.style.display = "flex";
    document.getElementById("download-pdf-btn").onclick = function() {
        window.open("/download-memorandum/" + scriptId + "?format=pdf", "_blank");
        modal.style.display = "none";
    };
    document.getElementById("download-docx-btn").onclick = function() {
        window.open("/download-memorandum/" + scriptId + "?format=docx", "_blank");
        modal.style.display = "none";
    };
}

function closeDownloadModal() {
    document.getElementById("download-format-modal").style.display = "none";
}

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
    
    const modal = document.getElementById("download-format-modal");
    if (modal) {
        modal.addEventListener("click", function(e) {
            if (e.target === modal) {
                closeDownloadModal();
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
            <button onclick="downloadMemorandum()" class="btn-secondary">
                <i class="fas fa-download"></i> Download
            </button>
            <button id="regenerate-btn" class="btn-secondary" onclick="regenerateMemorandum()" title="Fix inconsistencies or errors">
                <i class="fas fa-sync-alt"></i> Regenerate
            </button>
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

    <div style="margin-bottom: 20px;">
        <strong>Subject:</strong> <?php echo htmlspecialchars($script['subject'] ?? 'N/A'); ?> |
        <strong>Grade Level:</strong> <?php echo htmlspecialchars($script['grade_level'] ?? 'N/A'); ?> |
        <strong>Uploaded:</strong> <?php echo date('M d, Y', strtotime($script['uploaded_at'])); ?>
    </div>

    <h4 style="margin: 20px 0 10px 0;"><i class="fas fa-list"></i> Key Topics</h4>
    <?php
    $topics = json_decode($script['processed_topics'], true) ?? [];
    if (!empty($topics)):
    ?>
        <ul style="margin-bottom: 20px;">
            <?php foreach ($topics as $topic): ?>
                <li><?php echo htmlspecialchars($topic); ?></li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p style="color: #6b7280;">No topics extracted yet.</p>
    <?php endif; ?>

    <h4 style="margin: 20px 0 10px 0;"><i class="fas fa-exclamation-triangle"></i> Challenging Topics</h4>
    <?php
    $challengingTopics = json_decode($script['challenging_topics'], true) ?? [];
    if (!empty($challengingTopics)):
    ?>
        <ul style="margin-bottom: 20px;">
            <?php foreach ($challengingTopics as $topic): ?>
                <li><?php echo htmlspecialchars($topic); ?></li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p style="color: #6b7280;">No challenging topics identified.</p>
    <?php endif; ?>

    <h4 style="margin: 20px 0 10px 0;"><i class="fas fa-book"></i> Memorandum Content</h4>
    <div id="memo-content" style="background: #f9fafb; padding: 20px; border-radius: 8px; white-space: pre-wrap;">
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

        <!-- Question Display -->
        <div id="question-container" style="background: white; color: #1e293b; padding: 25px; border-radius: 10px; margin-bottom: 20px; min-height: 120px;">
            <h4 style="margin-top: 0; color: #667eea;"><i class="fas fa-question"></i> Question:</h4>
            <p id="current-question" style="font-size: 18px; line-height: 1.6; margin-bottom: 0;"></p>
        </div>

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

<!-- Download Format Selection Modal -->
<div id="download-format-modal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3>
                <i class="fas fa-download" style="color: #667eea;"></i> Download Memorandum
            </h3>
            <button onclick="closeDownloadModal()" class="modal-close">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <p style="color: #6b7280; margin-bottom: 20px;">Choose your preferred format:</p>
            <div class="modal-format-grid">
                <button id="download-pdf-btn" class="btn-download-format btn-download-pdf">
                    <i class="fas fa-file-pdf"></i> PDF Format
                </button>
                <button id="download-docx-btn" class="btn-download-format btn-download-docx">
                    <i class="fas fa-file-word"></i> Word Format
                </button>
            </div>
            <p style="color: #9ca3af; font-size: 12px; margin-top: 15px;">
                <i class="fas fa-info-circle"></i> PDF downloads as HTML (open in browser, then print to PDF)
            </p>
        </div>
        <div class="modal-footer">
            <button onclick="closeDownloadModal()" class="btn-secondary">
                <i class="fas fa-times"></i> Cancel
            </button>
        </div>
    </div>
</div>

<?php if ($showCalculator): ?>
<!-- Calculator Modal -->
<div id="calculator-modal" class="modal-overlay" style="display: none; z-index: 10000;">
    <div class="calculator-modal-content" style="z-index: 10001; position: relative; max-height: 90vh; overflow-y: auto;">
        <div class="calculator-header">
            <button onclick="toggleCalculator()" class="calc-close-btn">
                <i class="fas fa-times"></i>
            </button>
            <div>
                <div class="calc-brand">CASIO</div>
                <div class="calc-model">FX-300ES PLUS</div>
            </div>
            <div class="calc-solar-panel">
                <div class="calc-solar-cell"></div>
                <div class="calc-solar-cell"></div>
                <div class="calc-solar-cell"></div>
                <div class="calc-solar-cell"></div>
            </div>
        </div>

        <!-- Display -->
        <div class="calc-display-frame">
            <div class="calc-display">
                <div class="calc-display-indicators">
                    <div class="calc-ind-left">
                        <span class="calc-indicator" id="calc-ind-shift">S</span>
                        <span class="calc-indicator" id="calc-ind-alpha">A</span>
                        <span class="calc-indicator" id="calc-ind-m">M</span>
                    </div>
                    <div class="calc-ind-right">
                        <span class="calc-indicator active" id="calc-ind-deg">D</span>
                    </div>
                </div>
                <div class="calc-expression" id="calc-expression"></div>
                <div class="calc-result" id="calc-result">0</div>
                <div class="calc-fraction-result" id="calc-fractionResult">
                    <span class="calc-frac-whole" id="calc-fracWhole"></span>
                    <div class="calc-frac">
                        <div class="calc-frac-num" id="calc-fracNum"></div>
                        <div class="calc-frac-den" id="calc-fracDen"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Buttons -->
        <div class="calc-buttons-container">
            <div class="calc-btn-row calc-btn-row-5">
                <button class="calc-btn btn-shift" onclick="calc_shiftKey()">SHIFT</button>
                <button class="calc-btn btn-alpha" onclick="calc_alphaKey()">ALPHA</button>
                <button class="calc-btn btn-nav" onclick="calc_delKey()">◄DEL</button>
                <button class="calc-btn btn-nav" onclick="calc_closeParen()">►)</button>
                <button class="calc-btn btn-mode" onclick="calc_modeKey()">MODE</button>
            </div>

            <div class="calc-btn-row calc-btn-row-6">
                <button class="calc-btn btn-function" onclick="calc_calcKey()"><span class="calc-shift-label">STO</span>CALC</button>
                <button class="calc-btn btn-function" onclick="calc_integralKey()"><span class="calc-shift-label">d/dx</span>∫dx</button>
                <button class="calc-btn btn-function" onclick="calc_derivativeKey()"><span class="calc-shift-label">∫</span>d/dx</button>
                <button class="calc-btn btn-function" onclick="calc_sumKey()"><span class="calc-shift-label">Π</span>Σ</button>
                <button class="calc-btn btn-function" onclick="calc_sqrtKey()"><span class="calc-shift-label">∛</span>√</button>
                <button class="calc-btn btn-function" onclick="calc_powerKey()"><span class="calc-shift-label">ˣ√</span>x^y</button>
            </div>

            <div class="calc-btn-row calc-btn-row-6">
                <button class="calc-btn btn-function" onclick="calc_squareKey()"><span class="calc-shift-label">x!</span>x²</button>
                <button class="calc-btn btn-function" onclick="calc_cubeKey()"><span class="calc-shift-label">Abs</span>x³</button>
                <button class="calc-btn btn-function" onclick="calc_reciprocalKey()"><span class="calc-shift-label">Ran#</span>x⁻¹</button>
                <button class="calc-btn btn-function" onclick="calc_logKey()"><span class="calc-shift-label">10ˣ</span>log</button>
                <button class="calc-btn btn-function" onclick="calc_lnKey()"><span class="calc-shift-label">eˣ</span>ln</button>
                <button class="calc-btn btn-function" onclick="calc_negateKey()"><span class="calc-shift-label">RanInt</span>(-)</button>
            </div>

            <div class="calc-btn-row calc-btn-row-6">
                <button class="calc-btn btn-trig" onclick="calc_hypKey()">hyp</button>
                <button class="calc-btn btn-trig" onclick="calc_sinKey()"><span class="calc-shift-label">sin⁻¹</span>sin</button>
                <button class="calc-btn btn-trig" onclick="calc_cosKey()"><span class="calc-shift-label">cos⁻¹</span>cos</button>
                <button class="calc-btn btn-trig" onclick="calc_tanKey()"><span class="calc-shift-label">tan⁻¹</span>tan</button>
                <button class="calc-btn btn-function" onclick="calc_openParen()"><span class="calc-shift-label">Ins</span>(</button>
                <button class="calc-btn btn-function" onclick="calc_closeParen()"><span class="calc-shift-label">⇦</span>)</button>
            </div>

            <div class="calc-btn-row calc-btn-row-6">
                <button class="calc-btn btn-sd" onclick="calc_sdKey()"><span class="calc-shift-label">STO</span>S⇔D</button>
                <button class="calc-btn btn-function" onclick="calc_mPlusKey()"><span class="calc-shift-label">M-</span>M+</button>
                <button class="calc-btn btn-del" onclick="calc_delKey()"><span class="calc-shift-label">INS</span>DEL</button>
                <button class="calc-btn btn-function" onclick="calc_percentKey()"><span class="calc-shift-label">Ran#</span>%</button>
                <button class="calc-btn btn-function" onclick="calc_expKey()"><span class="calc-shift-label">π</span>EXP</button>
                <button class="calc-btn btn-function" onclick="calc_ansKey()"><span class="calc-shift-label">PreAns</span>Ans</button>
            </div>

            <div class="calc-btn-row calc-btn-row-6">
                <button class="calc-btn btn-fraction" onclick="calc_fractionKey()"><span class="calc-shift-label">d/c</span>a b/c</button>
                <button class="calc-btn btn-function" onclick="calc_nPrKey()">nPr</button>
                <button class="calc-btn btn-function" onclick="calc_nCrKey()">nCr</button>
                <button class="calc-btn btn-operator" onclick="calc_operatorKey('*')">×</button>
                <button class="calc-btn btn-operator" onclick="calc_operatorKey('/')">÷</button>
                <button style="visibility: hidden;"></button>
            </div>

            <div class="calc-btn-row calc-btn-row-6">
                <button class="calc-btn btn-number" onclick="calc_numberKey('7')"><span class="calc-shift-label">A</span>7</button>
                <button class="calc-btn btn-number" onclick="calc_numberKey('8')"><span class="calc-shift-label">B</span>8</button>
                <button class="calc-btn btn-number" onclick="calc_numberKey('9')"><span class="calc-shift-label">C</span>9</button>
                <button class="calc-btn btn-operator" onclick="calc_operatorKey('+')">+</button>
                <button class="calc-btn btn-operator" onclick="calc_operatorKey('-')">−</button>
                <button style="visibility: hidden;"></button>
            </div>

            <div class="calc-btn-row calc-btn-row-6">
                <button class="calc-btn btn-number" onclick="calc_numberKey('4')"><span class="calc-shift-label">D</span>4</button>
                <button class="calc-btn btn-number" onclick="calc_numberKey('5')"><span class="calc-shift-label">E</span>5</button>
                <button class="calc-btn btn-number" onclick="calc_numberKey('6')"><span class="calc-shift-label">F</span>6</button>
                <button style="visibility: hidden;"></button>
                <button style="visibility: hidden;"></button>
                <button style="visibility: hidden;"></button>
            </div>

            <div class="calc-btn-row calc-btn-row-6">
                <button class="calc-btn btn-number" onclick="calc_numberKey('1')"><span class="calc-shift-label">X</span>1</button>
                <button class="calc-btn btn-number" onclick="calc_numberKey('2')"><span class="calc-shift-label">Y</span>2</button>
                <button class="calc-btn btn-number" onclick="calc_numberKey('3')"><span class="calc-shift-label">Z</span>3</button>
                <button class="calc-btn btn-equals" onclick="calc_calculate()"><span class="calc-shift-label">≈</span>=</button>
                <button class="calc-btn btn-ac" onclick="calc_acKey()"><span class="calc-shift-label">CLR</span>AC</button>
                <button style="visibility: hidden;"></button>
            </div>

            <div class="calc-btn-row calc-btn-row-6">
                <button class="calc-btn btn-number" onclick="calc_numberKey('0')"><span class="calc-shift-label">Rnd</span>0</button>
                <button class="calc-btn btn-number" onclick="calc_decimalKey()"><span class="calc-shift-label">Ran#</span>.</button>
                <button style="visibility: hidden;"></button>
                <button style="visibility: hidden;"></button>
                <button style="visibility: hidden;"></button>
                <button style="visibility: hidden;"></button>
            </div>
        </div>
    </div>
</div>

<script>
// Calculator Modal Toggle
function toggleCalculator() {
    const modal = document.getElementById('calculator-modal');
    if (modal) {
        if (modal.style.display === 'none' || modal.style.display === '') {
            modal.style.display = 'flex';
            modal.style.zIndex = '10000';
            // Allow scrolling while modal is open
        } else {
            modal.style.display = 'none';
        }
    }
}

// Close modal when clicking outside the calculator
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('calculator-modal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            // Only close if clicking the overlay background, not the calculator itself
            if (e.target === modal) {
                toggleCalculator();
            }
        });
    }
});

// Calculator State
let calcExpression = '';
let calcDisplayExpr = '';
let calcResultValue = '0';
let calcLastAnswer = 0;
let calcIsShift = false;
let calcIsAlpha = false;
let calcIsHyp = false;
let calcMemory = 0;
let calcHasMemory = false;
let calcIsFractionResult = false;
let calcFractionResult = null;
let calcAngleMode = 'deg';

function calc_updateDisplay() {
    const exprDisplay = document.getElementById('calc-expression');
    const resultDisplay = document.getElementById('calc-result');
    const fractionResultDiv = document.getElementById('calc-fractionResult');
    
    if (exprDisplay) exprDisplay.textContent = calcDisplayExpr || '';
    if (resultDisplay) resultDisplay.textContent = calcResultValue;
    
    document.getElementById('calc-ind-shift').classList.toggle('active', calcIsShift);
    document.getElementById('calc-ind-alpha').classList.toggle('active', calcIsAlpha);
    document.getElementById('calc-ind-m').classList.toggle('active', calcHasMemory);
    
    if (calcFractionResult && calcIsFractionResult) {
        resultDisplay.style.display = 'none';
        fractionResultDiv.classList.add('active');
        document.getElementById('calc-fracWhole').textContent = calcFractionResult.whole > 0 ? calcFractionResult.whole : '';
        document.getElementById('calc-fracNum').textContent = calcFractionResult.num;
        document.getElementById('calc-fracDen').textContent = calcFractionResult.den;
    } else {
        resultDisplay.style.display = 'block';
        fractionResultDiv.classList.remove('active');
    }
}

function calc_formatNumber(num) {
    if (isNaN(num)) return 'Math ERROR';
    if (!isFinite(num)) return 'Math ERROR';
    if (Number.isInteger(num) && Math.abs(num) < 1e15) return num.toString();
    if (Math.abs(num) < 0.000001 || Math.abs(num) >= 1e10) {
        let expStr = num.toExponential(3);
        let parts = expStr.split('e');
        return parseFloat(parts[0]).toString() + '×10^' + parseInt(parts[1]);
    }
    return parseFloat(num.toFixed(8)).toString();
}

function calc_gcd(a, b) {
    a = Math.abs(Math.round(a));
    b = Math.abs(Math.round(b));
    while (b) { [a, b] = [b, a % b]; }
    return a;
}

function calc_decimalToFraction(decimal) {
    if (Number.isInteger(decimal)) return { whole: Math.abs(decimal), num: 0, den: 1 };
    let sign = decimal < 0 ? -1 : 1;
    decimal = Math.abs(decimal);
    let whole = Math.floor(decimal);
    let frac = decimal - whole;
    if (frac < 0.000001) return { whole: whole, num: 0, den: 1 };
    let tolerance = 1.0E-6;
    let x = frac, a = Math.floor(x), h1 = 1, k1 = 0, h = a, k = 1;
    for (let i = 0; i < 20; i++) {
        let r = x - a;
        if (Math.abs(r) < tolerance) break;
        x = 1 / r;
        a = Math.floor(x);
        let h2 = h1, k2 = k1;
        h1 = h; k1 = k;
        h = h2 + a * h1;
        k = k2 + a * k1;
    }
    let g = calc_gcd(h, k);
    return { whole: whole, num: (h / g), den: (k / g) };
}

function calc_numberKey(num) {
    calcIsFractionResult = false;
    calcFractionResult = null;
    calcDisplayExpr += num;
    calcExpression += num;
    calc_updateDisplay();
}

function calc_operatorKey(op) {
    calcIsFractionResult = false;
    calcFractionResult = null;
    let symbol = op === '*' ? '×' : op === '/' ? '÷' : op === '-' ? '−' : op;
    calcDisplayExpr += ' ' + symbol + ' ';
    calcExpression += op;
    calc_updateDisplay();
}

function calc_decimalKey() {
    let parts = calcDisplayExpr.split(/[+\\-×÷\\s]+/);
    let lastPart = parts[parts.length - 1];
    if (!lastPart.includes('.')) {
        calcDisplayExpr += '.';
        calcExpression += '.';
    }
    calc_updateDisplay();
}

function calc_calculate() {
    if (!calcExpression) return;
    try {
        let evalExpr = calcExpression;
        let openCount = (evalExpr.match(/\(/g) || []).length;
        let closeCount = (evalExpr.match(/\)/g) || []).length;
        while (closeCount < openCount) { evalExpr += ')'; closeCount++; }
        evalExpr = evalExpr.replace(/×/g, '*').replace(/÷/g, '/').replace(/−/g, '-');
        evalExpr = evalExpr.replace(/(\d)\(/g, '$1*(').replace(/\)(\d)/g, ')*$1');
        let result = Function('"use strict"; return (' + evalExpr + ')')();
        calcLastAnswer = result;
        calcDisplayExpr += ' =';
        calcResultValue = calc_formatNumber(result);
        let frac = calc_decimalToFraction(result);
        if (frac.den > 1 && frac.den < 10000) {
            calcFractionResult = frac;
            calcIsFractionResult = true;
        } else {
            calcFractionResult = null;
            calcIsFractionResult = false;
        }
        calcIsShift = false;
        calcIsAlpha = false;
        calcExpression = '';
        calc_updateDisplay();
    } catch (e) {
        calcResultValue = 'Math ERROR';
        calcExpression = '';
        calcFractionResult = null;
        calcIsFractionResult = false;
        calc_updateDisplay();
    }
}

function calc_acKey() {
    calcExpression = '';
    calcDisplayExpr = '';
    calcResultValue = '0';
    calcLastAnswer = 0;
    calcIsShift = false;
    calcIsAlpha = false;
    calcIsHyp = false;
    calcIsFractionResult = false;
    calcFractionResult = null;
    calc_updateDisplay();
}

function calc_delKey() {
    if (calcDisplayExpr.length > 0) {
        calcDisplayExpr = calcDisplayExpr.trimEnd();
        if (calcDisplayExpr.endsWith(' ')) calcDisplayExpr = calcDisplayExpr.slice(0, -1);
        else calcDisplayExpr = calcDisplayExpr.slice(0, -1);
        calcExpression = calcExpression.slice(0, -1);
        calc_updateDisplay();
    }
}

function calc_shiftKey() { calcIsShift = !calcIsShift; calcIsAlpha = false; calc_updateDisplay(); }
function calc_alphaKey() { calcIsAlpha = !calcIsAlpha; calcIsShift = false; calc_updateDisplay(); }
function calc_modeKey() {
    if (calcAngleMode === 'deg') { calcAngleMode = 'rad'; document.getElementById('calc-ind-deg').textContent = 'R'; }
    else if (calcAngleMode === 'rad') { calcAngleMode = 'grad'; document.getElementById('calc-ind-deg').textContent = 'G'; }
    else { calcAngleMode = 'deg'; document.getElementById('calc-ind-deg').textContent = 'D'; }
}

function calc_sqrtKey() {
    if (calcIsShift) { calcDisplayExpr = '∛('; calcExpression = 'Math.cbrt('; calcIsShift = false; }
    else { calcDisplayExpr = '√('; calcExpression = 'Math.sqrt('; }
    calc_updateDisplay();
}

function calc_squareKey() { calcDisplayExpr += '²'; calcExpression += '**2'; calc_updateDisplay(); }
function calc_cubeKey() { calcDisplayExpr += '³'; calcExpression += '**3'; calc_updateDisplay(); }
function calc_reciprocalKey() { calcDisplayExpr += '⁻¹'; calcExpression = '1/(' + calcExpression + ')'; calc_updateDisplay(); }

function calc_logKey() {
    if (calcIsShift) { calcDisplayExpr += '10^('; calcExpression += 'Math.pow(10,'; calcIsShift = false; }
    else { calcDisplayExpr += 'log('; calcExpression += 'Math.log10('; }
    calc_updateDisplay();
}

function calc_lnKey() {
    if (calcIsShift) { calcDisplayExpr += 'e^('; calcExpression += 'Math.exp('; calcIsShift = false; }
    else { calcDisplayExpr += 'ln('; calcExpression += 'Math.log('; }
    calc_updateDisplay();
}

function calc_negateKey() { calcExpression += '(-'; calcDisplayExpr += '(-'; calc_updateDisplay(); }
function calc_hypKey() { calcIsHyp = !calcIsHyp; }

function calc_sinKey() {
    if (calcIsShift) {
        calcDisplayExpr += 'sin⁻¹(';
        calcExpression += calcAngleMode === 'deg' ? '180/Math.PI*Math.asin(' : 'Math.asin(';
        calcIsShift = false;
    } else {
        calcDisplayExpr += 'sin(';
        calcExpression += calcIsHyp ? 'Math.sinh(' : (calcAngleMode === 'deg' ? 'Math.sin(Math.PI/180*' : 'Math.sin(');
        calcIsHyp = false;
    }
    calc_updateDisplay();
}

function calc_cosKey() {
    if (calcIsShift) {
        calcDisplayExpr += 'cos⁻¹(';
        calcExpression += calcAngleMode === 'deg' ? '180/Math.PI*Math.acos(' : 'Math.acos(';
        calcIsShift = false;
    } else {
        calcDisplayExpr += 'cos(';
        calcExpression += calcIsHyp ? 'Math.cosh(' : (calcAngleMode === 'deg' ? 'Math.cos(Math.PI/180*' : 'Math.cos(');
        calcIsHyp = false;
    }
    calc_updateDisplay();
}

function calc_tanKey() {
    if (calcIsShift) {
        calcDisplayExpr += 'tan⁻¹(';
        calcExpression += calcAngleMode === 'deg' ? '180/Math.PI*Math.atan(' : 'Math.atan(';
        calcIsShift = false;
    } else {
        calcDisplayExpr += 'tan(';
        calcExpression += calcIsHyp ? 'Math.tanh(' : (calcAngleMode === 'deg' ? 'Math.tan(Math.PI/180*' : 'Math.tan(');
        calcIsHyp = false;
    }
    calc_updateDisplay();
}

function calc_openParen() { calcDisplayExpr += '('; calcExpression += '('; calc_updateDisplay(); }
function calc_closeParen() { calcDisplayExpr += ')'; calcExpression += ')'; calc_updateDisplay(); }
function calc_sdKey() {
    if (calcIsFractionResult && calcFractionResult) {
        let dec = calcFractionResult.whole + (calcFractionResult.num / calcFractionResult.den);
        calcResultValue = calc_formatNumber(dec);
        calcIsFractionResult = false;
    } else {
        let num = parseFloat(calcResultValue);
        if (!isNaN(num)) { calcFractionResult = calc_decimalToFraction(num); calcIsFractionResult = true; }
    }
    calc_updateDisplay();
}

function calc_mPlusKey() { let num = parseFloat(calcResultValue); if (!isNaN(num)) { calcMemory += num; calcHasMemory = true; } }
function calc_fractionKey() { calcDisplayExpr += '/'; calcExpression += '/'; calc_updateDisplay(); }
function calc_nPrKey() { calcDisplayExpr += 'P'; calcExpression += 'P'; calc_updateDisplay(); }
function calc_nCrKey() { calcDisplayExpr += 'C'; calcExpression += 'C'; calc_updateDisplay(); }
function calc_percentKey() { calcDisplayExpr += '%'; calcExpression += '/100'; calc_updateDisplay(); }
function calc_expKey() { calcDisplayExpr += '×10^'; calcExpression += 'e'; calc_updateDisplay(); }
function calc_ansKey() { calcDisplayExpr += 'Ans'; calcExpression += calcLastAnswer.toString(); calc_updateDisplay(); }
function calc_calcKey() {}
function calc_integralKey() { calcDisplayExpr += '∫('; calcExpression += 'integral('; calc_updateDisplay(); }
function calc_derivativeKey() { calcDisplayExpr += 'd/dx('; calcExpression += 'derivative('; calc_updateDisplay(); }
function calc_sumKey() { calcDisplayExpr += 'Σ('; calcExpression += 'sum('; calc_updateDisplay(); }
function calc_powerKey() { calcDisplayExpr += '^('; calcExpression += 'Math.pow('; calc_updateDisplay(); }

// Keyboard support for calculator
document.addEventListener('keydown', function(e) {
    const modal = document.getElementById('calculator-modal');
    if (!modal || modal.style.display === 'none' || modal.style.display === '') return;
    
    if (e.key >= '0' && e.key <= '9') calc_numberKey(e.key);
    else if (e.key === '.') calc_decimalKey();
    else if (e.key === '+') calc_operatorKey('+');
    else if (e.key === '-') calc_operatorKey('-');
    else if (e.key === '*') calc_operatorKey('*');
    else if (e.key === '/') { e.preventDefault(); calc_operatorKey('/'); }
    else if (e.key === 'Enter' || e.key === '=') calc_calculate();
    else if (e.key === 'Escape') { calc_acKey(); toggleCalculator(); }
    else if (e.key === 'Backspace') calc_delKey();
});
</script>

<style>
/* Calculator Modal Styles */
.calculator-modal-content {
    background: linear-gradient(180deg, #1e5799 0%, #2989d8 20%, #1e5799 50%, #154580 100%);
    border-radius: 18px;
    padding: 20px 15px 15px;
    box-shadow: 0 30px 60px rgba(0, 0, 0, 0.7), inset 0 1px 0 rgba(255, 255, 255, 0.3);
    width: 100%;
    max-width: 340px;
    position: relative;
    margin: 20px;
}

.calculator-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
    padding: 0 5px;
    position: relative;
}

.calc-close-btn {
    position: absolute;
    top: 10px;
    right: 10px;
    background: #c05030;
    border: none;
    color: #fff;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    cursor: pointer;
    font-size: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 8px rgba(0,0,0,0.4);
    z-index: 10;
    transition: all 0.2s ease;
}

.calc-close-btn:hover {
    background: #d06040;
    transform: scale(1.15);
    box-shadow: 0 3px 12px rgba(0,0,0,0.5);
}

.calc-brand {
    color: #fff;
    font-size: 16px;
    font-weight: bold;
    letter-spacing: 1.5px;
    text-shadow: 0 1px 2px rgba(0,0,0,0.3);
}

.calc-model {
    color: #ffd700;
    font-size: 9px;
    font-weight: bold;
    letter-spacing: 0.5px;
}

.calc-solar-panel {
    background: #0a0a0a;
    height: 16px;
    width: 65px;
    border-radius: 2px;
    display: flex;
    gap: 1px;
    padding: 2px;
    border: 1px solid #333;
    box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.9);
}

.calc-solar-cell {
    flex: 1;
    background: linear-gradient(180deg, #1a3a5c 0%, #0d1f33 100%);
    border-right: 1px solid #0a0a0a;
}

.calc-solar-cell:last-child {
    border-right: none;
}

.calc-display-frame {
    background: #1a1a2e;
    border-radius: 6px;
    padding: 3px;
    margin-bottom: 12px;
    box-shadow: inset 0 2px 5px rgba(0, 0, 0, 0.6), 0 1px 0 rgba(255, 255, 255, 0.1);
}

.calc-display {
    background: linear-gradient(180deg, #e8f0d8 0%, #d8e8c8 50%, #c8d8b8 100%);
    border-radius: 4px;
    padding: 10px 12px;
    min-height: 75px;
    box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.15);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.calc-display-indicators {
    display: flex;
    justify-content: space-between;
    font-size: 8px;
    color: #6a7a5a;
    margin-bottom: 4px;
    font-weight: bold;
}

.calc-ind-left, .calc-ind-right {
    display: flex;
    gap: 6px;
}

.calc-indicator {
    opacity: 0.2;
    transition: opacity 0.2s;
}

.calc-indicator.active {
    opacity: 1;
    color: #2a3a1a;
}

.calc-expression {
    font-family: 'Courier New', monospace;
    font-size: 13px;
    color: #4a5a3a;
    min-height: 18px;
    text-align: left;
    width: 100%;
    margin-bottom: 6px;
    overflow-x: auto;
    white-space: nowrap;
}

.calc-result {
    font-family: 'Courier New', monospace;
    font-size: 24px;
    font-weight: bold;
    color: #1a2a0a;
    text-align: right;
    width: 100%;
    min-height: 30px;
}

.calc-fraction-result {
    display: none;
    align-items: center;
    justify-content: flex-end;
    margin-top: 4px;
}

.calc-fraction-result.active {
    display: flex;
}

.calc-frac {
    display: inline-flex;
    flex-direction: column;
    align-items: center;
    vertical-align: middle;
}

.calc-frac-num {
    font-family: 'Courier New', monospace;
    font-size: 13px;
    font-weight: bold;
    color: #1a2a0a;
    border-bottom: 2px solid #1a2a0a;
    padding: 0 6px 2px;
    text-align: center;
}

.calc-frac-den {
    font-family: 'Courier New', monospace;
    font-size: 13px;
    font-weight: bold;
    color: #1a2a0a;
    padding: 2px 6px 0;
    text-align: center;
}

.calc-buttons-container {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.calc-btn-row {
    display: grid;
    gap: 5px;
}

.calc-btn-row-5 {
    grid-template-columns: repeat(5, 1fr);
}

.calc-btn-row-6 {
    grid-template-columns: repeat(6, 1fr);
}

.calc-btn {
    padding: 10px 4px;
    font-size: 11px;
    font-weight: bold;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.1s ease;
    font-family: Arial, sans-serif;
    min-height: 32px;
    position: relative;
    box-shadow: 0 2px 0 rgba(0, 0, 0, 0.4), 0 3px 5px rgba(0, 0, 0, 0.3);
}

.calc-btn:active {
    transform: translateY(1px);
    box-shadow: 0 1px 0 rgba(0, 0, 0, 0.4), 0 2px 3px rgba(0, 0, 0, 0.3);
}

.calc-shift-label {
    display: block;
    font-size: 9px;
    color: #ffd700;
    font-weight: bold;
    margin-bottom: 2px;
    line-height: 1;
    text-align: center;
}

.btn-shift { background: linear-gradient(180deg, #8b7a3e 0%, #6b5a2e 100%); color: #fff; font-size: 10px; }
.btn-shift:hover { background: linear-gradient(180deg, #9b8a4e 0%, #7b6a3e 100%); }
.btn-alpha { background: linear-gradient(180deg, #a84a3a 0%, #883a2a 100%); color: #fff; font-size: 10px; }
.btn-alpha:hover { background: linear-gradient(180deg, #b85a4a 0%, #984a3a 100%); }
.btn-mode { background: linear-gradient(180deg, #5a6a7a 0%, #4a5a6a 100%); color: #fff; font-size: 9px; }
.btn-mode:hover { background: linear-gradient(180deg, #6a7a8a 0%, #5a6a7a 100%); }
.btn-nav { background: linear-gradient(180deg, #6a7a8a 0%, #5a6a7a 100%); color: #fff; font-size: 10px; }
.btn-nav:hover { background: linear-gradient(180deg, #7a8a9a 0%, #6a7a8a 100%); }
.btn-function { background: linear-gradient(180deg, #5a6a7a 0%, #4a5a6a 100%); color: #fff; font-size: 10px; }
.btn-function:hover { background: linear-gradient(180deg, #6a7a8a 0%, #5a6a7a 100%); }
.btn-trig { background: linear-gradient(180deg, #4a5a6a 0%, #3a4a5a 100%); color: #fff; font-size: 11px; }
.btn-trig:hover { background: linear-gradient(180deg, #5a6a7a 0%, #4a5a6a 100%); }
.btn-number { background: linear-gradient(180deg, #4a4a5a 0%, #3a3a4a 100%); color: #fff; font-size: 14px; }
.btn-number:hover { background: linear-gradient(180deg, #5a5a6a 0%, #4a4a5a 100%); }
.btn-operator { background: linear-gradient(180deg, #5a6a7a 0%, #4a5a6a 100%); color: #fff; font-size: 14px; }
.btn-operator:hover { background: linear-gradient(180deg, #6a7a8a 0%, #5a6a7a 100%); }
.btn-del { background: linear-gradient(180deg, #c05030 0%, #a04020 100%); color: #fff; font-size: 10px; }
.btn-del:hover { background: linear-gradient(180deg, #d06040 0%, #b05030 100%); }
.btn-ac { background: linear-gradient(180deg, #c05030 0%, #a04020 100%); color: #fff; font-size: 10px; }
.btn-ac:hover { background: linear-gradient(180deg, #d06040 0%, #b05030 100%); }
.btn-equals { background: linear-gradient(180deg, #2060a0 0%, #105090 100%); color: #fff; font-size: 16px; }
.btn-equals:hover { background: linear-gradient(180deg, #3070b0 0%, #2060a0 100%); }
.btn-fraction { background: linear-gradient(180deg, #6a5a8a 0%, #5a4a7a 100%); color: #fff; font-size: 11px; }
.btn-fraction:hover { background: linear-gradient(180deg, #7a6a9a 0%, #6a5a8a 100%); }
.btn-sd { background: linear-gradient(180deg, #6a5a8a 0%, #5a4a7a 100%); color: #fff; font-size: 10px; }
.btn-sd:hover { background: linear-gradient(180deg, #7a6a9a 0%, #6a5a8a 100%); }

@media (max-width: 768px) {
    .calculator-modal-content {
        max-width: 95%;
        width: calc(100% - 20px);
        margin: 10px auto;
        padding: 15px 8px 10px;
        border-radius: 14px;
        max-height: 85vh;
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
    }

    .calc-close-btn {
        top: 8px;
        right: 8px;
        width: 32px;
        height: 32px;
        font-size: 14px;
    }

    .calc-brand {
        font-size: 13px;
    }

    .calc-model {
        font-size: 7px;
    }

    .calc-solar-panel {
        height: 12px;
        width: 50px;
    }

    .calc-display-frame {
        margin-bottom: 10px;
        padding: 2px;
    }

    .calc-display {
        min-height: 60px;
        padding: 8px 10px;
    }

    .calc-display-indicators {
        font-size: 7px;
        gap: 4px;
    }

    .calc-expression {
        font-size: 11px;
        min-height: 15px;
        margin-bottom: 4px;
    }

    .calc-result {
        font-size: 20px;
        min-height: 26px;
    }

    .calc-frac-num,
    .calc-frac-den {
        font-size: 11px;
    }

    .buttons-container {
        gap: 4px;
    }

    .calc-btn-row {
        gap: 4px;
    }

    .calc-btn {
        padding: 14px 3px;
        font-size: 13px;
        min-height: 44px;
        border-radius: 6px;
        -webkit-tap-highlight-color: rgba(255, 255, 255, 0.2);
        touch-action: manipulation;
    }

    .calc-btn:active {
        transform: scale(0.95);
    }

    .calc-shift-label {
        font-size: 9px;
        margin-bottom: 1px;
    }

    .btn-number {
        font-size: 16px;
    }

    .btn-operator,
    .btn-function,
    .btn-trig {
        font-size: 12px;
    }

    .btn-equals {
        font-size: 18px;
    }

    .btn-del,
    .btn-ac,
    .btn-shift,
    .btn-alpha,
    .btn-mode,
    .btn-nav {
        font-size: 11px;
    }
}

@media (max-width: 480px) {
    .calculator-modal-content {
        max-width: 100%;
        width: 100%;
        margin: 0;
        border-radius: 0;
        max-height: 100vh;
        height: 100vh;
        padding: 20px 6px 10px;
        display: flex;
        flex-direction: column;
    }

    .calc-close-btn {
        top: 10px;
        right: 10px;
        width: 36px;
        height: 36px;
    }

    .calc-buttons-container {
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: space-evenly;
        gap: 3px;
    }

    .calc-btn-row {
        gap: 3px;
    }

    .calc-btn {
        padding: 8px 2px;
        min-height: 40px;
        font-size: 12px;
        touch-action: manipulation;
    }

    .btn-number {
        font-size: 15px;
    }

    .btn-operator,
    .btn-function,
    .btn-trig {
        font-size: 11px;
    }

    .btn-equals {
        font-size: 16px;
    }

    .calc-shift-label {
        font-size: 8px;
    }

    .calc-display {
        min-height: 70px;
    }

    .calc-expression {
        font-size: 12px;
    }

    .calc-result {
        font-size: 22px;
    }
}

@media (max-height: 600px) and (orientation: landscape) {
    .calculator-modal-content {
        max-height: 95vh;
        padding: 10px 5px;
    }

    .calc-display {
        min-height: 50px;
        padding: 6px 8px;
    }

    .calc-expression {
        font-size: 10px;
    }

    .calc-result {
        font-size: 16px;
    }

    .calc-btn {
        padding: 6px 2px;
        min-height: 32px;
        font-size: 10px;
    }

    .btn-number {
        font-size: 12px;
    }

    .btn-operator,
    .btn-function {
        font-size: 9px;
    }
}

/* Improve touch interactions */
@media (hover: none) and (pointer: coarse) {
    .calc-btn {
        min-height: 44px;
        -webkit-tap-highlight-color: rgba(255, 255, 255, 0.3);
    }

    .calc-btn:hover {
        transform: none;
    }

    .calc-btn:active {
        transform: scale(0.95);
        opacity: 0.8;
    }
}
</style>
<?php endif; ?>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
