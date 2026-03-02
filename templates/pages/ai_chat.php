<?php
$pageTitle = 'AI Chat - StudySmart';
$currentPage = 'ai-chat';
$extraScripts = '<script>
const chatForm = document.getElementById("chat-form");
const messageInput = document.getElementById("message-input");
const messagesContainer = document.getElementById("messages-container");
const fileInput = document.getElementById("file-input");
const voiceModeToggle = document.getElementById("voice-mode-toggle");
const micButton = document.getElementById("mic-btn");
const micIcon = document.getElementById("mic-icon");

let isVoiceMode = false;
let isListening = false;
let recognition = null;
let synthesis = window.speechSynthesis;
let currentUtterance = null;
let shouldContinueListening = false;

// Initialize Speech Recognition
if ("webkitSpeechRecognition" in window || "SpeechRecognition" in window) {
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    recognition = new SpeechRecognition();
    recognition.continuous = false;
    recognition.interimResults = true;
    recognition.lang = "en-US";

    recognition.onresult = (event) => {
        let finalTranscript = "";
        for (let i = event.resultIndex; i < event.results.length; i++) {
            const transcript = event.results[i][0].transcript;
            if (event.results[i].isFinal) {
                finalTranscript += transcript;
            }
        }
        if (finalTranscript) {
            messageInput.value = finalTranscript;
            if (isVoiceMode) {
                sendMessage();
            }
        }
    };

    recognition.onerror = (event) => {
        console.error("Speech recognition error:", event.error);
        if (event.error === "no-speech") {
            if (shouldContinueListening && isVoiceMode) {
                setTimeout(() => startListening(), 500);
            } else {
                stopListening();
            }
        } else if (event.error === "audio-capture") {
            addMessage("No microphone found. Please ensure microphone is connected.", "ai");
            stopListening();
        }
    };

    recognition.onend = () => {
        isListening = false;
        micButton.classList.remove("listening");
        micIcon.classList.remove("fa-microphone-slash");
        micIcon.classList.add("fa-microphone");
        
        console.log("Recognition ended, shouldContinueListening:", shouldContinueListening, "isVoiceMode:", isVoiceMode);
        
        // Auto-restart listening in voice mode after AI finishes speaking
        if (shouldContinueListening && isVoiceMode) {
            // Wait for speech to finish, then restart
            const checkAndRestart = () => {
                if (!synthesis.speaking) {
                    console.log("Restarting recognition...");
                    setTimeout(() => {
                        try {
                            recognition.start();
                            isListening = true;
                            micButton.classList.add("listening");
                            micIcon.classList.remove("fa-microphone");
                            micIcon.classList.add("fa-microphone-slash");
                        } catch (e) {
                            console.error("Failed to restart recognition:", e);
                        }
                    }, 500);
                } else {
                    // Speech still ongoing, check again in 200ms
                    setTimeout(checkAndRestart, 200);
                }
            };
            checkAndRestart();
        }
    };
} else {
    micButton.style.display = "none";
    voiceModeToggle.style.display = "none";
    console.log("Speech recognition not supported");
}

chatForm.addEventListener("submit", async (e) => {
    e.preventDefault();
    sendMessage();
});

async function sendMessage() {
    const message = messageInput.value.trim();
    if (!message) return;

    // Stop listening while processing
    stopListening();

    // Add user message
    addMessage(message, "user");
    messageInput.value = "";

    // Show loading
    const loadingId = addMessage("Thinking...", "ai");

    try {
        const formData = new FormData();
        formData.append("message", message);

        const response = await fetch("/api/chatbot", {
            method: "POST",
            body: formData
        });

        const data = await response.json();

        // Remove loading message
        document.getElementById(loadingId).remove();

        if (data.reply) {
            addMessage(data.reply, "ai");
            // Speak the response in voice mode
            if (isVoiceMode) {
                await speakMessage(data.reply);
            }
        } else if (data.error) {
            addMessage("⚠️ " + data.error, "ai");
        }
    } catch (error) {
        document.getElementById(loadingId).remove();
        addMessage("Error: Failed to connect to server", "ai");
    }
}

function addMessage(text, type) {
    const div = document.createElement("div");
    div.className = "chat-message " + type;
    div.id = "msg-" + Date.now();
    div.textContent = text;
    messagesContainer.appendChild(div);
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
    return div.id;
}

// File upload functionality
document.getElementById("upload-btn").addEventListener("click", () => {
    fileInput.click();
});

fileInput.addEventListener("change", (e) => {
    const file = e.target.files[0];
    if (file) {
        addMessage("📎 Attached: " + file.name, "user");
    }
    fileInput.value = "";
});

// Voice Mode Toggle
voiceModeToggle.addEventListener("change", (e) => {
    isVoiceMode = e.target.checked;
    if (isVoiceMode) {
        addMessage("🎤 Voice mode enabled. Click the microphone to start the conversation!", "ai");
        micButton.style.display = "flex";
        shouldContinueListening = true;
    } else {
        addMessage("Voice mode disabled.", "ai");
        micButton.style.display = "none";
        shouldContinueListening = false;
        stopListening();
        if (synthesis) {
            synthesis.cancel();
        }
    }
});

// Microphone Button
micButton.addEventListener("click", () => {
    if (isListening) {
        stopListening();
        shouldContinueListening = false;
    } else {
        shouldContinueListening = true;
        startListening();
    }
});

function startListening() {
    if (!recognition) {
        addMessage("Speech recognition is not supported in your browser.", "ai");
        return;
    }
    try {
        recognition.start();
        isListening = true;
        micButton.classList.add("listening");
        micIcon.classList.remove("fa-microphone");
        micIcon.classList.add("fa-microphone-slash");
        // Only show message if user manually started (not auto-restart)
        if (!shouldContinueListening) {
            addMessage("🎤 Listening... Speak now!", "user");
        }
    } catch (e) {
        console.error("Failed to start recognition:", e);
    }
}

function stopListening() {
    if (recognition) {
        recognition.stop();
    }
    isListening = false;
    micButton.classList.remove("listening");
    micIcon.classList.remove("fa-microphone-slash");
    micIcon.classList.add("fa-microphone");
}

// Text-to-Speech
function speakMessage(text) {
    return new Promise((resolve) => {
        if (!synthesis) {
            resolve();
            return;
        }
        
        // Cancel any ongoing speech
        synthesis.cancel();
        
        const utterance = new SpeechSynthesisUtterance(text);
        utterance.lang = "en-US";
        utterance.rate = 1;
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
            resolve();
        };
        
        utterance.onerror = () => {
            resolve();
        };
        
        synthesis.speak(utterance);
    });
}

// Stop speech when leaving page
window.addEventListener("beforeunload", () => {
    if (synthesis) {
        synthesis.cancel();
    }
});

// Stop speech when clicking stop button
document.getElementById("stop-speech-btn")?.addEventListener("click", () => {
    if (synthesis) {
        synthesis.cancel();
    }
});
</script>';
include __DIR__ . '/../layouts/header.php';
?>

<h1 class="title">AI Chat Assistant</h1>
<p class="subtitle">Ask me anything about your studies!</p>

<div class="chat-container">
    <div class="chat-messages" id="messages-container">
        <div class="chat-message ai">
            Hello! I\'m your AI Study Assistant. I can help you with questions about various subjects, explain concepts, provide study tips, or create quiz questions. What would you like to know?
        </div>
    </div>

    <div class="chat-controls">
        <div class="voice-mode-control">
            <label for="voice-mode-toggle" class="voice-mode-label">
                <i class="fas fa-robot"></i> Voice Mode
            </label>
            <label class="toggle-switch">
                <input type="checkbox" id="voice-mode-toggle">
                <span class="toggle-slider"></span>
            </label>
        </div>
        <button type="button" id="stop-speech-btn" class="btn-icon btn-icon-small" title="Stop speech">
            <i class="fas fa-stop"></i>
        </button>
    </div>

    <form class="chat-input-form" id="chat-form">
        <input type="file" id="file-input" name="file" style="display: none;" accept=".pdf,.doc,.docx,.txt,.png,.jpg,.jpeg">
        <input type="text" id="message-input" name="message" placeholder="Type your message..." required>
        <button type="button" id="upload-btn" class="btn-icon" title="Upload file">
            <i class="fas fa-paperclip"></i>
        </button>
        <button type="button" id="mic-btn" class="btn-icon" title="Voice input" style="display: none;">
            <i class="fas fa-microphone" id="mic-icon"></i>
        </button>
        <button type="submit" class="btn-icon" title="Send message">
            <i class="fas fa-paper-plane"></i>
        </button>
    </form>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
