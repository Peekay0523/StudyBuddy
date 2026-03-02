<?php
$pageTitle = 'View Memorandum - StudySmart';
$currentPage = 'scripts';
$extraScripts = '<script>
let synthesis = window.speechSynthesis;
let isSpeaking = false;
let currentUtterance = null;

function toggleSpeech() {
    const btn = document.getElementById("speech-btn");
    const icon = document.getElementById("speech-icon");
    
    if (isSpeaking) {
        // Stop speech
        synthesis.cancel();
        isSpeaking = false;
        btn.classList.remove("btn-danger");
        btn.classList.add("btn-primary");
        icon.classList.remove("fa-stop");
        icon.classList.add("fa-volume-high");
        btn.innerHTML = "<i class=\"fas fa-volume-high\" id=\"speech-icon\"></i> Recite Memorandum";
    } else {
        // Start speech
        const content = document.getElementById("memo-content").textContent;
        speakContent(content);
        isSpeaking = true;
        btn.classList.remove("btn-primary");
        btn.classList.add("btn-danger");
        icon.classList.remove("fa-volume-high");
        icon.classList.add("fa-stop");
        btn.innerHTML = "<i class=\"fas fa-stop\" id=\"speech-icon\"></i> Stop Recitation";
    }
}

function speakContent(text) {
    if (!synthesis) return;
    
    synthesis.cancel();
    
    const utterance = new SpeechSynthesisUtterance(text);
    utterance.lang = "en-US";
    utterance.rate = 0.9;
    utterance.pitch = 1;
    utterance.volume = 1;
    
    // Select a natural voice if available
    const voices = synthesis.getVoices();
    const preferredVoice = voices.find(voice => 
        voice.lang.includes("en-US") && voice.name.includes("Natural")
    ) || voices.find(voice => voice.lang.includes("en")) || voices[0];
    
    if (preferredVoice) {
        utterance.voice = preferredVoice;
    }
    
    currentUtterance = utterance;
    
    utterance.onend = () => {
        isSpeaking = false;
        const btn = document.getElementById("speech-btn");
        const icon = document.getElementById("speech-icon");
        if (btn) {
            btn.classList.remove("btn-danger");
            btn.classList.add("btn-primary");
            icon.classList.remove("fa-stop");
            icon.classList.add("fa-volume-high");
            btn.innerHTML = "<i class=\"fas fa-volume-high\" id=\"speech-icon\"></i> Recite Memorandum";
        }
    };
    
    utterance.onerror = () => {
        isSpeaking = false;
        const btn = document.getElementById("speech-btn");
        if (btn) {
            btn.classList.remove("btn-danger");
            btn.classList.add("btn-primary");
        }
    };
    
    synthesis.speak(utterance);
}

// Stop speech when leaving page
window.addEventListener("beforeunload", () => {
    if (synthesis) {
        synthesis.cancel();
    }
});
</script>';
include __DIR__ . '/../layouts/header.php';
?>

<h1 class="title">Memorandum</h1>
<p class="subtitle">AI-generated summary for: <?php echo htmlspecialchars($script['title']); ?></p>

<a href="/dashboard" class="btn-primary" style="text-decoration: none; display: inline-block; margin-bottom: 20px;">
    <i class="fas fa-arrow-left"></i> Back to Dashboard
</a>

<div class="feature-card" style="max-width: 800px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h3 style="margin: 0;">
            <i class="fas fa-file-alt"></i> <?php echo htmlspecialchars($script['title']); ?>
        </h3>
        <button id="speech-btn" class="btn-primary" onclick="toggleSpeech()">
            <i class="fas fa-volume-high" id="speech-icon"></i> Recite Memorandum
        </button>
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

<?php include __DIR__ . '/../layouts/footer.php'; ?>
