/**
 * Quiz JS - Handles AI-generated voice quizzes for memorandums
 */

let quizState = {
    active: false,
    currentQuestion: 0,
    totalQuestions: 5,
    score: 0,
    questions: [],
    recognition: null,
    isListening: false,
    synthesis: window.speechSynthesis,
    lastQuestion: '',
    lastAnswer: '',
    inputMode: 'voice' // 'voice' or 'text'
};

function toggleInputMode() {
    const toggleBtn = document.getElementById('input-mode-toggle');
    const voiceUI = document.getElementById('voice-answer-ui');
    const textUI = document.getElementById('text-answer-ui');
    const voiceControls = document.getElementById('voice-controls');

    if (quizState.inputMode === 'voice') {
        quizState.inputMode = 'text';
        toggleBtn.textContent = 'Switch to Voice Answer';
        voiceUI.style.display = 'none';
        textUI.style.display = 'block';
        voiceControls.style.display = 'none';
        stopVoiceInput();
    } else {
        quizState.inputMode = 'voice';
        toggleBtn.textContent = 'Switch to Type Answer';
        voiceUI.style.display = 'block';
        textUI.style.display = 'none';
        voiceControls.style.display = 'block';
    }
}

function submitTextAnswer() {
    const input = document.getElementById('user-answer-text');
    const answer = input.value.trim();
    
    if (!answer) {
        alert('Please type an answer before submitting.');
        return;
    }
    
    quizState.lastAnswer = answer;
    // Set text UI to show the answer like voice does
    document.getElementById('user-answer').textContent = answer;
    document.getElementById('voice-answer-ui').style.display = 'block';
    document.getElementById('text-answer-ui').style.display = 'none';
    
    evaluateAnswer(answer);
    input.value = ''; // Clear input for next question
}

// Initialize Speech Recognition
function initSpeechRecognition() {
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    if (SpeechRecognition) {
        quizState.recognition = new SpeechRecognition();
        quizState.recognition.continuous = false;
        quizState.recognition.interimResults = false;
        quizState.recognition.lang = 'en-ZA'; // South African English

        quizState.recognition.onresult = (event) => {
            const transcript = event.results[0][0].transcript;
            document.getElementById('user-answer').textContent = transcript;
            quizState.lastAnswer = transcript;
            evaluateAnswer(transcript);
        };

        quizState.recognition.onerror = (event) => {
            console.error('Speech recognition error:', event.error);
            document.getElementById('voice-status').textContent = 'Error: ' + event.error;
            stopVoiceInput();
        };

        quizState.recognition.onend = () => {
            quizState.isListening = false;
            updateMicButton();
        };
    }
}

function toggleQuiz() {
    const quizSection = document.getElementById('quiz-section');
    const takeQuizBtn = document.getElementById('take-quiz-btn');
    
    if (quizSection.style.display === 'none' || quizSection.style.display === '') {
        quizSection.style.display = 'block';
        takeQuizBtn.style.display = 'none';
        startQuiz();
        quizSection.scrollIntoView({ behavior: 'smooth' });
    } else {
        quizSection.style.display = 'none';
        takeQuizBtn.style.display = 'inline-block';
        if (quizState.synthesis) quizState.synthesis.cancel();
        stopVoiceInput();
    }
}

async function startQuiz() {
    quizState.currentQuestion = 0;
    quizState.score = 0;
    quizState.active = true;
    quizState.inputMode = 'voice'; // Reset to default
    
    document.getElementById('quiz-results').style.display = 'none';
    document.getElementById('quiz-progress').style.display = 'block';
    document.getElementById('question-container').style.display = 'block';
    document.getElementById('voice-controls').style.display = 'block';
    document.getElementById('answer-display').style.display = 'block';
    
    // Reset UIs
    document.getElementById('voice-answer-ui').style.display = 'block';
    document.getElementById('text-answer-ui').style.display = 'none';
    document.getElementById('input-mode-toggle').textContent = 'Switch to Type Answer';
    
    updateProgress();
    await generateQuestion();
}

async function generateQuestion() {
    const questionEl = document.getElementById('current-question');
    const voiceStatus = document.getElementById('voice-status');
    const userAnswerEl = document.getElementById('user-answer');
    const feedbackEl = document.getElementById('feedback-display');
    const nextBtnContainer = document.getElementById('next-btn-container');
    const textUI = document.getElementById('text-answer-ui');
    const voiceUI = document.getElementById('voice-answer-ui');

    questionEl.textContent = 'Generating question...';
    voiceStatus.textContent = '';
    userAnswerEl.textContent = '';
    feedbackEl.style.display = 'none';
    nextBtnContainer.style.display = 'none';

    // Restore input mode UI
    if (quizState.inputMode === 'text') {
        textUI.style.display = 'block';
        voiceUI.style.display = 'none';
    } else {
        textUI.style.display = 'none';
        voiceUI.style.display = 'block';
    }

    try {
        const formData = new FormData();
        formData.append('script_id', SCRIPT_ID);

        // Add timeout to fetch
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 45000); // 45 second timeout

        const response = await fetch('/api/quiz/generate', {
            method: 'POST',
            body: formData,
            signal: controller.signal
        });

        clearTimeout(timeoutId);

        const data = await response.json();
        
        if (data.success && data.question) {
            quizState.lastQuestion = data.question;
            questionEl.textContent = data.question;
            speakText(data.question);
        } else {
            questionEl.textContent = 'Failed to generate question: ' + (data.error || 'Unknown error');
        }
    } catch (error) {
        console.error('Error generating question:', error);
        questionEl.textContent = 'Error connecting to AI. Please check your connection.';
    }
}

function toggleVoiceInput() {
    if (!quizState.recognition) {
        initSpeechRecognition();
    }

    if (!quizState.recognition) {
        alert('Speech recognition is not supported in your browser.');
        return;
    }

    if (quizState.isListening) {
        stopVoiceInput();
    } else {
        startVoiceInput();
    }
}

function startVoiceInput() {
    if (quizState.synthesis) quizState.synthesis.cancel();
    
    try {
        quizState.recognition.start();
        quizState.isListening = true;
        document.getElementById('voice-status').textContent = 'Listening... Speak now!';
        updateMicButton();
    } catch (e) {
        console.error('Failed to start recognition:', e);
    }
}

function stopVoiceInput() {
    if (quizState.recognition) {
        quizState.recognition.stop();
    }
    quizState.isListening = false;
    updateMicButton();
}

function updateMicButton() {
    const micBtn = document.getElementById('mic-btn');
    if (quizState.isListening) {
        micBtn.style.background = '#ef4444';
        micBtn.style.color = 'white';
        micBtn.innerHTML = '<i class="fas fa-stop"></i> Stop Listening';
    } else {
        micBtn.style.background = 'white';
        micBtn.style.color = '#667eea';
        micBtn.innerHTML = '<i class="fas fa-microphone"></i> Click to Answer (Voice)';
    }
}

async function evaluateAnswer(answer) {
    const feedbackEl = document.getElementById('feedback-display');
    const feedbackTitle = document.getElementById('feedback-title');
    const feedbackText = document.getElementById('feedback-text');
    const nextBtnContainer = document.getElementById('next-btn-container');
    const voiceStatus = document.getElementById('voice-status');

    voiceStatus.textContent = 'Evaluating your answer...';
    
    try {
        const formData = new FormData();
        formData.append('script_id', SCRIPT_ID);
        formData.append('question', quizState.lastQuestion);
        formData.append('answer', answer);

        // Add timeout to fetch
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 45000); // 45 second timeout

        const response = await fetch('/api/quiz/evaluate', {
            method: 'POST',
            body: formData,
            signal: controller.signal
        });

        clearTimeout(timeoutId);

        const data = await response.json();
        
        if (data.success) {
            const isCorrect = data.is_correct;
            const explanation = data.explanation;

            feedbackEl.style.display = 'block';
            if (isCorrect) {
                quizState.score++;
                feedbackEl.style.background = 'rgba(34, 197, 94, 0.2)';
                feedbackTitle.innerHTML = '<i class="fas fa-check-circle"></i> Correct!';
                feedbackTitle.style.color = '#4ade80';
            } else {
                feedbackEl.style.background = 'rgba(239, 68, 68, 0.2)';
                feedbackTitle.innerHTML = '<i class="fas fa-times-circle"></i> Not quite';
                feedbackTitle.style.color = '#fca5a5';
            }
            
            feedbackText.innerHTML = explanation;
            renderFeedbackMath();
            voiceStatus.textContent = 'Evaluation complete.';
            nextBtnContainer.style.display = 'block';
            
            updateScoreDisplay();
            speakText((isCorrect ? 'Correct. ' : 'Not quite. ') + explanation);
        } else {
            voiceStatus.textContent = 'Error: ' + (data.error || 'Evaluation failed');
        }
    } catch (error) {
        console.error('Error evaluating answer:', error);
        voiceStatus.textContent = 'Error evaluating answer.';
    }
}

function nextQuestion() {
    quizState.currentQuestion++;
    
    if (quizState.currentQuestion >= quizState.totalQuestions) {
        showResults();
    } else {
        updateProgress();
        generateQuestion();
    }
}

function updateProgress() {
    const progressText = document.getElementById('quiz-progress-text');
    const progressBar = document.getElementById('quiz-progress-bar');
    
    progressText.textContent = `Question ${quizState.currentQuestion + 1} of ${quizState.totalQuestions}`;
    const percent = ((quizState.currentQuestion) / quizState.totalQuestions) * 100;
    progressBar.style.width = percent + '%';
}

function updateScoreDisplay() {
    document.getElementById('quiz-score').textContent = `Score: ${quizState.score}/${quizState.currentQuestion + 1}`;
}

function showResults() {
    document.getElementById('quiz-progress').style.display = 'none';
    document.getElementById('question-container').style.display = 'none';
    document.getElementById('voice-controls').style.display = 'none';
    document.getElementById('answer-display').style.display = 'none';
    document.getElementById('feedback-display').style.display = 'none';
    document.getElementById('next-btn-container').style.display = 'none';
    
    const resultsEl = document.getElementById('quiz-results');
    resultsEl.style.display = 'block';
    
    document.getElementById('final-score').textContent = quizState.score;
    document.getElementById('total-questions').textContent = quizState.totalQuestions;
    
    const percentage = Math.round((quizState.score / quizState.totalQuestions) * 100);
    document.getElementById('score-percentage').textContent = percentage + '%';
    
    let message = '';
    if (percentage === 100) message = 'Perfect! You have mastered this content.';
    else if (percentage >= 80) message = 'Great job! You have a strong understanding.';
    else if (percentage >= 50) message = 'Good effort! A bit more review and you will be there.';
    else message = 'Keep studying! Review the memorandum and try again.';
    
    document.getElementById('score-message').textContent = message;
    
    speakText(`Quiz complete. Your final score is ${quizState.score} out of ${quizState.totalQuestions}. ${message}`);
}

function restartQuiz() {
    startQuiz();
}

function speakText(text) {
    if (!quizState.synthesis) return;
    
    quizState.synthesis.cancel();
    const utterance = new SpeechSynthesisUtterance(text);
    utterance.lang = 'en-US';
    utterance.rate = 1.0;
    utterance.pitch = 1.0;
    
    // Choose a better voice if available
    const voices = quizState.synthesis.getVoices();
    const preferredVoice = voices.find(v => v.name.includes('Google') || v.name.includes('Natural'));
    if (preferredVoice) utterance.voice = preferredVoice;
    
    quizState.synthesis.speak(utterance);
}

// Handle voices changed event
if (window.speechSynthesis) {
    window.speechSynthesis.onvoiceschanged = () => {
        // Just to ensure voices are loaded
    };
}

function renderFeedbackMath() {
    const feedback = document.getElementById("feedback-text");
    if (!feedback) return;
    let content = feedback.innerHTML;
    // Convert common math into LaTeX-friendly syntax
    content = content
        .replace(/1\/2/g, "\\frac{1}{2}")
        .replace(/x1/g, "x_1")
        .replace(/x2/g, "x_2")
        .replace(/x3/g, "x_3")
        .replace(/y1/g, "y_1")
        .replace(/y2/g, "y_2")
        .replace(/y3/g, "y_3");
    feedback.innerHTML = content;
    renderMathInElement(feedback, {
        delimiters: [
            { left: "$$", right: "$$", display: true },
            { left: "$", right: "$", display: false },
            { left: "\\(", right: "\\)", display: false },
            { left: "\\[", right: "\\]", display: true }
        ],
        throwOnError: false
    });
}
