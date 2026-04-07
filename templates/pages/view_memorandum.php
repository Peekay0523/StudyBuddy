<?php
$pageTitle = 'View Memorandum - StudySmart';
$currentPage = 'scripts';
$scriptId = $script['id'];
$scriptTitle = addslashes($script['title']);

$extraHead = '<style>
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
    const sentence = sentenceSpan.textContent;

    // Highlight current sentence
    clearHighlights();
    sentenceSpan.classList.add("highlight-sentence");
    sentenceSpan.scrollIntoView({ behavior: "smooth", block: "center" });
    
    // Update progress
    updateProgress(currentSentenceIndex);

    const utterance = new SpeechSynthesisUtterance(sentence);
    utterance.lang = "en-US";
    utterance.rate = 0.95;
    utterance.pitch = 1;
    utterance.volume = 1;

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
            <button id="speech-btn" class="btn-primary" onclick="toggleSpeech()">
                <i class="fas fa-volume-high" id="speech-icon"></i> Recite Memorandum
            </button>
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

<?php include __DIR__ . '/../layouts/footer.php'; ?>
