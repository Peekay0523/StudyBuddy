<!-- Ask App - Smart Search Assistant -->
<!-- Floating Search Button + Modal -->

<!-- Floating Button -->
<button id="askAppBtn" class="ask-app-fab" title="Ask App - Smart Search" aria-label="Open Search">
    <i class="fas fa-search"></i>
</button>

<!-- Search Modal -->
<div id="askAppModal" class="ask-app-modal" role="dialog" aria-label="Ask App Search">
    <div class="ask-app-backdrop" id="askAppBackdrop"></div>
    <div class="ask-app-dialog">
        <!-- Search Header -->
        <div class="ask-app-header">
            <div class="ask-app-search-bar">
                <i class="fas fa-search ask-app-search-icon"></i>
                <input 
                    type="text" 
                    id="askAppInput" 
                    class="ask-app-input" 
                    placeholder="Ask App anything..." 
                    autocomplete="off"
                    autofocus
                />
                <button id="askAppVoiceBtn" class="ask-app-voice-btn" title="Voice Search" aria-label="Voice Search">
                    <i class="fas fa-microphone"></i>
                </button>
                <button id="askAppCloseBtn" class="ask-app-close-btn" title="Close" aria-label="Close Search">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div id="askAppVoiceStatus" class="ask-app-voice-status" style="display:none;">
                <i class="fas fa-circle" style="color:#ef4444; animation: askPulse 1s infinite;"></i>
                <span>Listening...</span>
            </div>
        </div>

        <!-- Suggestions (shown when empty or typing) -->
        <div id="askAppSuggestions" class="ask-app-suggestions">
            <div id="askAppDefaultSuggestions" class="ask-app-suggestion-group">
                <p class="ask-app-suggestion-label">Popular searches</p>
                <div id="askAppDefaultList" class="ask-app-suggestion-chips"></div>
            </div>
            <div id="askAppTypingSuggestions" class="ask-app-suggestion-group" style="display:none;">
                <p class="ask-app-suggestion-label">Suggestions</p>
                <ul id="askAppTypingList" class="ask-app-suggestion-list"></ul>
            </div>
        </div>

        <!-- Results -->
        <div id="askAppResults" class="ask-app-results" style="display:none;">
            <div id="askAppResultsList" class="ask-app-results-list"></div>
        </div>

        <!-- No Results -->
        <div id="askAppNoResults" class="ask-app-no-results" style="display:none;">
            <i class="fas fa-search" style="font-size:2rem; color:#cbd5e1; margin-bottom:12px;"></i>
            <p class="ask-app-no-results-title">Sorry, I could not find that content in this application.</p>
            <p class="ask-app-no-results-sub">Try different keywords or check the suggestions below.</p>
            <div id="askAppSimilarResults" class="ask-app-similar-results"></div>
        </div>

        <!-- Footer -->
        <div class="ask-app-footer">
            <span><kbd>Enter</kbd> to search</span>
            <span><kbd>Esc</kbd> to close</span>
            <span title='Try voice: "Check doppler effect" or "Open scanned docs" or "Create maths study plan"'><kbd>🎤</kbd> Voice commands</span>
        </div>
    </div>
</div>

<style>
/* Ask App Search - Floating Button */
.ask-app-fab {
    position: fixed;
    bottom: 100px;
    right: 20px;
    width: 56px;
    height: 56px;
    padding: 0 !important;
    border: none !important;
    border-radius: 50% !important;
    background: linear-gradient(135deg, #6366f1, #8b5cf6) !important;
    color: #fff !important;
    cursor: pointer;
    z-index: 10001;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    line-height: 1;
    box-shadow: 0 8px 25px rgba(99, 102, 241, 0.4) !important;
    transition: transform .3s cubic-bezier(.175, .885, .32, 1.275), box-shadow .3s ease !important;
}

.ask-app-fab i {
    line-height: 1;
}

.ask-app-fab:hover {
    transform: scale(1.08) translateY(-2px) !important;
    box-shadow: 0 12px 30px rgba(99, 102, 241, 0.5) !important;
}

.ask-app-fab:active {
    transform: scale(.95) !important;
    box-shadow: 0 4px 14px rgba(99, 102, 241, 0.45) !important;
}

.ask-app-fab:focus-visible,
.ask-app-voice-btn:focus-visible,
.ask-app-close-btn:focus-visible {
    outline: 3px solid rgba(99, 102, 241, .35);
    outline-offset: 2px;
}

@media (max-width: 768px) {
    .ask-app-fab {
        bottom: 90px;
        right: 16px;
        width: 50px;
        height: 50px;
        font-size: 20px;
    }
}

/* Ask App Modal */
.ask-app-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 10002;
    justify-content: center;
    align-items: flex-start;
    padding-top: 5vh;
}

.ask-app-modal.active {
    display: flex;
}

.ask-app-backdrop {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
    animation: askFadeIn 0.2s ease;
}

.ask-app-dialog {
    position: relative;
    width: 95%;
    max-width: 600px;
    max-height: 80vh;
    background: white;
    border-radius: 20px;
    box-shadow: 0 25px 60px rgba(0, 0, 0, 0.3);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    animation: askSlideIn 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

/* Search Header */
.ask-app-header {
    padding: 16px;
    border-bottom: 1px solid #f1f5f9;
}

.ask-app-search-bar {
    display: flex;
    align-items: center;
    gap: 10px;
    background: #f8fafc;
    border: 2px solid #e2e8f0;
    border-radius: 14px;
    padding: 4px 12px;
    transition: border-color 0.2s;
}

.ask-app-search-bar:focus-within {
    border-color: #6366f1;
    background: white;
}

.ask-app-search-icon {
    color: #94a3b8;
    font-size: 16px;
    flex-shrink: 0;
}

.ask-app-input {
    flex: 1;
    border: none;
    background: transparent;
    font-size: 16px;
    padding: 12px 0;
    outline: none;
    color: #1e293b;
    font-family: inherit;
}

.ask-app-input::placeholder {
    color: #94a3b8;
}

.ask-app-voice-btn, .ask-app-close-btn {
    width: 36px;
    height: 36px;
    padding: 0 !important;
    border: none !important;
    border-radius: 50% !important;
    background: #f1f5f9;
    color: #475569;
    box-shadow: none !important;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 15px;
    line-height: 1;
    flex-shrink: 0;
    transition: background .2s ease, color .2s ease, transform .2s ease !important;
}

.ask-app-voice-btn i, .ask-app-close-btn i {
    line-height: 1;
}

.ask-app-voice-btn:hover {
    background: #e0e7ff;
    color: #4f46e5;
    transform: scale(1.06);
}

.ask-app-voice-btn:active {
    transform: scale(.92) !important;
}

.ask-app-voice-btn.listening {
    background: #fee2e2 !important;
    color: #ef4444 !important;
    box-shadow: 0 0 0 0 rgba(239, 68, 68, .45) !important;
    animation: askPulse 1s infinite;
}

.ask-app-close-btn:hover {
    background: #e2e8f0;
    color: #0f172a;
    transform: rotate(90deg);
}

.ask-app-close-btn:active {
    transform: scale(.92) !important;
}

.ask-app-voice-btn:focus-visible,
.ask-app-close-btn:focus-visible,
.ask-app-fab:focus-visible {
    outline: 3px solid rgba(99, 102, 241, .35);
    outline-offset: 2px;
}

.ask-app-voice-status {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    font-size: 0.85rem;
    color: #ef4444;
    font-weight: 600;
}

/* Suggestions */
.ask-app-suggestions {
    overflow-y: auto;
    max-height: 50vh;
    padding: 8px 0;
}

.ask-app-suggestion-group {
    padding: 8px 16px;
}

.ask-app-suggestion-label {
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #94a3b8;
    margin: 0 0 10px 0;
}

.ask-app-suggestion-chips {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.ask-app-chip {
    padding: 8px 14px;
    border-radius: 20px;
    background: #f1f5f9;
    color: #475569;
    font-size: 0.85rem;
    cursor: pointer;
    transition: all 0.2s;
    border: 1px solid transparent;
    white-space: nowrap;
}

.ask-app-chip:hover {
    background: #ede9fe;
    color: #6366f1;
    border-color: #c4b5fd;
}

.ask-app-suggestion-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.ask-app-suggestion-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    cursor: pointer;
    transition: background 0.15s;
    border-radius: 12px;
    margin: 2px 0;
}

.ask-app-suggestion-item:hover {
    background: #f8fafc;
}

.ask-app-suggestion-item i {
    color: #94a3b8;
    font-size: 14px;
    width: 20px;
    text-align: center;
}

.ask-app-suggestion-item span {
    font-size: 0.95rem;
    color: #334155;
}

/* Results */
.ask-app-results {
    overflow-y: auto;
    max-height: 55vh;
    padding: 8px 12px;
}

.ask-app-results-list {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.ask-app-result-item {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 12px 14px;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.15s;
    text-decoration: none;
    color: inherit;
}

.ask-app-result-item:hover {
    background: #f0f0ff;
}

.ask-app-result-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: #f1f5f9;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    color: #6366f1;
    flex-shrink: 0;
}

.ask-app-result-info {
    flex: 1;
    min-width: 0;
}

.ask-app-result-title {
    font-size: 0.95rem;
    font-weight: 600;
    color: #1e293b;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.ask-app-result-category {
    font-size: 0.75rem;
    color: #94a3b8;
    margin-top: 2px;
}

.ask-app-result-arrow {
    color: #cbd5e1;
    font-size: 14px;
    flex-shrink: 0;
}

/* No Results */
.ask-app-no-results {
    padding: 40px 20px;
    text-align: center;
}

.ask-app-no-results-title {
    font-size: 1rem;
    font-weight: 600;
    color: #475569;
    margin: 0 0 6px 0;
}

.ask-app-no-results-sub {
    font-size: 0.85rem;
    color: #94a3b8;
    margin: 0;
}

.ask-app-similar-results {
    margin-top: 16px;
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    justify-content: center;
}

/* Footer */
.ask-app-footer {
    display: flex;
    justify-content: center;
    gap: 20px;
    padding: 10px 16px;
    border-top: 1px solid #f1f5f9;
    font-size: 0.75rem;
    color: #94a3b8;
}

.ask-app-footer kbd {
    display: inline-block;
    padding: 2px 6px;
    border-radius: 4px;
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    font-family: inherit;
    font-size: 0.7rem;
    color: #64748b;
}

/* Animations */
@keyframes askFadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes askSlideIn {
    from { opacity: 0; transform: translateY(-20px) scale(0.95); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}

@keyframes askPulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.4; }
}

/* Mobile adjustments */
@media (max-width: 768px) {
    .ask-app-modal {
        padding-top: 0;
        align-items: flex-end;
    }
    
    .ask-app-dialog {
        max-width: 100%;
        max-height: 90vh;
        border-radius: 20px 20px 0 0;
        animation: askSlideUp 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }
    
    .ask-app-footer {
        padding-bottom: max(10px, env(safe-area-inset-bottom));
    }
}

@keyframes askSlideUp {
    from { transform: translateY(100%); }
    to { transform: translateY(0); }
}
</style>

<script>
(function() {
    const fab = document.getElementById('askAppBtn');
    const modal = document.getElementById('askAppModal');
    const backdrop = document.getElementById('askAppBackdrop');
    const input = document.getElementById('askAppInput');
    const closeBtn = document.getElementById('askAppCloseBtn');
    const voiceBtn = document.getElementById('askAppVoiceBtn');
    const voiceStatus = document.getElementById('askAppVoiceStatus');
    const suggestionsEl = document.getElementById('askAppSuggestions');
    const defaultSuggestions = document.getElementById('askAppDefaultSuggestions');
    const defaultList = document.getElementById('askAppDefaultList');
    const typingSuggestions = document.getElementById('askAppTypingSuggestions');
    const typingList = document.getElementById('askAppTypingList');
    const resultsEl = document.getElementById('askAppResults');
    const resultsList = document.getElementById('askAppResultsList');
    const noResultsEl = document.getElementById('askAppNoResults');
    const similarResults = document.getElementById('askAppSimilarResults');

    let searchTimeout = null;
    let recognition = null;
    let isListening = false;

    // Default suggestions data
    const defaultSuggestionsData = [
        'Grade 12 Scripts', 'Mathematics', 'Study Plan', 'Career Recommendations',
        'Calculator', 'Memorandum', 'AI Chat', 'Scan to PDF', 'Bursaries', 'Apply For Me'
    ];

    // =============================================
    // VOICE COMMAND NAVIGATION SYSTEM
    // =============================================

    // Route mapping: keywords/aliases → { url, action? }
    const navigationRoutes = {
        // Home
        'home': { url: '/' },
        'landing': { url: '/' },
        'main page': { url: '/' },
        'start': { url: '/' },

        // Dashboard
        'dashboard': { url: '/dashboard' },
        'profile': { url: '/profile' },
        'my account': { url: '/profile' },
        'overview': { url: '/dashboard' },

        // Upload Script
        'upload script': { url: '/upload-script' },
        'upload': { url: '/upload-script' },
        'upload document': { url: '/upload-script' },
        'upload paper': { url: '/upload-script' },
        'upload memorandum': { url: '/upload-script' },
        'upload file': { url: '/upload-script' },

        // Browse Scripts
        'browse scripts': { url: '/browse-scripts/12' },
        'past papers': { url: '/browse-scripts/12' },
        'scripts': { url: '/browse-scripts/12' },
        'papers': { url: '/browse-scripts/12' },
        'documents': { url: '/browse-scripts/12' },

        // Grade-specific content (dynamic - handled in parseVoiceCommand)
        // Grade 8-12 patterns are handled separately

        // Study Plan
        'study plan': { url: '/study-plan' },
        'timetable': { url: '/study-plan' },
        'schedule': { url: '/study-plan' },
        'revision plan': { url: '/study-plan' },
        'calendar': { url: '/study-plan' },
        'study timetable': { url: '/study-plan' },
        'revision timetable': { url: '/study-plan' },

        // Study Group
        'study group': { url: '/study-group' },
        'group chat': { url: '/study-group' },
        'collaborate': { url: '/study-group' },
        'study groups': { url: '/study-group' },

        // Scan
        'scan': { url: '/scan' },
        'scan to pdf': { url: '/scan' },
        'scanner': { url: '/scan' },
        'camera': { url: '/scan' },
        'ocr': { url: '/scan' },
        'pdf scanner': { url: '/scan' },

        // Report Card
        'upload report card': { url: '/upload-report-card' },
        'report card': { url: '/upload-report-card' },
        'grades': { url: '/upload-report-card' },
        'academic results': { url: '/upload-report-card' },

        // Career
        'career recommendations': { url: '/upload-report-card' },
        'career': { url: '/upload-report-card' },
        'careers': { url: '/upload-report-card' },
        'career guidance': { url: '/upload-report-card' },
        'professions': { url: '/upload-report-card' },

        // AI Chat
        'ai chat': { url: '/ai-chat' },
        'chat': { url: '/ai-chat' },
        'assistant': { url: '/ai-chat' },
        'ai assistant': { url: '/ai-chat' },
        'ask question': { url: '/ai-chat' },
        'help': { url: '/ai-chat' },

        // Simulate
        'simulate': { url: '/simulate' },
        'simulation': { url: '/simulate' },
        'exam': { url: '/simulate' },
        'practice test': { url: '/simulate' },
        'mock exam': { url: '/simulate' },
        'practice': { url: '/simulate' },

        // Data Sheets
        'data sheets': { url: '/data-sheets' },
        'formula sheet': { url: '/data-sheets' },
        'formulas': { url: '/data-sheets' },
        'reference': { url: '/data-sheets' },
        'cheat sheet': { url: '/data-sheets' },

        // Subscription
        'subscription': { url: '/subscription' },
        'pricing': { url: '/subscription' },
        'plans': { url: '/subscription' },
        'premium': { url: '/subscription' },
        'upgrade': { url: '/subscription' },
        'payment': { url: '/subscription' },

        // Apply For Me
        'apply for me': { url: '/apply-for-me' },
        'apply': { url: '/apply-for-me' },
        'application': { url: '/apply-for-me' },
        'applications': { url: '/my-applications' },
        'my applications': { url: '/my-applications' },
        'university application': { url: '/apply-for-me' },
        'bursary application': { url: '/dashboard#open-bursary' },

        // Subjects
        'math': { url: '/math' },
        'mathematics': { url: '/math' },
        'maths': { url: '/math' },
        'math lab': { url: '/math' },
        'mathematics lab': { url: '/math' },

        'physics': { url: '/physics' },
        'physics lab': { url: '/physics' },

        'chemistry': { url: '/chemistry' },
        'chemistry lab': { url: '/chemistry' },

        // Quiz
        'quizzes': { url: '/simulate' },
        'make a quiz': { url: '/simulate' },
        'create quiz': { url: '/simulate' },
        'practice quiz': { url: '/simulate' },
        'quiz section': { url: '/simulate' },
        'test myself': { url: '/simulate' },
        'mock test': { url: '/simulate' },

        // Calculator (action-based)
        'calculator': { url: '#calculator', action: 'toggleCalculator' },
        'calc': { url: '#calculator', action: 'toggleCalculator' },

        // Bursaries
        'bursaries': { url: '/dashboard#open-bursary' },
        'bursary': { url: '/dashboard#open-bursary' },
        'bursary applications': { url: '/dashboard#open-bursary' },
        'my bursaries': { url: '/dashboard#open-bursary' },
        'applied bursaries': { url: '/dashboard#open-bursary' },
        'bursaries applied': { url: '/dashboard#open-bursary' },
        'scholarships': { url: '/upload-report-card' },
        'scholarship': { url: '/upload-report-card' },
        'financial aid': { url: '/upload-report-card' },
        'funding': { url: '/upload-report-card' },

        // Institution Applications
        'institution applications': { url: '/dashboard#open-institution' },
        'my institutions': { url: '/dashboard#open-institution' },
        'university applications': { url: '/dashboard#open-institution' },
        'applications status': { url: '/dashboard#open-institution' },
        'my applications': { url: '/my-applications' },

        // Memorandum
        'memorandum': { url: '/browse-scripts/12' },
        'memorandums': { url: '/browse-scripts/12' },
        'memos': { url: '/browse-scripts/12' },
        'memo': { url: '/browse-scripts/12' },
        'answers': { url: '/browse-scripts/12' },
        'solutions': { url: '/browse-scripts/12' },

        // Login / Register
        'login': { url: '/login' },
        'sign in': { url: '/login' },
        'log in': { url: '/login' },
        'register': { url: '/register' },
        'sign up': { url: '/register' },
        'create account': { url: '/register' },

        // Parent Dashboard
        'parent dashboard': { url: '/parent-dashboard' },
        'parent': { url: '/parent-dashboard' },
        'parent progress': { url: '/parent-dashboard' },
    };

    // Institution aliases → Apply For Me
    const institutionAliases = {
        'tut': 'Tshwane University of Technology',
        'uct': 'University of Cape Town',
        'wits': 'University of the Witwatersrand',
        'up': 'University of Pretoria',
        'tuks': 'University of Pretoria',
        'uj': 'University of Johannesburg',
        'stellenbosch': 'Stellenbosch University',
        'ukzn': 'University of KwaZulu-Natal',
        'ufs': 'University of the Free State',
        'nwu': 'North-West University',
        'ul': 'University of Limpopo',
        'univen': 'University of Venda',
        'cput': 'Cape Peninsula University of Technology',
        'unisa': 'UNISA',
    };

    // Navigation command patterns (regex patterns to detect intent)
    const navigationPatterns = [
        // "go to ..."
        /^(?:go\s+to|g\s+to|goto)\s+(.+)/i,
        // "open ..."
        /^(?:open|launch|start)\s+(.+)/i,
        // "take me to ..."
        /^(?:take\s+me\s+to|take\s+me\s+there)\s*(.*)?/i,
        // "show me ..."
        /^(?:show\s+me|show|display)\s+(.+)/i,
        // "navigate to ..."
        /^(?:navigate\s+to|move\s+to|switch\s+to)\s+(.+)/i,
        // "I want to ..."
        /^(?:i\s+want\s+to|i\s+need\s+to|i\s+wanna)\s+(.+)/i,
        // "let me ..."
        /^(?:let\s+me|let\s+me\s+go\s+to|let\s+me\s+see)\s+(.+)/i,
        // "apply to [institution] for me"
        /^(?:apply\s+to|apply\s+at)\s+(.+?)(?:\s+for\s+me)?$/i,
        // "apply for me" (generic)
        /^(?:apply\s+for\s+me|do\s+(?:my\s+)?application)/i,
        // "help me apply"
        /^(?:help\s+me\s+apply|assist\s+me\s+(?:to\s+)?apply)/i,
        // "find [bursary/scholarship]"
        /^(?:find|search\s+for|look\s+for)\s+(?:a\s+)?(?:bursar(?:y|ies)|scholarship|funding|financial\s+aid)/i,
    ];

    // =============================================
    // SYSTEM ACTION COMMANDS (with voice confirmation)
    // =============================================

    // Patterns for actions that require confirmation
    // IMPORTANT: These are checked BEFORE navigation patterns to prevent conflicts
    const systemActionPatterns = [
        // "open scanned docs" / "open my scans" - MUST be before generic "open" pattern
        {
            pattern: /^(?:open|show|view|go\s+to)\s+(?:my\s+)?(?:scan(?:ned)?\s+(?:docs?|uments?|files?|pdfs?)|scans?)/i,
            type: 'open_scans',
            handler: handleOpenScans
        },
        // "scanned docs" / "my scans" - without open prefix
        {
            pattern: /^(?:my\s+)?(?:scan(?:ned)?\s+(?:docs?|uments?|files?|pdfs?)|scans?)/i,
            type: 'open_scans',
            handler: handleOpenScans
        },
        // "create maths study plan" / "create physics study plan"
        {
            pattern: /^(?:create|make|generate|start|set\s+up)\s+(.+?)\s+(?:study\s+plan|revision\s+plan|timetable|schedule)/i,
            type: 'create_study_plan',
            handler: handleCreateStudyPlan
        },
        // "check hydrogen molecules" / "check doppler effect" - use "check" only (not "open"/"show" to avoid nav conflicts)
        {
            pattern: /^(?:check|explain|tell\s+me\s+about|what\s+(?:is|are|about)|describe)\s+(.+)/i,
            type: 'content_check',
            handler: handleContentCheck
        },
        // "create quiz" / "make me a quiz" / "generate a math quiz"
        {
            pattern: /^(?:create|make|generate|start|set\s+up|give\s+me|write)\s+(?:me\s+|a\s+|an\s+|the\s+)?(.+?)\s+quiz(?:\s+for\s+me)?$/i,
            type: 'create_quiz',
            handler: handleCreateQuiz
        },
        // "quiz me on physics" / "quiz me about trigonometry"
        {
            pattern: /^(?:quiz\s+me|test\s+me|ask\s+me)\s+(?:on|about|in)\s+(.+)/i,
            type: 'create_quiz',
            handler: handleCreateQuiz
        },
        // "solve" / "help me solve" / "how to solve"
        {
            pattern: /^(?:solve|help\s+me\s+(?:solve|with)|how\s+(?:do\s+I|to|can\s+I))\s+(.+)/i,
            type: 'ai_help',
            handler: handleAIHelp
        },
        // "summarize" / "summarise"
        {
            pattern: /^(?:summarize|summarise|give\s+me\s+a\s+summary\s+of)\s+(.+)/i,
            type: 'ai_help',
            handler: handleAIHelp
        },
        // "teach me" / "explain how"
        {
            pattern: /^(?:teach\s+me|explain\s+how|how\s+does)\s+(.+)/i,
            type: 'ai_help',
            handler: handleAIHelp
        },
        // "revise" / "revision on" / "revise for"
        {
            pattern: /^(?:revise|revision\s+(?:on|for|about))\s+(.+)/i,
            type: 'ai_help',
            handler: handleAIHelp
        },
    ];

    // Science topics for "check [topic]" voice commands
    const scienceTopics = {
        'pendulum': { subject: 'physics', topic: 'Pendulum' },
        'doppler': { subject: 'physics', topic: 'Doppler Effect' },
        'doppler effect': { subject: 'physics', topic: 'Doppler Effect' },
        'newton': { subject: 'physics', topic: "Newton's Laws" },
        "newton's laws": { subject: 'physics', topic: "Newton's Laws" },
        'gravity': { subject: 'physics', topic: 'Gravitation' },
        'coulomb': { subject: 'physics', topic: "Coulomb's Law" },
        'optics': { subject: 'physics', topic: 'Optics' },
        'circuits': { subject: 'physics', topic: 'Electric Circuits' },
        'electricity': { subject: 'physics', topic: 'Electricity' },
        'magnetism': { subject: 'physics', topic: 'Magnetism' },
        'momentum': { subject: 'physics', topic: 'Momentum' },
        'energy': { subject: 'physics', topic: 'Energy & Work' },
        'waves': { subject: 'physics', topic: 'Waves & Sound' },
        'nuclear': { subject: 'physics', topic: 'Nuclear Physics' },
        'thermodynamics': { subject: 'physics', topic: 'Thermodynamics' },
        'forces': { subject: 'physics', topic: 'Forces' },
        'periodic table': { subject: 'chemistry', topic: 'Periodic Table' },
        'chemical bonding': { subject: 'chemistry', topic: 'Chemical Bonding' },
        'organic chemistry': { subject: 'chemistry', topic: 'Organic Chemistry' },
        'reactions': { subject: 'chemistry', topic: 'Chemical Reactions' },
        'acids': { subject: 'chemistry', topic: 'Acids & Bases' },
        'electrolysis': { subject: 'chemistry', topic: 'Electrochemistry' },
        'stoichiometry': { subject: 'chemistry', topic: 'Stoichiometry' },
        'redox': { subject: 'chemistry', topic: 'Redox Reactions' },
        'equilibrium': { subject: 'chemistry', topic: 'Chemical Equilibrium' },
        'geometry': { subject: 'math', topic: 'Geometry' },
        'trigonometry': { subject: 'math', topic: 'Trigonometry' },
        'algebra': { subject: 'math', topic: 'Algebra' },
        'calculus': { subject: 'math', topic: 'Calculus' },
        'statistics': { subject: 'math', topic: 'Statistics' },
        'probability': { subject: 'math', topic: 'Probability' },
        'functions': { subject: 'math', topic: 'Functions' },
        'exponents': { subject: 'math', topic: 'Exponents & Surds' },
    };

    // Comprehensive topic map: keyword → { url, topic, description }
    // This covers ALL pages in the system
    const topicMap = {
        // ===== PHYSICS LAB (with simulation modes) =====
        'pendulum': { url: '/physics?sim=pendulum', topic: 'Pendulum', description: 'Simple harmonic motion of a pendulum' },
        'free fall': { url: '/physics?sim=freefall', topic: 'Free Fall', description: 'Objects falling under gravity' },
        'freefall': { url: '/physics?sim=freefall', topic: 'Free Fall', description: 'Objects falling under gravity' },
        'projectile': { url: '/physics?sim=projectile', topic: 'Projectile Motion', description: 'Vertical and horizontal motion of projectiles' },
        'projectile motion': { url: '/physics?sim=projectile', topic: 'Projectile Motion', description: 'Trajectory of launched objects' },
        'doppler': { url: '/physics?sim=doppler', topic: 'Doppler Effect', description: 'Frequency changes due to relative motion' },
        'doppler effect': { url: '/physics?sim=doppler', topic: 'Doppler Effect', description: 'Frequency changes due to relative motion' },
        'doppler shift': { url: '/physics?sim=doppler', topic: 'Doppler Effect', description: 'Frequency shift in waves' },
        "newton's first law": { url: '/physics?sim=newton1', topic: "Newton's First Law", description: 'Law of inertia' },
        "newton's second law": { url: '/physics?sim=newton2', topic: "Newton's Second Law", description: 'F = ma relationship' },
        "newton's third law": { url: '/physics?sim=newton3', topic: "Newton's Third Law", description: 'Action and reaction forces' },
        "newton's laws": { url: '/physics?sim=newton1', topic: "Newton's Laws", description: 'Laws of motion and forces' },
        'newton': { url: '/physics?sim=newton1', topic: "Newton's Laws", description: 'Laws of motion and forces' },
        'gravity': { url: '/physics?sim=gravity', topic: 'Gravitation', description: 'Gravitational force and acceleration' },
        'gravitational': { url: '/physics?sim=gravity', topic: 'Gravitation', description: 'Gravitational force and acceleration' },
        'coulomb': { url: '/physics?sim=coulomb', topic: "Coulomb's Law", description: 'Electrostatic force between charges' },
        'electrostatic': { url: '/physics?sim=coulomb', topic: "Coulomb's Law", description: 'Electrostatic force between charges' },
        'electric force': { url: '/physics?sim=coulomb', topic: "Coulomb's Law", description: 'Force between electric charges' },
        'optics': { url: '/physics', topic: 'Optics', description: 'Light, mirrors, and lenses' },
        'light': { url: '/physics', topic: 'Optics', description: 'Light properties and behaviour' },
        'mirrors': { url: '/physics', topic: 'Optics - Mirrors', description: 'Concave and convex mirrors' },
        'lenses': { url: '/physics', topic: 'Optics - Lenses', description: 'Convex and concave lenses' },
        'circuits': { url: '/physics', topic: 'Electric Circuits', description: 'Series and parallel circuits' },
        'electric circuits': { url: '/physics', topic: 'Electric Circuits', description: 'Series and parallel circuits' },
        'electricity': { url: '/physics', topic: 'Electricity & Magnetism', description: 'Electric charge, current, and voltage' },
        'magnetism': { url: '/physics', topic: 'Magnetism', description: 'Magnetic fields and forces' },
        'momentum': { url: '/physics', topic: 'Momentum', description: 'Conservation of momentum and collisions' },
        'energy': { url: '/physics', topic: 'Energy & Work', description: 'Kinetic, potential energy, and work-energy theorem' },
        'waves': { url: '/physics', topic: 'Waves & Sound', description: 'Wave properties and sound waves' },
        'sound': { url: '/physics', topic: 'Waves & Sound', description: 'Sound waves and acoustics' },
        'nuclear': { url: '/physics', topic: 'Nuclear Physics', description: 'Radioactivity and nuclear reactions' },
        'radioactivity': { url: '/physics', topic: 'Nuclear Physics', description: 'Types of radiation and half-life' },
        'thermodynamics': { url: '/physics', topic: 'Thermodynamics', description: 'Heat, temperature, and thermal processes' },
        'heat': { url: '/physics', topic: 'Thermodynamics', description: 'Heat transfer and thermal processes' },
        'motion': { url: '/physics', topic: 'Linear Motion', description: 'Velocity, acceleration, and displacement' },
        'velocity': { url: '/physics', topic: 'Linear Motion', description: 'Velocity-time graphs and calculations' },
        'acceleration': { url: '/physics', topic: 'Linear Motion', description: 'Acceleration and equations of motion' },
        'forces': { url: '/physics', topic: 'Forces', description: 'Types of forces and free body diagrams' },
        'hydrogen molecule': { url: '/physics', topic: 'Hydrogen Molecules', description: 'Atomic structure and bonding of hydrogen' },
        'hydrogen molecules': { url: '/physics', topic: 'Hydrogen Molecules', description: 'Atomic structure and bonding of hydrogen' },
        'electromagnetic': { url: '/physics', topic: 'Electromagnetic Waves', description: 'EM spectrum and wave properties' },
        'electromagnetic waves': { url: '/physics', topic: 'Electromagnetic Waves', description: 'EM spectrum and wave properties' },

        // ===== CHEMISTRY LAB =====
        'periodic table': { url: '/chemistry', topic: 'Periodic Table', description: 'Elements, groups, and periods' },
        'periodic': { url: '/chemistry', topic: 'Periodic Table', description: 'Elements, groups, and periods' },
        'elements': { url: '/chemistry', topic: 'Periodic Table', description: 'Chemical elements and properties' },
        'bonds': { url: '/chemistry', topic: 'Chemical Bonding', description: 'Ionic, covalent, and metallic bonds' },
        'chemical bonding': { url: '/chemistry', topic: 'Chemical Bonding', description: 'Ionic, covalent, and metallic bonds' },
        'organic': { url: '/chemistry', topic: 'Organic Chemistry', description: 'Carbon compounds and hydrocarbons' },
        'organic chemistry': { url: '/chemistry', topic: 'Organic Chemistry', description: 'Carbon compounds and hydrocarbons' },
        'reactions': { url: '/chemistry', topic: 'Chemical Reactions', description: 'Types of reactions and balancing equations' },
        'chemical reactions': { url: '/chemistry', topic: 'Chemical Reactions', description: 'Types of reactions and balancing equations' },
        'acids': { url: '/chemistry', topic: 'Acids & Bases', description: 'pH, neutralization, and titrations' },
        'acids and bases': { url: '/chemistry', topic: 'Acids & Bases', description: 'pH, neutralization, and titrations' },
        'acids bases and salts': { url: '/chemistry', topic: 'Acids, Bases & Salts', description: 'pH scale, neutralization, salt formation' },
        'bases': { url: '/chemistry', topic: 'Acids & Bases', description: 'pH, neutralization, and titrations' },
        'salts': { url: '/chemistry', topic: 'Acids, Bases & Salts', description: 'Preparation and properties of salts' },
        'ph': { url: '/chemistry', topic: 'Acids & Bases - pH Scale', description: 'pH calculations and indicators' },
        'electrochemistry': { url: '/chemistry', topic: 'Electrochemistry', description: 'Electrochemical cells and electrolysis' },
        'electrolysis': { url: '/chemistry', topic: 'Electrochemistry', description: 'Electrolysis and electroplating' },
        'stoichiometry': { url: '/chemistry', topic: 'Stoichiometry', description: 'Mole calculations and equations' },
        'moles': { url: '/chemistry', topic: 'The Mole', description: 'Molar mass and Avogadro\'s number' },
        'mole': { url: '/chemistry', topic: 'The Mole', description: 'Molar mass and Avogadro\'s number' },
        'redox': { url: '/chemistry', topic: 'Redox Reactions', description: 'Oxidation and reduction reactions' },
        'oxidation': { url: '/chemistry', topic: 'Redox Reactions', description: 'Oxidation and reduction reactions' },
        'solution': { url: '/chemistry', topic: 'Solutions & Solubility', description: 'Concentration, solubility curves' },
        'solutions': { url: '/chemistry', topic: 'Solutions & Solubility', description: 'Concentration, solubility curves' },
        'solubility': { url: '/chemistry', topic: 'Solutions & Solubility', description: 'Solubility curves and calculations' },
        'gas laws': { url: '/chemistry', topic: 'Gases', description: 'Gas laws and ideal gas equation' },
        'chemical equilibrium': { url: '/chemistry', topic: 'Chemical Equilibrium', description: 'Le Chatelier\'s principle and equilibrium constant' },
        'equilibrium': { url: '/chemistry', topic: 'Chemical Equilibrium', description: 'Le Chatelier\'s principle and equilibrium constant' },
        'rates of reaction': { url: '/chemistry', topic: 'Reaction Rates', description: 'Factors affecting reaction rates' },
        'rates': { url: '/chemistry', topic: 'Reaction Rates', description: 'Factors affecting reaction rates' },
        'hydrocarbons': { url: '/chemistry', topic: 'Organic Chemistry - Hydrocarbons', description: 'Alkanes, alkenes, alkynes' },
        'molecular': { url: '/chemistry', topic: 'Molecular Structure', description: 'Molecular geometry and bonding' },

        // ===== MATH LAB (with sections) =====
        'geometry': { url: '/math', topic: 'Geometry', description: 'Shapes, angles, circle theorems, and proofs' },
        'circle theorems': { url: '/math', topic: 'Euclidean Geometry', description: 'Circle theorems and proofs' },
        'euclidean': { url: '/math', topic: 'Euclidean Geometry', description: 'Circle theorems and proofs' },
        'trigonometry': { url: '/math', topic: 'Trigonometry', description: 'Sine, cosine, tangent ratios and graphs' },
        'trig': { url: '/math', topic: 'Trigonometry', description: 'Sine, cosine, tangent ratios and graphs' },
        'sine': { url: '/math', topic: 'Trigonometry - Sine', description: 'Sine function and graphs' },
        'cosine': { url: '/math', topic: 'Trigonometry - Cosine', description: 'Cosine function and graphs' },
        'tangent': { url: '/math', topic: 'Trigonometry - Tangent', description: 'Tangent function and graphs' },
        'algebra': { url: '/math', topic: 'Algebra', description: 'Equations, expressions, and inequalities' },
        'linear': { url: '/math', topic: 'Algebra - Linear Functions', description: 'Linear equations y = mx + c' },
        'parabola': { url: '/math', topic: 'Algebra - Quadratic Functions', description: 'Parabola graphs y = ax² + bx + c' },
        'hyperbola': { url: '/math', topic: 'Algebra - Exponential Functions', description: 'Exponential and hyperbola graphs' },
        'exponential': { url: '/math', topic: 'Algebra - Exponential Functions', description: 'Exponential growth and decay' },
        'quadratic': { url: '/math', topic: 'Algebra - Quadratic Functions', description: 'Factoring, completing the square, formula' },
        'quadratic equations': { url: '/math', topic: 'Algebra - Quadratic Functions', description: 'Solving quadratic equations' },
        'calculus': { url: '/math', topic: 'Calculus', description: 'Differentiation and integration' },
        'differentiation': { url: '/math', topic: 'Calculus - Differentiation', description: 'Derivatives and gradient' },
        'integration': { url: '/math', topic: 'Calculus - Integration', description: 'Area under curves' },
        'statistics': { url: '/math', topic: 'Statistics', description: 'Data analysis, mean, median, mode' },
        'probability': { url: '/math', topic: 'Probability', description: 'Chance and probability calculations' },
        'functions': { url: '/math', topic: 'Functions', description: 'Linear, quadratic, exponential functions' },
        'sequences': { url: '/math', topic: 'Number Patterns', description: 'Arithmetic and geometric sequences' },
        'number patterns': { url: '/math', topic: 'Number Patterns', description: 'Sequences and series' },
        'exponents': { url: '/math', topic: 'Exponents & Surds', description: 'Laws of exponents and simplifying surds' },
        'surds': { url: '/math', topic: 'Exponents & Surds', description: 'Simplifying and rationalizing surds' },
    };

    let pendingAction = null;
    let confirmationRecognition = null;
    let isConfirming = false;

    /**
     * Speak text using the Web Speech API (Text-to-Speech)
     */
    function speak(text) {
        return new Promise((resolve) => {
            if (!('speechSynthesis' in window)) {
                console.warn('Speech synthesis not supported');
                resolve();
                return;
            }
            // Cancel any ongoing speech
            speechSynthesis.cancel();
            const utterance = new SpeechSynthesisUtterance(text);
            utterance.lang = 'en-ZA';
            utterance.rate = 1.0;
            utterance.pitch = 1.0;
            utterance.volume = 1.0;
            utterance.onend = () => resolve();
            utterance.onerror = () => resolve();
            speechSynthesis.speak(utterance);
        });
    }

    /**
     * Show confirmation UI and listen for yes/no
     */
    function askConfirmation(actionDescription, onConfirm, onCancel) {
        pendingAction = { onConfirm, onCancel };
        isConfirming = true;

        // Show confirmation UI
        suggestionsEl.style.display = 'none';
        noResultsEl.style.display = 'none';
        resultsEl.style.display = 'block';
        resultsList.innerHTML = `
            <div style="text-align:center; padding:24px 16px;" id="confirmUI">
                <div style="width:56px; height:56px; border-radius:50%; background:linear-gradient(135deg, #f59e0b, #d97706); margin:0 auto 16px; display:flex; align-items:center; justify-content:center;">
                    <i class="fas fa-question" style="color:white; font-size:22px;"></i>
                </div>
                <p style="font-size:1.05rem; font-weight:600; color:#1e293b; margin:0 0 6px 0;">Confirm Action</p>
                <p style="font-size:0.9rem; color:#475569; margin:0 0 16px 0;" id="confirmText">${escapeHtml(actionDescription)}</p>
                <div style="display:flex; gap:10px; justify-content:center; flex-wrap:wrap;">
                    <button id="confirmYesBtn" onclick="window._askAppConfirm(true)" style="padding:10px 24px; border-radius:10px; border:none; background:#16a34a; color:white; font-weight:600; font-size:0.9rem; cursor:pointer; transition:all 0.2s;">
                        <i class="fas fa-check"></i> Yes, do it
                    </button>
                    <button id="confirmNoBtn" onclick="window._askAppConfirm(false)" style="padding:10px 24px; border-radius:10px; border:none; background:#dc2626; color:white; font-weight:600; font-size:0.9rem; cursor:pointer; transition:all 0.2s;">
                        <i class="fas fa-times"></i> No, cancel
                    </button>
                </div>
                <p style="font-size:0.75rem; color:#94a3b8; margin-top:12px;">
                    <i class="fas fa-microphone"></i> Say <strong>"yes"</strong> or <strong>"no"</strong>, or click a button
                </p>
            </div>
        `;

        // Button handlers
        window._askAppConfirm = (confirmed) => {
            isConfirming = false;
            if (confirmationRecognition) {
                confirmationRecognition.stop();
                confirmationRecognition = null;
            }
            if (confirmed) {
                onConfirm();
            } else {
                speak('Okay, action cancelled.');
                if (onCancel) onCancel();
            }
        };

        // Voice confirmation
        speak(actionDescription + '. Would you like me to proceed? Say yes or no.').then(() => {
            startConfirmationListening();
        });
    }

    /**
     * Start listening for yes/no confirmation
     */
    function startConfirmationListening() {
        if (!('webkitSpeechRecognition' in window) && !('SpeechRecognition' in window)) return;

        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        confirmationRecognition = new SpeechRecognition();
        confirmationRecognition.continuous = false;
        confirmationRecognition.interimResults = false;
        confirmationRecognition.lang = 'en-ZA';
        confirmationRecognition.maxAlternatives = 3;

        confirmationRecognition.onresult = (event) => {
            let said = '';
            for (let i = 0; i < event.results[0].length; i++) {
                said += event.results[0][i].transcript + ' ';
            }
            said = said.toLowerCase().trim();
            console.log('Voice confirmation heard:', said);

            if (said.includes('yes') || said.includes('yeah') || said.includes('yep') || said.includes('correct') || said.includes('confirm') || said.includes('go') || said.includes('do it') || said === 'y') {
                window._askAppConfirm(true);
            } else if (said.includes('no') || said.includes('nah') || said.includes('nope') || said.includes('cancel') || said.includes('never mind') || said.includes('stop') || said === 'n') {
                window._askAppConfirm(false);
            } else {
                // Not recognized, ask again
                speak('I did not catch that. Please say yes or no.').then(() => {
                    startConfirmationListening();
                });
            }
        };

        confirmationRecognition.onerror = (event) => {
            console.error('Confirmation recognition error:', event.error);
            if (event.error !== 'aborted') {
                speak('I could not hear you. Please try again or click a button.').then(() => {
                    if (isConfirming) startConfirmationListening();
                });
            }
        };

        confirmationRecognition.onend = () => {
            // Restart if still confirming
            if (isConfirming && confirmationRecognition) {
                // Don't restart automatically, wait for speak to finish
            }
        };

        confirmationRecognition.start();
    }

    /**
     * Handle "check [topic]" commands - navigate to subject lab
     */
    function handleContentCheck(transcript) {
        const text = transcript.toLowerCase().trim();
        // Extract the topic after "check", "show", "explain", etc.
        const match = text.match(/^(?:check|show|explain|tell\s+me\s+about|look\s+at|open)\s+(.+)/i);
        if (!match) return null;
        
        const topic = match[1].trim();
        
        // Look up in science topics
        let bestMatch = null;
        let bestScore = 0;
        
        for (const [key, value] of Object.entries(scienceTopics)) {
            if (topic === key || topic.includes(key) || key.includes(topic)) {
                const score = key.length;
                if (score > bestScore) {
                    bestScore = score;
                    bestMatch = { ...value, key };
                }
            }
        }

        if (!bestMatch) {
            // Check for partial word matches
            const topicWords = topic.split(/\s+/);
            for (const [key, value] of Object.entries(scienceTopics)) {
                const keyWords = key.split(/\s+/);
                const matchCount = topicWords.filter(w => keyWords.some(kw => kw.includes(w) || w.includes(kw))).length;
                if (matchCount >= Math.ceil(keyWords.length * 0.6) && matchCount > bestScore) {
                    bestScore = matchCount;
                    bestMatch = { ...value, key };
                }
            }
        }

        if (bestMatch) {
            const url = `/${bestMatch.subject}`;
            return {
                isSystemAction: true,
                description: `Open ${bestMatch.topic} in the ${bestMatch.subject === 'math' ? 'Mathematics' : bestMatch.subject === 'physics' ? 'Physics' : 'Chemistry'} Lab`,
                action: () => {
                    window.location.href = url;
                }
            };
        }

        // Fallback: search the system for the topic
        return {
            isSystemAction: true,
            description: `Search for "${topic}" in the system`,
            action: () => {
                closeModal();
                setTimeout(() => {
                    input.value = topic;
                    performSearch(topic);
                }, 300);
            }
        };
    }

    /**
     * Handle "create [subject] study plan" commands
     */
    function handleCreateStudyPlan(transcript) {
        const text = transcript.toLowerCase().trim();
        const match = text.match(/^(?:create|make|generate|start|set\s+up)\s+(.+?)\s+(?:study\s+plan|revision\s+plan|timetable|schedule)/i);
        if (!match) return null;
        
        const subject = match[1].trim();
        
        return {
            isSystemAction: true,
            description: `Create a ${subject} study plan and add it to your calendar`,
            action: () => {
                window.location.href = '/study-plan';
            }
        };
    }

    /**
     * Handle "open scanned docs" commands
     */
    function handleOpenScans(transcript) {
        return {
            isSystemAction: true,
            description: 'Open your scanned documents',
            action: () => {
                window.location.href = '/scan';
            }
        };
    }

    /**
     * Handle "create quiz" / "quiz me on" commands
     * Extracts the topic and navigates to AI chat with a quiz prompt
     */
    function handleCreateQuiz(transcript) {
        const text = transcript.toLowerCase().trim();
        let topic = '';
        
        // Pattern 1: "create/make/generate [topic] quiz"
        let match = text.match(/^(?:create|make|generate|start|set\s+up|give\s+me|write)\s+(?:me\s+|a\s+|an\s+|the\s+)?(.+?)\s+quiz(?:\s+for\s+me)?$/i);
        if (match) {
            topic = match[1].trim();
        }
        
        // Pattern 2: "quiz me on/about [topic]"
        if (!topic) {
            match = text.match(/^(?:quiz\s+me|test\s+me|ask\s+me)\s+(?:on|about|in)\s+(.+)/i);
            if (match) {
                topic = match[1].trim();
            }
        }

        // Clean up the topic - remove common filler words
        topic = topic.replace(/\b(grade\s*\d+\s*)?(june|march|november|september|april|february|may|july|august|october|december)\s*(paper\s*\d+)?\s*(caps|ib|nsc)?\s*(for\s+me)?\s*$/i, '').trim();
        
        // Extract grade if mentioned
        const gradeMatch = text.match(/grade\s*(\d{1,2})/i);
        const grade = gradeMatch ? gradeMatch[1] : '';
        
        // Extract paper/exam info
        const paperMatch = text.match(/(paper\s*\d|june|march|november|september|caps|nsc)/i);
        const paperInfo = paperMatch ? paperMatch[0] : '';

        const quizPrompt = `Create a short quiz for me${grade ? ' (Grade ' + grade + ')' : ''} on: ${topic}${paperInfo ? ' - Focus on ' + paperInfo + ' style questions' : ''}. Please give me 5 multiple choice questions with 4 options each, and tell me the correct answer after each question.`;

        return {
            isSystemAction: true,
            description: `Create a ${topic} quiz${grade ? ' for Grade ' + grade : ''} and take it in AI Chat`,
            action: () => {
                // Navigate to AI chat and pre-fill the message
                window.location.href = '/ai-chat#' + encodeURIComponent(quizPrompt);
            }
        };
    }

    /**
     * Handle "solve", "explain how", "teach me", "revise" commands
     * Navigates to AI chat with a help prompt
     */
    function handleAIHelp(transcript) {
        const text = transcript.toLowerCase().trim();
        let topic = '';
        let actionType = 'help';

        // Extract the topic based on the command type
        let match = text.match(/^(?:solve|help\s+me\s+(?:solve|with)|how\s+(?:do\s+I|to|can\s+I))\s+(.+)/i);
        if (match) {
            topic = match[1].trim();
            actionType = 'solve';
        }

        if (!topic) {
            match = text.match(/^(?:summarize|summarise|give\s+me\s+a\s+summary\s+of)\s+(.+)/i);
            if (match) {
                topic = match[1].trim();
                actionType = 'summarize';
            }
        }

        if (!topic) {
            match = text.match(/^(?:teach\s+me|explain\s+how|how\s+does)\s+(.+)/i);
            if (match) {
                topic = match[1].trim();
                actionType = 'explain';
            }
        }

        if (!topic) {
            match = text.match(/^(?:revise|revision\s+(?:on|for|about))\s+(.+)/i);
            if (match) {
                topic = match[1].trim();
                actionType = 'revise';
            }
        }

        if (!topic) return null;

        const prompts = {
            solve: `Help me solve this: ${topic}. Please explain the solution step by step.`,
            summarize: `Give me a summary of: ${topic}. Cover the key points concisely.`,
            explain: `Explain how ${topic} works. Use simple language and give examples.`,
            revise: `Help me revise ${topic}. Give me key points, formulas, and practice tips.`
        };

        const descriptions = {
            solve: `Help you solve: ${topic}`,
            summarize: `Summarize: ${topic}`,
            explain: `Explain: ${topic}`,
            revise: `Help you revise: ${topic}`
        };

        return {
            isSystemAction: true,
            description: descriptions[actionType] + ' in AI Chat',
            action: () => {
                window.location.href = '/ai-chat#' + encodeURIComponent(prompts[actionType]);
            }
        };
    }

    /**
     * Parse a voice transcript to detect navigation intent
     * Returns { isNavigation, destination, route?, url?, action? }
     */
    function parseVoiceCommand(transcript) {
        const text = transcript.toLowerCase().trim();

        // 1. Check for grade-specific patterns first (e.g., "go to grade 11 notes")
        const gradeMatch = text.match(/(?:grade|class|gr)\s*(\d{1,2})\s*(?:notes?|scripts?|papers?|past\s*papers?|content|memorandums?|memos?|materials?|books?)?/i);
        if (gradeMatch) {
            const grade = parseInt(gradeMatch[1]);
            if (grade >= 8 && grade <= 12) {
                // Check if there's a navigation prefix
                if (hasNavigationIntent(text)) {
                    return {
                        isNavigation: true,
                        destination: `Grade ${grade}`,
                        url: `/browse-scripts/${grade}`
                    };
                }
            }
        }

        // 2. Check for "apply to [institution]" patterns
        const applyToMatch = text.match(/(?:apply\s+(?:to|at)|application\s+(?:to|at))\s+(\S+?)(?:\s+for\s+me)?(?:\s+please)?$/i);
        if (applyToMatch) {
            const potentialInst = applyToMatch[1].toLowerCase();
            // Check if it's a known institution alias or name
            for (const [alias, fullName] of Object.entries(institutionAliases)) {
                if (potentialInst === alias || potentialInst.includes(alias)) {
                    return {
                        isNavigation: true,
                        destination: fullName,
                        url: '/apply-for-me'
                    };
                }
            }
            // Also check full institution names
            for (const name of Object.keys(institutionAliases).map(k => institutionAliases[k])) {
                if (text.toLowerCase().includes(name.toLowerCase())) {
                    return {
                        isNavigation: true,
                        destination: name,
                        url: '/apply-for-me'
                    };
                }
            }
            // If "apply to" is detected but institution not recognized, still go to apply page
            return {
                isNavigation: true,
                destination: 'Apply For Me',
                url: '/apply-for-me'
            };
        }

        // 3. Check for generic "apply for me" patterns
        if (/(?:apply\s+for\s+me|help\s+me\s+apply|assist\s+me\s+apply|do\s+(?:my\s+)?application)/i.test(text)) {
            return {
                isNavigation: true,
                destination: 'Apply For Me',
                url: '/apply-for-me'
            };
        }

        // 4. Check for "find bursary/scholarship" patterns
        if (/(?:find|search|look)\s+(?:for\s+)?(?:a\s+)?(?:bursar(?:y|ies)|scholarship|funding|financial\s+aid)/i.test(text)) {
            return {
                isNavigation: true,
                destination: 'Bursaries',
                url: '/upload-report-card'
            };
        }

        // 5. Check for system action commands (check topics, create study plans, open scans)
        // MUST be before navigation patterns to prevent "open scanned docs" from matching "open [destination]"
        for (const actionDef of systemActionPatterns) {
            const match = text.match(actionDef.pattern);
            if (match) {
                const result = actionDef.handler(transcript.trim());
                if (result) {
                    return {
                        isNavigation: false,
                        isSystemAction: true,
                        systemAction: result
                    };
                }
            }
        }

        // 6. Try navigation patterns
        for (const pattern of navigationPatterns) {
            const match = text.match(pattern);
            if (match) {
                const destination = (match[1] || '').trim().toLowerCase();
                if (destination) {
                    const route = findRouteForDestination(destination);
                    if (route) {
                        return {
                            isNavigation: true,
                            destination: destination,
                            url: route.url,
                            action: route.action || null
                        };
                    }
                }
            }
        }

        // 7. Check if the text itself directly matches a route (without command prefix)
        // e.g., user just says "study plan" or "mathematics"
        const directRoute = findRouteForDestination(text);
        if (directRoute && hasNavigationIntent(text)) {
            return {
                isNavigation: true,
                destination: text,
                url: directRoute.url,
                action: directRoute.action || null
            };
        }

        // Not a navigation command
        return { isNavigation: false };
    }

    /**
     * Check if the text contains any navigation intent keywords
     */
    function hasNavigationIntent(text) {
        const intentKeywords = [
            'go to', 'go', 'open', 'take me', 'show me', 'show',
            'navigate', 'switch to', 'move to', 'i want', 'i need',
            'let me', 'apply to', 'apply for', 'help me apply',
            'find', 'search for', 'launch', 'start'
        ];
        return intentKeywords.some(kw => text.includes(kw));
    }

    /**
     * Find the best matching route for a destination string
     */
    function findRouteForDestination(destination) {
        const dest = destination.toLowerCase().trim();

        // Direct match
        if (navigationRoutes[dest]) {
            return navigationRoutes[dest];
        }

        // Check if destination contains a route key (e.g., "grade 11 notes" contains "notes" → but we want grade logic)
        // Sort keys by length (longest first) for best match
        const sortedKeys = Object.keys(navigationRoutes).sort((a, b) => b.length - a.length);
        for (const key of sortedKeys) {
            if (dest.includes(key) || key.includes(dest)) {
                return navigationRoutes[key];
            }
        }

        // Fuzzy word matching
        const destWords = dest.split(/\s+/);
        for (const key of sortedKeys) {
            const keyWords = key.split(/\s+/);
            const matchCount = destWords.filter(w => keyWords.some(kw => kw.includes(w) || w.includes(kw))).length;
            if (matchCount >= Math.ceil(keyWords.length * 0.6)) {
                return navigationRoutes[key];
            }
        }

        return null;
    }

    // Initialize default chips
    function initDefaultChips() {
        defaultList.innerHTML = '';
        defaultSuggestionsData.forEach(term => {
            const chip = document.createElement('span');
            chip.className = 'ask-app-chip';
            chip.textContent = term;
            chip.onclick = () => {
                input.value = term;
                performSearch(term);
            };
            defaultList.appendChild(chip);
        });
    }
    initDefaultChips();

    // Open modal
    function openModal() {
        modal.classList.add('active');
        setTimeout(() => input.focus(), 100);
        input.value = '';
        showSuggestions();
    }

    // Close modal
    function closeModal() {
        modal.classList.remove('active');
        input.value = '';
        hideAll();
        if (isListening) stopVoiceSearch();
    }

    function hideAll() {
        suggestionsEl.style.display = 'none';
        resultsEl.style.display = 'none';
        noResultsEl.style.display = 'none';
    }

    function showSuggestions() {
        suggestionsEl.style.display = 'block';
        resultsEl.style.display = 'none';
        noResultsEl.style.display = 'none';
        defaultSuggestions.style.display = 'block';
        typingSuggestions.style.display = 'none';
    }

    // Event listeners
    fab.addEventListener('click', openModal);
    closeBtn.addEventListener('click', closeModal);
    backdrop.addEventListener('click', closeModal);

    document.addEventListener('keydown', (e) => {
        if (e.key === '/' && !modal.classList.contains('active') && !['INPUT','TEXTAREA'].includes(document.activeElement.tagName)) {
            e.preventDefault();
            openModal();
        }
        if (e.key === 'Escape' && modal.classList.contains('active')) {
            closeModal();
        }
    });

    // Input handling with debounce
    input.addEventListener('input', () => {
        const query = input.value.trim();
        clearTimeout(searchTimeout);
        
        if (query.length === 0) {
            showSuggestions();
            return;
        }

        if (query.length >= 2) {
            // Show typing suggestions
            defaultSuggestions.style.display = 'none';
            typingSuggestions.style.display = 'block';
            suggestionsEl.style.display = 'block';
            resultsEl.style.display = 'none';
            noResultsEl.style.display = 'none';

            searchTimeout = setTimeout(() => fetchSuggestions(query), 200);
        }
    });

    input.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            const query = input.value.trim();
            if (query) performSearch(query);
        }
    });

    // Fetch suggestions
    async function fetchSuggestions(query) {
        try {
            const res = await fetch('/api/search/suggestions?q=' + encodeURIComponent(query));
            const data = await res.json();
            if (data.success && data.suggestions.length > 0) {
                typingList.innerHTML = '';
                data.suggestions.forEach(suggestion => {
                    const li = document.createElement('li');
                    li.className = 'ask-app-suggestion-item';
                    li.innerHTML = '<i class="fas fa-arrow-right"></i><span>' + escapeHtml(suggestion) + '</span>';
                    li.onclick = () => {
                        input.value = suggestion;
                        performSearch(suggestion);
                    };
                    typingList.appendChild(li);
                });
                typingSuggestions.style.display = 'block';
            } else {
                typingSuggestions.style.display = 'none';
            }
        } catch (err) {
            console.error('Suggestion fetch error:', err);
        }
    }

    // =============================================
    // BROAD TOPIC SEARCH INDEX
    // Maps search terms to specific pages/sections
    // =============================================
    const topicSearchIndex = [
        // Physics simulations
        { terms: ['doppler', 'doppler effect', 'doppler shift', 'frequency shift', 'sound waves moving'], url: '/physics?sim=doppler', title: 'Doppler Effect', category: 'Physics Simulation', icon: 'fas fa-wave-square' },
        { terms: ['pendulum', 'simple pendulum', 'oscillation', 'oscillatory motion'], url: '/physics?sim=pendulum', title: 'Simple Pendulum', category: 'Physics Simulation', icon: 'fas fa-dot-circle' },
        { terms: ['free fall', 'freefall', 'falling object', 'gravity acceleration'], url: '/physics?sim=freefall', title: 'Free Fall', category: 'Physics Simulation', icon: 'fas fa-arrow-down' },
        { terms: ['projectile', 'projectile motion', 'trajectory', '2d motion', 'launched object'], url: '/physics?sim=projectile', title: 'Projectile Motion', category: 'Physics Simulation', icon: 'fas fa-rocket' },
        { terms: ['newton first', "newton's first", 'newton 1', "newton's 1", 'inertia', 'first law'], url: '/physics?sim=newton1', title: "Newton's 1st Law (Inertia)", category: 'Physics Simulation', icon: 'fas fa-square' },
        { terms: ['newton second', "newton's second", 'newton 2', "newton's 2", 'f=ma', 'force mass acceleration', 'second law'], url: '/physics?sim=newton2', title: "Newton's 2nd Law (F=ma)", category: 'Physics Simulation', icon: 'fas fa-weight-hanging' },
        { terms: ['newton third', "newton's third", 'newton 3', "newton's 3", 'action reaction', 'third law', 'collisions'], url: '/physics?sim=newton3', title: "Newton's 3rd Law (Action-Reaction)", category: 'Physics Simulation', icon: 'fas fa-exchange-alt' },
        { terms: ['gravity', 'gravitation', 'universal gravitation', 'gravitational force'], url: '/physics?sim=gravity', title: 'Gravitation', category: 'Physics Simulation', icon: 'fas fa-globe' },
        { terms: ['coulomb', "coulomb's law", 'electrostatic', 'electric charge', 'charges'], url: '/physics?sim=coulomb', title: "Coulomb's Law", category: 'Physics Simulation', icon: 'fas fa-bolt' },

        // Physics general
        { terms: ['physics simulation', 'physics lab', 'physics experiments', 'physics practical'], url: '/physics', title: 'Physics Laboratory', category: 'Subject Lab', icon: 'fas fa-atom' },
        { terms: ['waves', 'wave properties', 'wave motion', 'sound waves'], url: '/physics?sim=doppler', title: 'Waves & Sound', category: 'Physics Simulation', icon: 'fas fa-wave-square' },
        { terms: ['electricity', 'electric circuits', 'circuits', 'current', 'voltage'], url: '/physics?sim=coulomb', title: 'Electricity & Magnetism', category: 'Physics Simulation', icon: 'fas fa-bolt' },
        { terms: ['optics', 'light', 'mirrors', 'lenses', 'reflection', 'refraction'], url: '/physics', title: 'Optics', category: 'Physics Lab', icon: 'fas fa-atom' },
        { terms: ['momentum', 'conservation of momentum', 'collisions physics'], url: '/physics?sim=newton3', title: 'Momentum & Collisions', category: 'Physics Simulation', icon: 'fas fa-exchange-alt' },
        { terms: ['energy', 'kinetic energy', 'potential energy', 'work'], url: '/physics', title: 'Energy & Work', category: 'Physics Lab', icon: 'fas fa-atom' },
        { terms: ['thermodynamics', 'heat', 'temperature', 'thermal'], url: '/physics', title: 'Thermodynamics', category: 'Physics Lab', icon: 'fas fa-atom' },
        { terms: ['nuclear', 'radioactivity', 'nuclear physics', 'half life'], url: '/physics', title: 'Nuclear Physics', category: 'Physics Lab', icon: 'fas fa-atom' },
        { terms: ['magnetism', 'magnetic fields', 'magnetic force'], url: '/physics', title: 'Magnetism', category: 'Physics Lab', icon: 'fas fa-atom' },
        { terms: ['electromagnetic', 'em waves', 'em spectrum'], url: '/physics', title: 'Electromagnetic Waves', category: 'Physics Lab', icon: 'fas fa-atom' },
        { terms: ['forces', 'types of forces', 'free body diagram'], url: '/physics?sim=newton1', title: 'Forces', category: 'Physics Simulation', icon: 'fas fa-square' },
        { terms: ['velocity', 'acceleration', 'linear motion', 'displacement', 'motion graphs'], url: '/physics?sim=freefall', title: 'Linear Motion', category: 'Physics Simulation', icon: 'fas fa-arrow-down' },

        // Chemistry
        { terms: ['periodic table', 'elements', 'periodic'], url: '/chemistry', title: 'Periodic Table', category: 'Chemistry Lab', icon: 'fas fa-flask' },
        { terms: ['chemical bonding', 'bonds', 'ionic', 'covalent', 'metallic bonds'], url: '/chemistry', title: 'Chemical Bonding', category: 'Chemistry Lab', icon: 'fas fa-flask' },
        { terms: ['organic chemistry', 'organic', 'hydrocarbons', 'carbon compounds'], url: '/chemistry', title: 'Organic Chemistry', category: 'Chemistry Lab', icon: 'fas fa-flask' },
        { terms: ['chemical reactions', 'reactions', 'balancing equations'], url: '/chemistry', title: 'Chemical Reactions', category: 'Chemistry Lab', icon: 'fas fa-flask' },
        { terms: ['acids', 'bases', 'acids and bases', 'acids bases and salts', 'ph', 'neutralization'], url: '/chemistry', title: 'Acids & Bases', category: 'Chemistry Lab', icon: 'fas fa-flask' },
        { terms: ['electrochemistry', 'electrolysis', 'electroplating'], url: '/chemistry', title: 'Electrochemistry', category: 'Chemistry Lab', icon: 'fas fa-flask' },
        { terms: ['stoichiometry', 'moles', 'mole', 'molar mass', 'avogadro'], url: '/chemistry', title: 'Stoichiometry', category: 'Chemistry Lab', icon: 'fas fa-flask' },
        { terms: ['redox', 'oxidation', 'reduction'], url: '/chemistry', title: 'Redox Reactions', category: 'Chemistry Lab', icon: 'fas fa-flask' },
        { terms: ['solutions', 'solubility', 'concentration'], url: '/chemistry', title: 'Solutions & Solubility', category: 'Chemistry Lab', icon: 'fas fa-flask' },
        { terms: ['gas laws', 'ideal gas', ' Boyle', 'Charles'], url: '/chemistry', title: 'Gas Laws', category: 'Chemistry Lab', icon: 'fas fa-flask' },
        { terms: ['equilibrium', 'chemical equilibrium', 'le chatelier'], url: '/chemistry', title: 'Chemical Equilibrium', category: 'Chemistry Lab', icon: 'fas fa-flask' },
        { terms: ['rates of reaction', 'reaction rates', 'factors affecting rates'], url: '/chemistry', title: 'Reaction Rates', category: 'Chemistry Lab', icon: 'fas fa-flask' },
        { terms: ['chemistry lab', 'chemistry simulation', 'chemistry experiments'], url: '/chemistry', title: 'Chemistry Laboratory', category: 'Subject Lab', icon: 'fas fa-flask' },

        // Math
        { terms: ['geometry', 'euclidean geometry', 'circle theorems', 'circle geometry'], url: '/math#geometry', title: 'Euclidean Geometry', category: 'Mathematics Lab', icon: 'fas fa-shapes' },
        { terms: ['trigonometry', 'trig', 'sine', 'cosine', 'tangent', 'trig identities'], url: '/math#trigonometry', title: 'Trigonometry', category: 'Mathematics Lab', icon: 'fas fa-shapes' },
        { terms: ['algebra', 'equations', 'inequalities', 'expressions'], url: '/math', title: 'Algebra', category: 'Mathematics Lab', icon: 'fas fa-shapes' },
        { terms: ['quadratic', 'quadratic equations', 'parabola', 'factoring'], url: '/math', title: 'Quadratic Equations', category: 'Mathematics Lab', icon: 'fas fa-shapes' },
        { terms: ['calculus', 'differentiation', 'integration', 'derivatives'], url: '/math', title: 'Calculus', category: 'Mathematics Lab', icon: 'fas fa-shapes' },
        { terms: ['statistics', 'data analysis', 'mean', 'median', 'mode', 'standard deviation'], url: '/math', title: 'Statistics', category: 'Mathematics Lab', icon: 'fas fa-shapes' },
        { terms: ['probability', 'chance', 'probability calculations'], url: '/math', title: 'Probability', category: 'Mathematics Lab', icon: 'fas fa-shapes' },
        { terms: ['functions', 'linear functions', 'exponential functions', 'graphs'], url: '/math', title: 'Functions', category: 'Mathematics Lab', icon: 'fas fa-shapes' },
        { terms: ['sequences', 'number patterns', 'arithmetic', 'geometric sequences'], url: '/math', title: 'Number Patterns', category: 'Mathematics Lab', icon: 'fas fa-shapes' },
        { terms: ['exponents', 'surds', 'indices'], url: '/math', title: 'Exponents & Surds', category: 'Mathematics Lab', icon: 'fas fa-shapes' },
        { terms: ['logarithms', 'log', 'log rules'], url: '/math', title: 'Logarithms', category: 'Mathematics Lab', icon: 'fas fa-shapes' },
        { terms: ['matrices', 'matrix operations', 'determinants'], url: '/math', title: 'Matrices', category: 'Mathematics Lab', icon: 'fas fa-shapes' },
        { terms: ['vectors', 'vector operations'], url: '/math', title: 'Vectors', category: 'Mathematics Lab', icon: 'fas fa-shapes' },
        { terms: ['finance', 'interest', 'compound interest', 'simple interest', 'depreciation'], url: '/math', title: 'Mathematical Finance', category: 'Mathematics Lab', icon: 'fas fa-shapes' },
        { terms: ['analytical geometry', 'conic sections', 'equation of circle'], url: '/math', title: 'Analytical Geometry', category: 'Mathematics Lab', icon: 'fas fa-shapes' },
        { terms: ['math lab', 'mathematics lab', 'math simulation', 'math experiments'], url: '/math', title: 'Mathematics Laboratory', category: 'Subject Lab', icon: 'fas fa-shapes' },
    ];

    /**
     * Find the best matching topic for a search query
     */
    function findTopicMatch(query) {
        const text = query.toLowerCase().trim();
        if (!text) return null;

        let bestMatch = null;
        let bestScore = 0;

        for (const entry of topicSearchIndex) {
            for (const term of entry.terms) {
                let score = 0;
                // Exact match
                if (text === term) {
                    score = 1000;
                }
                // Query contains the term
                else if (text.includes(term)) {
                    score = 500 + term.length;
                }
                // Term contains the query
                else if (term.includes(text) && text.length >= 3) {
                    score = 300 + text.length;
                }
                // Word-level matching
                else {
                    const queryWords = text.split(/\s+/);
                    const termWords = term.split(/\s+/);
                    const matchedWords = queryWords.filter(qw => 
                        termWords.some(tw => tw.includes(qw) || qw.includes(tw))
                    );
                    if (matchedWords.length > 0) {
                        score = matchedWords.length * 100 + (matchedWords.length / queryWords.length) * 50;
                    }
                }

                if (score > bestScore) {
                    bestScore = score;
                    bestMatch = entry;
                }
            }
        }

        // Only return if score is meaningful (at least 100)
        return bestScore >= 100 ? bestMatch : null;
    }

    // =============================================
    // APP-AWARE QUERY PATTERNS
    // Questions about the user's own data (study plans, progress, etc.)
    // =============================================
    const appAwarePatterns = [
        /(?:do\s+i\s+have|did\s+i\s+(?:do|complete|finish))\s+(?:any\s+)?(?:study\s+plan|revision|todo|task)/i,
        /(?:am\s+i\s+behind|falling\s+behind|left\s+behind|falling\s+back)/i,
        /(?:how\s+many|what)\s+(?:study\s+plan|revision|task|todo)s?\s+(?:should\s+i|do\s+i|did\s+i|have\s+i|are\s+(?:there|pending|left))/i,
        /(?:my\s+(?:progress|status|performance|results|grades|marks|score))/i,
        /(?:where\s+(?:am\s+i|do\s+i)\s+(?:lacking|struggling|weak|behind))/i,
        /(?:what\s+should\s+i\s+(?:study|revise|practice|focus|work\s+on))/i,
        /(?:how\s+(?:am\s+i\s+doing|is\s+my\s+progress|are\s+my\s+grades))/i,
        /(?:which\s+(?:topics?|subjects?|chapters?)\s+(?:should\s+i|do\s+i\s+need\s+to)\s+(?:revise|study|focus))/i,
        /(?:show\s+me\s+my\s+(?:progress|results|grades|marks|study\s+plan|schedule))/i,
        /(?:what\s+(?:have\s+i|did\s+i)\s+(?:done|completed|finished|covered))/i,
        /(?:my\s+weak\s+(?:areas?|topics?|subjects?))/i,
        /(?:what\s+topics?\s+(?:have\s+i|did\s+i)\s+(?:not|haven.t)\s+(?:done|cover|complete))/i,
    ];

    /**
     * Check if a query is asking about the user's own app data
     */
    function isAppAwareQuery(query) {
        return appAwarePatterns.some(pattern => pattern.test(query));
    }

    /**
     * Try to parse a typed query as a navigation command
     * Reuses the same patterns as voice commands
     */
    function tryParseNavigation(query) {
        const text = query.toLowerCase().trim();

        // 1. Check system action patterns first (create quiz, open scans, etc.)
        for (const actionDef of systemActionPatterns) {
            const match = text.match(actionDef.pattern);
            if (match) {
                const result = actionDef.handler(query);
                if (result) {
                    return { isSystemAction: true, systemAction: result };
                }
            }
        }

        // 2. Check grade-specific patterns
        const gradeMatch = text.match(/(?:grade|class|gr)\s*(\d{1,2})\s*(?:notes?|scripts?|papers?|past\s*papers?|content|memorandums?|memos?|materials?|books?)?/i);
        if (gradeMatch) {
            const grade = parseInt(gradeMatch[1]);
            if (grade >= 8 && grade <= 12 && hasNavigationIntent(text)) {
                return { isNavigation: true, destination: `Grade ${grade}`, url: `/browse-scripts/${grade}` };
            }
        }

        // 3. Check navigation patterns (go to, open, show me, etc.)
        for (const pattern of navigationPatterns) {
            const match = text.match(pattern);
            if (match) {
                const destination = (match[1] || '').trim().toLowerCase();
                if (destination) {
                    // Check for exact route match FIRST (navigationRoutes has priority)
                    const route = findRouteForDestination(destination);
                    if (route) {
                        return { isNavigation: true, destination: destination, url: route.url, action: route.action || null };
                    }

                    // Then check for topic search index (doppler effect, pendulum, etc.)
                    const topicMatch = findTopicMatch(destination);
                    if (topicMatch) {
                        return { isNavigation: true, destination: topicMatch.title, url: topicMatch.url };
                    }

                    // Check for multiple fuzzy matches (show selection)
                    const fuzzyMatches = findAllFuzzyMatches(destination);
                    if (fuzzyMatches.length === 1) {
                        return { isNavigation: true, destination: fuzzyMatches[0].label, url: fuzzyMatches[0].url };
                    }
                    if (fuzzyMatches.length > 1) {
                        return { isMultiMatch: true, matches: fuzzyMatches, query: destination };
                    }

                    // No match found
                    return { isNotFound: true, query: destination };
                }
            }
        }

        // 4. Check if the text directly matches a route with navigation intent
        if (hasNavigationIntent(text)) {
            // Check exact route match FIRST (navigationRoutes has priority)
            const directRoute = findRouteForDestination(text);
            if (directRoute) {
                return { isNavigation: true, destination: text, url: directRoute.url, action: directRoute.action || null };
            }

            // Then check topic search index
            const topicMatch = findTopicMatch(text);
            if (topicMatch) {
                return { isNavigation: true, destination: topicMatch.title, url: topicMatch.url };
            }

            const fuzzyMatches = findAllFuzzyMatches(text);
            if (fuzzyMatches.length === 1) {
                return { isNavigation: true, destination: fuzzyMatches[0].label, url: fuzzyMatches[0].url };
            }
            if (fuzzyMatches.length > 1) {
                return { isMultiMatch: true, matches: fuzzyMatches, query: text };
            }

            return { isNotFound: true, query: text };
        }

        return null;
    }

    /**
     * Find all fuzzy matching routes for a destination
     */
    function findAllFuzzyMatches(destination) {
        const matches = [];
        const dest = destination.toLowerCase().trim();
        const destWords = dest.split(/\s+/);
        const sortedKeys = Object.keys(navigationRoutes).sort((a, b) => b.length - a.length);

        for (const key of sortedKeys) {
            const keyWords = key.split(/\s+/);
            const matchCount = destWords.filter(w => keyWords.some(kw => kw.includes(w) || w.includes(kw))).length;
            if (matchCount > 0 && matchCount >= Math.ceil(destWords.length * 0.5)) {
                matches.push({ label: key, url: navigationRoutes[key].url, action: navigationRoutes[key].action });
            }
        }

        // Also check topic search index
        for (const entry of topicSearchIndex) {
            for (const term of entry.terms) {
                if (term.includes(dest) || dest.includes(term)) {
                    matches.push({ label: entry.title, url: entry.url });
                    break;
                }
            }
        }

        // Deduplicate by URL
        const seen = new Set();
        return matches.filter(m => {
            const key = m.url + m.label;
            if (seen.has(key)) return false;
            seen.add(key);
            return true;
        }).slice(0, 6);
    }

    /**
     * Show a navigation result and navigate after brief feedback
     */
    function showNavigationResult(destination, url) {
        suggestionsEl.style.display = 'none';
        noResultsEl.style.display = 'none';
        resultsEl.style.display = 'block';
        resultsList.innerHTML = `
            <div style="text-align:center; padding:24px 16px;">
                <div style="width:56px; height:56px; border-radius:50%; background:linear-gradient(135deg, #6366f1, #8b5cf6); margin:0 auto 16px; display:flex; align-items:center; justify-content:center;">
                    <i class="fas fa-arrow-right" style="color:white; font-size:22px;"></i>
                </div>
                <p style="font-size:1.1rem; font-weight:600; color:#1e293b; margin:0 0 4px 0;">Taking you to ${escapeHtml(destination)}</p>
                <p style="font-size:0.85rem; color:#94a3b8; margin:0;">Navigating now...</p>
            </div>
        `;
        setTimeout(() => {
            closeModal();
            window.location.href = url;
        }, 600);
    }

    /**
     * Show a system action result and execute after brief feedback
     */
    function showActionResult(sysAction) {
        suggestionsEl.style.display = 'none';
        noResultsEl.style.display = 'none';
        resultsEl.style.display = 'block';
        resultsList.innerHTML = `
            <div style="text-align:center; padding:24px 16px;">
                <div style="width:56px; height:56px; border-radius:50%; background:linear-gradient(135deg, #10b981, #059669); margin:0 auto 16px; display:flex; align-items:center; justify-content:center;">
                    <i class="fas fa-check" style="color:white; font-size:22px;"></i>
                </div>
                <p style="font-size:1.05rem; font-weight:600; color:#1e293b; margin:0 0 4px 0;">${escapeHtml(sysAction.description)}</p>
                <p style="font-size:0.85rem; color:#94a3b8; margin:0;">Opening now...</p>
            </div>
        `;
        setTimeout(() => {
            closeModal();
            if (sysAction.action) sysAction.action();
        }, 600);
    }

    /**
     * Show multiple matching options for the user to choose from
     */
    function showMultiMatchOptions(matches, query) {
        suggestionsEl.style.display = 'none';
        noResultsEl.style.display = 'none';
        resultsEl.style.display = 'block';
        resultsList.innerHTML = '';

        // Header message
        const header = document.createElement('div');
        header.style.cssText = 'text-align:center; padding:16px 16px 8px; color:#64748b; font-size:0.9rem;';
        header.innerHTML = `I found multiple results for "<strong>${escapeHtml(query)}</strong>". Which one would you like?`;
        resultsList.appendChild(header);

        // Show each option as a clickable item
        matches.forEach(match => {
            const a = document.createElement('a');
            a.className = 'ask-app-result-item';
            a.href = match.url;
            a.innerHTML = `
                <div class="ask-app-result-icon">
                    <i class="fas fa-arrow-right"></i>
                </div>
                <div class="ask-app-result-info">
                    <div class="ask-app-result-title">${escapeHtml(match.label)}</div>
                    <div class="ask-app-result-category">${escapeHtml(match.url)}</div>
                </div>
                <i class="fas fa-chevron-right ask-app-result-arrow"></i>
            `;
            a.onclick = (e) => {
                e.preventDefault();
                closeModal();
                window.location.href = match.url;
            };
            resultsList.appendChild(a);
        });
    }

    /**
     * Show a "not found" message with suggestions
     */
    function showNotFound(query) {
        suggestionsEl.style.display = 'none';
        resultsEl.style.display = 'none';
        noResultsEl.style.display = 'block';
        similarResults.innerHTML = '';

        // Update the no results message to be more specific
        const titleEl = noResultsEl.querySelector('.ask-app-no-results-title');
        if (titleEl) {
            titleEl.textContent = `Sorry, I couldn't find "${query}" in this application.`;
        }

        // Show related topic suggestions
        const topicSuggestions = topicSearchIndex
            .filter(t => t.terms.some(term => term.includes(query.toLowerCase()) || query.toLowerCase().includes(term)))
            .slice(0, 5);
        
        if (topicSuggestions.length > 0) {
            const label = document.createElement('p');
            label.style.cssText = 'font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #94a3b8; margin: 0 0 8px 0;';
            label.textContent = 'Did you mean one of these?';
            similarResults.appendChild(label);
            topicSuggestions.forEach(topic => {
                const chip = document.createElement('span');
                chip.className = 'ask-app-chip';
                chip.textContent = topic.title;
                chip.onclick = () => {
                    closeModal();
                    window.location.href = topic.url;
                };
                similarResults.appendChild(chip);
            });
        } else {
            // Show default suggestions
            const label = document.createElement('p');
            label.style.cssText = 'font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #94a3b8; margin: 0 0 8px 0;';
            label.textContent = 'Try these instead';
            similarResults.appendChild(label);
            defaultSuggestionsData.slice(0, 5).forEach(term => {
                const chip = document.createElement('span');
                chip.className = 'ask-app-chip';
                chip.textContent = term;
                chip.onclick = () => {
                    input.value = term;
                    performSearch(term);
                };
                similarResults.appendChild(chip);
            });
        }
    }

    // Perform search - routes to AI or standard search based on query type
    async function performSearch(query) {
        // 1. Check for navigation commands ("go to physics", "open calculator", "create study plan")
        const navResult = tryParseNavigation(query);
        if (navResult) {
            if (navResult.isSystemAction && navResult.systemAction) {
                showActionResult(navResult.systemAction);
                return;
            }
            if (navResult.isNavigation && navResult.url) {
                showNavigationResult(navResult.destination || query, navResult.url);
                return;
            }
            if (navResult.isMultiMatch && navResult.matches) {
                showMultiMatchOptions(navResult.matches, navResult.query || query);
                return;
            }
            if (navResult.isNotFound) {
                showNotFound(navResult.query || query);
                return;
            }
        }

        // 2. Check if this is an app-aware question about the user's data
        if (isAppAwareQuery(query)) {
            return performAISearch(query);
        }

        // 3. Check if this is a natural language question that AI should handle
        if (isAIQuery(query)) {
            return performAISearch(query);
        }

        // 4. Check if the query matches a known topic in our search index
        const topicMatch = findTopicMatch(query);
        if (topicMatch) {
            suggestionsEl.style.display = 'none';
            noResultsEl.style.display = 'none';
            resultsEl.style.display = 'block';
            resultsList.innerHTML = '';

            // Show the topic match as the primary result
            const a = document.createElement('a');
            a.className = 'ask-app-result-item';
            a.href = topicMatch.url;
            a.innerHTML = `
                <div class="ask-app-result-icon" style="background: linear-gradient(135deg, #ede9fe, #ddd6fe);">
                    <i class="${escapeHtml(topicMatch.icon)}" style="color: #6366f1;"></i>
                </div>
                <div class="ask-app-result-info">
                    <div class="ask-app-result-title">${escapeHtml(topicMatch.title)}</div>
                    <div class="ask-app-result-category">${escapeHtml(topicMatch.category)}</div>
                </div>
                <i class="fas fa-chevron-right ask-app-result-arrow"></i>
            `;
            a.onclick = (e) => {
                e.preventDefault();
                closeModal();
                window.location.href = topicMatch.url;
            };
            resultsList.appendChild(a);

            // Also show related results from the API
            try {
                const res = await fetch('/api/search?q=' + encodeURIComponent(query));
                const data = await res.json();
                if (data.success && data.results.length > 0) {
                    data.results.forEach(result => {
                        // Skip if same URL as topic match
                        if (result.url === topicMatch.url) return;
                        const link = document.createElement('a');
                        link.className = 'ask-app-result-item';
                        link.href = result.url;
                        link.innerHTML = `
                            <div class="ask-app-result-icon">
                                <i class="${escapeHtml(result.icon)}"></i>
                            </div>
                            <div class="ask-app-result-info">
                                <div class="ask-app-result-title">${escapeHtml(result.title)}</div>
                                <div class="ask-app-result-category">${escapeHtml(result.category)}</div>
                            </div>
                            <i class="fas fa-chevron-right ask-app-result-arrow"></i>
                        `;
                        link.onclick = (e) => {
                            e.preventDefault();
                            closeModal();
                            if (result.action && typeof window[result.action] === 'function') {
                                setTimeout(() => window[result.action](), 300);
                            } else {
                                window.location.href = result.url;
                            }
                        };
                        resultsList.appendChild(link);
                    });
                }
            } catch (err) {
                // Ignore API errors, topic match is sufficient
            }
            return;
        }

        suggestionsEl.style.display = 'none';
        resultsEl.style.display = 'none';
        noResultsEl.style.display = 'none';

        try {
            const res = await fetch('/api/search?q=' + encodeURIComponent(query));
            const data = await res.json();
            
            if (data.success && data.results.length > 0) {
                resultsList.innerHTML = '';
                data.results.forEach(result => {
                    const a = document.createElement('a');
                    a.className = 'ask-app-result-item';
                    a.href = result.url;
                    a.innerHTML = `
                        <div class="ask-app-result-icon">
                            <i class="${escapeHtml(result.icon)}"></i>
                        </div>
                        <div class="ask-app-result-info">
                            <div class="ask-app-result-title">${escapeHtml(result.title)}</div>
                            <div class="ask-app-result-category">${escapeHtml(result.category)}</div>
                        </div>
                        <i class="fas fa-chevron-right ask-app-result-arrow"></i>
                    `;
                    a.onclick = (e) => {
                        e.preventDefault();
                        closeModal();
                        // Handle JavaScript actions (e.g., toggleCalculator)
                        if (result.action && typeof window[result.action] === 'function') {
                            setTimeout(() => window[result.action](), 300);
                        } else {
                            window.location.href = result.url;
                        }
                    };
                    resultsList.appendChild(a);
                });
                resultsEl.style.display = 'block';
            } else {
                // No results - show topic suggestions
                noResultsEl.style.display = 'block';
                similarResults.innerHTML = '';
                // Show topic chips based on query
                const topicSuggestions = topicSearchIndex
                    .filter(t => t.terms.some(term => term.includes(query.toLowerCase()) || query.toLowerCase().includes(term)))
                    .slice(0, 5);
                
                if (topicSuggestions.length > 0) {
                    const label = document.createElement('p');
                    label.style.cssText = 'font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #94a3b8; margin: 0 0 8px 0;';
                    label.textContent = 'Try these topics';
                    similarResults.appendChild(label);
                    topicSuggestions.forEach(topic => {
                        const chip = document.createElement('span');
                        chip.className = 'ask-app-chip';
                        chip.textContent = topic.title;
                        chip.onclick = () => {
                            closeModal();
                            window.location.href = topic.url;
                        };
                        similarResults.appendChild(chip);
                    });
                } else {
                    // Fallback to default suggestions
                    defaultSuggestionsData.slice(0, 5).forEach(term => {
                        const chip = document.createElement('span');
                        chip.className = 'ask-app-chip';
                        chip.textContent = term;
                        chip.onclick = () => {
                            input.value = term;
                            performSearch(term);
                        };
                        similarResults.appendChild(chip);
                    });
                }
            }
        } catch (err) {
            console.error('Search error:', err);
        }
    }

    // Voice Search
    voiceBtn.addEventListener('click', () => {
        if (isListening) {
            stopVoiceSearch();
        } else {
            startVoiceSearch();
        }
    });

    function startVoiceSearch() {
        if (!('webkitSpeechRecognition' in window) && !('SpeechRecognition' in window)) {
            alert('Voice search is not supported in your browser. Please use Chrome, Edge, or Safari.');
            return;
        }

        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        recognition = new SpeechRecognition();
        recognition.continuous = false;
        recognition.interimResults = true;
        recognition.lang = 'en-ZA';
        recognition.maxAlternatives = 1;

        recognition.onstart = () => {
            isListening = true;
            voiceBtn.classList.add('listening');
            voiceBtn.innerHTML = '<i class="fas fa-stop"></i>';
            voiceStatus.style.display = 'flex';
            input.placeholder = 'Listening...';
        };

        recognition.onresult = (event) => {
            let interimTranscript = '';
            let finalTranscript = '';

            for (let i = event.resultIndex; i < event.results.length; i++) {
                const transcript = event.results[i][0].transcript;
                if (event.results[i].isFinal) {
                    finalTranscript += transcript;
                } else {
                    interimTranscript += transcript;
                }
            }

            input.value = finalTranscript || interimTranscript;

            if (finalTranscript) {
                const command = parseVoiceCommand(finalTranscript.trim());

                if (command.isNavigation) {
                    // Show navigation feedback
                    stopVoiceSearch();
                    input.value = command.destination;
                    input.placeholder = 'Taking you to ' + command.destination + '...';

                    // Show visual feedback in results area
                    suggestionsEl.style.display = 'none';
                    noResultsEl.style.display = 'none';
                    resultsEl.style.display = 'block';
                    resultsList.innerHTML = `
                        <div style="text-align:center; padding:24px 16px;">
                            <div style="width:56px; height:56px; border-radius:50%; background:linear-gradient(135deg, #6366f1, #8b5cf6); margin:0 auto 16px; display:flex; align-items:center; justify-content:center;">
                                <i class="fas fa-arrow-right" style="color:white; font-size:22px;"></i>
                            </div>
                            <p style="font-size:1.1rem; font-weight:600; color:#1e293b; margin:0 0 4px 0;">Taking you to ${escapeHtml(command.destination)}</p>
                            <p style="font-size:0.85rem; color:#94a3b8; margin:0;">Navigating now...</p>
                        </div>
                    `;

                    // Navigate after a brief delay for visual feedback
                    setTimeout(() => {
                        if (command.action && typeof window[command.action] === 'function') {
                            closeModal();
                            setTimeout(() => window[command.action](), 300);
                        } else {
                            window.location.href = command.url;
                        }
                    }, 800);
                } else if (command.isSystemAction && command.systemAction) {
                    // System action with voice confirmation
                    stopVoiceSearch();
                    const sysAction = command.systemAction;
                    
                    askConfirmation(
                        sysAction.description,
                        () => {
                            // On confirm
                            if (sysAction.action) sysAction.action();
                        },
                        () => {
                            // On cancel
                            showSuggestions();
                            input.value = '';
                            input.placeholder = 'Ask App anything...';
                        }
                    );
                } else if (isAIQuery(finalTranscript.trim())) {
                    // Natural language question - use AI
                    stopVoiceSearch();
                    performAISearch(finalTranscript.trim());
                } else {
                    // Not a navigation command - fall back to normal search
                    performSearch(finalTranscript.trim());
                    stopVoiceSearch();
                }
            }
        };

        recognition.onerror = (event) => {
            console.error('Speech recognition error:', event.error);
            stopVoiceSearch();
            if (event.error === 'not-allowed') {
                alert('Microphone access was denied. Please allow microphone access in your browser settings.');
            }
        };

        recognition.onend = () => {
            stopVoiceSearch();
        };

        recognition.start();
    }

    function stopVoiceSearch() {
        if (recognition) {
            recognition.stop();
            recognition = null;
        }
        isListening = false;
        voiceBtn.classList.remove('listening');
        voiceBtn.innerHTML = '<i class="fas fa-microphone"></i>';
        voiceStatus.style.display = 'none';
        input.placeholder = 'Ask App anything...';
    }

    // =============================================
    // AI-POWERED SMART RESPONSES
    // =============================================

    // Patterns that indicate a natural language question needing AI
    const aiQueryPatterns = [
        /^(?:what|how|why|when|where|which|who)\s+/i,
        /^(?:can|could|would|should|will|do|does|did)\s+/i,
        /^(?:i\s+(?:need|want|would like|am looking))/i,
        /^(?:help|teach|explain|show me how)/i,
        /^(?:what\s+am\s+i|what\s+do\s+i|what\s+should\s+i)/i,
        /^(?:how\s+(?:do|can|should|to|do\s+I|can\s+I|should\s+I))/i,
        /^(?:improve|boost|increase|get\s+better)/i,
        /^(?:left\s+(?:with|to)\s+(?:study|learn|do))/i,
        /^(?:remaining|missing|incomplete|pending)/i,
        /^(?:study|learn|revise|practice|prepare)/i,
        /^(?:which|what)\s+(?:grade|subject|topic|book|resource)/i,
        /^(?:where|when)\s+(?:can|do|should|did)/i,
        /marks|grades|score|results|performance/i,
        /improve|better|enhance|boost|increase/i,
        /struggling|difficult|hard|weak|challenge/i,
        /recommend|suggest|advice|tip|guide/i,
        /study\s+plan|revision|schedule|timetable/i,
        /left\s+with|remaining|what\s+else|still\s+need/i,
    ];

    /**
     * Check if a query should be handled by AI
     */
    function isAIQuery(query) {
        const text = query.toLowerCase().trim();
        // Short queries (1-2 words) are likely search terms, not questions
        if (text.split(/\s+/).length < 3) return false;
        return aiQueryPatterns.some(pattern => pattern.test(text));
    }

    /**
     * Send query to AI and display response
     */
    async function performAISearch(query) {
        suggestionsEl.style.display = 'none';
        resultsEl.style.display = 'block';
        noResultsEl.style.display = 'none';

        // Show loading state
        resultsList.innerHTML = `
            <div style="text-align:center; padding:30px 16px;">
                <div style="width:48px; height:48px; border-radius:50%; background:linear-gradient(135deg, #6366f1, #8b5cf6); margin:0 auto 16px; display:flex; align-items:center; justify-content:center; animation: askPulse 1s infinite;">
                    <i class="fas fa-robot" style="color:white; font-size:20px;"></i>
                </div>
                <p style="font-size:0.95rem; color:#6b7280; margin:0;">Thinking about your question...</p>
            </div>
        `;

        try {
            const res = await fetch('/api/search/ai', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ query: query })
            });
            const data = await res.json();

            if (data.success && data.response) {
                resultsList.innerHTML = '';

                // AI Response card
                const responseDiv = document.createElement('div');
                responseDiv.style.cssText = 'padding: 16px; background: linear-gradient(135deg, #f0f0ff, #ede9fe); border-radius: 12px; margin-bottom: 12px;';
                responseDiv.innerHTML = `
                    <div style="display:flex; align-items:center; gap:10px; margin-bottom:10px;">
                        <div style="width:32px; height:32px; border-radius:8px; background:linear-gradient(135deg, #6366f1, #8b5cf6); display:flex; align-items:center; justify-content:center;">
                            <i class="fas fa-robot" style="color:white; font-size:14px;"></i>
                        </div>
                        <span style="font-weight:700; color:#4338ca; font-size:0.85rem;">StudySmart AI</span>
                    </div>
                    <p style="font-size:0.95rem; color:#1e293b; line-height:1.6; margin:0;">${escapeHtml(data.response).replace(/\n/g, '<br>')}</p>
                `;
                resultsList.appendChild(responseDiv);

                // Suggestion buttons
                if (data.suggestions && data.suggestions.length > 0) {
                    const suggestionsDiv = document.createElement('div');
                    suggestionsDiv.style.cssText = 'display:flex; flex-wrap:wrap; gap:8px; padding: 4px 0;';

                    data.suggestions.forEach(suggestion => {
                        const btn = document.createElement('a');
                        btn.href = suggestion.url || '#';
                        btn.className = 'ask-app-chip';
                        btn.style.cssText = 'display:inline-flex; align-items:center; gap:6px; background:#6366f1; color:white; padding:10px 16px; border-radius:10px; font-size:0.85rem; font-weight:600; text-decoration:none; cursor:pointer; transition:all 0.2s;';
                        btn.innerHTML = `<i class="${escapeHtml(suggestion.icon || 'fas fa-arrow-right')}" style="font-size:12px;"></i> ${escapeHtml(suggestion.text)}`;
                        btn.onmouseover = () => btn.style.background = '#4f46e5';
                        btn.onmouseout = () => btn.style.background = '#6366f1';
                        btn.onclick = (e) => {
                            e.preventDefault();
                            closeModal();
                            window.location.href = suggestion.url;
                        };
                        suggestionsDiv.appendChild(btn);
                    });

                    resultsList.appendChild(suggestionsDiv);
                }
            } else {
                // Fallback to normal search
                performSearch(query);
            }
        } catch (err) {
            console.error('AI search error:', err);
            // Fallback to normal search on error
            performSearch(query);
        }
    }

    // Utility
    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }
})();
</script>