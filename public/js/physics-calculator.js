/**
 * Physics Calculator - Formula & Equation Solver (AI Enhanced)
 */

class PhysicsCalculator extends BaseCalculator {
    constructor() {
        super('Physics', 'phys-calc-panel', 'phys-calc-trigger');
        this.variableData = {
            'F': { name: 'Force', unit: 'N' },
            'm': { name: 'Mass', unit: 'kg' },
            'a': { name: 'Acceleration', unit: 'm/s²' },
            'v': { name: 'Final Velocity', unit: 'm/s' },
            'u': { name: 'Initial Velocity', unit: 'm/s' },
            't': { name: 'Time', unit: 's' },
            's': { name: 'Displacement', unit: 'm' },
            'E_k': { name: 'Kinetic Energy', unit: 'J' },
            'E_p': { name: 'Potential Energy', unit: 'J' },
            'g': { name: 'Gravity', unit: 'm/s²' },
            'h': { name: 'Height', unit: 'm' },
            'p': { name: 'Momentum', unit: 'kg·m/s' },
            'W': { name: 'Work', unit: 'J' },
            'd': { name: 'Distance', unit: 'm' },
            'P': { name: 'Power', unit: 'W' },
            'V': { name: 'Voltage', unit: 'V' },
            'I': { name: 'Current', unit: 'A' },
            'R': { name: 'Resistance', unit: 'Ω' },
            'Q': { name: 'Charge/Heat', unit: 'C/J' },
            'f': { name: 'Frequency', unit: 'Hz' },
            '\\lambda': { name: 'Wavelength', unit: 'm' },
            'T': { name: 'Period/Temp', unit: 's/K' },
            'n': { name: 'Refractive Index', unit: '' },
            'c': { name: 'Speed of Light', unit: 'm/s' },
            '\\Delta T': { name: 'Δ Temperature', unit: 'K' },
            '\\Delta U': { name: 'Internal Energy', unit: 'J' },
            'F_e': { name: 'Electric Force', unit: 'N' },
            'q_1': { name: 'Charge 1', unit: 'C' },
            'q_2': { name: 'Charge 2', unit: 'C' },
            'k': { name: 'Coulomb Const', unit: '' },
            'r': { name: 'Distance', unit: 'm' },
            'W_0': { name: 'Work Function', unit: 'J' },
            'f_0': { name: 'Threshold Freq', unit: 'Hz' }
        };
    }

    insert(latex, value, formulaId = null) {
        // Detect if a complete formula was inserted
        const isAssignment = latex.includes('=');
        
        // Clean LaTeX for variable extraction
        const cleaned = latex.replace(/\\(cdot|frac|times|sqrt|pm|mp|left|right|text|mathbf|s)/g, ' ');
        const varsRegex = /(?:\\Delta\s*[a-zA-Z]|\\lambda|\\theta|\\phi|\\sigma|\\omega|\\epsilon|F_e|E_k|E_p|W_0|f_0|q_1|q_2|[a-zA-Z])(?:_{?[\d\w]*}?)?/g;
        const vars = cleaned.match(varsRegex) || [];
        const uniqueVars = [...new Set(vars)].filter(v => !/^[0-9]+$/.test(v));
        
        // Improved constant detection
        const isConstant = /^[a-zA-Z\\]+\s*=\s*[0-9.e\^x\-\+\s\\]+$/.test(latex);

        // If it's a formula assignment (Ep = mgh) or has a formulaId, reset and start fresh
        if (formulaId || (isAssignment && uniqueVars.length > 0 && !isConstant)) {
            console.log(`[Physics Calc] New formula selected: ${latex} (ID: ${formulaId})`);
            this.reset(); // Full reset to prevent leakage
            this.expression = latex;
            this.rawExpression = latex;
            this.activeFormulaId = formulaId;
            this.updateDisplay();
            this.promptForVariables(latex, uniqueVars);
        } else {
            // Numbers, symbols, constants (like g=9.8) or partial insertions
            super.insert(latex, value);
        }
    }

    async promptForVariables(formula, uniqueVars) {
        if (!this.result) return;

        this.result.style.textAlign = 'left'; 
        this.result.innerHTML = `
            <div class="calc-step-container" data-formula="${formula}">
                <div class="calc-formula-header" style="text-align: center; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 1px solid #f0f0f0;">
                    <span style="font-size: 0.75rem; color: #999; text-transform: uppercase;">Active Formula</span>
                    <div class="active-formula-display" style="font-size: 1.2rem; color: #333; margin-top: 5px;">\\[ ${formula} \\]</div>
                </div>
                <span class="calc-step-title">Select Target Variable</span>
                <p style="font-size: 0.85rem; margin-bottom: 12px; color: #555;">Which value do you want to calculate for?</p>
                <div class="calc-var-selector">
                    ${uniqueVars.map(v => `
                        <button type="button" class="var-select-btn" data-var="${v}" style="touch-action: manipulation;">
                            $${v}$
                        </button>
                    `).join('')}
                </div>
            </div>
        `;

        this.queueTypeset(this.result);
    }

    handleSciButton(btn) {
        if (btn.dataset.formulaId) {
            // Formula button clicked - handle specialized insertion
            this.insert(btn.dataset.latex, btn.dataset.value, btn.dataset.formulaId);
        } else {
            // Standard button (AC, DEL, Solve, or partial latex)
            super.handleSciButton(btn);
        }
    }

    handleExtraButtons(btn) {
        if (btn.classList.contains('var-select-btn')) {
            const targetVar = btn.dataset.var;
            // ALWAYS use this.expression as the source of truth
            const formula = this.expression;
            const vars = Array.from(this.result.querySelectorAll('.var-select-btn')).map(b => b.dataset.var);
            
            console.log(`[Physics Calc] Variable selected: ${targetVar} for formula: ${formula}`);
            this.showInputForm(formula, vars, targetVar);
        } else if (btn.classList.contains('calc-reset-btn')) {
            this.reset();
        } else if (btn.classList.contains('calc-submit-btn')) {
            this.validateAndSolve();
        }
    }

    validateAndSolve() {
        const submitBtn = this.result.querySelector('.calc-submit-btn');
        if (!submitBtn) return;

        const targetVar = submitBtn.dataset.target || '';
        if (!targetVar) {
            console.error('[Physics Calc] No target variable found');
            return;
        }
        
        // Ensure formula is correct
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

        if (allFilled) {
            console.log(`[Physics Calc] Solving for ${targetVar} in ${formula}`);
            this.performAIsolve(formula, values, targetVar);
        }
    }

    showInputForm(formula, uniqueVars, targetVar) {
        const otherVars = uniqueVars.filter(v => v !== targetVar);
        
        this.result.innerHTML = `
            <div class="calc-step-container">
                <div class="calc-formula-header" style="text-align: center; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 1px solid #f0f0f0;">
                    <span style="font-size: 0.75rem; color: #999; text-transform: uppercase;">Solving For $${targetVar}$</span>
                    <div class="active-formula-display" style="font-size: 1.1rem; color: #666; margin-top: 5px;">\\[ ${formula} \\]</div>
                </div>
                <span class="calc-step-title">Enter Known Values</span>
                <div class="calc-input-grid">
                    ${otherVars.map(v => {
                        const data = this.variableData[v] || { name: v.replace(/\\/g, ''), unit: '' };
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
                    <button type="button" class="calc-reset-btn" style="touch-action: manipulation;"><i class="fas fa-undo"></i> Reset</button>
                    <button type="button" class="calc-submit-btn" data-target="${targetVar}" style="touch-action: manipulation;">Solve for $${targetVar}$</button>
                </div>
            </div>
        `;

        this.queueTypeset(this.result);
    }

    async performAIsolve(formula, values, targetVar) {
        const data = this.variableData[targetVar] || { name: '', unit: '' };
        const cleanTarget = targetVar.replace(/\\/g, '');
        let problemDescription = `ACT AS A PURE PHYSICS ENGINE. 
        Formula: ${formula}
        Given Values:
        `;
        for (const [v, val] of Object.entries(values)) {
            const vData = this.variableData[v] || { unit: '' };
            problemDescription += `- ${v} = ${val} ${vData.unit}\n`;
        }
        problemDescription += `TASK: Solve for ${targetVar}. 
        REQUIREMENT: Output ONLY the result in the exact format: ${cleanTarget}=[value]${data.unit}
        STRICT RULES:
        1. NO spaces.
        2. NO steps, NO formulas, NO substitutions.
        3. NO introductory text.
        4. Use a DOT (.) as the decimal separator.
        Example: F=15.5N`;
        
        this.result.style.textAlign = 'right';
        this.solveWithAI(problemDescription);
    }

    async solveWithAI(customMessage = null) {
        // Store reference to super.solveWithAI's original behavior but we need more control
        if (!customMessage && !this.expression) return;
        
        if (this.loading) this.loading.style.display = 'flex';
        if (this.result) {
            this.result.innerHTML = '<span style="font-size: 0.9rem; color: #666;">Calculating...</span>';
        }

        try {
            const formData = new FormData();
            formData.append('message', customMessage || `ACT AS A PURE PHYSICS ENGINE.
            INPUT: ${this.expression}
            TASK: Solve.
            REQUIREMENT: FINAL ANSWER ONLY.
            SUBJECT: PHYSICS`);

            const response = await fetch('/api/chatbot', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();
            if (data.reply) {
                let reply = data.reply.trim();
                
                // Remove any LaTeX code blocks if AI included them
                reply = reply.replace(/```mathlab\s*|```/g, '');
                
                // Set the exact reply as requested
                this.result.innerHTML = reply;
                
                if (window.MathJax) {
                    // Use inline typesetting for the compact result
                    window.MathJax.typesetPromise([this.result]).catch((err) => console.log(err));
                }
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

    backspace() {
        const hadEquals = this.expression.includes('=');
        super.backspace();
        // If they deleted the equals sign or cleared it, reset the result area
        if ((hadEquals && !this.expression.includes('=')) || this.expression === '') {
            if (this.result) {
                this.result.innerHTML = '';
                this.result.style.textAlign = 'right';
            }
        }
    }
}

// Initialize when DOM is ready
function initPhysicsCalculator() {
    try {
        if (window.physicsCalculator) return;
        
        // Check if BaseCalculator is available
        if (typeof BaseCalculator === 'undefined') {
            console.error('BaseCalculator not found. PhysicsCalculator requires calculators-core.js');
            return;
        }

        window.physicsCalculator = new PhysicsCalculator();
        console.log('Physics Calculator initialized successfully.');
    } catch (err) {
        console.error('Failed to initialize Physics Calculator:', err);
    }
}

// Support both global onclick and delegated listeners
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPhysicsCalculator);
} else {
    initPhysicsCalculator();
}

// Immediate global assignment to prevent ReferenceErrors if called very early
if (typeof PhysicsCalculator !== 'undefined' && !window.physicsCalculator) {
    try {
        // Speculative initialization if DOM is already mature
        if (document.getElementById('phys-calc-panel')) {
            initPhysicsCalculator();
        }
    } catch(e) {}
}

