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
</style>
<script>
let synthesis = window.speechSynthesis;
let isSpeaking = false;
let currentUtterance = null;
const SCRIPT_ID = ' . $scriptId . ';
const SCRIPT_TITLE = "' . $scriptTitle . '";

function toggleSpeech() {
    const btn = document.getElementById("speech-btn");
    const icon = document.getElementById("speech-icon");

    if (isSpeaking) {
        synthesis.cancel();
        isSpeaking = false;
        clearHighlights();
        restoreOriginalContent();
        btn.classList.remove("btn-danger");
        btn.classList.add("btn-primary");
        icon.classList.remove("fa-stop");
        icon.classList.add("fa-volume-high");
        btn.innerHTML = "<i class=\"fas fa-volume-high\" id=\"speech-icon\"></i> Recite Memorandum";
    } else {
        const contentDiv = document.getElementById("memo-content");
        const content = contentDiv.textContent;
        isSpeaking = true;
        prepareContentForSentences(contentDiv);
        speakContent(contentDiv);
        btn.classList.remove("btn-primary");
        btn.classList.add("btn-danger");
        icon.classList.remove("fa-volume-high");
        icon.classList.add("fa-stop");
        btn.innerHTML = "<i class=\"fas fa-stop\" id=\"speech-icon\"></i> Stop Recitation";
    }
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
    
    const sentences = contentDiv.querySelectorAll(".sentence-span");
    let currentIndex = 0;
    
    speakNextSentence(sentences, currentIndex);
}

function speakNextSentence(sentences, index) {
    if (!isSpeaking) return;
    
    if (index >= sentences.length) {
        isSpeaking = false;
        clearHighlights();
        restoreOriginalContent();
        const btn = document.getElementById("speech-btn");
        const icon = document.getElementById("speech-icon");
        if (btn) {
            btn.classList.remove("btn-danger");
            btn.classList.add("btn-primary");
            icon.classList.remove("fa-stop");
            icon.classList.add("fa-volume-high");
            btn.innerHTML = "<i class=\"fas fa-volume-high\" id=\"speech-icon\"></i> Recite Memorandum";
        }
        return;
    }
    
    const sentenceSpan = sentences[index];
    const sentence = sentenceSpan.textContent;
    
    // Highlight current sentence
    clearHighlights();
    sentenceSpan.classList.add("highlight-sentence");
    sentenceSpan.scrollIntoView({ behavior: "smooth", block: "center" });
    
    const utterance = new SpeechSynthesisUtterance(sentence);
    utterance.lang = "en-US";
    utterance.rate = 0.95;
    utterance.pitch = 1;
    utterance.volume = 1;
    
    utterance.onend = function() {
        setTimeout(function() {
            speakNextSentence(sentences, index + 1);
        }, 100);
    };
    
    utterance.onerror = function() {
        isSpeaking = false;
        clearHighlights();
        restoreOriginalContent();
        const btn = document.getElementById("speech-btn");
        if (btn) {
            btn.classList.remove("btn-danger");
            btn.classList.add("btn-primary");
        }
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

document.addEventListener("DOMContentLoaded", function() {
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
