<!-- Chemistry Calculator Component -->
<div class="calc-backdrop"></div>

<button class="subject-calc-trigger" id="chem-calc-trigger" onclick="chemistryCalculator.toggle()" style="background: linear-gradient(135deg, #00B4D8, #0077B6);">
    <i class="fas fa-flask"></i>
</button>

<div class="scientific-calc-panel tex2jax_ignore" id="chem-calc-panel">
    <div class="calc-panel-header">
        <h3><i class="fas fa-atom"></i> Chemistry Lab Assistant</h3>
        <button class="calc-close-btn" onclick="chemistryCalculator.close()">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <div class="calc-panel-display">
        <div class="calc-preview-area" id="chem-preview"></div>
        <div class="calc-result-area" id="chem-result"></div>
        <div class="calc-loading-overlay">
            <i class="fas fa-spinner fa-spin"></i> AI is balancing...
        </div>
    </div>

    <div class="calc-tabs-nav">
        <button class="calc-tab-trigger active" data-tab="elements">Elements</button>
        <button class="calc-tab-trigger" data-tab="reactions">Reactions</button>
        <button class="calc-tab-trigger" data-tab="molarity">Molarity</button>
        <button class="calc-tab-trigger" data-tab="ph">pH</button>
        <button class="calc-tab-trigger" data-tab="gas">Gas Laws</button>
    </div>

    <div class="calc-panel-body">
        <!-- Elements Tab -->
        <div class="calc-tab-content active" id="calc-tab-elements">
            <button class="sci-btn" data-latex="H">H<small>1.008</small></button>
            <button class="sci-btn" data-latex="He">He<small>4.002</small></button>
            <button class="sci-btn" data-latex="Li">Li<small>6.941</small></button>
            <button class="sci-btn" data-latex="Be">Be<small>9.012</small></button>
            <button class="sci-btn" data-latex="B">B<small>10.811</small></button>
            <button class="sci-btn" data-latex="C">C<small>12.011</small></button>
            <button class="sci-btn" data-latex="N">N<small>14.007</small></button>
            <button class="sci-btn" data-latex="O">O<small>15.999</small></button>
            <button class="sci-btn" data-latex="F">F<small>18.998</small></button>
            <button class="sci-btn" data-latex="Ne">Ne<small>20.180</small></button>
            <button class="sci-btn" data-latex="Na">Na<small>22.990</small></button>
            <button class="sci-btn" data-latex="Mg">Mg<small>24.305</small></button>
            <button class="sci-btn" data-latex="Al">Al<small>26.982</small></button>
            <button class="sci-btn" data-latex="Si">Si<small>28.085</small></button>
            <button class="sci-btn" data-latex="P">P<small>30.974</small></button>
            <button class="sci-btn" data-latex="S">S<small>32.065</small></button>
            <button class="sci-btn" data-latex="Cl">Cl<small>35.453</small></button>
            <button class="sci-btn" data-latex="Ar">Ar<small>39.948</small></button>
            <button class="sci-btn" data-latex="K">K<small>39.098</small></button>
            <button class="sci-btn" data-latex="Ca">Ca<small>40.078</small></button>
        </div>

        <!-- Reactions Tab -->
        <div class="calc-tab-content" id="calc-tab-reactions">
            <button class="sci-btn operator" data-latex="+">+</button>
            <button class="sci-btn operator" data-latex="\rightarrow">→</button>
            <button class="sci-btn operator" data-latex="\rightleftharpoons">⇌</button>
            <button class="sci-btn" data-latex="_2">₂</button>
            <button class="sci-btn" data-latex="_3">₃</button>
            <button class="sci-btn" data-latex="_4">₄</button>
            <button class="sci-btn" data-latex="^+">+</button>
            <button class="sci-btn" data-latex="^-">-</button>
            <button class="sci-btn" data-latex="(s)"> (s)</button>
            <button class="sci-btn" data-latex="(l)"> (l)</button>
            <button class="sci-btn" data-latex="(g)"> (g)</button>
            <button class="sci-btn" data-latex="(aq)"> (aq)</button>
            
            <button class="sci-btn" data-latex="1">1</button>
            <button class="sci-btn" data-latex="2">2</button>
            <button class="sci-btn" data-latex="3">3</button>
            <button class="sci-btn" data-latex="4">4</button>
            <button class="sci-btn" data-latex="5">5</button>
            <button class="sci-btn action-secondary calc-clear-btn">AC</button>
            <button class="sci-btn action-secondary calc-del-btn">DEL</button>
            <button class="sci-btn action calc-solve-btn" style="grid-column: span 4;">Balance</button>
        </div>

        <!-- Molarity Tab -->
        <div class="calc-tab-content" id="calc-tab-molarity">
            <button class="sci-btn" data-latex="C = \frac{n}{V}"><strong>C = n/V</strong></button>
            <button class="sci-btn" data-latex="n = \frac{m}{M}"><strong>n = m/M</strong></button>
            <button class="sci-btn" data-latex="C_1V_1 = C_2V_2"><strong>Dilution</strong></button>
            <button class="sci-btn" data-latex="\rho = \frac{m}{V}"><strong>Density</strong></button>

            <button class="sci-btn action-secondary calc-clear-btn" style="grid-column: span 2;">AC</button>
            <button class="sci-btn action-secondary calc-del-btn" style="grid-column: span 2;">DEL</button>
            <button class="sci-btn action calc-solve-btn" style="grid-column: span 4;">Solve</button>
        </div>

        <!-- pH Tab -->
        <div class="calc-tab-content" id="calc-tab-ph">
            <button class="sci-btn" data-latex="pH = -\log[H^+]"><strong>pH</strong></button>
            <button class="sci-btn" data-latex="pOH = -\log[OH^-]"><strong>pOH</strong></button>
            <button class="sci-btn" data-latex="pH + pOH = 14"><strong>pH+pOH=14</strong></button>
            <button class="sci-btn" data-latex="K_w = [H^+][OH^-]"><strong>K_w</strong></button>

            <button class="sci-btn action-secondary calc-clear-btn" style="grid-column: span 2;">AC</button>
            <button class="sci-btn action-secondary calc-del-btn" style="grid-column: span 2;">DEL</button>
            <button class="sci-btn action calc-solve-btn" style="grid-column: span 4;">Solve</button>
        </div>

        <!-- Gas Laws Tab -->
        <div class="calc-tab-content" id="calc-tab-gas">
            <button class="sci-btn" data-latex="PV = nRT"><strong>PV=nRT</strong></button>
            <button class="sci-btn" data-latex="P_1V_1 = P_2V_2"><strong>Boyle's</strong></button>
            <button class="sci-btn" data-latex="\frac{V_1}{T_1} = \frac{V_2}{T_2}"><strong>Charles'</strong></button>
            <button class="sci-btn" data-latex="\frac{P_1V_1}{T_1} = \frac{P_2V_2}{T_2}"><strong>Combined</strong></button>

            <button class="sci-btn action-secondary calc-clear-btn" style="grid-column: span 2;">AC</button>
            <button class="sci-btn action-secondary calc-del-btn" style="grid-column: span 2;">DEL</button>
            <button class="sci-btn action calc-solve-btn" style="grid-column: span 4;">Solve</button>
        </div>
    </div>
</div>
