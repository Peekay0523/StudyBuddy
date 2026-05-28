<!-- Physics Calculator Component -->
<div class="calc-backdrop"></div>

<button type="button" class="subject-calc-trigger" id="phys-calc-trigger" onclick="physicsCalculator.toggle()" style="background: linear-gradient(135deg, #FF9E00, #FF6D00); touch-action: manipulation;">
    <i class="fas fa-atom"></i>
</button>

<div class="scientific-calc-panel tex2jax_ignore" id="phys-calc-panel">
    <div class="calc-panel-header">
        <h3><i class="fas fa-bolt"></i> Physics Lab Solver</h3>
        <button type="button" class="calc-close-btn" aria-label="Close">
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
        <button type="button" class="calc-tab-trigger active" data-tab="mechanics" style="touch-action: manipulation;">Mechanics</button>
        <button type="button" class="calc-tab-trigger" data-tab="elec" style="touch-action: manipulation;">Electricity</button>
        <button type="button" class="calc-tab-trigger" data-tab="waves" style="touch-action: manipulation;">Waves/Optics</button>
        <button type="button" class="calc-tab-trigger" data-tab="thermo" style="touch-action: manipulation;">Thermo</button>
        <button type="button" class="calc-tab-trigger" data-tab="modern" style="touch-action: manipulation;">Modern</button>
        <button type="button" class="calc-tab-trigger" data-tab="const" style="touch-action: manipulation;">Constants</button>
    </div>

    <div class="calc-panel-body">
        <!-- Mechanics Tab -->
        <div class="calc-tab-content active" id="calc-tab-mechanics">
            <button type="button" class="sci-btn" data-formula-id="f_ma" data-latex="F = m \cdot a" style="touch-action: manipulation;"><strong>F = ma</strong></button>
            <button type="button" class="sci-btn" data-formula-id="v_uat" data-latex="v = u + a \cdot t" style="touch-action: manipulation;"><strong>v = u+at</strong></button>
            <button type="button" class="sci-btn" data-formula-id="s_ut_at2" data-latex="s = u \cdot t + \frac{1}{2}a \cdot t^2" style="touch-action: manipulation;"><strong>s=ut+½at²</strong></button>
            <button type="button" class="sci-btn" data-formula-id="ek_mv2" data-latex="E_k = \frac{1}{2}m \cdot v^2" style="touch-action: manipulation;"><strong>Eₖ = ½mv²</strong></button>
            <button type="button" class="sci-btn" data-formula-id="ep_mgh" data-latex="E_p = m \cdot g \cdot h" style="touch-action: manipulation;"><strong>Eₚ = mgh</strong></button>
            <button type="button" class="sci-btn" data-formula-id="p_mv" data-latex="p = m \cdot v" style="touch-action: manipulation;"><strong>p = mv</strong></button>
            <button type="button" class="sci-btn" data-formula-id="w_fd" data-latex="W = F \cdot d" style="touch-action: manipulation;"><strong>W = Fd</strong></button>
            <button type="button" class="sci-btn" data-formula-id="p_wt" data-latex="P = \frac{W}{t}" style="touch-action: manipulation;"><strong>P = W/t</strong></button>
            
            <button type="button" class="sci-btn action-secondary calc-clear-btn" style="touch-action: manipulation;">AC</button>
            <button type="button" class="sci-btn action-secondary calc-del-btn" style="touch-action: manipulation;">DEL</button>
            <button type="button" class="sci-btn action calc-solve-btn" style="grid-column: span 4; touch-action: manipulation;">Solve</button>
        </div>

        <!-- Electricity Tab -->
        <div class="calc-tab-content" id="calc-tab-elec">
            <button type="button" class="sci-btn" data-formula-id="v_ir" data-latex="V = I \cdot R" style="touch-action: manipulation;"><strong>V = IR</strong></button>
            <button type="button" class="sci-btn" data-formula-id="p_vi" data-latex="P = V \cdot I" style="touch-action: manipulation;"><strong>P = VI</strong></button>
            <button type="button" class="sci-btn" data-formula-id="q_it" data-latex="Q = I \cdot t" style="touch-action: manipulation;"><strong>Q = It</strong></button>
            <button type="button" class="sci-btn" data-formula-id="fe_coulomb" data-latex="F_e = k \frac{q_1 q_2}{r^2}" style="touch-action: manipulation;"><strong>Coulomb</strong></button>
            <button type="button" class="sci-btn" data-formula-id="e_fq" data-latex="E = \frac{F}{q}" style="touch-action: manipulation;"><strong>Field</strong></button>
            <button type="button" class="sci-btn" data-formula-id="v_wq" data-latex="V = \frac{W}{q}" style="touch-action: manipulation;"><strong>Potential</strong></button>

            <button type="button" class="sci-btn action-secondary calc-clear-btn" style="touch-action: manipulation;">AC</button>
            <button type="button" class="sci-btn action-secondary calc-del-btn" style="touch-action: manipulation;">DEL</button>
            <button type="button" class="sci-btn action calc-solve-btn" style="grid-column: span 4; touch-action: manipulation;">Solve</button>
        </div>

        <!-- Waves/Optics Tab -->
        <div class="calc-tab-content" id="calc-tab-waves">
            <button type="button" class="sci-btn" data-formula-id="v_fl" data-latex="v = f \cdot \lambda" style="touch-action: manipulation;"><strong>v = fλ</strong></button>
            <button type="button" class="sci-btn" data-formula-id="t_1f" data-latex="T = \frac{1}{f}" style="touch-action: manipulation;"><strong>T = 1/f</strong></button>
            <button type="button" class="sci-btn" data-formula-id="n_cv" data-latex="n = \frac{c}{v}" style="touch-action: manipulation;"><strong>n = c/v</strong></button>
            <button type="button" class="sci-btn" data-formula-id="mirror_lens" data-latex="\frac{1}{f} = \frac{1}{u} + \frac{1}{v}" style="touch-action: manipulation;"><strong>Mirror/Lens</strong></button>
            <button type="button" class="sci-btn" data-formula-id="doppler" data-latex="f = \frac{v \pm v_o}{v \mp v_s} f_s" style="touch-action: manipulation;"><strong>Doppler</strong></button>

            <button type="button" class="sci-btn action-secondary calc-clear-btn" style="touch-action: manipulation;">AC</button>
            <button type="button" class="sci-btn action-secondary calc-del-btn" style="touch-action: manipulation;">DEL</button>
            <button type="button" class="sci-btn action calc-solve-btn" style="grid-column: span 4; touch-action: manipulation;">Solve</button>
        </div>

        <!-- Thermo Tab -->
        <div class="calc-tab-content" id="calc-tab-thermo">
            <button type="button" class="sci-btn" data-formula-id="q_mct" data-latex="Q = m \cdot c \cdot \Delta T" style="touch-action: manipulation;"><strong>Q=mcΔT</strong></button>
            <button type="button" class="sci-btn" data-formula-id="pv_nrt" data-latex="PV = nRT" style="touch-action: manipulation;"><strong>PV=nRT</strong></button>
            <button type="button" class="sci-btn" data-formula-id="du_qw" data-latex="\Delta U = Q - W" style="touch-action: manipulation;"><strong>1st Law</strong></button>

            <button type="button" class="sci-btn action-secondary calc-clear-btn" style="touch-action: manipulation;">AC</button>
            <button type="button" class="sci-btn action-secondary calc-del-btn" style="touch-action: manipulation;">DEL</button>
            <button type="button" class="sci-btn action calc-solve-btn" style="grid-column: span 4; touch-action: manipulation;">Solve</button>
        </div>

        <!-- Modern Physics Tab -->
        <div class="calc-tab-content" id="calc-tab-modern">
            <button type="button" class="sci-btn" data-formula-id="e_mc2" data-latex="E = m \cdot c^2" style="touch-action: manipulation;"><strong>E=mc²</strong></button>
            <button type="button" class="sci-btn" data-formula-id="e_hf" data-latex="E = h \cdot f" style="touch-action: manipulation;"><strong>E=hf</strong></button>
            <button type="button" class="sci-btn" data-formula-id="l_hp" data-latex="\lambda = \frac{h}{p}" style="touch-action: manipulation;"><strong>λ=h/p</strong></button>
            <button type="button" class="sci-btn" data-formula-id="w0_hf0" data-latex="W_0 = h \cdot f_0" style="touch-action: manipulation;"><strong>Work Fn</strong></button>

            <button type="button" class="sci-btn action-secondary calc-clear-btn" style="touch-action: manipulation;">AC</button>
            <button type="button" class="sci-btn action-secondary calc-del-btn" style="touch-action: manipulation;">DEL</button>
            <button type="button" class="sci-btn action calc-solve-btn" style="grid-column: span 4; touch-action: manipulation;">Solve</button>
        </div>

        <!-- Constants Tab -->
        <div class="calc-tab-content" id="calc-tab-const">
            <button type="button" class="sci-btn" data-formula-id="const_g" data-latex="g = 9.81" style="touch-action: manipulation;"><strong>g</strong><small>9.81</small></button>
            <button type="button" class="sci-btn" data-formula-id="const_c" data-latex="c = 3 \times 10^8" style="touch-action: manipulation;"><strong>c</strong><small>3e8</small></button>
            <button type="button" class="sci-btn" data-formula-id="const_G" data-latex="G = 6.67 \times 10^{-11}" style="touch-action: manipulation;"><strong>G</strong><small>6.67e-11</small></button>
            <button type="button" class="sci-btn" data-formula-id="const_h" data-latex="h = 6.63 \times 10^{-34}" style="touch-action: manipulation;"><strong>h</strong><small>6.63e-34</small></button>
            <button type="button" class="sci-btn" data-formula-id="const_e" data-latex="e = 1.6 \times 10^{-19}" style="touch-action: manipulation;"><strong>e</strong><small>1.6e-19</small></button>
            <button type="button" class="sci-btn" data-formula-id="const_R" data-latex="R = 8.31" style="touch-action: manipulation;"><strong>R</strong><small>8.31</small></button>

            <button type="button" class="sci-btn action-secondary calc-clear-btn" style="touch-action: manipulation;">AC</button>
            <button type="button" class="sci-btn action-secondary calc-del-btn" style="touch-action: manipulation;">DEL</button>
            <button type="button" class="sci-btn action calc-solve-btn" style="grid-column: span 4; touch-action: manipulation;">Solve</button>
        </div>
    </div>
</div>

