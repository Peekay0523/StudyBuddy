<?php
$pageTitle = 'View Memorandum - StudySmart';
$currentPage = 'scripts';
$extraScripts = <<<EOT
<script>
let synthesis = window.speechSynthesis;
let isSpeaking = false;
let currentUtterance = null;

function preprocessMathForSpeech(text) {
    // Replace mathematical symbols with spoken words
    let processed = text;

    // Remove bullet point dashes first (before processing math symbols)
    // This handles: "   - Understanding" or "- Understanding"
    processed = processed.replace(/^\s*-\s+/gm, " ");

    // Replace division symbols
    processed = processed.replace(/\s*÷\s*/g, " divided by ");
    processed = processed.replace(/\s*\//g, " divided by ");
    
    // Replace multiplication symbols
    processed = processed.replace(/\s*×\s*/g, " times ");
    processed = processed.replace(/\s*\*/g, " times ");
    processed = processed.replace(/\s*·\s*/g, " times ");
    
    // Replace addition and subtraction (only when surrounded by numbers/variables, not bullet points)
    processed = processed.replace(/(\d)\s*\+\s*(\d)/g, "\$1 plus \$2");
    processed = processed.replace(/(\d)\s*-\s*(\d)/g, "\$1 minus \$2");
    processed = processed.replace(/([a-zA-Z])\s*\+\s*([a-zA-Z])/g, "\$1 plus \$2");
    processed = processed.replace(/([a-zA-Z])\s*-\s*([a-zA-Z])/g, "\$1 minus \$2");
    
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
    processed = processed.replace(/\^(\d+)/g, " to the power of \$1 ");
    
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
    processed = processed.replace(/(\d)\s*</g, "\$1 less than ");
    processed = processed.replace(/>/g, " greater than ");
    
    // Replace "m =" with "m equals" for gradient context
    processed = processed.replace(/\b([a-zA-Z])\s*=\s*/g, "\$1 equals ");
    
    // Clean up multiple spaces
    processed = processed.replace(/\s+/g, " ").trim();
    
    return processed;
}

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
        const processedContent = preprocessMathForSpeech(content);
        speakContent(processedContent);
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
</script>
EOT;
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
