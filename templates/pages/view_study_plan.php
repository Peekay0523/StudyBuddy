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
        <span style="color: <?php echo $studyPlan['is_active'] ? '#16a34a' : '#6b7280'; ?>;">
            <?php echo $studyPlan['is_active'] ? 'Active' : 'Inactive'; ?>
        </span>
    </div>

    <div style="display: flex; gap: 10px; margin-bottom: 20px;">
        <button id="reciteBtn" onclick="reciteStudyPlan()" class="btn-primary" style="cursor: pointer;">
            <i class="fas fa-volume-up"></i> AI Recite Study Plan
        </button>
        <button id="stopReciteBtn" onclick="stopRecitation()" class="btn-secondary" style="cursor: pointer; display: none;">
            <i class="fas fa-stop"></i> Stop Recitation
        </button>
    </div>

    <div id="recitationOutput" class="feature-card" style="display: none; background: #f0f9ff; border: 1px solid #bae6fd;">
        <h4 style="margin-bottom: 10px;"><i class="fas fa-robot"></i> AI Recitation</h4>
        <div id="recitationText" style="white-space: pre-wrap; line-height: 1.6;"></div>
    </div>

    <h4 style="margin: 20px 0 10px 0;"><i class="fas fa-clipboard-list"></i> Plan Details</h4>
    <div style="background: #f9fafb; padding: 20px; border-radius: 8px; white-space: pre-wrap;">
        <?php echo htmlspecialchars($studyPlan['content']); ?>
    </div>
</div>

<script>
let isReciting = false;
let synthesis = null;
let availableVoices = [];

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

            synthesis = new SpeechSynthesisUtterance(data.recitation);
            
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
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
