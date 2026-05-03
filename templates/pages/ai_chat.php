<?php
$pageTitle = 'AI Chat - StudySmart';
$currentPage = 'ai-chat';
?>
<script>
window.canUseVoiceModeConfig = <?php echo $canUseVoiceMode ? 'true' : 'false'; ?>;
</script>
<?php
$extraScripts = <<<'EOT'
<script>
// Global variables
let chatForm, messageInput, messagesContainer, fileInput, voiceModeToggle, micButton, micIcon;
let isVoiceMode = false;
let isListening = false;
let recognition = null;
let synthesis = window.speechSynthesis;
let currentUtterance = null;
let shouldContinueListening = false;
let isSpeaking = false;
let isPaused = false;
let currentSentenceIndex = 0;
let elapsedTime = 0;
let timerInterval = null;
let sentences = [];
let totalSentences = 0;
let lastSpokenMessage = null;

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

// Initialize DOM elements and event listeners when DOM is ready
document.addEventListener("DOMContentLoaded", function() {
    // Initialize DOM elements
    chatForm = document.getElementById("chat-form");
    messageInput = document.getElementById("message-input");
    messagesContainer = document.getElementById("messages-container");
    fileInput = document.getElementById("file-input");
    voiceModeToggle = document.getElementById("voice-mode-toggle");
    micButton = document.getElementById("mic-btn");
    micIcon = document.getElementById("mic-icon");

    console.log("chatForm:", chatForm);
    console.log("messageInput:", messageInput);

    // Form submission handler
    if (chatForm) {
        chatForm.addEventListener("submit", async (e) => {
            e.preventDefault();
            console.log("Form submitted, message:", messageInput.value);
            sendMessage();
        });
    } else {
        console.error("chatForm is null! DOM not ready?");
    }

    // File upload functionality
    const uploadBtn = document.getElementById("upload-btn");
    if (uploadBtn) {
        uploadBtn.addEventListener("click", () => {
            fileInput.click();
        });
    }

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
                // Robot introduces itself
                setTimeout(() => {
                    robotSpeak("Hello! I'm your AI study assistant. Click the microphone and start talking!");
                }, 500);
            } else {
                addMessage("Voice mode disabled.", "ai");
                micButton.style.display = "none";
                shouldContinueListening = false;
                stopListening();
                if (synthesis) {
                    synthesis.cancel();
                }
                isSpeaking = false;
                clearHighlights();
                if (lastSpokenMessage) {
                    restoreOriginalMessage(lastSpokenMessage);
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

    // Stop speech button
    if (canUseVoiceMode) {
        const stopSpeechBtn = document.getElementById("stop-speech-btn");
        if (stopSpeechBtn) {
            stopSpeechBtn.addEventListener("click", () => {
                if (synthesis) {
                    synthesis.cancel();
                }
                isSpeaking = false;
                isPaused = false;
                robotSpeaking = false;
                stopTimer();
                hideControlBar();
                clearHighlights();
                if (lastSpokenMessage) {
                    restoreOriginalMessage(lastSpokenMessage);
                }
                const statusEl = document.getElementById("robot-status");
                if (statusEl) {
                    statusEl.textContent = "Ready";
                    statusEl.style.color = "#10a37f";
                }
            });
        }
    }

    // Progress bar click to seek
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
                speakNextSentence(sentences, () => {});
            }
        });
    }

    // Browser support check for voice
    if (canUseVoiceMode) {
        if (!("webkitSpeechRecognition" in window || "SpeechRecognition" in window)) {
            if (micButton) micButton.style.display = "none";
            if (voiceModeToggle) voiceModeToggle.style.display = "none";
            console.log("Speech recognition not supported");
        }
    } else {
        // Free user - hide voice controls
        if (micButton) micButton.style.display = "none";
        if (voiceModeToggle) voiceModeToggle.style.display = "none";
        const stopBtn = document.getElementById("stop-speech-btn");
        if (stopBtn) stopBtn.style.display = "none";
    }
});

// Control Bar Functions
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

function stopRecitation() {
    if (synthesis) {
        synthesis.cancel();
    }
    isSpeaking = false;
    isPaused = false;
    robotSpeaking = false;
    stopTimer();
    hideControlBar();
    clearHighlights();
    if (lastSpokenMessage) {
        restoreOriginalMessage(lastSpokenMessage);
    }
    const statusEl = document.getElementById("robot-status");
    if (statusEl) {
        statusEl.textContent = "Ready";
        statusEl.style.color = "#10a37f";
    }
}

// Only initialize voice features for paid users
const canUseVoiceMode = window.canUseVoiceModeConfig || false;

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

async function sendMessage() {
    console.log("sendMessage called");
    console.log("messageInput:", messageInput);
    
    if (!messageInput) {
        console.error("messageInput is null!");
        return;
    }
    
    const message = messageInput.value.trim();
    console.log("Message to send:", message);
    if (!message) return;

    // Stop listening while processing
    stopListening();

    // Add user message
    addMessage(message, "user");
    messageInput.value = "";

    // Show loading
    const loadingId = addMessage("Thinking...", "ai");

    try {
        console.log("Sending request to /api/chatbot...");
        const formData = new FormData();
        formData.append("message", message);

        const response = await fetch("/api/chatbot", {
            method: "POST",
            body: formData
        });

        console.log("Response status:", response.status);
        const data = await response.json();
        console.log("Response data:", data);

        // Remove loading message
        const loadingElement = document.getElementById(loadingId);
        if (loadingElement) {
            loadingElement.remove();
        }

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
        console.error("Error in sendMessage:", error);
        const loadingElement = document.getElementById(loadingId);
        if (loadingElement) {
            loadingElement.remove();
        }
        addMessage("Error: Failed to connect to server - " + error.message, "ai");
    }
}

function addMessage(text, type) {
    const div = document.createElement("div");
    div.className = "chat-message " + type;
    div.id = "msg-" + Date.now();
    
    if (type === "ai") {
        div.innerHTML = renderMarkdown(text);
    } else {
        div.textContent = text;
    }
    
    messagesContainer.appendChild(div);
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
    return div.id;
}

function renderMarkdown(text) {
    let html = text;
    
    // Escape HTML first
    html = html.replace(/&/g, '&amp;')
               .replace(/</g, '&lt;')
               .replace(/>/g, '&gt;');
    
    // Code blocks
    html = html.replace(/```([\s\S]*?)```/g, '<pre><code>$1</code></pre>');
    
    // Inline code
    html = html.replace(/`([^`]+)`/g, '<code class="inline-code">$1</code>');
    
    // Headers
    html = html.replace(/^### (.+)$/gm, '<h3>$1</h3>');
    html = html.replace(/^## (.+)$/gm, '<h2>$1</h2>');
    html = html.replace(/^# (.+)$/gm, '<h1>$1</h1>');
    
    // Bold
    html = html.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
    
    // Italic
    html = html.replace(/\*(.+?)\*/g, '<em>$1</em>');
    
    // Blockquotes
    html = html.replace(/^&gt; (.+)$/gm, '<blockquote>$1</blockquote>');
    
    // List items
    html = html.replace(/^[\-\*] (.+)$/gm, '<li>$1</li>');
    html = html.replace(/^\d+\. (.+)$/gm, '<li>$1</li>');
    
    // Wrap consecutive <li> in <ul>
    html = html.replace(/((?:<li>.*?<\/li>\n?)+)/g, '<ul>$1</ul>');
    
    // Paragraphs
    html = html.replace(/\n\n/g, '</p><p>');
    html = html.replace(/(<\/h[1-3]>)\n/g, '$1');
    html = html.replace(/(<\/li>)\n/g, '$1');
    html = html.replace(/(<\/ul>)\n/g, '$1');
    html = html.replace(/(<\/blockquote>)\n/g, '$1');
    html = html.replace(/(<\/pre>)\n/g, '$1');
    html = html.replace(/\n/g, '<br>');
    
    if (!html.startsWith('<')) {
        html = '<p>' + html + '</p>';
    }
    
    html = html.replace(/<p><\/p>/g, '');
    
    return html;
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

// Text-to-Speech with Highlighting
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

        // Cancel any ongoing speech and clear highlights
        synthesis.cancel();
        clearHighlights();
        stopTimer();

        // Also activate robot mouth animation
        robotSpeak(text);

        // Find the last AI message and prepare it for highlighting
        const lastMessage = document.querySelector(".chat-message.ai:last-of-type");
        if (lastMessage) {
            lastSpokenMessage = lastMessage;
            prepareMessageForSentences(lastMessage, text);
        }

        // Small delay after cancel (needed on some mobile browsers)
        setTimeout(() => {
            // Get sentences from the message
            const sentences = lastMessage ? lastMessage.querySelectorAll(".sentence-span") : [];
            totalSentences = sentences.length;
            currentSentenceIndex = 0;

            if (totalSentences === 0) {
                resolve();
                return;
            }

            showControlBar();
            startTimer();
            speakNextSentence(sentences, resolve);
        }, 50);
    });
}

function speakNextSentence(sentences, resolvePromise) {
    if (!synthesis || !synthesis.speaking) {
        // Speech was stopped
        if (!isPaused) {
            clearHighlights();
            hideControlBar();
            stopTimer();
            if (resolvePromise) resolvePromise();
        }
        return;
    }
    
    if (isPaused) {
        setTimeout(() => speakNextSentence(sentences, resolvePromise), 100);
        return;
    }

    if (currentSentenceIndex >= sentences.length) {
        // All sentences spoken
        isSpeaking = false;
        clearHighlights();
        hideControlBar();
        stopTimer();
        if (resolvePromise) resolvePromise();
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

    // Preprocess the sentence for math symbols
    const processedSentence = preprocessMathForSpeech(sentence);
    const utterance = new SpeechSynthesisUtterance(processedSentence);
    utterance.lang = "en-US";
    utterance.rate = 0.9;
    utterance.pitch = 1;
    utterance.volume = 1;

    // Select best available voice
    const voices = synthesis.getVoices();
    const preferredVoice = voices.find(v =>
        v.name.includes('Google') || v.name.includes('Microsoft') || v.name.includes('Natural')
    ) || voices.find(v => v.lang.startsWith('en')) || voices[0];

    if (preferredVoice) {
        utterance.voice = preferredVoice;
    }

    utterance.onend = function() {
        currentSentenceIndex++;
        setTimeout(function() {
            speakNextSentence(sentences, resolvePromise);
        }, 100);
    };

    utterance.onerror = function(event) {
        console.error("Speech synthesis error:", event.error);
        isSpeaking = false;
        clearHighlights();
        hideControlBar();
        stopTimer();
        if (resolvePromise) resolvePromise();
    };

    isSpeaking = true;
    synthesis.speak(utterance);
}

function prepareMessageForSentences(messageEl, originalText) {
    if (!messageEl.dataset.originalHtml) {
        messageEl.dataset.originalHtml = messageEl.innerHTML;
    }

    // Split text by sentences
    const sentences = originalText.split(/([.!?]\s+)/);
    let html = "";

    for (let i = 0; i < sentences.length; i++) {
        const sentence = sentences[i];
        if (sentence.trim()) {
            html += "<span class=\"sentence-span\">" + escapeHtml(sentence) + "</span>";
        } else if (sentence) {
            html += sentence;
        }
    }

    messageEl.innerHTML = html;
}

function clearHighlights() {
    const highlights = document.querySelectorAll(".highlight-word, .highlight-sentence");
    highlights.forEach(function(span) {
        span.classList.remove("highlight-word", "highlight-sentence");
    });
}

function escapeHtml(text) {
    const div = document.createElement("div");
    div.textContent = text;
    return div.innerHTML;
}

function restoreOriginalMessage(messageEl) {
    if (messageEl && messageEl.dataset.originalHtml) {
        messageEl.innerHTML = messageEl.dataset.originalHtml;
        messageEl.dataset.originalHtml = "";
    }
}

// Stop speech when leaving page
window.addEventListener("beforeunload", () => {
    if (synthesis) {
        synthesis.cancel();
    }
    stopTimer();
    hideControlBar();
    clearHighlights();
    if (lastSpokenMessage) {
        restoreOriginalMessage(lastSpokenMessage);
    }
});

// ===== Pixel Talking Robot =====
let robotSpeaking = false;
let robotMouthIndex = 0;
let mouthAnimationInterval = null;
let eyeBlinkTimeout = null;

function initPixelRobot() {
    const robot = document.getElementById("robot");
    if (!robot) return;

    const layout = [
        "0 0 0 1 0 0 0 0 0 0 0 1 0 0 0",
        "0 0 0 0 1 0 0 0 0 0 1 0 0 0 0",
        "0 0 0 1 1 1 1 1 1 1 1 1 0 0 0",
        "0 0 1 1 1 1 1 1 1 1 1 1 1 0 0",
        "0 1 1 2 2 2 2 2 2 2 2 2 1 1 0",
        "0 1 2 2 3 3 2 2 2 3 3 2 2 1 0",
        "1 1 2 2 3 3 2 2 2 3 3 2 2 1 1",
        "1 1 2 2 2 2 2 2 2 2 2 2 2 1 1",
        "1 1 1 2 2 2 4 4 4 2 2 2 1 1 1",
        "1 0 1 2 2 2 4 5 4 2 2 2 1 0 1",
        "1 0 1 2 2 2 4 4 4 2 2 2 1 0 1",
        "0 0 1 1 1 1 1 1 1 1 1 1 1 0 0",
        "0 0 0 1 1 1 1 1 1 1 1 1 0 0 0",
        "0 0 0 1 0 0 0 0 0 0 0 1 0 0 0",
        "0 0 1 0 0 0 0 0 0 0 0 0 1 0 0"
    ];

    layout.flatMap(row => row.split(" ")).forEach((cell) => {
        const div = document.createElement("div");
        div.classList.add("pixel");

        if (cell === "1") div.classList.add("green");
        if (cell === "2") div.classList.add("dark-green");
        if (cell === "3") div.classList.add("eye-white");
        if (cell === "4") div.classList.add("mouth-black");
        if (cell === "5") div.classList.add("mouth-teeth");

        robot.appendChild(div);
    });

    scheduleNextBlink();
    startMouthAnimation();
}

function scheduleNextBlink() {
    const randomDelay = 2000 + Math.random() * 4000;
    if (eyeBlinkTimeout) clearTimeout(eyeBlinkTimeout);
    eyeBlinkTimeout = setTimeout(() => {
        blinkEyes();
        scheduleNextBlink();
    }, randomDelay);
}

function blinkEyes() {
    const eyes = document.querySelectorAll(".pixel.eye-white");
    if (eyes.length === 0) return;
    eyes.forEach(e => {
        e.style.transition = "all 0.1s ease";
        e.style.background = "#0d8a6a";
        e.style.boxShadow = "none";
        e.style.transform = "scaleY(0.1)";
    });
    setTimeout(() => {
        eyes.forEach(e => {
            e.style.background = "#ffffff";
            e.style.boxShadow = "0 0 6px rgba(255, 255, 255, 0.6)";
            e.style.transform = "scaleY(1)";
        });
    }, 150);
}

function startMouthAnimation() {
    if (mouthAnimationInterval) clearInterval(mouthAnimationInterval);
    mouthAnimationInterval = setInterval(() => {
        animateRobotMouth();
    }, 50);
}

function animateRobotMouth() {
    const mouths = document.querySelectorAll(".pixel.mouth-black");
    if (mouths.length === 0) return;

    if (robotSpeaking) {
        robotMouthIndex++;
        const mouthShape = robotMouthIndex % 8;
        mouths.forEach((mouth) => {
            switch(mouthShape) {
                case 0:
                case 5:
                    mouth.style.background = "#10a37f";
                    mouth.style.boxShadow = "0 0 4px rgba(16, 163, 127, 0.4)";
                    mouth.style.transform = "scaleY(0.5)";
                    break;
                case 1:
                case 4:
                    mouth.style.background = "#10a37f";
                    mouth.style.boxShadow = "0 0 6px rgba(16, 163, 127, 0.6)";
                    mouth.style.transform = "scaleY(0.9)";
                    break;
                case 2:
                case 3:
                    mouth.style.background = "#10a37f";
                    mouth.style.boxShadow = "0 0 8px rgba(16, 163, 127, 0.8)";
                    mouth.style.transform = "scaleY(1.2)";
                    break;
                case 6:
                    mouth.style.background = "#1a1a2e";
                    mouth.style.boxShadow = "none";
                    mouth.style.transform = "scaleY(0.1)";
                    break;
                case 7:
                    mouth.style.background = "#10a37f";
                    mouth.style.boxShadow = "0 0 3px rgba(16, 163, 127, 0.3)";
                    mouth.style.transform = "scaleY(0.3)";
                    break;
            }
        });
    } else {
        mouths.forEach(mouth => {
            mouth.style.background = "#1a1a2e";
            mouth.style.boxShadow = "none";
            mouth.style.transform = "scaleY(0.1)";
        });
    }
}

function robotSpeak(text) {
    if (!text || !('speechSynthesis' in window)) return;

    speechSynthesis.cancel();
    const utter = new SpeechSynthesisUtterance(text);
    utter.rate = 0.9;
    utter.pitch = 1.0;
    robotSpeaking = true;
    robotMouthIndex = 0;

    const statusEl = document.getElementById("robot-status");
    if (statusEl) {
        statusEl.textContent = "Speaking...";
        statusEl.style.color = "#4a9eff";
        statusEl.style.borderColor = "rgba(74, 158, 255, 0.3)";
        statusEl.style.background = "rgba(74, 158, 255, 0.1)";
    }
    
    utter.onend = () => {
        robotSpeaking = false;
        if (statusEl) {
            statusEl.textContent = "Ready";
            statusEl.style.color = "#10a37f";
            statusEl.style.borderColor = "rgba(16, 163, 127, 0.3)";
            statusEl.style.background = "rgba(16, 163, 127, 0.1)";
        }
    };

    utter.onerror = () => {
        robotSpeaking = false;
        if (statusEl) {
            statusEl.textContent = "Error";
            setTimeout(() => {
                statusEl.textContent = "Ready";
                statusEl.style.color = "#10a37f";
            }, 2000);
        }
    };

    speechSynthesis.speak(utter);
}

// Initialize robot
document.addEventListener("DOMContentLoaded", function() {
    initPixelRobot();
});
</script>
EOT;
include __DIR__ . '/../layouts/header.php';
?>

<h1 class="title" style="font-size: 1.5rem; margin-bottom: 0.25rem;">AI Chat Assistant</h1>
<p class="subtitle" style="font-size: 0.85rem; margin-bottom: 0.5rem;">Ask me anything about your studies!</p>

<style>
/* Yellow Highlight Styles for Voice Mode */
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
    bottom: 80px;
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
    z-index: 999;
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

/* Mobile Voice Mode Enhancements */
@media (max-width: 768px) {
    /* Make mic button more prominent when listening */
    #mic-btn.listening {
        background: linear-gradient(135deg, #dc2626, #ef4444) !important;
        color: white !important;
        animation: mic-pulse 1.5s infinite;
    }

    @keyframes mic-pulse {
        0%, 100% {
            box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7);
            transform: scale(1);
        }
        50% {
            box-shadow: 0 0 0 15px rgba(239, 68, 68, 0);
            transform: scale(1.1);
        }
    }

    /* Voice mode toggle container */
    .voice-mode-control {
        background: #f3f4f6;
        border-radius: 8px;
        padding: 10px 15px !important;
    }

    /* Chat message text size */
    .chat-message {
        line-height: 1.5;
    }

    /* Ensure buttons are touch-friendly */
    .chat-input-form .btn-icon {
        touch-action: manipulation;
        -webkit-tap-highlight-color: transparent;
    }

    /* Stop speech button visibility */
    #stop-speech-btn {
        background: #fee2e2 !important;
        color: #dc2626 !important;
        border: 1px solid #fca5a5;
    }

    #stop-speech-btn:hover {
        background: #fecaca !important;
    }

    /* Adjust chat container height for mobile */
    #chat-container {
        height: calc(100vh - 240px);
        max-height: calc(100vh - 240px);
    }
}

@media (max-width: 480px) {
    /* Extra small screens */
    #mic-btn {
        min-width: 40px !important;
        min-height: 40px !important;
    }

    /* Make voice mode toggle easier to tap */
    .toggle-switch {
        touch-action: manipulation;
    }

    /* Further reduce height for small screens */
    #chat-container {
        height: calc(100vh - 220px);
        max-height: calc(100vh - 220px);
    }

    /* Compact chat controls */
    .chat-controls {
        padding: 6px 15px;
    }

    .chat-input-form {
        padding: 10px 15px;
    }
}

.chat-message.ai h1, .chat-message.ai h2, .chat-message.ai h3 {
    margin: 16px 0 8px 0; font-weight: 600; line-height: 1.3;
}
.chat-message.ai h1 { font-size: 1.4em; border-bottom: 2px solid #e5e7eb; padding-bottom: 4px; }
.chat-message.ai h2 { font-size: 1.2em; border-bottom: 1px solid #e5e7eb; padding-bottom: 3px; }
.chat-message.ai h3 { font-size: 1.05em; }
.chat-message.ai p { margin: 8px 0; line-height: 1.6; }
.chat-message.ai ul, .chat-message.ai ol { margin: 8px 0; padding-left: 24px; }
.chat-message.ai li { margin: 4px 0; line-height: 1.6; }
.chat-message.ai strong { font-weight: 600; color: #1f2937; }
.chat-message.ai blockquote {
    border-left: 4px solid #667eea; padding: 8px 12px; margin: 12px 0;
    background: #f9fafb; border-radius: 4px;
}
.chat-message.ai pre {
    background: #1f2937; color: #f9fafb; padding: 12px; border-radius: 6px;
    overflow-x: auto; margin: 12px 0; font-size: 0.9em;
}
.chat-message.ai code { font-family: 'Courier New', monospace; font-size: 0.9em; }
.chat-message.ai code.inline-code {
    background: #f3f4f6; padding: 2px 6px; border-radius: 4px; color: #667eea;
}

/* Full viewport height layout */
#chat-container {
    display: flex;
    flex-direction: column;
    height: calc(100vh - 280px);
    max-height: calc(100vh - 280px);
}

.chat-messages {
    flex: 1;
    overflow-y: auto;
    overflow-x: hidden;
    -webkit-overflow-scrolling: touch;
}

.chat-input-form {
    flex-shrink: 0;
}

.chat-controls {
    flex-shrink: 0;
    padding: 8px 20px;
}

/* ===== Pixel Talking Robot ===== */
.robot-section {
    padding: 12px 20px 8px;
    text-align: center;
    background: linear-gradient(180deg, rgba(16, 163, 127, 0.05) 0%, transparent 100%);
    border-bottom: 1px solid #e5e7eb;
    flex-shrink: 0;
}

.robot-wrapper {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
}

.robot {
    width: 100px;
    height: 100px;
    display: grid;
    grid-template-columns: repeat(15, 1fr);
    grid-template-rows: repeat(15, 1fr);
    gap: 1px;
    animation: float 3s infinite ease-in-out;
    position: relative;
    background: linear-gradient(135deg, rgba(16, 163, 127, 0.1) 0%, rgba(74, 158, 255, 0.1) 100%);
    border-radius: 12px;
    padding: 8px;
    border: 2px solid rgba(16, 163, 127, 0.3);
    box-shadow: 0 4px 16px rgba(16, 163, 127, 0.2),
                inset 0 0 12px rgba(16, 163, 127, 0.05);
}

.robot::after {
    content: '';
    position: absolute;
    bottom: -8px;
    left: 50%;
    transform: translateX(-50%);
    width: 80px;
    height: 6px;
    background: radial-gradient(ellipse, rgba(16, 163, 127, 0.5) 0%, transparent 70%);
    border-radius: 50%;
    animation: shadow 3s infinite ease-in-out;
}

@keyframes shadow {
    0%, 100% { transform: translateX(-50%) scaleX(1); opacity: 0.5; }
    50% { transform: translateX(-50%) scaleX(0.7); opacity: 0.3; }
}

@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-4px); }
}

.pixel {
    width: 100%;
    height: 100%;
    background: transparent;
    image-rendering: pixelated;
    border-radius: 1px;
    transition: all 0.2s ease;
}

.pixel.green {
    background: #10a37f;
    filter: brightness(1.2);
}
.pixel.dark-green {
    background: #0d8a6a;
}
.pixel.eye-white {
    background: #ffffff;
    filter: brightness(1.3);
    box-shadow: 0 0 4px rgba(255, 255, 255, 0.6);
    transition: all 0.15s ease;
    transform-origin: center;
}
.pixel.mouth-black {
    background: #1a1a2e;
    transition: all 0.05s ease;
    transform-origin: center;
}
.pixel.mouth-teeth {
    background: #ffffff;
    transition: all 0.05s ease;
}

.robot-status {
    font-size: 9px;
    color: #10a37f;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    font-weight: 600;
    background: rgba(16, 163, 127, 0.1);
    padding: 3px 10px;
    border-radius: 10px;
    border: 1px solid rgba(16, 163, 127, 0.3);
}

.robot-hint {
    font-size: 10px;
    color: #6b7280;
    margin-top: 2px;
}

/* Mobile Responsive for Robot */
@media (max-width: 768px) {
    .robot-section {
        padding: 8px 12px 4px;
    }

    .robot {
        width: 80px;
        height: 80px;
        padding: 6px;
        border-radius: 10px;
    }

    .robot::after {
        width: 60px;
        height: 5px;
        bottom: -6px;
    }

    .robot-wrapper {
        gap: 4px;
    }

    .robot-status {
        font-size: 8px;
        padding: 2px 8px;
    }

    .robot-hint {
        font-size: 9px;
    }
}

@media (max-width: 480px) {
    .robot-section {
        padding: 6px 10px 3px;
    }

    .robot {
        width: 70px;
        height: 70px;
        padding: 5px;
    }

    .robot::after {
        width: 50px;
        height: 4px;
        bottom: -5px;
    }

    .robot-hint {
        display: none;
    }
}
</style>

<!-- Pixel Robot Section -->
<div class="robot-section" id="robot-section">
    <div class="robot-wrapper">
        <div id="robot" class="robot"></div>
        <div class="robot-status" id="robot-status">Ready</div>
        <div class="robot-hint">Enable Voice Mode to chat with me!</div>
    </div>
</div>

<div class="chat-container" id="chat-container">
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
