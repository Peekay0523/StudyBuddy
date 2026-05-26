/**
 * Math Calculator - Advanced Academic Expression Builder (AI Enhanced)
 */

class MathCalculator extends BaseCalculator {
    constructor() {
        super('Mathematics', 'math-calc-panel', 'math-calc-trigger');
        this.variableData = {
            'x': { name: 'Variable x', unit: '' },
            'y': { name: 'Variable y', unit: '' },
            'z': { name: 'Variable z', unit: '' },
            'a': { name: 'Coefficient a', unit: '' },
            'b': { name: 'Coefficient b', unit: '' },
            'c': { name: 'Constant c', unit: '' },
            'm': { name: 'Slope', unit: '' },
            'r': { name: 'Radius/Rate', unit: '' },
            'n': { name: 'Sample Size', unit: '' },
            'P(A)': { name: 'Prob(A)', unit: '' },
            'P(B)': { name: 'Prob(B)', unit: '' },
            'P(A \\cup B)': { name: 'Union', unit: '' },
            'P(A \\cap B)': { name: 'Intersection', unit: '' },
            'P(A|B)': { name: 'Conditional', unit: '' },
            '\\mu': { name: 'Mean', unit: '' },
            '\\sigma': { name: 'Std Dev', unit: '' },
            '\\Delta': { name: 'Change', unit: '' }
        };
    }

    insert(latex, value) {
        // Intercept matrix insertions to trigger dynamic operation selection
        if (latex.includes('bmatrix')) {
            this.expression = latex;
            this.rawExpression = latex;
            this.updateDisplay();
            // Ask user what they want to do with the matrix
            this.promptForVariables(latex, ['Determinant', 'Inverse', 'Transpose', 'Rank', 'Trace']);
            return;
        }
        super.insert(latex, value);
    }

    handleExtraButtons(btn) {
        // Handle Matrix dimension prompt from button
        if (btn.dataset.latex && btn.dataset.latex.includes('bmatrix')) {
            this.promptMatrixDimensions();
        } else {
            super.handleExtraButtons(btn);
        }
    }

    promptMatrixDimensions() {
        if (!this.result) return;
        this.result.style.textAlign = 'center';
        this.result.innerHTML = `
            <div class="calc-step-container">
                <span class="calc-step-title">Matrix Dimensions</span>
                <p style="font-size: 0.8rem; color: #666; margin-bottom: 12px;">Choose size (max 4x4)</p>
                <div style="display: flex; gap: 10px; justify-content: center; margin-bottom: 15px;">
                    <input type="number" id="matrix-rows" value="2" min="1" max="4" class="form-control" style="width: 70px;">
                    <span style="align-self: center;">&times;</span>
                    <input type="number" id="matrix-cols" value="2" min="1" max="4" class="form-control" style="width: 70px;">
                </div>
                <button class="sci-btn action" id="generate-matrix-btn" style="width: 100%;">Create Grid</button>
            </div>
        `;

        this.result.querySelector('#generate-matrix-btn').onclick = () => {
            const r = parseInt(this.result.querySelector('#matrix-rows').value);
            const c = parseInt(this.result.querySelector('#matrix-cols').value);
            this.showMatrixInput(r, c);
        };
    }

    showMatrixInput(rows, cols) {
        let gridHtml = `<div style="display: grid; grid-template-columns: repeat(${cols}, 1fr); gap: 8px; margin-bottom: 15px;">`;
        for (let i = 0; i < rows; i++) {
            for (let j = 0; j < cols; j++) {
                gridHtml += `<input type="number" class="matrix-input" data-row="${i}" data-col="${j}" placeholder="0" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 8px; text-align: center; font-size: 0.9rem;">`;
            }
        }
        gridHtml += `</div>`;

        this.result.innerHTML = `
            <div class="calc-step-container">
                <span class="calc-step-title">${rows} &times; ${cols} Matrix Entry</span>
                ${gridHtml}
                <div class="calc-form-actions">
                    <button class="calc-reset-btn" id="cancel-matrix"><i class="fas fa-times"></i> Cancel</button>
                    <button class="calc-submit-btn" id="insert-matrix-btn" style="width: 100%;">Analyze Matrix</button>
                </div>
            </div>
        `;

        this.result.querySelector('#cancel-matrix').onclick = () => this.clear();

        this.result.querySelector('#insert-matrix-btn').onclick = () => {
            const values = [];
            for (let i = 0; i < rows; i++) {
                const row = [];
                for (let j = 0; j < cols; j++) {
                    const val = this.result.querySelector(`.matrix-input[data-row="${i}"][data-col="${j}"]`).value || '0';
                    row.push(val);
                }
                values.push(row.join(' & '));
            }
            const latex = `\\begin{bmatrix} ${values.join(' \\\\ ')} \\end{bmatrix}`;
            // Use this.insert to trigger the operation selection flow
            this.insert(latex, latex);
        };
    }
}

// Initialize when DOM is ready
function initMathCalculator() {
    if (window.mathCalculator) return;
    window.mathCalculator = new MathCalculator();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initMathCalculator);
} else {
    initMathCalculator();
}
