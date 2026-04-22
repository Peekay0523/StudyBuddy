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
    .switch-btn.active.math {
        background: #4f46e5;
        color: white;
        box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.3);
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
                    <div class="formula-item">
                        <span class="formula-label">Motion Equations</span>
                        <div class="formula-math">
                            $v_f = v_i + a \Delta t$<br>
                            $\Delta x = v_i \Delta t + \frac{1}{2} a \Delta t^2$<br>
                            $v_f^2 = v_i^2 + 2 a \Delta x$
                        </div>
                    </div>
                    <div class="formula-item">
                        <span class="formula-label">Force & Momentum</span>
                        <div class="formula-math">
                            $F_{net} = m a$<br>
                            $p = m v$<br>
                            $F_{net} \Delta t = \Delta p$
                        </div>
                    </div>
                    <div class="formula-item">
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
                    <div class="formula-item">
                        <span class="formula-label">Electrostatics</span>
                        <div class="formula-math">
                            $F = \frac{k Q_1 Q_2}{r^2}$<br>
                            $E = \frac{k Q}{r^2} = \frac{F}{q}$
                        </div>
                    </div>
                    <div class="formula-item">
                        <span class="formula-label">Electric Circuits</span>
                        <div class="formula-math">
                            $V = I R$<br>
                            $P = V I = I^2 R = \frac{V^2}{R}$
                        </div>
                    </div>
                    <div class="formula-item">
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
                    <div class="formula-item">
                        <span class="formula-label">Quadratic Formula</span>
                        <div class="formula-math">
                            $x = \frac{-b \pm \sqrt{b^2 - 4ac}}{2a}$
                        </div>
                    </div>
                    <div class="formula-item">
                        <span class="formula-label">Sequences & Series</span>
                        <div class="formula-math">
                            $T_n = a + (n-1)d$<br>
                            $S_n = \frac{n}{2}[2a + (n-1)d]$<br>
                            $T_n = ar^{n-1}$
                        </div>
                    </div>
                    <div class="formula-item">
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
                    <div class="formula-item">
                        <span class="formula-label">Differentiation</span>
                        <div class="formula-math">
                            $f'(x) = \lim_{h \to 0} \frac{f(x+h) - f(x)}{h}$<br>
                            $\frac{d}{dx}[x^n] = nx^{n-1}$
                        </div>
                    </div>
                    <div class="formula-item">
                        <span class="formula-label">Analytical Geometry</span>
                        <div class="formula-math">
                            $d = \sqrt{(x_2-x_1)^2 + (y_2-y_1)^2}$<br>
                            $M(\frac{x_1+x_2}{2} ; \frac{y_1+y_2}{2})$
                        </div>
                    </div>
                    <div class="formula-item">
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
                    <div class="formula-item">
                        <span class="formula-label">Identities</span>
                        <div class="formula-math">
                            $\sin^2 \theta + \cos^2 \theta = 1$<br>
                            $\tan \theta = \frac{\sin \theta}{\cos \theta}$
                        </div>
                    </div>
                    <div class="formula-item">
                        <span class="formula-label">Double Angles</span>
                        <div class="formula-math">
                            $\sin 2\alpha = 2\sin \alpha \cos \alpha$<br>
                            $\cos 2\alpha = \cos^2 \alpha - \sin^2 \alpha$
                        </div>
                    </div>
                    <div class="formula-item">
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

<script>
    function switchTab(subject) {
        document.getElementById('btn-science').classList.toggle('active', subject === 'science');
        document.getElementById('btn-math').classList.toggle('active', subject === 'math');
        
        document.getElementById('science-sheet').classList.toggle('active', subject === 'science');
        document.getElementById('math-sheet').classList.toggle('active', subject === 'math');
        
        if (window.MathJax && window.MathJax.typesetPromise) {
            MathJax.typesetPromise();
        }
    }
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
