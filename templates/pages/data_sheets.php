<?php include __DIR__ . '/../layouts/header.php'; ?>

<!-- MathJax Configuration -->
<script>
window.MathJax = {
  tex: {
    inlineMath: [['$', '$'], ['\\(', '\\)']],
    displayMath: [['$$', '$$'], ['\\[', '\\]']],
    processEscapes: true
  },
  options: {
    enableMenu: false
  }
};
</script>
<script id="MathJax-script" async src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js"></script>

<style>
    .datasheet-wrapper {
        max-width: 1200px;
        margin: 40px auto;
        padding: 0 20px;
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }
    .subject-switcher {
        display: flex;
        gap: 15px;
        margin-bottom: 30px;
        background: #f1f5f9;
        padding: 8px;
        border-radius: 14px;
        width: fit-content;
        box-shadow: inset 0 2px 4px rgba(0,0,0,0.05);
    }
    .switch-btn {
        padding: 12px 30px;
        border: none;
        border-radius: 10px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        align-items: center;
        gap: 10px;
        color: #64748b;
        background: transparent;
        font-size: 1rem;
    }
    .switch-btn.active.science {
        background: #059669;
        color: white;
        box-shadow: 0 10px 15px -3px rgba(5, 150, 105, 0.3);
    }
    .switch-btn.active.chemistry {
        background: #dc2626;
        color: white;
        box-shadow: 0 10px 15px -3px rgba(220, 38, 38, 0.3);
    }
    .sheet-card {
        background: white;
        border-radius: 24px;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 10px 10px -5px rgba(0, 0, 0, 0.02);
        border: 1px solid #e2e8f0;
        overflow: hidden;
        display: none;
    }
    .sheet-card.active {
        display: block;
        animation: slideUp 0.5s ease-out;
    }
    @keyframes slideUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .sheet-header {
        padding: 35px 40px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #fafafa;
    }
    .sheet-body {
        padding: 45px 40px;
    }
    .formula-section {
        margin-bottom: 50px;
    }
    .section-title {
        font-size: 1.25rem;
        font-weight: 800;
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        gap: 12px;
        letter-spacing: -0.02em;
    }
    .science .section-title { color: #059669; }
    .math .section-title { color: #4f46e5; }
    .chemistry .section-title { color: #dc2626; }
    
    .formula-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
        gap: 30px;
    }
    .formula-item {
        background: white;
        padding: 24px;
        border-radius: 16px;
        border: 1px solid #f1f5f9;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);
        transition: all 0.2s;
        position: relative;
    }
    .formula-item:hover {
        border-color: #e2e8f0;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
        transform: translateY(-2px);
        cursor: pointer;
    }
    
    /* Modal Styles */
    .formula-modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        animation: fadeIn 0.3s ease-out;
    }
    
    .formula-modal.show {
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    
    .modal-content {
        background-color: white;
        padding: 40px;
        border-radius: 20px;
        max-width: 600px;
        width: 90%;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        animation: slideUp 0.3s ease-out;
        max-height: 80vh;
        overflow-y: auto;
    }
    
    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 20px;
    }
    
    .modal-title {
        font-size: 1.5rem;
        font-weight: 800;
        color: #1e293b;
        margin: 0;
    }
    
    .close-btn {
        background: none;
        border: none;
        font-size: 28px;
        font-weight: bold;
        color: #94a3b8;
        cursor: pointer;
        padding: 0;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        transition: all 0.2s;
    }
    
    .close-btn:hover {
        background-color: #f1f5f9;
        color: #475569;
    }
    
    .modal-section {
        margin-bottom: 25px;
    }
    
    .modal-section-title {
        font-size: 0.875rem;
        color: #94a3b8;
        margin-bottom: 10px;
        display: block;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    
    .modal-description {
        color: #475569;
        line-height: 1.6;
        font-size: 1rem;
        margin-bottom: 15px;
    }
    
    .modal-usage {
        background: #f8fafc;
        border-left: 4px solid #667eea;
        padding: 15px;
        border-radius: 8px;
        font-size: 0.95rem;
        color: #475569;
        margin-top: 15px;
    }
    .formula-label {
        font-size: 0.75rem;
        color: #94a3b8;
        margin-bottom: 12px;
        display: block;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .formula-math {
        font-size: 1.3rem;
        color: #1e293b;
        line-height: 1.6;
    }
    .constants-container {
        background: #f8fafc;
        border-radius: 16px;
        padding: 25px;
        border: 1px solid #f1f5f9;
    }
    .constants-table {
        width: 100%;
        border-collapse: collapse;
    }
    .constants-table td {
        padding: 14px 0;
        border-bottom: 1px solid #e2e8f0;
        font-size: 0.95rem;
    }
    .constants-table tr:last-child td { border-bottom: none; }
    .sym { font-weight: 700; color: #1e293b; width: 60px; font-size: 1.1rem; }
    .desc { color: #64748b; }
    .val { text-align: right; font-weight: 600; color: #1e293b; white-space: nowrap; padding-left: 20px; }
</style>

<div class="datasheet-wrapper">
    <div style="margin-bottom: 25px;">
        <a href="/upload-script" class="btn-secondary" style="text-decoration: none; display: inline-flex; align-items: center; gap: 8px; font-weight: 600;">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    <div class="subject-switcher">
        <button onclick="switchTab('science')" id="btn-science" class="switch-btn active science">
            <i class="fas fa-atom"></i> Physical Science
        </button>
        <button onclick="switchTab('chemistry')" id="btn-chemistry" class="switch-btn chemistry">
            <i class="fas fa-flask"></i> Chemistry
        </button>
        <button onclick="switchTab('math')" id="btn-math" class="switch-btn math">
            <i class="fas fa-calculator"></i> Mathematics
        </button>
    </div>

    <!-- PHYSICAL SCIENCE -->
    <div id="science-sheet" class="sheet-card active science">
        <div class="sheet-header">
            <div>
                <h2 style="margin: 0; color: #1e293b; font-weight: 800;">Physical Science Data Sheet</h2>
                <p style="margin: 5px 0 0; color: #64748b; font-weight: 500;">CAPS Curriculum Grade 10-12 Reference</p>
            </div>
            <div style="display: flex; gap: 10px;">
                <button class="btn-secondary" style="padding: 8px 16px;"><i class="fas fa-print"></i> Print</button>
            </div>
        </div>
        <div class="sheet-body">
            <div class="formula-section">
                <div class="section-title"><i class="fas fa-microscope"></i> Physical Constants</div>
                <div class="constants-container">
                    <table class="constants-table">
                        <tr>
                            <td class="sym">$g$</td>
                            <td class="desc">Acceleration due to gravity</td>
                            <td class="val">$9,8 \, m \cdot s^{-2}$</td>
                        </tr>
                        <tr>
                            <td class="sym">$G$</td>
                            <td class="desc">Universal gravitational constant</td>
                            <td class="val">$6,67 \times 10^{-11} \, N \cdot m^2 \cdot kg^{-2}$</td>
                        </tr>
                        <tr>
                            <td class="sym">$c$</td>
                            <td class="desc">Speed of light in a vacuum</td>
                            <td class="val">$3,0 \times 10^8 \, m \cdot s^{-1}$</td>
                        </tr>
                        <tr>
                            <td class="sym">$h$</td>
                            <td class="desc">Planck's constant</td>
                            <td class="val">$6,63 \times 10^{-34} \, J \cdot s$</td>
                        </tr>
                        <tr>
                            <td class="sym">$e$</td>
                            <td class="desc">Charge on electron</td>
                            <td class="val">$-1,6 \times 10^{-19} \, C$</td>
                        </tr>
                        <tr>
                            <td class="sym">$k$</td>
                            <td class="desc">Coulomb's constant</td>
                            <td class="val">$9,0 \times 10^9 \, N \cdot m^2 \cdot C^{-2}$</td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="formula-section">
                <div class="section-title"><i class="fas fa-running"></i> Mechanics</div>
                <div class="formula-grid">
                    <div class="formula-item" onclick="openFormulaModal(this)" data-title="Motion Equations" data-formula="$v_f = v_i + at$ | $\Delta x = v_i t + \frac{1}{2}at^2$ | $v_f^2 = v_i^2 + 2a\Delta x$" data-description="These equations describe how objects move under constant acceleration. v_f is final velocity, v_i is initial velocity, a is acceleration, t is time, and Δx is displacement." data-usage="Use these when solving kinematics problems involving constant acceleration, such as free fall, projectile motion, or objects moving with constant acceleration.">
                        <span class="formula-label">Motion Equations</span>
                        <div class="formula-math">
                            $v_f = v_i + a \Delta t$<br>
                            $\Delta x = v_i \Delta t + \frac{1}{2} a \Delta t^2$<br>
                            $v_f^2 = v_i^2 + 2 a \Delta x$
                        </div>
                    </div>
                    <div class="formula-item" onclick="openFormulaModal(this)" data-title="Force & Momentum" data-formula="$F_{net} = ma$ | $p = mv$ | $F_{net}\Delta t = \Delta p$" data-description="Newton's second law relates net force to mass and acceleration. Momentum (p) is the product of mass and velocity. The impulse-momentum theorem states that the change in momentum equals the impulse (force × time)." data-usage="Use F=ma to find forces or accelerations. Use momentum equations when analyzing collisions, explosions, or conservation of momentum problems.">
                        <span class="formula-label">Force & Momentum</span>
                        <div class="formula-math">
                            $F_{net} = m a$<br>
                            $p = m v$<br>
                            $F_{net} \Delta t = \Delta p$
                        </div>
                    </div>
                    <div class="formula-item" onclick="openFormulaModal(this)" data-title="Work & Energy" data-formula="$W = F\Delta x\cos\theta$ | $E_k = \frac{1}{2}mv^2$ | $E_p = mgh$" data-description="Work is the product of force and displacement in the direction of the force. Kinetic energy is the energy of motion. Potential energy is energy stored due to position or height." data-usage="Calculate work when analyzing energy transfers. Use kinetic and potential energy in conservation of energy problems or when finding velocities and heights.">
                        <span class="formula-label">Work & Energy</span>
                        <div class="formula-math">
                            $W = F \Delta x \cos \theta$<br>
                            $E_k = \frac{1}{2} m v^2$<br>
                            $E_p = m g h$
                        </div>
                    </div>
                </div>
            </div>

            <div class="formula-section" style="margin-bottom: 0;">
                <div class="section-title"><i class="fas fa-bolt"></i> Electricity & Waves</div>
                <div class="formula-grid">
                    <div class="formula-item" onclick="openFormulaModal(this)" data-title="Electrostatics" data-formula="$F = \frac{kQ_1Q_2}{r^2}$ | $E = \frac{kQ}{r^2} = \frac{F}{q}$" data-description="Coulomb's law describes the electrostatic force between two charged objects. Electric field strength is the force per unit charge." data-usage="Use Coulomb's law to find forces between charges or to analyze electric fields around point charges.">
                        <span class="formula-label">Electrostatics</span>
                        <div class="formula-math">
                            $F = \frac{k Q_1 Q_2}{r^2}$<br>
                            $E = \frac{k Q}{r^2} = \frac{F}{q}$
                        </div>
                    </div>
                    <div class="formula-item" onclick="openFormulaModal(this)" data-title="Electric Circuits" data-formula="$V = IR$ | $P = VI = I^2R = \frac{V^2}{R}$" data-description="Ohm's law relates voltage, current, and resistance. Power is the rate of energy transfer, and can be calculated three equivalent ways." data-usage="Use Ohm's law to find unknown values in circuits. Use power equations when analyzing energy consumption and heat generation in resistors.">
                        <span class="formula-label">Electric Circuits</span>
                        <div class="formula-math">
                            $V = I R$<br>
                            $P = V I = I^2 R = \frac{V^2}{R}$
                        </div>
                    </div>
                    <div class="formula-item" onclick="openFormulaModal(this)" data-title="Waves & Light" data-formula="$v = f\lambda$ | $E = hf = \frac{hc}{\lambda}$" data-description="The wave equation relates velocity, frequency, and wavelength. Energy of a photon is proportional to its frequency." data-usage="Use wave equations to analyze wave properties like speed and wavelength. Use photon energy for electromagnetic radiation problems.">
                        <span class="formula-label">Waves & Light</span>
                        <div class="formula-math">
                            $v = f \lambda$<br>
                            $E = h f = \frac{h c}{\lambda}$
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- CHEMISTRY -->
    <div id="chemistry-sheet" class="sheet-card chemistry">
        <div class="sheet-header">
            <div>
                <h2 style="margin: 0; color: #1e293b; font-weight: 800;">Chemistry Data Sheet</h2>
                <p style="margin: 5px 0 0; color: #64748b; font-weight: 500;">CAPS Curriculum Grade 10-12 Reference</p>
            </div>
            <div style="display: flex; gap: 10px;">
                <button class="btn-secondary" style="padding: 8px 16px;"><i class="fas fa-print"></i> Print</button>
            </div>
        </div>
        <div class="sheet-body">
            <div class="formula-section">
                <div class="section-title"><i class="fas fa-flask"></i> Physical Constants</div>
                <div class="constants-container">
                    <table class="constants-table">
                        <tr>
                            <td class="sym">$R$</td>
                            <td class="desc">Universal gas constant</td>
                            <td class="val">$8,31 \, J \cdot mol^{-1} \cdot K^{-1}$</td>
                        </tr>
                        <tr>
                            <td class="sym">$N_A$</td>
                            <td class="desc">Avogadro's number</td>
                            <td class="val">$6,02 \times 10^{23} \, mol^{-1}$</td>
                        </tr>
                        <tr>
                            <td class="sym">$F$</td>
                            <td class="desc">Faraday constant</td>
                            <td class="val">$96485 \, C \cdot mol^{-1}$</td>
                        </tr>
                        <tr>
                            <td class="sym">$V_m$</td>
                            <td class="desc">Molar volume at STP</td>
                            <td class="val">$22,4 \, dm^3 \cdot mol^{-1}$</td>
                        </tr>
                        <tr>
                            <td class="sym">$K_w$</td>
                            <td class="desc">Ionic product of water</td>
                            <td class="val">$1,0 \times 10^{-14} \, mol^2 \cdot dm^{-6}$</td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="formula-section">
                <div class="section-title"><i class="fas fa-wind"></i> Gas Laws</div>
                <div class="formula-grid">
                    <div class="formula-item" onclick="openFormulaModal(this)" data-title="Ideal Gas Law" data-formula="$PV = nRT$" data-description="The ideal gas law relates pressure (P), volume (V), amount of gas (n), gas constant (R), and temperature (T) for an ideal gas." data-usage="Use this law to calculate any one variable when the other four are known. Remember to convert temperature to Kelvin and use consistent units.">
                        <span class="formula-label">Ideal Gas Law</span>
                        <div class="formula-math">
                            $PV = nRT$
                        </div>
                    </div>
                    <div class="formula-item" onclick="openFormulaModal(this)" data-title="Boyle's Law" data-formula="$P_1V_1 = P_2V_2$" data-description="At constant temperature, the pressure and volume of a gas are inversely proportional." data-usage="Use when temperature is constant and you need to find pressure or volume changes.">
                        <span class="formula-label">Boyle's Law</span>
                        <div class="formula-math">
                            $P_1 V_1 = P_2 V_2$
                        </div>
                    </div>
                    <div class="formula-item" onclick="openFormulaModal(this)" data-title="Charles's Law" data-formula="$V_1/T_1 = V_2/T_2$" data-description="At constant pressure, the volume of a gas is directly proportional to its temperature in Kelvin." data-usage="Use when pressure is constant and you need to find volume or temperature changes. Remember to use Kelvin temperatures.">
                        <span class="formula-label">Charles's Law</span>
                        <div class="formula-math">
                            $\frac{V_1}{T_1} = \frac{V_2}{T_2}$
                        </div>
                    </div>
                </div>
            </div>

            <div class="formula-section">
                <div class="section-title"><i class="fas fa-balance-scale"></i> Acids & Bases</div>
                <div class="formula-grid">
                    <div class="formula-item" onclick="openFormulaModal(this)" data-title="pH & pOH" data-formula="$pH = -\log[H^+]$ | $pOH = -\log[OH^-]$ | $pH + pOH = 14$" data-description="pH measures hydrogen ion concentration. pOH measures hydroxide ion concentration. Their sum equals 14 at 25°C." data-usage="Use to calculate pH from [H⁺], or vice versa. Use the relationship pH + pOH = 14 for basic solutions.">
                        <span class="formula-label">pH & pOH</span>
                        <div class="formula-math">
                            $pH = -\log [H^+]$<br>
                            $pOH = -\log [OH^-]$<br>
                            $pH + pOH = 14$
                        </div>
                    </div>
                    <div class="formula-item" onclick="openFormulaModal(this)" data-title="Acid Dissociation" data-formula="$K_a = \frac{[H^+][A^-]}{[HA]}$ | $pK_a = -\log K_a$" data-description="Ka is the acid dissociation constant, measuring acid strength. pKa is the negative log of Ka." data-usage="Use Ka to compare acid strengths. Use pKa for buffer calculations and acid-base equilibria.">
                        <span class="formula-label">Acid Dissociation</span>
                        <div class="formula-math">
                            $K_a = \frac{[H^+][A^-]}{[HA]}$<br>
                            $pK_a = -\log K_a$
                        </div>
                    </div>
                    <div class="formula-item" onclick="openFormulaModal(this)" data-title="Henderson-Hasselbalch" data-formula="$pH = pK_a + \log \frac{[A^-]}{[HA]}$" data-description="This equation calculates the pH of a buffer solution from the pKa and the ratio of conjugate base to acid concentrations." data-usage="Use for buffer solutions to find pH or to determine the ratio of components needed for a specific pH.">
                        <span class="formula-label">Henderson-Hasselbalch</span>
                        <div class="formula-math">
                            $pH = pK_a + \log \frac{[A^-]}{[HA]}$
                        </div>
                    </div>
                </div>
            </div>

            <div class="formula-section" style="margin-bottom: 0;">
                <div class="section-title"><i class="fas fa-bolt"></i> Electrochemistry</div>
                <div class="formula-grid">
                    <div class="formula-item" onclick="openFormulaModal(this)" data-title="Cell Potential" data-formula="$E_{cell} = E_{cathode} - E_{anode}$ | $E_{cell} = E^\circ_{cell} - \frac{RT}{nF} \ln Q$" data-description="Cell potential is the difference between cathode and anode potentials. The Nernst equation relates cell potential to standard potential and reaction quotient." data-usage="Use the first equation for standard conditions. Use the Nernst equation for non-standard conditions.">
                        <span class="formula-label">Cell Potential</span>
                        <div class="formula-math">
                            $E_{cell} = E_{cathode} - E_{anode}$<br>
                            $E_{cell} = E^\circ_{cell} - \frac{RT}{nF} \ln Q$
                        </div>
                    </div>
                    <div class="formula-item" onclick="openFormulaModal(this)" data-title="Gibbs Free Energy" data-formula="$\Delta G = -nFE_{cell}$ | $\Delta G^\circ = -RT \ln K$" data-description="Gibbs free energy change is related to cell potential and the equilibrium constant K." data-usage="Use to find spontaneity (negative ΔG means spontaneous) or to calculate equilibrium constants from standard potentials.">
                        <span class="formula-label">Gibbs Free Energy</span>
                        <div class="formula-math">
                            $\Delta G = -n F E_{cell}$<br>
                            $\Delta G^\circ = -R T \ln K$
                        </div>
                    </div>
                    <div class="formula-item" onclick="openFormulaModal(this)" data-title="Equilibrium Constant" data-formula="$K = \frac{[products]}{[reactants]}$ | $K_c = \frac{[C]^c[D]^d}{[A]^a[B]^b}$" data-description="The equilibrium constant K expresses the ratio of product concentrations to reactant concentrations at equilibrium." data-usage="Use to determine reaction direction and calculate concentrations at equilibrium.">
                        <span class="formula-label">Equilibrium Constant</span>
                        <div class="formula-math">
                            $K = \frac{[products]}{[reactants]}$<br>
                            $K_c = \frac{[C]^c [D]^d}{[A]^a [B]^b}$
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MATHEMATICS -->
    <div id="math-sheet" class="sheet-card math">
        <div class="sheet-header">
            <div>
                <h2 style="margin: 0; color: #1e293b; font-weight: 800;">Mathematics Data Sheet</h2>
                <p style="margin: 5px 0 0; color: #64748b; font-weight: 500;">CAPS Curriculum Grade 10-12 Reference</p>
            </div>
            <div style="display: flex; gap: 10px;">
                <button class="btn-secondary" style="padding: 8px 16px;"><i class="fas fa-print"></i> Print</button>
            </div>
        </div>
        <div class="sheet-body">
            <div class="formula-section">
                <div class="section-title"><i class="fas fa-calculator"></i> Algebra & Finance</div>
                <div class="formula-grid">
                    <div class="formula-item" onclick="openFormulaModal(this)" data-title="Quadratic Formula" data-formula="$x = \frac{-b \pm \sqrt{b^2-4ac}}{2a}$" data-description="This formula finds the solutions (roots) of any quadratic equation ax² + bx + c = 0. The ± symbol means there are typically two solutions." data-usage="Use this when you need to solve quadratic equations, especially when factoring is difficult or impossible.">
                        <span class="formula-label">Quadratic Formula</span>
                        <div class="formula-math">
                            $x = \frac{-b \pm \sqrt{b^2 - 4ac}}{2a}$
                        </div>
                    </div>
                    <div class="formula-item" onclick="openFormulaModal(this)" data-title="Sequences & Series" data-formula="$T_n = a + (n-1)d$ | $S_n = \frac{n}{2}[2a+(n-1)d]$ | $T_n = ar^{n-1}$" data-description="Arithmetic sequence: Tₙ finds the nth term, Sₙ finds the sum. Geometric sequence: each term is found by multiplying by a common ratio r." data-usage="Use these for arithmetic and geometric sequence problems, finding missing terms or sums.">
                        <span class="formula-label">Sequences & Series</span>
                        <div class="formula-math">
                            $T_n = a + (n-1)d$<br>
                            $S_n = \frac{n}{2}[2a + (n-1)d]$<br>
                            $T_n = ar^{n-1}$
                        </div>
                    </div>
                    <div class="formula-item" onclick="openFormulaModal(this)" data-title="Finance" data-formula="$A = P(1+i)^n$ | $F = \frac{x[(1+i)^n-1]}{i}$" data-description="First formula: compound interest where A is final amount, P is principal. Second formula: future value of an annuity." data-usage="Use for investment, loan, and annuity calculations involving compound interest.">
                        <span class="formula-label">Finance</span>
                        <div class="formula-math">
                            $A = P(1 + i)^n$<br>
                            $F = \frac{x[(1+i)^n - 1]}{i}$
                        </div>
                    </div>
                </div>
            </div>

            <div class="formula-section">
                <div class="section-title"><i class="fas fa-chart-line"></i> Calculus & Geometry</div>
                <div class="formula-grid">
                    <div class="formula-item" onclick="openFormulaModal(this)" data-title="Differentiation" data-formula="$f'(x) = \lim_{h \to 0} \frac{f(x+h)-f(x)}{h}$ | $\frac{d}{dx}[x^n] = nx^{n-1}$" data-description="First formula: the definition of derivative as the limit. Second: power rule for finding derivatives quickly." data-usage="Use to find slopes of curves, rates of change, and critical points in calculus problems.">
                        <span class="formula-label">Differentiation</span>
                        <div class="formula-math">
                            $f'(x) = \lim_{h \to 0} \frac{f(x+h) - f(x)}{h}$<br>
                            $\frac{d}{dx}[x^n] = nx^{n-1}$
                        </div>
                    </div>
                    <div class="formula-item" onclick="openFormulaModal(this)" data-title="Analytical Geometry" data-formula="$d = \sqrt{(x_2-x_1)^2+(y_2-y_1)^2}$ | $M(\frac{x_1+x_2}{2};\frac{y_1+y_2}{2})$" data-description="Distance formula calculates the distance between two points. Midpoint formula finds the coordinates of the point halfway between two points." data-usage="Use for finding distances between coordinates and locating midpoints in coordinate geometry.">
                        <span class="formula-label">Analytical Geometry</span>
                        <div class="formula-math">
                            $d = \sqrt{(x_2-x_1)^2 + (y_2-y_1)^2}$<br>
                            $M(\frac{x_1+x_2}{2} ; \frac{y_1+y_2}{2})$
                        </div>
                    </div>
                    <div class="formula-item" onclick="openFormulaModal(this)" data-title="Straight Line" data-formula="$y - y_1 = m(x-x_1)$ | $m = \tan\theta$" data-description="Point-slope form finds the equation of a line given a point and slope. Slope is the tangent of the angle the line makes with the x-axis." data-usage="Use to find linear equations and analyze slopes in coordinate geometry.">
                        <span class="formula-label">Straight Line</span>
                        <div class="formula-math">
                            $y - y_1 = m(x - x_1)$<br>
                            $m = \tan \theta$
                        </div>
                    </div>
                </div>
            </div>

            <div class="formula-section" style="margin-bottom: 0;">
                <div class="section-title"><i class="fas fa-shapes"></i> Trigonometry</div>
                <div class="formula-grid">
                    <div class="formula-item" onclick="openFormulaModal(this)" data-title="Identities" data-formula="$\sin^2\theta + \cos^2\theta = 1$ | $\tan\theta = \frac{\sin\theta}{\cos\theta}$" data-description="Pythagorean identity: fundamental relationship between sine and cosine. Tangent definition: the ratio of sine to cosine." data-usage="Use these fundamental trigonometric identities to simplify expressions and solve trigonometric equations.">
                        <span class="formula-label">Identities</span>
                        <div class="formula-math">
                            $\sin^2 \theta + \cos^2 \theta = 1$<br>
                            $\tan \theta = \frac{\sin \theta}{\cos \theta}$
                        </div>
                    </div>
                    <div class="formula-item" onclick="openFormulaModal(this)" data-title="Double Angles" data-formula="$\sin 2\alpha = 2\sin\alpha\cos\alpha$ | $\cos 2\alpha = \cos^2\alpha - \sin^2\alpha$" data-description="These formulas express trigonometric functions of double angles in terms of single angles." data-usage="Use when simplifying trigonometric expressions or solving equations involving double angles.">
                        <span class="formula-label">Double Angles</span>
                        <div class="formula-math">
                            $\sin 2\alpha = 2\sin \alpha \cos \alpha$<br>
                            $\cos 2\alpha = \cos^2 \alpha - \sin^2 \alpha$
                        </div>
                    </div>
                    <div class="formula-item" onclick="openFormulaModal(this)" data-title="Triangle Rules" data-formula="$\frac{a}{\sin A} = \frac{b}{\sin B} = \frac{c}{\sin C}$ | $a^2 = b^2 + c^2 - 2bc\cos A$" data-description="Sine rule relates sides and angles in any triangle. Cosine rule (law of cosines) is a generalization of Pythagorean theorem." data-usage="Use sine rule when you know an angle and its opposite side. Use cosine rule when you know two sides and the included angle.">
                        <span class="formula-label">Triangle Rules</span>
                        <div class="formula-math">
                            $\frac{a}{\sin A} = \frac{b}{\sin B} = \frac{c}{\sin C}$<br>
                            $a^2 = b^2 + c^2 - 2bc \cos A$
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Formula Info Modal -->
<div id="formulaModal" class="formula-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title" id="modalTitle">Formula</h2>
            <button class="close-btn" onclick="closeFormulaModal()">&times;</button>
        </div>
        
        <div class="modal-section">
            <span class="modal-section-title">Formula</span>
            <div id="modalFormula" class="formula-math" style="font-size: 1.2rem; margin-bottom: 20px;"></div>
        </div>
        
        <div class="modal-section">
            <span class="modal-section-title">What It Means</span>
            <p class="modal-description" id="modalDescription"></p>
        </div>
        
        <div class="modal-section">
            <span class="modal-section-title">When To Use It</span>
            <div class="modal-usage" id="modalUsage"></div>
        </div>
    </div>
</div>

<script>
    function switchTab(subject) {
        document.getElementById('btn-science').classList.toggle('active', subject === 'science');
        document.getElementById('btn-chemistry').classList.toggle('active', subject === 'chemistry');
        document.getElementById('btn-math').classList.toggle('active', subject === 'math');
        
        document.getElementById('science-sheet').classList.toggle('active', subject === 'science');
        document.getElementById('chemistry-sheet').classList.toggle('active', subject === 'chemistry');
        document.getElementById('math-sheet').classList.toggle('active', subject === 'math');
        
        if (window.MathJax && window.MathJax.typesetPromise) {
            MathJax.typesetPromise();
        }
    }    
    function openFormulaModal(element) {
        const title = element.getAttribute('data-title');
        const formula = element.getAttribute('data-formula');
        const description = element.getAttribute('data-description');
        const usage = element.getAttribute('data-usage');
        
        document.getElementById('modalTitle').textContent = title;
        document.getElementById('modalFormula').innerHTML = formula;
        document.getElementById('modalDescription').textContent = description;
        document.getElementById('modalUsage').textContent = usage;
        
        const modal = document.getElementById('formulaModal');
        modal.classList.add('show');
        
        // Reprocess MathJax for the new content
        if (window.MathJax && window.MathJax.typesetPromise) {
            MathJax.typesetPromise();
        }
    }
    
    function closeFormulaModal() {
        document.getElementById('formulaModal').classList.remove('show');
    }
    
    // Close modal when clicking outside the content
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('formulaModal');
        window.addEventListener('click', function(event) {
            if (event.target === modal) {
                closeFormulaModal();
            }
        });
        
        // Allow Escape key to close modal
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeFormulaModal();
            }
        });
    });</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
