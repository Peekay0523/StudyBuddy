/**
 * Shared Logic for Subject-Specific Floating Calculators
 */

class BaseCalculator {
    constructor(subject, panelId, triggerId) {
        this.subject = subject;
        this.panel = document.getElementById(panelId);
        
        if (!this.panel) {
            console.warn(`Calculator panel not found: ${panelId}`);
            return;
        }

        this.trigger = document.getElementById(triggerId);
        this.backdrop = document.querySelector('.calc-backdrop');
        this.preview = this.panel.querySelector('.calc-preview-area');
        this.result = this.panel.querySelector('.calc-result-area');
        this.loading = this.panel.querySelector('.calc-loading-overlay');
        
        this.reset();
        this.initBase();
    }

    reset() {
        this.expression = '';
        this.rawExpression = '';
        this.isTypesetting = false;
        this.typesetQueue = [];
        if (this.preview) this.preview.innerHTML = '';
        if (this.result) {
            this.result.innerHTML = '';
            this.result.style.textAlign = 'right';
        }
        if (this.loading) this.loading.style.display = 'none';
        console.log(`[${this.subject} Calc] State reset.`);
    }

    initBase() {
        if (!this.panel) return;
        this.initDraggable();
        this.initClickOutside();
        this.initDelegatedEvents(); 
    }

    initDraggable() {
        const header = this.panel.querySelector('.calc-panel-header');
        if (!header) return;

        let isDragging = false;
        let startX, startY, initialX, initialY;

        const startDrag = (e) => {
            if (window.innerWidth <= 480) return; 
            if (e.target.closest('.calc-close-btn')) return; // Don't drag when closing
            
            isDragging = true;
            const clientX = e.type === 'mousedown' ? e.clientX : e.touches[0].clientX;
            const clientY = e.type === 'mousedown' ? e.clientY : e.touches[0].clientY;
            
            startX = clientX;
            startY = clientY;
            const rect = this.panel.getBoundingClientRect();
            initialX = rect.left;
            initialY = rect.top;
            header.style.cursor = 'grabbing';
        };

        const onDrag = (e) => {
            if (!isDragging) return;
            const clientX = e.type === 'mousemove' ? e.clientX : e.touches[0].clientX;
            const clientY = e.type === 'mousemove' ? e.clientY : e.touches[0].clientY;
            
            const dx = clientX - startX;
            const dy = clientY - startY;
            
            // Apply new position
            this.panel.style.left = (initialX + dx + this.panel.offsetWidth/2) + 'px';
            this.panel.style.top = (initialY + dy + this.panel.offsetHeight/2) + 'px';
            this.panel.style.transform = 'translate(-50%, -50%)';
        };

        const stopDrag = () => {
            isDragging = false;
            header.style.cursor = 'move';
        };

        header.addEventListener('mousedown', startDrag);
        header.addEventListener('touchstart', startDrag, { passive: true });
        document.addEventListener('mousemove', onDrag);
        document.addEventListener('touchmove', onDrag, { passive: false });
        document.addEventListener('mouseup', stopDrag);
        document.addEventListener('touchend', stopDrag);
    }

    initTabs() {
        // Obsolete: merged into initDelegatedEvents
    }

    initClickOutside() {
        if (this.backdrop) {
            this.backdrop.addEventListener('click', (e) => {
                e.preventDefault();
                this.close();
            });
        }
    }

    initDelegatedEvents() {
        if (!this.panel) return;

        // Optimized event handler for instant responsiveness
        const handleInteraction = (e) => {
            // Check if it's a real user interaction
            if (e.type === 'pointerdown' && e.button !== 0) return; // Only left click/primary touch

            // 1. Find the target button or interactive element
            const interactiveEl = e.target.closest('.sci-btn, .var-select-btn, .calc-submit-btn, .calc-reset-btn, .calc-close-btn, .calc-tab-trigger');
            
            if (!interactiveEl) {
                console.debug(`[${this.subject} Calc] Click ignored on:`, e.target);
                return;
            }

            console.log(`[${this.subject} Calc] ${e.type} captured on:`, interactiveEl.className || interactiveEl.tagName);

            // Prevent default for all calculator buttons to avoid any unwanted form behavior or double-firing
            e.preventDefault();
            e.stopPropagation();

            // Visual feedback for non-CSS handled states
            interactiveEl.style.opacity = '0.7';
            setTimeout(() => interactiveEl.style.opacity = '', 100);

            // 2. Route the interaction
            try {
                if (interactiveEl.classList.contains('calc-tab-trigger')) {
                    this.handleTabSwitch(interactiveEl);
                } else if (interactiveEl.classList.contains('sci-btn')) {
                    this.handleSciButton(interactiveEl);
                } else if (interactiveEl.classList.contains('calc-close-btn')) {
                    this.close();
                } else {
                    // Let subclasses handle their specific buttons (var-select, submit, etc)
                    this.handleExtraButtons(interactiveEl);
                }
            } catch (err) {
                console.error(`[${this.subject} Calc] Error handling interaction:`, err);
            }
        };

        // Use pointerdown for immediate response, falling back to click for accessibility
        // Use a flag to prevent double-firing on some environments
        let lastEventTime = 0;
        const throttledHandler = (e) => {
            const now = Date.now();
            if (now - lastEventTime < 50) return; // Throttle very fast repeated events
            lastEventTime = now;
            handleInteraction(e);
        };

        // Remove existing listener to prevent double-binding if init is called multiple times
        this.panel.removeEventListener('click', throttledHandler);
        this.panel.removeEventListener('pointerdown', throttledHandler);

        this.panel.addEventListener('click', throttledHandler);
        this.panel.addEventListener('pointerdown', throttledHandler);
        
        // Touch-action optimization to remove click delay
        this.panel.style.touchAction = 'manipulation';
        this.panel.style.pointerEvents = 'auto';
    }

    handleTabSwitch(tab) {
        const target = tab.dataset.tab;
        const tabs = this.panel.querySelectorAll('.calc-tab-trigger');
        const contents = this.panel.querySelectorAll('.calc-tab-content');

        tabs.forEach(t => t.classList.toggle('active', t === tab));
        contents.forEach(c => {
            c.classList.toggle('active', c.id === 'calc-tab-' + target);
        });
    }

    handleSciButton(btn) {
        if (btn.dataset.latex !== undefined) {
            this.insert(btn.dataset.latex, btn.dataset.value);
        } else if (btn.classList.contains('calc-clear-btn')) {
            this.clear();
        } else if (btn.classList.contains('calc-del-btn')) {
            this.backspace();
        } else if (btn.classList.contains('calc-solve-btn')) {
            this.solveWithAI();
        }
    }

    // Hook for subclasses
    handleExtraButtons(btn) {
        // Base implementation does nothing
    }

    // Optimized typesetting with queueing to prevent thread blocking
    async queueTypeset(element) {
        if (!window.MathJax) return;
        
        return new Promise((resolve) => {
            this.typesetQueue.push({ element, resolve });
            this.processTypesetQueue();
        });
    }

    async processTypesetQueue() {
        if (this.isTypesetting || this.typesetQueue.length === 0) return;
        
        this.isTypesetting = true;
        const { element, resolve } = this.typesetQueue.shift();
        
        try {
            await window.MathJax.typesetPromise([element]);
        } catch (err) {
            console.error('MathJax error:', err);
        } finally {
            this.isTypesetting = false;
            resolve();
            this.processTypesetQueue();
        }
    }

    bindBaseButtons() {
        // This method is now handled by initDelegatedEvents
        // Keeping empty shell for compatibility
    }

    toggle() {
        if (!this.panel) return;
        if (this.panel.classList.contains('active')) {
            this.close();
        } else {
            this.open();
        }
    }

    open() {
        if (!this.panel) return;
        this.panel.classList.add('active');
        if (this.backdrop) this.backdrop.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    close() {
        if (!this.panel) return;
        this.panel.classList.remove('active');
        if (this.backdrop) this.backdrop.classList.remove('active');
        document.body.style.overflow = '';
    }

    insert(latex, value) {
        // Detect if a formula was inserted (contains an equals sign)
        const isAssignment = latex.includes('=');
        
        if (isAssignment) {
            this.handleFormulaInsertion(latex, value);
        } else {
            // Normal insertion (numbers, symbols, etc)
            this.expression += latex;
            this.rawExpression += value || latex;
            this.updateDisplay();
        }
    }

    handleFormulaInsertion(latex, value) {
        // Clean LaTeX for variable extraction
        const cleaned = latex.replace(/\\(cdot|frac|times|sqrt|pm|mp|left|right|text|mathbf|s|begin|end|bmatrix)/g, ' ');
        // Broad regex for variables including Greek, subscripts, and probability functions
        const varsRegex = /(?:\\Delta\s*[a-zA-Z]|\\mu|\\sigma|\\alpha|\\beta|\\gamma|\\theta|\\phi|\\omega|\\epsilon|\\lambda|P\([a-zA-Z\s\\cap\\cup|]+\)|[a-zA-Z])(?:_{?[\d\w]*}?)?/g;
        const vars = cleaned.match(varsRegex) || [];
        
        const uniqueVars = [...new Set(vars)].filter(v => {
            if (/^[0-9]+$/.test(v)) return false; // Ignore pure numbers
            if (['e', 'pi', 'i', 'R'].includes(v) && !latex.includes('\\Delta ' + v)) return false; // Ignore common constants
            return true;
        });

        // Improved constant detection: only match single variable assignments like x=5 or g=9.8m/s
        const isConstant = /^[a-zA-Z\\]+\s*=\s*-?[0-9.]+(?:[eE][+-]?[0-9]+)?(?:\s*[a-zA-Z/³²^]+)?$/.test(latex.trim());

        if (uniqueVars.length > 0 && !isConstant) {
            this.expression = latex;
            this.rawExpression = latex;
            this.updateDisplay();
            this.promptForVariables(latex, uniqueVars);
        } else {
            this.expression += latex;
            this.rawExpression += value || latex;
            this.updateDisplay();
        }
    }

    async promptForVariables(formula, uniqueVars) {
        if (!this.result) return;

        this.result.style.textAlign = 'left'; 
        this.result.innerHTML = `
            <div class="calc-step-container">
                <div class="calc-formula-header" style="text-align: center; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 1px solid #f0f0f0;">
                    <span style="font-size: 0.75rem; color: #999; text-transform: uppercase;">Active Formula</span>
                    <div style="font-size: 1.2rem; color: #333; margin-top: 5px;">\\[ ${formula} \\]</div>
                </div>
                <span class="calc-step-title">Select Target Variable</span>
                <p style="font-size: 0.85rem; margin-bottom: 12px; color: #555;">Which value do you want to calculate for?</p>
                <div class="calc-var-selector">
                    ${uniqueVars.map(v => `
                        <button class="var-select-btn" data-var="${v}">
                            $${v}$
                        </button>
                    `).join('')}
                </div>
            </div>
        `;

        this.queueTypeset(this.result);
    }

    showInputForm(formula, uniqueVars, targetVar) {
        const otherVars = uniqueVars.filter(v => v !== targetVar);
        
        // If no other variables are needed, solve immediately
        if (otherVars.length === 0) {
            this.performAIsolve(formula, {}, targetVar);
            return;
        }

        this.result.innerHTML = `
            <div class="calc-step-container">
                <span class="calc-step-title">Enter Known Values</span>
                <div class="calc-input-grid">
                    ${otherVars.map(v => {
                        const data = (this.variableData && this.variableData[v]) || { name: v.replace(/\\/g, ''), unit: '' };
                        return `
                            <div class="calc-input-group">
                                <label>$${v}$ - ${data.name}</label>
                                <div class="calc-input-wrapper">
                                    <input type="number" step="any" class="calc-var-input" data-var="${v}" placeholder="0.00">
                                    ${data.unit ? `<span class="calc-input-unit">${data.unit}</span>` : ''}
                                </div>
                            </div>
                        `;
                    }).join('')}
                </div>
                <div class="calc-form-actions">
                    <button class="calc-reset-btn"><i class="fas fa-undo"></i> Reset</button>
                    <button class="calc-submit-btn" data-target="${targetVar}">Solve for $${targetVar}$</button>
                </div>
            </div>
        `;

        this.queueTypeset(this.result);
    }

    handleExtraButtons(btn) {
        if (btn.classList.contains('var-select-btn')) {
            const targetVar = btn.dataset.var;
            const formulaDiv = this.result.querySelector('.calc-formula-header div');
            const formula = formulaDiv ? formulaDiv.textContent.replace(/\\\[|\\\]/g, '').trim() : this.expression;
            const vars = Array.from(this.result.querySelectorAll('.var-select-btn')).map(b => b.dataset.var);
            this.showInputForm(formula, vars, targetVar);
        } else if (btn.classList.contains('calc-reset-btn')) {
            this.clear();
        } else if (btn.classList.contains('calc-submit-btn')) {
            this.validateAndSolve();
        }
    }

    validateAndSolve() {
        const submitBtn = this.result.querySelector('.calc-submit-btn');
        if (!submitBtn) return;

        const targetVar = submitBtn.dataset.target;
        const formula = this.expression;

        const values = {};
        let allFilled = true;
        this.result.querySelectorAll('.calc-var-input').forEach(input => {
            const val = input.value.trim();
            if (val === '' || isNaN(parseFloat(val))) {
                allFilled = false;
                input.style.borderColor = '#ef4444';
                input.parentElement.classList.add('shake');
                setTimeout(() => input.parentElement.classList.remove('shake'), 500);
            } else {
                values[input.dataset.var] = val;
                input.style.borderColor = '#e0e0e0';
            }
        });

        if (allFilled && targetVar) {
            this.performAIsolve(formula, values, targetVar);
        }
    }

    async performAIsolve(formula, values, targetVar) {
        const data = (this.variableData && this.variableData[targetVar]) || { name: '', unit: '' };
        const cleanTarget = targetVar.replace(/\\/g, '');
        let problemDescription = `ACT AS A PURE ${this.subject.toUpperCase()} ENGINE. 
        Formula: ${formula}
        Given Values:
        `;
        for (const [v, val] of Object.entries(values)) {
            const vData = (this.variableData && this.variableData[v]) || { unit: '' };
            problemDescription += `- ${v} = ${val} ${vData.unit}\n`;
        }
        problemDescription += `TASK: Solve for ${targetVar}. 
        REQUIREMENT: Output ONLY the result in the exact format: ${cleanTarget}=[value]${data.unit}
        STRICT RULES:
        1. NO spaces.
        2. NO steps, NO formulas.
        3. FINAL ANSWER ONLY.`;
        
        this.result.style.textAlign = 'right';
        this.solveWithAI(problemDescription);
    }

    updateDisplay() {
        if (this.preview) {
            this.preview.innerHTML = '\\[ ' + this.expression + ' \\]';
            this.queueTypeset(this.preview);
        }
    }

    clear() {
        this.reset();
    }

    backspace() {
        if (this.expression.length > 0) {
            if (this.expression.endsWith('}')) {
                const lastBrace = this.expression.lastIndexOf('{');
                const lastCommand = this.expression.lastIndexOf('\\', lastBrace);
                if (lastCommand !== -1) {
                    this.expression = this.expression.substring(0, lastCommand);
                } else {
                    this.expression = this.expression.slice(0, -1);
                }
            } else if (this.expression.endsWith(' ')) {
                this.expression = this.expression.trimEnd();
                const lastSpace = this.expression.lastIndexOf(' ');
                if (lastSpace !== -1) {
                    this.expression = this.expression.substring(0, lastSpace + 1);
                } else {
                    this.expression = '';
                }
            } else {
                this.expression = this.expression.slice(0, -1);
            }
            this.updateDisplay();
        }
    }

    async solveWithAI(customMessage = null) {
        const inputExpression = customMessage || this.expression;
        if (!inputExpression) return;
        
        if (this.loading) this.loading.style.display = 'flex';
        if (this.result) this.result.innerHTML = '<span style="font-size: 0.9rem; color: #666;">Analyzing...</span>';

        try {
            const formData = new FormData();
            formData.append('message', customMessage || `ACT AS A PURE MATHEMATICAL/SCIENTIFIC ENGINE. 
            INPUT: ${this.expression}
            TASK: Solve or Balance.
            REQUIREMENT: FINAL ANSWER ONLY. ABSOLUTELY NO WORDS, NO STEPS, NO EXPLANATIONS. 
            If it's an equation for x, output "x = [value]". 
            If it's a chemistry reaction, output the balanced LaTeX equation only.
            SUBJECT: ${this.subject.toUpperCase()}`);

            const response = await fetch('/api/chatbot', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();
            if (data.reply) {
                let reply = data.reply;
                const mathMatch = reply.match(/```mathlab\s*([\s\S]*?)```/);
                if (mathMatch) {
                    reply = mathMatch[1];
                }
                
                this.result.innerHTML = reply;
                this.queueTypeset(this.result);
            } else {
                this.result.innerHTML = 'Error: AI failed to respond.';
            }
        } catch (e) {
            console.error(e);
            this.result.innerHTML = 'Error: Connection failed.';
        } finally {
            if (this.loading) this.loading.style.display = 'none';
        }
    }
}
