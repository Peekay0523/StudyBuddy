// Quiz and Voice Recognition Variables
let quizQuestions = [];
let currentQuestionIndex = 0;
let score = 0;
let answeredCount = 0;
let recognition = null;
let isListening = false;
let userAnswer = "";

// Generate quiz questions from memorandum content
function generateQuizQuestions() {
    const memoContent = document.getElementById("memo-content").textContent;
    const topics = window.QUIZ_TOPICS || [];

    // Generate questions based on topics and content
    const questions = [];

    // Question type 1: Extract Q&A pairs from memorandum (Question X.X.X: ... Answer: ...)
    var qaPairs = extractQuestionAnswerPairs(memoContent);
    qaPairs.forEach(function(qa) {
        questions.push({
            question: qa.question,
            answer: qa.answer,
            type: "direct-recall",
            keywords: qa.answer.toLowerCase().split(/\s+/).filter(function(w) { return w.length > 3; })
        });
    });

    // Question type 2: "What is [key term]?" questions from extracted concepts
    var keyTerms = extractKeyTerms(memoContent);
    keyTerms.forEach(function(term) {
        questions.push({
            question: "What is " + term + "?",
            answer: term,
            type: "definition",
            keywords: [term.toLowerCase()]
        });
    });

    // Question type 3: Definition questions from topics
    if (topics.length > 0) {
        topics.forEach(function(topic) {
            // Find content related to this topic from memorandum
            var topicContent = findTopicContentInMemo(memoContent, topic);
            questions.push({
                question: "Define or explain: " + topic,
                answer: topicContent, // Store the full explanation, not just the topic name
                answerShort: topic, // Keep short version for keyword matching
                type: "topic-definition",
                keywords: topic.toLowerCase().split(/\s+/).filter(function(w) { return w.length > 3; })
            });
        });
    }

    // Question type 4: "What is the term for..." based on definitions in memorandum
    var definitionQuestions = extractDefinitionQuestions(memoContent);
    definitionQuestions.forEach(function(def) {
        questions.push({
            question: def.question,
            answer: def.term,
            type: "term-identification",
            keywords: [def.term.toLowerCase()]
        });
    });

    // Shuffle and select 5 questions
    var shuffled = questions.sort(function() { return 0.5 - Math.random(); });
    return shuffled.slice(0, Math.min(5, shuffled.length));
}

// Extract Q&A pairs from memorandum (e.g., "Question 1.2.8: ... Answer: Glyphosate")
function extractQuestionAnswerPairs(content) {
    var qaPairs = [];
    var lines = content.split(/\n+/);
    
    for (var i = 0; i < lines.length - 1; i++) {
        var line = lines[i].trim();
        var nextLine = lines[i + 1] ? lines[i + 1].trim() : "";
        
        // Match patterns like "Question 1.2.8: The plant hormone..."
        var questionMatch = line.match(/^(?:Question|Q)\s*[\d.]+[:.]?\s*(.+)$/i);
        
        if (questionMatch) {
            var questionText = questionMatch[1];
            var answer = "";
            
            // Look for answer on next line or after "Answer:"
            var answerMatch = nextLine.match(/^(?:Answer|Ans)[:\s]+(.+)$/i);
            if (answerMatch) {
                answer = answerMatch[1].trim();
            } else if (nextLine.length > 0 && nextLine.length < 100) {
                // If next line is short, it might be the answer
                answer = nextLine;
            }
            
            if (answer && answer.length > 2) {
                qaPairs.push({
                    question: questionText,
                    answer: answer
                });
            }
        }
    }
    
    return qaPairs.slice(0, 3); // Max 3 Q&A questions
}

// Extract key terms (capitalized, technical terms) from memorandum
function extractKeyTerms(content) {
    var keyTerms = [];
    
    // Common words to exclude
    var excludeWords = ["answer", "question", "correct", "incorrect", "score", "complete", "following", "calculate", 
                        "determine", "find", "show", "given", "using", "based", "state", "name",
                        "explain", "describe", "discuss", "identify", "list", "define", "what", "which", "where",
                        "when", "why", "how", "this", "that", "these", "those", "their", "there", "they", "them",
                        "from", "with", "without", "within", "through", "throughout", "between", "during", "after",
                        "before", "above", "below", "under", "over", "into", "onto", "upon", "about", "around",
                        "against", "among", "along", "across", "behind", "beyond", "beside", "beneath", "besides",
                        "including", "despite", "although", "though", "because", "whether", "unless", "until",
                        "while", "whereas", "however", "therefore", "moreover", "furthermore", "otherwise",
                        "nevertheless", "meanwhile", "consequently", "accordingly", "thus", "hence", "then",
                        "also", "too", "very", "just", "only", "even", "still", "yet", "now", "here", "well",
                        "much", "many", "some", "any", "all", "both", "few", "more", "most", "other", "such",
                        "than", "each", "every", "either", "neither", "several", "enough", "quite", "rather",
                        "almost", "already", "always", "never", "sometimes", "usually", "often", "generally",
                        "being", "having", "doing", "been", "have", "has", "had", "does", "did",
                        "was", "were", "are", "is", "do", "will", "would",
                        "could", "should", "may", "might", "must", "shall", "can", "need", "dare", "ought",
                        "used", "one", "two", "three", "four", "five", "first", "second", "third", "next",
                        "last", "final", "example", "part", "section", "figure", "table", "note", "total",
                        "number", "mark", "marks", "questions", "answers", "solution",
                        "solutions", "problem", "problems", "formula", "formulas", "formulae", "equation",
                        "equations", "method", "methods", "step", "steps", "result", "results", "work",
                        "working", "showing", "shown", "use", "apply", "applied", "application", "consider",
                        "considered", "provide", "provided", "includes", "include", "included", "make", "makes",
                        "made", "take", "takes", "taken", "give", "gives", "get", "gets", "got", "gotten",
                        "become", "becomes", "see", "sees", "saw", "seen", "look", "looks", "looked", "come",
                        "comes", "came", "go", "goes", "went", "gone", "know", "knows", "knew", "known",
                        "think", "thinks", "thought", "say", "says", "said", "tell", "tells", "told", "speak",
                        "speaks", "spoke", "spoken", "read", "reads", "write", "writes", "written", "learn",
                        "learns", "learned", "study", "studies", "studied", "understand", "understands",
                        "understood", "remember", "remembers", "forgot", "forgotten", "recall", "recalls",
                        "recalled", "recognize", "recognizes", "recognized", "identify", "identifies", "identified"];
    
    var contentLines = content.split(/\n+/);
    
    for (var i = 0; i < contentLines.length; i++) {
        var line = contentLines[i].trim();
        
        // Skip short lines, headers, and bullet points
        if (line.length < 10 || line.length > 150) continue;
        if (line.startsWith("#") || line.startsWith("-") || line.startsWith("*")) continue;
        
        // Find technical/important terms (capitalized words)
        var words = line.split(/\s+/);
        for (var j = 0; j < words.length; j++) {
            var word = words[j].replace(/[^a-zA-Z0-9]/g, "");
            var wordLower = word.toLowerCase();
            
            // Check if it's a meaningful term
            if (word.length > 4 && 
                word.length < 30 &&
                !excludeWords.includes(wordLower) &&
                word[0] === word[0].toUpperCase()) {
                keyTerms.push(word);
            }
        }
    }
    
    // Deduplicate
    var uniqueTerms = keyTerms.filter(function(item, pos) {
        return keyTerms.indexOf(item) === pos;
    });
    
    return uniqueTerms.slice(0, 3); // Max 3 terms
}

// Extract definition-style questions (e.g., "X is defined as..." or "X refers to...")
function extractDefinitionQuestions(content) {
    var definitions = [];
    var sentences = content.split(/[.!?]+/);

    for (var i = 0; i < sentences.length; i++) {
        var sentence = sentences[i].trim();

        // Look for patterns like "X is..." or "X refers to..."
        var match = sentence.match(/^([A-Z][a-zA-Z\s]+?)\s+(?:is|refers to|are|was|were)\s+(.+)$/);

        if (match) {
            var term = match[1].trim();
            var definition = match[2].trim();

            // Only use if term is short (not a full sentence)
            if (term.length > 2 && term.length < 50 && term.split(/\s+/).length <= 5) {
                definitions.push({
                    question: "What is the term for: " + definition + "?",
                    term: term,
                    keywords: [term.toLowerCase()]
                });
            }
        }
    }

    return definitions.slice(0, 2); // Max 2 definition questions
}

// Find content related to a topic in the memorandum
function findTopicContentInMemo(memoContent, topic) {
    var lines = memoContent.split(/\n+/);
    var topicLower = topic.toLowerCase();
    var relevantContent = "";

    // Search for the topic in the memorandum
    for (var i = 0; i < lines.length; i++) {
        var line = lines[i].trim();

        // Check if line contains the topic
        if (line.toLowerCase().includes(topicLower)) {
            // Collect this line and the next few lines (up to 3-4 sentences)
            var contentLines = [];
            var sentenceCount = 0;
            var maxLines = 5;

            for (var j = i; j < Math.min(i + maxLines, lines.length) && sentenceCount < 4; j++) {
                var currentLine = lines[j].trim();
                contentLines.push(currentLine);

                // Count sentences
                var sentences = currentLine.split(/[.!?]+/).filter(function(s) { return s.trim().length > 0; });
                sentenceCount += sentences.length;
            }

            relevantContent = contentLines.join(' ');
            break;
        }
    }

    // If no specific content found, create a general response
    if (!relevantContent || relevantContent.length < 20) {
        relevantContent = "This topic relates to the key concepts covered in the memorandum. Review the memorandum content above for detailed information about " + topic + ".";
    }

    return relevantContent;
}

// Toggle quiz visibility
function toggleQuiz() {
    const quizSection = document.getElementById("quiz-section");
    const quizBtn = document.getElementById("take-quiz-btn");

    if (quizSection.style.display === "none") {
        quizSection.style.display = "block";
        quizBtn.style.display = "none";
        initQuiz();
    } else {
        quizSection.style.display = "none";
        quizBtn.style.display = "inline-block";
    }
}

// Initialize quiz
function initQuiz() {
    quizQuestions = generateQuizQuestions();
    currentQuestionIndex = 0;
    score = 0;
    answeredCount = 0;

    document.getElementById("quiz-progress").style.display = "block";
    document.getElementById("question-container").style.display = "block";
    document.getElementById("voice-controls").style.display = "block";
    document.getElementById("answer-display").style.display = "block";
    document.getElementById("feedback-display").style.display = "none";
    document.getElementById("next-btn-container").style.display = "none";
    document.getElementById("quiz-results").style.display = "none";

    loadQuestion();
}

// Load current question
function loadQuestion() {
    if (currentQuestionIndex >= quizQuestions.length) {
        showResults();
        return;
    }

    const question = quizQuestions[currentQuestionIndex];
    document.getElementById("current-question").textContent = question.question;
    document.getElementById("user-answer").textContent = "";
    document.getElementById("voice-status").textContent = "Click the microphone to answer";
    document.getElementById("mic-btn").innerHTML = '<i class="fas fa-microphone"></i> Click to Answer (Voice)';
    document.getElementById("mic-btn").style.background = "white";
    document.getElementById("mic-btn").style.color = "#667eea";
    document.getElementById("feedback-display").style.display = "none";
    document.getElementById("next-btn-container").style.display = "none";
    document.getElementById("voice-controls").style.display = "block";
    document.getElementById("answer-display").style.display = "block";

    // Update progress
    document.getElementById("quiz-progress-text").textContent = "Question " + (currentQuestionIndex + 1) + " of " + quizQuestions.length;
    document.getElementById("quiz-progress-bar").style.width = ((currentQuestionIndex / quizQuestions.length) * 100) + "%";
    document.getElementById("quiz-score").textContent = "Score: " + score + "/" + answeredCount;

    userAnswer = "";
}

// Toggle voice input
function toggleVoiceInput() {
    if (isListening) {
        stopListening();
    } else {
        startListening();
    }
}

// Start voice recognition
function startListening() {
    // Check for browser support
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;

    if (!SpeechRecognition) {
        document.getElementById("voice-status").textContent = "Voice recognition not supported in this browser";
        alert("Voice recognition is not supported in your browser. Please use Chrome or Edge.");
        return;
    }

    recognition = new SpeechRecognition();
    recognition.lang = "en-US";
    recognition.interimResults = false;
    recognition.maxAlternatives = 1;
    recognition.continuous = false;

    recognition.onstart = function() {
        isListening = true;
        document.getElementById("mic-btn").innerHTML = '<i class="fas fa-circle"></i> Listening...';
        document.getElementById("mic-btn").style.background = "#ef4444";
        document.getElementById("mic-btn").style.color = "white";
        document.getElementById("voice-status").textContent = "Listening... Speak your answer";
    };

    recognition.onresult = function(event) {
        const transcript = event.results[0][0].transcript;
        userAnswer = transcript;
        document.getElementById("user-answer").textContent = transcript;
        document.getElementById("voice-status").textContent = "Answer received!";

        // Evaluate answer after a short delay
        setTimeout(function() {
            evaluateAnswer();
        }, 500);
    };

    recognition.onerror = function(event) {
        console.error("Speech recognition error:", event.error);
        isListening = false;
        document.getElementById("mic-btn").innerHTML = '<i class="fas fa-microphone"></i> Click to Answer (Voice)';
        document.getElementById("mic-btn").style.background = "white";
        document.getElementById("mic-btn").style.color = "#667eea";

        if (event.error === "not-allowed") {
            document.getElementById("voice-status").textContent = "Microphone access denied. Please allow microphone access.";
        } else if (event.error === "no-speech") {
            document.getElementById("voice-status").textContent = "No speech detected. Please try again.";
        } else {
            document.getElementById("voice-status").textContent = "Error: " + event.error + ". Please try again.";
        }
    };

    recognition.onend = function() {
        isListening = false;
        if (userAnswer === "") {
            document.getElementById("mic-btn").innerHTML = '<i class="fas fa-microphone"></i> Click to Answer (Voice)';
            document.getElementById("mic-btn").style.background = "white";
            document.getElementById("mic-btn").style.color = "#667eea";
            document.getElementById("voice-status").textContent = "Click the microphone to answer";
        }
    };

    recognition.start();
}

// Stop listening
function stopListening() {
    if (recognition) {
        recognition.stop();
        isListening = false;
        document.getElementById("mic-btn").innerHTML = '<i class="fas fa-microphone"></i> Click to Answer (Voice)';
        document.getElementById("mic-btn").style.background = "white";
        document.getElementById("mic-btn").style.color = "#667eea";
    }
}

// Evaluate the user's answer
function evaluateAnswer() {
    const currentQuestion = quizQuestions[currentQuestionIndex];
    const userAnswerLower = userAnswer.toLowerCase().trim();
    let isCorrect = false;

    // Different evaluation based on question type
    if (currentQuestion.type === "direct-recall") {
        // For direct recall from memo Q&A, check if answer matches
        const correctAnswer = currentQuestion.answer.toLowerCase();
        const userWords = userAnswerLower.split(/\s+/);
        
        // Exact or near-exact match
        if (userAnswerLower.includes(correctAnswer) || correctAnswer.includes(userAnswerLower)) {
            isCorrect = true;
        }
        
        // Keyword match
        if (!isCorrect && currentQuestion.keywords.length > 0) {
            let matchCount = 0;
            currentQuestion.keywords.forEach(function(word) {
                if (userWords.some(function(uw) { return uw.includes(word) || word.includes(uw); })) {
                    matchCount++;
                }
            });
            isCorrect = matchCount >= Math.ceil(currentQuestion.keywords.length / 2);
        }
        
    } else if (currentQuestion.type === "definition") {
        // For "What is X?" questions, accept any reasonable definition attempt
        // Since these are open-ended definition questions, reward attempts to explain
        const userWords = userAnswerLower.split(/\s+/);
        const isDetailed = userWords.length >= 3; // At least 3 words
        
        // Avoid just repeating the term itself
        const termLength = currentQuestion.answer.length;
        const notJustTerm = userAnswerLower.length > termLength + 2;
        
        isCorrect = isDetailed && notJustTerm;
        
    } else if (currentQuestion.type === "topic-definition") {
        // For topic definition questions - check if user provided a meaningful explanation
        const userWords = userAnswerLower.split(/\s+/);
        const correctAnswerFull = currentQuestion.answer; // Full explanation from memo
        const correctAnswerShort = currentQuestion.answerShort || ''; // Just the topic name
        
        // Check if user's answer contains key terms from the full explanation
        const fullAnswerKeywords = correctAnswerFull.toLowerCase().split(/\s+/).filter(function(w) { 
            return w.length > 4 && !['which', 'that', 'this', 'these', 'those', 'their', 'there', 'they', 'them', 'with', 'from', 'have', 'has', 'had', 'been', 'being', 'were', 'was', 'are', 'is', 'was', 'will', 'would', 'could', 'should'].includes(w); 
        });
        
        let keywordMatchCount = 0;
        fullAnswerKeywords.forEach(function(keyword) {
            if (userAnswerLower.includes(keyword)) {
                keywordMatchCount++;
            }
        });
        
        // User is correct if they have enough detail and match at least 30% of keywords
        const isDetailed = userWords.length >= 5; // At least 5 words
        const hasEnoughKeywords = keywordMatchCount >= Math.max(2, Math.floor(fullAnswerKeywords.length * 0.3));
        
        isCorrect = isDetailed && hasEnoughKeywords;

    } else if (currentQuestion.type === "term-identification") {
        // For "What is the term for..." questions
        const correctTerm = currentQuestion.answer.toLowerCase();
        
        if (userAnswerLower.includes(correctTerm) || correctTerm.includes(userAnswerLower)) {
            isCorrect = true;
        }
        
    } else {
        // Default evaluation for other question types
        const correctAnswer = currentQuestion.answer.toLowerCase();
        const correctWords = correctAnswer.split(/\s+/).filter(function(w) { return w.length > 3; });
        const userWords = userAnswerLower.split(/\s+/);

        // Exact match
        if (userAnswerLower.includes(correctAnswer) || correctAnswer.includes(userAnswerLower)) {
            isCorrect = true;
        }

        // Keyword match (at least 50% of key words match)
        if (!isCorrect && correctWords.length > 0) {
            let matchCount = 0;
            correctWords.forEach(function(word) {
                if (userWords.some(function(uw) { return uw.includes(word) || word.includes(uw); })) {
                    matchCount++;
                }
            });
            
            if (matchCount / correctWords.length >= 0.5) {
                isCorrect = true;
            }
        }
    }

    // Show feedback
    const feedbackDisplay = document.getElementById("feedback-display");
    const feedbackTitle = document.getElementById("feedback-title");
    const feedbackText = document.getElementById("feedback-text");

    feedbackDisplay.style.display = "block";

    if (isCorrect) {
        score++;
        feedbackDisplay.style.background = "rgba(34, 197, 94, 0.3)";
        feedbackTitle.innerHTML = '<i class="fas fa-check-circle"></i> Correct!';
        feedbackTitle.style.color = "#86efac";
        feedbackText.textContent = "Excellent! Your answer shows good understanding of the material.";

        // Speak feedback
        speakFeedback("Correct! Well done.");
    } else {
        feedbackDisplay.style.background = "rgba(239, 68, 68, 0.3)";
        feedbackTitle.innerHTML = '<i class="fas fa-times-circle"></i> Needs Improvement';
        feedbackTitle.style.color = "#fca5a5";
        
        // For topic-definition questions, show the full explanation from the memorandum
        if (currentQuestion.type === "topic-definition" && currentQuestion.answer && currentQuestion.answer.length > 30) {
            feedbackText.textContent = "Your answer needs more detail. Just saying '" + (currentQuestion.answerShort || currentQuestion.keywords.join(' ')) + "' is not enough. Here's what the memorandum says: " + currentQuestion.answer;
        } else {
            feedbackText.textContent = "Incorrect. The correct answer is: " + currentQuestion.answer;
        }

        // Speak feedback (speak shorter version for voice)
        let speakText = "Incorrect.";
        if (currentQuestion.type === "topic-definition" && currentQuestion.answer && currentQuestion.answer.length > 30) {
            speakText += " Here's what the memorandum says: " + currentQuestion.answer;
        } else {
            speakText += " The correct answer is: " + currentQuestion.answer;
        }
        speakFeedback(speakText);
    }

    answeredCount++;
    document.getElementById("quiz-score").textContent = "Score: " + score + "/" + answeredCount;

    // Hide voice controls and show next button
    document.getElementById("voice-controls").style.display = "none";
    document.getElementById("next-btn-container").style.display = "block";
}

// Speak feedback using text-to-speech
function speakFeedback(text) {
    const synthesis = window.speechSynthesis;
    if (synthesis) {
        const utterance = new SpeechSynthesisUtterance(text);
        utterance.lang = "en-US";
        utterance.rate = 0.95;
        synthesis.speak(utterance);
    }
}

// Go to next question
function nextQuestion() {
    currentQuestionIndex++;
    loadQuestion();
}

// Show quiz results
function showResults() {
    document.getElementById("quiz-progress").style.display = "none";
    document.getElementById("question-container").style.display = "none";
    document.getElementById("voice-controls").style.display = "none";
    document.getElementById("answer-display").style.display = "none";
    document.getElementById("feedback-display").style.display = "none";
    document.getElementById("next-btn-container").style.display = "none";
    document.getElementById("quiz-results").style.display = "block";

    const percentage = Math.round((score / quizQuestions.length) * 100);
    document.getElementById("final-score").textContent = score;
    document.getElementById("total-questions").textContent = quizQuestions.length;
    document.getElementById("score-percentage").textContent = percentage + "%";

    let message = "";
    if (percentage >= 80) {
        message = "Excellent! You have a great understanding of the material!";
        document.getElementById("score-percentage").style.color = "#22c55e";
    } else if (percentage >= 60) {
        message = "Good job! Keep studying to improve your knowledge!";
        document.getElementById("score-percentage").style.color = "#3b82f6";
    } else if (percentage >= 40) {
        message = "Not bad, but you may want to review the memorandum again.";
        document.getElementById("score-percentage").style.color = "#f59e0b";
    } else {
        message = "You should review the memorandum content more carefully.";
        document.getElementById("score-percentage").style.color = "#ef4444";
    }

    document.getElementById("score-message").textContent = message;

    // Speak results
    speakFeedback("Quiz complete! You scored " + score + " out of " + quizQuestions.length + ", that is " + percentage + " percent.");
}

// Restart quiz
function restartQuiz() {
    initQuiz();
}
