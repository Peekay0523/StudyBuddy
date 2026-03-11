<?php
$pageTitle = 'AI Chat - StudySmart';
$currentPage = 'ai-chat';
$canUseVoiceModeJs = $canUseVoiceMode ? 'true' : 'false';
$extraScripts = <<<EOT
<script>
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
let isSpeaking = false;

// Initialize voices on mobile browsers
function initVoices() {
    if (synthesis) {
        synthesis.getVoices();
        if (synthesis.onvoiceschanged !== undefined) {
            synthesis.onvoiceschanged = () => {
                console.log("Voices loaded:", synthesis.getVoices().length);
            };
        }
    }
}

initVoices();

// Only initialize voice features for paid users
const canUseVoiceMode = {$canUseVoiceModeJs};

if (canUseVoiceMode) {
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
        if (micButton) {
            micButton.classList.remove("listening");
            micIcon.classList.remove("fa-microphone-slash");
            micIcon.classList.add("fa-microphone");
        }

        console.log("Recognition ended, shouldContinueListening:", shouldContinueListening, "isVoiceMode:", isVoiceMode, "isSpeaking:", isSpeaking);

        // Auto-restart listening in voice mode after AI finishes speaking
        if (shouldContinueListening && isVoiceMode) {
            // Wait for speech to finish completely, then restart
            const checkAndRestart = () => {
                // Keep checking until speech is done
                if (isSpeaking || (synthesis && synthesis.speaking)) {
                    console.log("Waiting for speech to finish... isSpeaking:", isSpeaking, "synthesis.speaking:", synthesis?.speaking);
                    setTimeout(checkAndRestart, 300);
                } else {
                    // Add extra delay to ensure audio is fully done
                    setTimeout(() => {
                        console.log("Restarting recognition...");
                        try {
                            if (recognition) {
                                recognition.start();
                                isListening = true;
                                if (micButton) {
                                    micButton.classList.add("listening");
                                    micIcon.classList.remove("fa-microphone");
                                    micIcon.classList.add("fa-microphone-slash");
                                }
                            }
                        } catch (e) {
                            console.error("Failed to restart recognition:", e);
                        }
                    }, 800);
                }
            };
            checkAndRestart();
        }
    };
} else {
    console.log("Voice mode not available for free users");
}

// Browser support check (only for paid users)
if (canUseVoiceMode) {
    if (!("webkitSpeechRecognition" in window || "SpeechRecognition" in window)) {
        micButton.style.display = "none";
        voiceModeToggle.style.display = "none";
        console.log("Speech recognition not supported");
    }
} else {
    // Free user - hide voice controls
    if (micButton) micButton.style.display = "none";
    if (voiceModeToggle) voiceModeToggle.style.display = "none";
    if (document.getElementById("stop-speech-btn")) document.getElementById("stop-speech-btn").style.display = "none";
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
if (canUseVoiceMode && voiceModeToggle) {
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
}

// Microphone Button
if (canUseVoiceMode && micButton) {
    micButton.addEventListener("click", () => {
        if (isListening) {
            stopListening();
            shouldContinueListening = false;
        } else {
            shouldContinueListening = true;
            startListening();
        }
    });
}

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

function speakMessage(text) {
    return new Promise((resolve) => {
        if (!synthesis) {
            console.log("Speech synthesis not available");
            resolve();
            return;
        }

        // Cancel any ongoing speech
        synthesis.cancel();

        // Small delay after cancel (needed on some mobile browsers)
        setTimeout(() => {
            // Preprocess the text to convert math symbols to spoken words
            const processedText = preprocessMathForSpeech(text);
            const utterance = new SpeechSynthesisUtterance(processedText);
            utterance.lang = "en-US";
            utterance.rate = 0.9;
            utterance.pitch = 1;
            utterance.volume = 1;

            const voices = synthesis.getVoices();
            console.log("Available voices:", voices.length);

            // Select a natural voice if available
            let preferredVoice = null;

            if (voices.length > 0) {
                // First try: English Natural voice
                preferredVoice = voices.find(voice =>
                    voice.lang.includes("en-US") && voice.name.includes("Natural")
                );

                // Second try: Any English US voice
                if (!preferredVoice) {
                    preferredVoice = voices.find(voice =>
                        voice.lang === "en-US" || voice.lang.includes("en-US")
                    );
                }

                // Third try: Any English voice
                if (!preferredVoice) {
                    preferredVoice = voices.find(voice =>
                        voice.lang.includes("en")
                    );
                }

                // Fallback: First available voice
                if (!preferredVoice) {
                    preferredVoice = voices[0];
                }

                console.log("Selected voice:", preferredVoice.name, preferredVoice.lang);
            }

            if (preferredVoice) {
                utterance.voice = preferredVoice;
            }

            // Set speaking flag
            isSpeaking = true;

            utterance.onend = () => {
                console.log("Speech ended");
                isSpeaking = false;
                resolve();
            };

            utterance.onerror = (event) => {
                console.error("Speech synthesis error:", event.error);
                isSpeaking = false;
                resolve();
            };

            utterance.onstart = () => {
                console.log("Speech started");
            };

            // Speak the message
            console.log("Speaking message...");
            synthesis.speak(utterance);

            // Mobile fix: Resume if paused (some browsers pause automatically)
            setTimeout(() => {
                if (synthesis.paused) {
                    console.log("Resuming paused speech");
                    synthesis.resume();
                }
                if (!synthesis.speaking) {
                    console.log("Speech not started, retrying...");
                    synthesis.speak(utterance);
                }
            }, 200);
        }, 50);
    });
}

// Stop speech when leaving page
window.addEventListener("beforeunload", () => {
    if (synthesis) {
        synthesis.cancel();
    }
});

// Stop speech when clicking stop button
if (canUseVoiceMode) {
    document.getElementById("stop-speech-btn")?.addEventListener("click", () => {
        if (synthesis) {
            synthesis.cancel();
        }
    });
}
</script>
EOT;
include __DIR__ . '/../layouts/header.php';
?>

<h1 class="title">AI Chat Assistant</h1>
<p class="subtitle">Ask me anything about your studies!</p>

<div class="chat-container">
    <div class="chat-messages" id="messages-container">
        <div class="chat-message ai">
            Hello! I'm your AI Study Assistant. I can help you with questions about various subjects, explain concepts, provide study tips, or create quiz questions. What would you like to know?
        </div>
    </div>

    <div class="chat-controls">
        <?php if ($canUseVoiceMode): ?>
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
        <?php else: ?>
        <div class="voice-mode-control" style="flex: 1; text-align: center; color: #f59e0b; font-size: 13px;">
            <i class="fas fa-lock"></i> Voice Mode available in Basic and Premium plans
        </div>
        <?php endif; ?>
    </div>

    <form class="chat-input-form" id="chat-form">
        <input type="file" id="file-input" name="file" style="display: none;" accept=".pdf,.doc,.docx,.txt,.png,.jpg,.jpeg">
        <input type="text" id="message-input" name="message" placeholder="Type your message..." required>
        <button type="button" id="upload-btn" class="btn-icon" title="Upload file">
            <i class="fas fa-paperclip"></i>
        </button>
        <?php if ($canUseVoiceMode): ?>
        <button type="button" id="mic-btn" class="btn-icon" title="Voice input" style="display: none;">
            <i class="fas fa-microphone" id="mic-icon"></i>
        </button>
        <?php endif; ?>
        <button type="submit" class="btn-icon" title="Send message">
            <i class="fas fa-paper-plane"></i>
        </button>
    </form>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
