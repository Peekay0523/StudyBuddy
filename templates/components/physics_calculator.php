<!-- Physics Calculator Component -->
<div class="calc-backdrop"></div>

<button class="subject-calc-trigger" id="phys-calc-trigger" onclick="physicsCalculator.toggle()" style="background: linear-gradient(135deg, #FF9E00, #FF6D00);">
    <i class="fas fa-atom"></i>
</button>

<div class="scientific-calc-panel tex2jax_ignore" id="phys-calc-panel">
    <div class="calc-panel-header">
        <h3><i class="fas fa-bolt"></i> Physics Lab Solver</h3>
        <button class="calc-close-btn" onclick="physicsCalculator.close()">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <div class="calc-panel-display">
        <div class="calc-preview-area" id="phys-preview"></div>
        <div class="calc-result-area" id="phys-result"></div>
        <div class="calc-loading-overlay">
            <i class="fas fa-spinner fa-spin"></i> AI is calculating...
        </div>
    </div>

    <div class="calc-tabs-nav">
        <button class="calc-tab-trigger active" data-tab="mechanics">Mechanics</button>
        <button class="calc-tab-trigger" data-tab="elec">Electricity</button>
        <button class="calc-tab-trigger" data-tab="waves">Waves/Optics</button>
        <button class="calc-tab-trigger" data-tab="thermo">Thermo</button>
        <button class="calc-tab-trigger" data-tab="modern">Modern</button>
        <button class="calc-tab-trigger" data-tab="const">Constants</button>
    </div>

    <div class="calc-panel-body">
        <!-- Mechanics Tab -->
        <div class="calc-tab-content active" id="calc-tab-mechanics">
            <button class="sci-btn" data-latex="F = m \cdot a"><strong>F = ma</strong></button>
            <button class="sci-btn" data-latex="v = u + a \cdot t"><strong>v = u+at</strong></button>
            <button class="sci-btn" data-latex="s = u \cdot t + \frac{1}{2}a \cdot t^2"><strong>s=ut+½at²</strong></button>
            <button class="sci-btn" data-latex="E_k = \frac{1}{2}m \cdot v^2"><strong>Eₖ = ½mv²</strong></button>
            <button class="sci-btn" data-latex="E_p = m \cdot g \cdot h"><strong>Eₚ = mgh</strong></button>
            <button class="sci-btn" data-latex="p = m \cdot v"><strong>p = mv</strong></button>
            <button class="sci-btn" data-latex="W = F \cdot d"><strong>W = Fd</strong></button>
            <button class="sci-btn" data-latex="P = \frac{W}{t}"><strong>P = W/t</strong></button>
            
            <button class="sci-btn action-secondary calc-clear-btn">AC</button>
            <button class="sci-btn action-secondary calc-del-btn">DEL</button>
            <button class="sci-btn action calc-solve-btn" style="grid-column: span 4;">Solve</button>
        </div>

        <!-- Electricity Tab -->
        <div class="calc-tab-content" id="calc-tab-elec">
            <button class="sci-btn" data-latex="V = I \cdot R"><strong>V = IR</strong></button>
            <button class="sci-btn" data-latex="P = V \cdot I"><strong>P = VI</strong></button>
            <button class="sci-btn" data-latex="Q = I \cdot t"><strong>Q = It</strong></button>
            <button class="sci-btn" data-latex="F_e = k \frac{q_1 q_2}{r^2}"><strong>Coulomb</strong></button>
            <button class="sci-btn" data-latex="E = \frac{F}{q}"><strong>Field</strong></button>
            <button class="sci-btn" data-latex="V = \frac{W}{q}"><strong>Potential</strong></button>

            <button class="sci-btn action-secondary calc-clear-btn">AC</button>
            <button class="sci-btn action-secondary calc-del-btn">DEL</button>
            <button class="sci-btn action calc-solve-btn" style="grid-column: span 4;">Solve</button>
        </div>

        <!-- Waves/Optics Tab -->
        <div class="calc-tab-content" id="calc-tab-waves">
            <button class="sci-btn" data-latex="v = f \cdot \lambda"><strong>v = fλ</strong></button>
            <button class="sci-btn" data-latex="T = \frac{1}{f}"><strong>T = 1/f</strong></button>
            <button class="sci-btn" data-latex="n = \frac{c}{v}"><strong>n = c/v</strong></button>
            <button class="sci-btn" data-latex="\frac{1}{f} = \frac{1}{u} + \frac{1}{v}"><strong>Mirror/Lens</strong></button>
            <button class="sci-btn" data-latex="f = \frac{v \pm v_o}{v \mp v_s} f_s"><strong>Doppler</strong></button>

            <button class="sci-btn action-secondary calc-clear-btn">AC</button>
            <button class="sci-btn action-secondary calc-del-btn">DEL</button>
            <button class="sci-btn action calc-solve-btn" style="grid-column: span 4;">Solve</button>
        </div>

        <!-- Thermo Tab -->
        <div class="calc-tab-content" id="calc-tab-thermo">
            <button class="sci-btn" data-latex="Q = m \cdot c \cdot \Delta T"><strong>Q=mcΔT</strong></button>
            <button class="sci-btn" data-latex="PV = nRT"><strong>PV=nRT</strong></button>
            <button class="sci-btn" data-latex="\Delta U = Q - W"><strong>1st Law</strong></button>

            <button class="sci-btn action-secondary calc-clear-btn">AC</button>
            <button class="sci-btn action-secondary calc-del-btn">DEL</button>
            <button class="sci-btn action calc-solve-btn" style="grid-column: span 4;">Solve</button>
        </div>

        <!-- Modern Physics Tab -->
        <div class="calc-tab-content" id="calc-tab-modern">
            <button class="sci-btn" data-latex="E = m \cdot c^2"><strong>E=mc²</strong></button>
            <button class="sci-btn" data-latex="E = h \cdot f"><strong>E=hf</strong></button>
            <button class="sci-btn" data-latex="\lambda = \frac{h}{p}"><strong>λ=h/p</strong></button>
            <button class="sci-btn" data-latex="W_0 = h \cdot f_0"><strong>Work Fn</strong></button>

            <button class="sci-btn action-secondary calc-clear-btn">AC</button>
            <button class="sci-btn action-secondary calc-del-btn">DEL</button>
            <button class="sci-btn action calc-solve-btn" style="grid-column: span 4;">Solve</button>
        </div>

        <!-- Constants Tab -->
        <div class="calc-tab-content" id="calc-tab-const">
            <button class="sci-btn" data-latex="g = 9.81"><strong>g</strong><small>9.81</small></button>
            <button class="sci-btn" data-latex="c = 3 \times 10^8"><strong>c</strong><small>3e8</small></button>
            <button class="sci-btn" data-latex="G = 6.67 \times 10^{-11}"><strong>G</strong><small>6.67e-11</small></button>
            <button class="sci-btn" data-latex="h = 6.63 \times 10^{-34}"><strong>h</strong><small>6.63e-34</small></button>
            <button class="sci-btn" data-latex="e = 1.6 \times 10^{-19}"><strong>e</strong><small>1.6e-19</small></button>
            <button class="sci-btn" data-latex="R = 8.31"><strong>R</strong><small>8.31</small></button>

            <button class="sci-btn action-secondary calc-clear-btn">AC</button>
            <button class="sci-btn action-secondary calc-del-btn">DEL</button>
            <button class="sci-btn action calc-solve-btn" style="grid-column: span 4;">Solve</button>
        </div>
    </div>
</div>
