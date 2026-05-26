<!-- Mathematics Calculator Component -->
<div class="calc-backdrop"></div>

<button class="subject-calc-trigger" id="math-calc-trigger" onclick="mathCalculator.toggle()" style="background: linear-gradient(135deg, #6366f1, #a855f7);">
    <i class="fas fa-calculator"></i>
</button>

<div class="scientific-calc-panel tex2jax_ignore" id="math-calc-panel">
    <div class="calc-panel-header">
        <h3><i class="fas fa-pi"></i> Mathematics Lab</h3>
        <button class="calc-close-btn" onclick="mathCalculator.close()">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <div class="calc-panel-display">
        <div class="calc-preview-area" id="math-preview"></div>
        <div class="calc-result-area" id="math-result"></div>
        <div class="calc-loading-overlay">
            <i class="fas fa-spinner fa-spin"></i> AI is thinking...
        </div>
    </div>

    <div class="calc-tabs-nav">
        <button class="calc-tab-trigger active" data-tab="basic">Basic</button>
        <button class="calc-tab-trigger" data-tab="algebra">Algebra</button>
        <button class="calc-tab-trigger" data-tab="calculus">Calculus</button>
        <button class="calc-tab-trigger" data-tab="prob">Prob/Stats</button>
        <button class="calc-tab-trigger" data-tab="matrices">Matrices</button>
        <button class="calc-tab-trigger" data-tab="greek">Greek</button>
    </div>

    <div class="calc-panel-body">
        <!-- Basic Tab -->
        <div class="calc-tab-content active" id="calc-tab-basic">
            <button class="sci-btn" data-latex="7">7</button>
            <button class="sci-btn" data-latex="8">8</button>
            <button class="sci-btn" data-latex="9">9</button>
            <button class="sci-btn operator" data-latex="+">+</button>
            
            <button class="sci-btn" data-latex="4">4</button>
            <button class="sci-btn" data-latex="5">5</button>
            <button class="sci-btn" data-latex="6">6</button>
            <button class="sci-btn operator" data-latex="-">-</button>
            
            <button class="sci-btn" data-latex="1">1</button>
            <button class="sci-btn" data-latex="2">2</button>
            <button class="sci-btn" data-latex="3">3</button>
            <button class="sci-btn operator" data-latex="\times" data-value="*">×</button>
            
            <button class="sci-btn" data-latex="0">0</button>
            <button class="sci-btn" data-latex=".">.</button>
            <button class="sci-btn" data-latex="\pi" data-value="pi">π</button>
            <button class="sci-btn operator" data-latex="\div" data-value="/">÷</button>
            
            <button class="sci-btn action-secondary calc-clear-btn">AC</button>
            <button class="sci-btn action-secondary calc-del-btn">DEL</button>
            <button class="sci-btn action calc-solve-btn" style="grid-column: span 4;">Solve</button>
        </div>

        <!-- Algebra Tab -->
        <div class="calc-tab-content" id="calc-tab-algebra">
            <button class="sci-btn" data-latex="ax + b = c"><strong>ax+b=c</strong></button>
            <button class="sci-btn" data-latex="x = \frac{-b \pm \sqrt{b^2 - 4ac}}{2a}"><strong>Quadratic</strong></button>
            <button class="sci-btn" data-latex="m = \frac{y_2 - y_1}{x_2 - x_1}"><strong>Slope</strong></button>
            <button class="sci-btn" data-latex="y = mx + c"><strong>Linear</strong></button>
            
            <button class="sci-btn" data-latex="x">x</button>
            <button class="sci-btn" data-latex="y">y</button>
            <button class="sci-btn" data-latex="x^2">x²</button>
            <button class="sci-btn" data-latex="x^n" data-value="^">xⁿ</button>
            
            <button class="sci-btn" data-latex="\sqrt{x}" data-value="sqrt(">√</button>
            <button class="sci-btn" data-latex="\log" data-value="log10(">log</button>
            <button class="sci-btn" data-latex="(">(</button>
            <button class="sci-btn" data-latex=")">)</button>
            
            <button class="sci-btn" data-latex="\sin" data-value="sin(">sin</button>
            <button class="sci-btn" data-latex="\cos" data-value="cos(">cos</button>
            <button class="sci-btn" data-latex="\tan" data-value="tan(">tan</button>
            <button class="sci-btn" data-latex="=" data-value="=">=</button>

            <button class="sci-btn action-secondary calc-clear-btn">AC</button>
            <button class="sci-btn action-secondary calc-del-btn">DEL</button>
            <button class="sci-btn action calc-solve-btn" style="grid-column: span 4;">Solve</button>
        </div>

        <!-- Calculus Tab -->
        <div class="calc-tab-content" id="calc-tab-calculus">
            <button class="sci-btn" data-latex="\frac{d}{dx}(f(x))"><strong>d/dx</strong></button>
            <button class="sci-btn" data-latex="\int f(x) \, dx"><strong>∫ f(x)</strong></button>
            <button class="sci-btn" data-latex="\lim_{x \to c} f(x)"><strong>lim</strong></button>
            <button class="sci-btn" data-latex="\frac{d^2y}{dx^2} + p\frac{dy}{dx} + qy = f(x)"><strong>ODE</strong></button>
            
            <button class="sci-btn" data-latex="\sum_{i=1}^n i">Σ</button>
            <button class="sci-btn" data-latex="\infty">∞</button>
            <button class="sci-btn" data-latex="e">e</button>
            <button class="sci-btn" data-latex="\Delta">Δ</button>

            <button class="sci-btn action-secondary calc-clear-btn">AC</button>
            <button class="sci-btn action-secondary calc-del-btn">DEL</button>
            <button class="sci-btn action calc-solve-btn" style="grid-column: span 4;">Solve</button>
        </div>

        <!-- Prob/Stats Tab -->
        <div class="calc-tab-content" id="calc-tab-prob">
            <button class="sci-btn" data-latex="P(A \cup B) = P(A) + P(B) - P(A \cap B)"><strong>Union</strong></button>
            <button class="sci-btn" data-latex="P(A|B) = \frac{P(B|A)P(A)}{P(B)}"><strong>Bayes</strong></button>
            <button class="sci-btn" data-latex="z = \frac{x - \mu}{\sigma}"><strong>z-score</strong></button>
            <button class="sci-btn" data-latex="\sigma = \sqrt{\frac{\sum(x-\mu)^2}{n}}"><strong>Std Dev</strong></button>
            
            <button class="sci-btn" data-latex="n!">n!</button>
            <button class="sci-btn" data-latex="nCr">nCr</button>
            <button class="sci-btn" data-latex="\mu">μ</button>
            <button class="sci-btn" data-latex="\sigma">σ</button>

            <button class="sci-btn action-secondary calc-clear-btn">AC</button>
            <button class="sci-btn action-secondary calc-del-btn">DEL</button>
            <button class="sci-btn action calc-solve-btn" style="grid-column: span 4;">Solve</button>
        </div>

        <!-- Matrices Tab -->
        <div class="calc-tab-content" id="calc-tab-matrices">
            <button class="sci-btn" data-latex="\begin{bmatrix} a & b \\ c & d \end{bmatrix}">2x2</button>
            <button class="sci-btn" data-latex="\det(A)">det</button>
            <button class="sci-btn" data-latex="A^{-1}">A⁻¹</button>
            <button class="sci-btn" data-latex="A^T">Aᵀ</button>

            <button class="sci-btn action-secondary calc-clear-btn">AC</button>
            <button class="sci-btn action-secondary calc-del-btn">DEL</button>
            <button class="sci-btn action calc-solve-btn" style="grid-column: span 4;">Solve</button>
        </div>

        <!-- Greek Tab -->
        <div class="calc-tab-content" id="calc-tab-greek">
            <button class="sci-btn" data-latex="\alpha">α</button>
            <button class="sci-btn" data-latex="\beta">β</button>
            <button class="sci-btn" data-latex="\gamma">γ</button>
            <button class="sci-btn" data-latex="\theta">θ</button>
            <button class="sci-btn" data-latex="\lambda">λ</button>
            <button class="sci-btn" data-latex="\omega">ω</button>
            <button class="sci-btn" data-latex="\phi">φ</button>
            <button class="sci-btn" data-latex="\psi">ψ</button>

            <button class="sci-btn action-secondary calc-clear-btn">AC</button>
            <button class="sci-btn action-secondary calc-del-btn">DEL</button>
            <button class="sci-btn action calc-solve-btn" style="grid-column: span 4;">Solve</button>
        </div>
    </div>
</div>
