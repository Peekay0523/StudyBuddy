<?php
$pageTitle = 'View Study Plan - StudySmart';
$currentPage = 'study-plan';
include __DIR__ . '/../layouts/header.php';
?>

<h1 class="title">Study Plan</h1>
<p class="subtitle"><?php echo htmlspecialchars($studyPlan['title']); ?></p>

<a href="/study-plan" class="btn-primary" style="text-decoration: none; display: inline-block; margin-bottom: 20px;">
    <i class="fas fa-arrow-left"></i> Back to Study Plans
</a>

<div class="feature-card" style="max-width: 800px;">
    <h3 style="margin-bottom: 20px;">
        <i class="fas fa-calendar-alt"></i> <?php echo htmlspecialchars($studyPlan['title']); ?>
    </h3>

    <div style="margin-bottom: 20px;">
        <strong>Created:</strong> <?php echo date('M d, Y', strtotime($studyPlan['created_at'])); ?> |
        <strong>Status:</strong>
        <span style="color: <?php echo $isCompleted ? '#16a34a' : '#f59e0b'; ?>; font-weight: 600;">
            <i class="fas fa-<?php echo $isCompleted ? 'check-circle' : 'clock'; ?>"></i>
            <?php echo $isCompleted ? 'Completed' : 'In Progress'; ?>
        </span>
    </div>

    <div style="display: flex; gap: 10px; margin-bottom: 20px;">
        <button id="reciteBtn" onclick="reciteStudyPlan()" class="btn-primary" style="cursor: pointer;">
            <i class="fas fa-volume-up"></i> AI Recite Study Plan
        </button>
        <button id="stopReciteBtn" onclick="stopRecitation()" class="btn-secondary" style="cursor: pointer; display: none;">
            <i class="fas fa-stop"></i> Stop Recitation
        </button>
        <?php 
        $isCompleted = isset($studyPlan['is_completed']) && $studyPlan['is_completed'];
        if (!$isCompleted): 
        ?>
            <button onclick="markStudyPlanComplete()" class="btn-primary" style="background: linear-gradient(135deg, #16a34a, #059669);">
                <i class="fas fa-check-circle"></i> Mark as Complete
            </button>
        <?php else: ?>
            <span style="background: linear-gradient(135deg, #16a34a, #059669); color: white; padding: 10px 20px; border-radius: 8px; font-weight: 600; display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-check-circle"></i> Completed
            </span>
        <?php endif; ?>
    </div>

    <div id="recitationOutput" class="feature-card" style="display: none; background: #f0f9ff; border: 1px solid #bae6fd;">
        <h4 style="margin-bottom: 10px;"><i class="fas fa-robot"></i> AI Recitation</h4>
        <div id="recitationText" style="white-space: pre-wrap; line-height: 1.6;"></div>
    </div>

    <h4 style="margin: 20px 0 10px 0;"><i class="fas fa-clipboard-list"></i> Plan Details</h4>
    <div style="background: #f9fafb; padding: 20px; border-radius: 8px; white-space: pre-wrap; line-height: 1.8;">
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

<script>
let isReciting = false;
let synthesis = null;
let availableVoices = [];

// Mark study plan as viewed when page loads (removes notification)
async function markStudyPlanAsViewed() {
    try {
        await fetch('/study-plan/<?php echo $studyPlan['id']; ?>/mark-viewed', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
        });
    } catch (error) {
        console.error('Error marking study plan as viewed:', error);
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
    if (isReciting) {
        stopRecitation();
        return;
    }

    const reciteBtn = document.getElementById('reciteBtn');
    const stopBtn = document.getElementById('stopReciteBtn');
    const outputDiv = document.getElementById('recitationOutput');
    const recitationText = document.getElementById('recitationText');

    reciteBtn.disabled = true;
    reciteBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';

    try {
        const response = await fetch('/recite-study-plan/<?php echo $studyPlan['id']; ?>', {
            method: 'GET',
            headers: { 'Accept': 'application/json' }
        });

        if (!response.ok) {
            throw new Error('Failed to generate recitation');
        }

        const data = await response.json();

        outputDiv.style.display = 'block';
        recitationText.textContent = data.recitation;

        // Use Web Speech API for text-to-speech
        if ('speechSynthesis' in window) {
            isReciting = true;
            stopBtn.style.display = 'inline-block';

            // Preprocess the text to convert math symbols to spoken words
            const processedText = preprocessMathForSpeech(data.recitation);
            synthesis = new SpeechSynthesisUtterance(processedText);
            
            // Select best available voice (prefer Google or Microsoft voices)
            const preferredVoice = availableVoices.find(v => 
                v.name.includes('Google') || v.name.includes('Microsoft') || v.name.includes('Natural')
            ) || availableVoices.find(v => v.lang.startsWith('en'));
            
            if (preferredVoice) {
                synthesis.voice = preferredVoice;
            }
            
            synthesis.lang = 'en-US';
            synthesis.rate = 0.95;
            synthesis.pitch = 1.0;
            synthesis.volume = 1.0;

            synthesis.onend = function() {
                isReciting = false;
                reciteBtn.disabled = false;
                reciteBtn.innerHTML = '<i class="fas fa-volume-up"></i> AI Recite Study Plan';
                stopBtn.style.display = 'none';
            };

            synthesis.onerror = function() {
                isReciting = false;
                reciteBtn.disabled = false;
                reciteBtn.innerHTML = '<i class="fas fa-volume-up"></i> AI Recite Study Plan';
                stopBtn.style.display = 'none';
            };

            window.speechSynthesis.speak(synthesis);
        } else {
            recitationText.innerHTML += '<p style="color: #6b7280; margin-top: 10px; font-size: 0.9em;"><i class="fas fa-info-circle"></i> Text-to-speech is not supported in your browser. Please read the recitation above.</p>';
        }

    } catch (error) {
        console.error('Error:', error);
        outputDiv.style.display = 'block';
        recitationText.innerHTML = '<span style="color: #dc2626;">Error generating recitation. Please try again.</span>';
    } finally {
        reciteBtn.disabled = false;
        reciteBtn.innerHTML = '<i class="fas fa-volume-up"></i> AI Recite Study Plan';
    }
}

function stopRecitation() {
    if (synthesis) {
        window.speechSynthesis.cancel();
    }
    isReciting = false;
    document.getElementById('reciteBtn').disabled = false;
    document.getElementById('reciteBtn').innerHTML = '<i class="fas fa-volume-up"></i> AI Recite Study Plan';
    document.getElementById('stopReciteBtn').style.display = 'none';
}

async function markStudyPlanComplete() {
    if (!confirm('Mark this study plan as complete?')) {
        return;
    }

    try {
        const response = await fetch('/study-plan/complete/<?php echo $studyPlan['id']; ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
        });

        const data = await response.json();

        if (data.success) {
            // Show success message
            alert('Study plan marked as complete!');
            // Reload the page to show updated status
            location.reload();
        } else {
            alert('Failed to mark study plan as complete. Please try again.');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    }
}
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
