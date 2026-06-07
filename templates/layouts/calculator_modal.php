<!-- Calculator Modal -->
<div id="calculator-modal" class="modal-overlay" style="display: none; z-index: 10000;">
    <div class="calculator-modal-content" style="z-index: 10001; position: relative; max-height: 90vh; overflow-y: auto;">
        <div class="calculator-header">
            <button onclick="toggleCalculator()" class="calc-close-btn">
                <i class="fas fa-times"></i>
            </button>
            <div>
                <div class="calc-brand">CASIO</div>
                <div class="calc-model">FX-300ES PLUS</div>
            </div>
            <div class="calc-solar-panel">
                <div class="calc-solar-cell"></div>
                <div class="calc-solar-cell"></div>
                <div class="calc-solar-cell"></div>
                <div class="calc-solar-cell"></div>
            </div>
        </div>

        <!-- Display -->
        <div class="calc-display-frame">
            <div class="calc-display">
                <div class="calc-display-indicators">
                    <div class="calc-ind-left">
                        <span class="calc-indicator" id="calc-ind-shift">S</span>
                        <span class="calc-indicator" id="calc-ind-alpha">A</span>
                        <span class="calc-indicator" id="calc-ind-m">M</span>
                    </div>
                    <div class="calc-ind-right">
                        <span class="calc-indicator active" id="calc-ind-deg">D</span>
                    </div>
                </div>
                <div class="calc-expression" id="calc-expression"></div>
                <div class="calc-result" id="calc-result">0</div>
                <div class="calc-fraction-result" id="calc-fractionResult">
                    <span class="calc-frac-whole" id="calc-fracWhole"></span>
                    <div class="calc-frac">
                        <div class="calc-frac-num" id="calc-fracNum"></div>
                        <div class="calc-frac-den" id="calc-fracDen"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Buttons -->
        <div class="calc-buttons-container">
            <div class="calc-btn-row calc-btn-row-5">
                <button class="calc-btn btn-shift" onclick="calc_shiftKey()">SHIFT</button>
                <button class="calc-btn btn-alpha" onclick="calc_alphaKey()">ALPHA</button>
                <button class="calc-btn btn-nav" onclick="calc_delKey()">◄DEL</button>
                <button class="calc-btn btn-nav" onclick="calc_closeParen()">►)</button>
                <button class="calc-btn btn-mode" onclick="calc_modeKey()">MODE</button>
            </div>

            <div class="calc-btn-row calc-btn-row-6">
                <button class="calc-btn btn-function" onclick="calc_calcKey()"><span class="calc-shift-label">STO</span>CALC</button>
                <button class="calc-btn btn-function" onclick="calc_integralKey()"><span class="calc-shift-label">d/dx</span>∫dx</button>
                <button class="calc-btn btn-function" onclick="calc_derivativeKey()"><span class="calc-shift-label">∫</span>d/dx</button>
                <button class="calc-btn btn-function" onclick="calc_sumKey()"><span class="calc-shift-label">Π</span>Σ</button>
                <button class="calc-btn btn-function" onclick="calc_sqrtKey()"><span class="calc-shift-label">∛</span>√</button>
                <button class="calc-btn btn-function" onclick="calc_powerKey()"><span class="calc-shift-label">ˣ√</span>x^y</button>
            </div>

            <div class="calc-btn-row calc-btn-row-6">
                <button class="calc-btn btn-function" onclick="calc_squareKey()"><span class="calc-shift-label">x!</span>x²</button>
                <button class="calc-btn btn-function" onclick="calc_cubeKey()"><span class="calc-shift-label">Abs</span>x³</button>
                <button class="calc-btn btn-function" onclick="calc_reciprocalKey()"><span class="calc-shift-label">Ran#</span>x⁻¹</button>
                <button class="calc-btn btn-function" onclick="calc_logKey()"><span class="calc-shift-label">10ˣ</span>log</button>
                <button class="calc-btn btn-function" onclick="calc_lnKey()"><span class="calc-shift-label">eˣ</span>ln</button>
                <button class="calc-btn btn-function" onclick="calc_negateKey()"><span class="calc-shift-label">RanInt</span>(-)</button>
            </div>

            <div class="calc-btn-row calc-btn-row-6">
                <button class="calc-btn btn-trig" onclick="calc_hypKey()">hyp</button>
                <button class="calc-btn btn-trig" onclick="calc_sinKey()"><span class="calc-shift-label">sin⁻¹</span>sin</button>
                <button class="calc-btn btn-trig" onclick="calc_cosKey()"><span class="calc-shift-label">cos⁻¹</span>cos</button>
                <button class="calc-btn btn-trig" onclick="calc_tanKey()"><span class="calc-shift-label">tan⁻¹</span>tan</button>
                <button class="calc-btn btn-function" onclick="calc_openParen()"><span class="calc-shift-label">Ins</span>(</button>
                <button class="calc-btn btn-function" onclick="calc_closeParen()"><span class="calc-shift-label">⇦</span>)</button>
            </div>

            <div class="calc-btn-row calc-btn-row-6">
                <button class="calc-btn btn-sd" onclick="calc_sdKey()"><span class="calc-shift-label">STO</span>S⇔D</button>
                <button class="calc-btn btn-function" onclick="calc_mPlusKey()"><span class="calc-shift-label">M-</span>M+</button>
                <button class="calc-btn btn-del" onclick="calc_delKey()"><span class="calc-shift-label">INS</span>DEL</button>
                <button class="calc-btn btn-function" onclick="calc_percentKey()"><span class="calc-shift-label">Ran#</span>%</button>
                <button class="calc-btn btn-function" onclick="calc_expKey()"><span class="calc-shift-label">π</span>EXP</button>
                <button class="calc-btn btn-function" onclick="calc_ansKey()"><span class="calc-shift-label">PreAns</span>Ans</button>
            </div>

            <div class="calc-btn-row calc-btn-row-6">
                <button class="calc-btn btn-fraction" onclick="calc_fractionKey()"><span class="calc-shift-label">d/c</span>a b/c</button>
                <button class="calc-btn btn-function" onclick="calc_nPrKey()">nPr</button>
                <button class="calc-btn btn-function" onclick="calc_nCrKey()">nCr</button>
                <button class="calc-btn btn-operator" onclick="calc_operatorKey('*')">×</button>
                <button class="calc-btn btn-operator" onclick="calc_operatorKey('/')">÷</button>
                <button style="visibility: hidden;"></button>
            </div>

            <div class="calc-btn-row calc-btn-row-6">
                <button class="calc-btn btn-number" onclick="calc_numberKey('7')"><span class="calc-shift-label">A</span>7</button>
                <button class="calc-btn btn-number" onclick="calc_numberKey('8')"><span class="calc-shift-label">B</span>8</button>
                <button class="calc-btn btn-number" onclick="calc_numberKey('9')"><span class="calc-shift-label">C</span>9</button>
                <button class="calc-btn btn-operator" onclick="calc_operatorKey('+')">+</button>
                <button class="calc-btn btn-operator" onclick="calc_operatorKey('-')">−</button>
                <button style="visibility: hidden;"></button>
            </div>

            <div class="calc-btn-row calc-btn-row-6">
                <button class="calc-btn btn-number" onclick="calc_numberKey('4')"><span class="calc-shift-label">D</span>4</button>
                <button class="calc-btn btn-number" onclick="calc_numberKey('5')"><span class="calc-shift-label">E</span>5</button>
                <button class="calc-btn btn-number" onclick="calc_numberKey('6')"><span class="calc-shift-label">F</span>6</button>
                <button style="visibility: hidden;"></button>
                <button style="visibility: hidden;"></button>
                <button style="visibility: hidden;"></button>
            </div>

            <div class="calc-btn-row calc-btn-row-6">
                <button class="calc-btn btn-number" onclick="calc_numberKey('1')"><span class="calc-shift-label">X</span>1</button>
                <button class="calc-btn btn-number" onclick="calc_numberKey('2')"><span class="calc-shift-label">Y</span>2</button>
                <button class="calc-btn btn-number" onclick="calc_numberKey('3')"><span class="calc-shift-label">Z</span>3</button>
                <button class="calc-btn btn-equals" onclick="calc_calculate()"><span class="calc-shift-label">≈</span>=</button>
                <button class="calc-btn btn-ac" onclick="calc_acKey()"><span class="calc-shift-label">CLR</span>AC</button>
                <button style="visibility: hidden;"></button>
            </div>

            <div class="calc-btn-row calc-btn-row-6">
                <button class="calc-btn btn-number" onclick="calc_numberKey('0')"><span class="calc-shift-label">Rnd</span>0</button>
                <button class="calc-btn btn-number" onclick="calc_decimalKey()"><span class="calc-shift-label">Ran#</span>.</button>
                <button style="visibility: hidden;"></button>
                <button style="visibility: hidden;"></button>
                <button style="visibility: hidden;"></button>
                <button style="visibility: hidden;"></button>
            </div>
        </div>
    </div>
</div>

<script>
// Calculator Modal Toggle
function toggleCalculator() {
    const modal = document.getElementById('calculator-modal');
    if (modal) {
        if (modal.style.display === 'none' || modal.style.display === '') {
            modal.style.display = 'flex';
            modal.style.zIndex = '10000';
            // Allow scrolling while modal is open
        } else {
            modal.style.display = 'none';
        }
    }
}

// Close modal when clicking outside the calculator
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('calculator-modal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            // Only close if clicking the overlay background, not the calculator itself
            if (e.target === modal) {
                toggleCalculator();
            }
        });
    }
});

// Calculator State
let calcExpression = '';
let calcDisplayExpr = '';
let calcResultValue = '0';
let calcLastAnswer = 0;
let calcIsShift = false;
let calcIsAlpha = false;
let calcIsHyp = false;
let calcMemory = 0;
let calcHasMemory = false;
let calcIsFractionResult = false;
let calcFractionResult = null;
let calcAngleMode = 'deg';

function calc_updateDisplay() {
    const exprDisplay = document.getElementById('calc-expression');
    const resultDisplay = document.getElementById('calc-result');
    const fractionResultDiv = document.getElementById('calc-fractionResult');
    
    if (exprDisplay) exprDisplay.textContent = calcDisplayExpr || '';
    if (resultDisplay) resultDisplay.textContent = calcResultValue;
    
    const shiftInd = document.getElementById('calc-ind-shift');
    const alphaInd = document.getElementById('calc-ind-alpha');
    const mInd = document.getElementById('calc-ind-m');
    
    if (shiftInd) shiftInd.classList.toggle('active', calcIsShift);
    if (alphaInd) alphaInd.classList.toggle('active', calcIsAlpha);
    if (mInd) mInd.classList.toggle('active', calcHasMemory);
    
    if (calcFractionResult && calcIsFractionResult) {
        if (resultDisplay) resultDisplay.style.display = 'none';
        if (fractionResultDiv) fractionResultDiv.classList.add('active');
        const wholeInd = document.getElementById('calc-fracWhole');
        const numInd = document.getElementById('calc-fracNum');
        const denInd = document.getElementById('calc-fracDen');
        if (wholeInd) wholeInd.textContent = calcFractionResult.whole > 0 ? calcFractionResult.whole : '';
        if (numInd) numInd.textContent = calcFractionResult.num;
        if (denInd) denInd.textContent = calcFractionResult.den;
    } else {
        if (resultDisplay) resultDisplay.style.display = 'block';
        if (fractionResultDiv) fractionResultDiv.classList.remove('active');
    }
}

function calc_formatNumber(num) {
    if (isNaN(num)) return 'Math ERROR';
    if (!isFinite(num)) return 'Math ERROR';
    if (Number.isInteger(num) && Math.abs(num) < 1e15) return num.toString();
    if (Math.abs(num) < 0.000001 || Math.abs(num) >= 1e10) {
        let expStr = num.toExponential(3);
        let parts = expStr.split('e');
        return parseFloat(parts[0]).toString() + '×10^' + parseInt(parts[1]);
    }
    return parseFloat(num.toFixed(8)).toString();
}

function calc_gcd(a, b) {
    a = Math.abs(Math.round(a));
    b = Math.abs(Math.round(b));
    while (b) { [a, b] = [b, a % b]; }
    return a;
}

function calc_decimalToFraction(decimal) {
    if (Number.isInteger(decimal)) return { whole: Math.abs(decimal), num: 0, den: 1 };
    let sign = decimal < 0 ? -1 : 1;
    decimal = Math.abs(decimal);
    let whole = Math.floor(decimal);
    let frac = decimal - whole;
    if (frac < 0.000001) return { whole: whole, num: 0, den: 1 };
    let tolerance = 1.0E-6;
    let x = frac, a = Math.floor(x), h1 = 1, k1 = 0, h = a, k = 1;
    for (let i = 0; i < 20; i++) {
        let r = x - a;
        if (Math.abs(r) < tolerance) break;
        x = 1 / r;
        a = Math.floor(x);
        let h2 = h1, k2 = k1;
        h1 = h; k1 = k;
        h = h2 + a * h1;
        k = k2 + a * k1;
    }
    let g = calc_gcd(h, k);
    return { whole: whole, num: (h / g), den: (k / g) };
}

function calc_numberKey(num) {
    calcIsFractionResult = false;
    calcFractionResult = null;
    calcDisplayExpr += num;
    calcExpression += num;
    calc_updateDisplay();
}

function calc_operatorKey(op) {
    calcIsFractionResult = false;
    calcFractionResult = null;
    let symbol = op === '*' ? '×' : op === '/' ? '÷' : op === '-' ? '−' : op;
    calcDisplayExpr += ' ' + symbol + ' ';
    calcExpression += op;
    calc_updateDisplay();
}

function calc_decimalKey() {
    let parts = calcDisplayExpr.split(/[+\\-×÷\\s]+/);
    let lastPart = parts[parts.length - 1];
    if (!lastPart.includes('.')) {
        calcDisplayExpr += '.';
        calcExpression += '.';
    }
    calc_updateDisplay();
}

function calc_calculate() {
    if (!calcExpression) return;
    try {
        let evalExpr = calcExpression;
        let openCount = (evalExpr.match(/\(/g) || []).length;
        let closeCount = (evalExpr.match(/\)/g) || []).length;
        while (closeCount < openCount) { evalExpr += ')'; closeCount++; }
        evalExpr = evalExpr.replace(/×/g, '*').replace(/÷/g, '/').replace(/−/g, '-');
        evalExpr = evalExpr.replace(/(\d)\(/g, '$1*(').replace(/\)(\d)/g, ')*$1');
        let result = Function('"use strict"; return (' + evalExpr + ')')();
        calcLastAnswer = result;
        calcDisplayExpr += ' =';
        calcResultValue = calc_formatNumber(result);
        let frac = calc_decimalToFraction(result);
        if (frac.den > 1 && frac.den < 10000) {
            calcFractionResult = frac;
            calcIsFractionResult = true;
        } else {
            calcFractionResult = null;
            calcIsFractionResult = false;
        }
        calcIsShift = false;
        calcIsAlpha = false;
        calcExpression = '';
        calc_updateDisplay();
    } catch (e) {
        calcResultValue = 'Math ERROR';
        calcExpression = '';
        calcFractionResult = null;
        calcIsFractionResult = false;
        calc_updateDisplay();
    }
}

function calc_acKey() {
    calcExpression = '';
    calcDisplayExpr = '';
    calcResultValue = '0';
    calcLastAnswer = 0;
    calcIsShift = false;
    calcIsAlpha = false;
    calcIsHyp = false;
    calcIsFractionResult = false;
    calcFractionResult = null;
    calc_updateDisplay();
}

function calc_delKey() {
    if (calcDisplayExpr.length > 0) {
        calcDisplayExpr = calcDisplayExpr.trimEnd();
        if (calcDisplayExpr.endsWith(' ')) calcDisplayExpr = calcDisplayExpr.slice(0, -1);
        else calcDisplayExpr = calcDisplayExpr.slice(0, -1);
        calcExpression = calcExpression.slice(0, -1);
        calc_updateDisplay();
    }
}

function calc_shiftKey() { calcIsShift = !calcIsShift; calcIsAlpha = false; calc_updateDisplay(); }
function calc_alphaKey() { calcIsAlpha = !calcIsAlpha; calcIsShift = false; calc_updateDisplay(); }
function calc_modeKey() {
    const degInd = document.getElementById('calc-ind-deg');
    if (calcAngleMode === 'deg') { 
        calcAngleMode = 'rad'; 
        if (degInd) degInd.textContent = 'R'; 
    }
    else if (calcAngleMode === 'rad') { 
        calcAngleMode = 'grad'; 
        if (degInd) degInd.textContent = 'G'; 
    }
    else { 
        calcAngleMode = 'deg'; 
        if (degInd) degInd.textContent = 'D'; 
    }
}

function calc_sqrtKey() {
    if (calcIsShift) { calcDisplayExpr = '∛('; calcExpression = 'Math.cbrt('; calcIsShift = false; }
    else { calcDisplayExpr = '√('; calcExpression = 'Math.sqrt('; }
    calc_updateDisplay();
}

function calc_squareKey() { calcDisplayExpr += '²'; calcExpression += '**2'; calc_updateDisplay(); }
function calc_cubeKey() { calcDisplayExpr += '³'; calcExpression += '**3'; calc_updateDisplay(); }
function calc_reciprocalKey() { calcDisplayExpr += '⁻¹'; calcExpression = '1/(' + calcExpression + ')'; calc_updateDisplay(); }

function calc_logKey() {
    if (calcIsShift) { calcDisplayExpr += '10^('; calcExpression += 'Math.pow(10,'; calcIsShift = false; }
    else { calcDisplayExpr += 'log('; calcExpression += 'Math.log10('; }
    calc_updateDisplay();
}

function calc_lnKey() {
    if (calcIsShift) { calcDisplayExpr += 'e^('; calcExpression += 'Math.exp('; calcIsShift = false; }
    else { calcDisplayExpr += 'ln('; calcExpression += 'Math.log('; }
    calc_updateDisplay();
}

function calc_negateKey() { calcExpression += '(-'; calcDisplayExpr += '(-'; calc_updateDisplay(); }
function calc_hypKey() { calcIsHyp = !calcIsHyp; }

function calc_sinKey() {
    if (calcIsShift) {
        calcDisplayExpr += 'sin⁻¹(';
        calcExpression += calcAngleMode === 'deg' ? '180/Math.PI*Math.asin(' : 'Math.asin(';
        calcIsShift = false;
    } else {
        calcDisplayExpr += 'sin(';
        calcExpression += calcIsHyp ? 'Math.sinh(' : (calcAngleMode === 'deg' ? 'Math.sin(Math.PI/180*' : 'Math.sin(');
        calcIsHyp = false;
    }
    calc_updateDisplay();
}

function calc_cosKey() {
    if (calcIsShift) {
        calcDisplayExpr += 'cos⁻¹(';
        calcExpression += calcAngleMode === 'deg' ? '180/Math.PI*Math.acos(' : 'Math.acos(';
        calcIsShift = false;
    } else {
        calcDisplayExpr += 'cos(';
        calcExpression += calcIsHyp ? 'Math.cosh(' : (calcAngleMode === 'deg' ? 'Math.cos(Math.PI/180*' : 'Math.cos(');
        calcIsHyp = false;
    }
    calc_updateDisplay();
}

function calc_tanKey() {
    if (calcIsShift) {
        calcDisplayExpr += 'tan⁻¹(';
        calcExpression += calcAngleMode === 'deg' ? '180/Math.PI*Math.atan(' : 'Math.atan(';
        calcIsShift = false;
    } else {
        calcDisplayExpr += 'tan(';
        calcExpression += calcIsHyp ? 'Math.tanh(' : (calcAngleMode === 'deg' ? 'Math.tan(Math.PI/180*' : 'Math.tan(');
        calcIsHyp = false;
    }
    calc_updateDisplay();
}

function calc_openParen() { calcDisplayExpr += '('; calcExpression += '('; calc_updateDisplay(); }
function calc_closeParen() { calcDisplayExpr += ')'; calcExpression += ')'; calc_updateDisplay(); }
function calc_sdKey() {
    if (calcIsFractionResult && calcFractionResult) {
        let dec = calcFractionResult.whole + (calcFractionResult.num / calcFractionResult.den);
        calcResultValue = calc_formatNumber(dec);
        calcIsFractionResult = false;
    } else {
        let num = parseFloat(calcResultValue);
        if (!isNaN(num)) { calcFractionResult = calc_decimalToFraction(num); calcIsFractionResult = true; }
    }
    calc_updateDisplay();
}

function calc_mPlusKey() { let num = parseFloat(calcResultValue); if (!isNaN(num)) { calcMemory += num; calcHasMemory = true; } }
function calc_fractionKey() { calcDisplayExpr += '/'; calcExpression += '/'; calc_updateDisplay(); }
function calc_nPrKey() { calcDisplayExpr += 'P'; calcExpression += 'P'; calc_updateDisplay(); }
function calc_nCrKey() { calcDisplayExpr += 'C'; calcExpression += 'C'; calc_updateDisplay(); }
function calc_percentKey() { calcDisplayExpr += '%'; calcExpression += '/100'; calc_updateDisplay(); }
function calc_expKey() { calcDisplayExpr += '×10^'; calcExpression += 'e'; calc_updateDisplay(); }
function calc_ansKey() { calcDisplayExpr += 'Ans'; calcExpression += calcLastAnswer.toString(); calc_updateDisplay(); }
function calc_calcKey() {}
function calc_integralKey() { calcDisplayExpr += '∫('; calcExpression += 'integral('; calc_updateDisplay(); }
function calc_derivativeKey() { calcDisplayExpr += 'd/dx('; calcExpression += 'derivative('; calc_updateDisplay(); }
function calc_sumKey() { calcDisplayExpr += 'Σ('; calcExpression += 'sum('; calc_updateDisplay(); }
function calc_powerKey() { calcDisplayExpr += '^('; calcExpression += 'Math.pow('; calc_updateDisplay(); }

// Keyboard support for calculator
document.addEventListener('keydown', function(e) {
    const modal = document.getElementById('calculator-modal');
    if (!modal || modal.style.display === 'none' || modal.style.display === '') return;
    
    if (e.key >= '0' && e.key <= '9') calc_numberKey(e.key);
    else if (e.key === '.') calc_decimalKey();
    else if (e.key === '+') calc_operatorKey('+');
    else if (e.key === '-') calc_operatorKey('-');
    else if (e.key === '*') calc_operatorKey('*');
    else if (e.key === '/') { e.preventDefault(); calc_operatorKey('/'); }
    else if (e.key === 'Enter' || e.key === '=') calc_calculate();
    else if (e.key === 'Escape') { calc_acKey(); toggleCalculator(); }
    else if (e.key === 'Backspace') calc_delKey();
});
</script>

<style>
/* Calculator Modal Styles */
.calculator-modal-content {
    background: linear-gradient(180deg, #1e5799 0%, #2989d8 20%, #1e5799 50%, #154580 100%);
    border-radius: 18px;
    padding: 20px 15px 15px;
    box-shadow: 0 30px 60px rgba(0, 0, 0, 0.7), inset 0 1px 0 rgba(255, 255, 255, 0.3);
    width: 100%;
    max-width: 340px;
    position: relative;
    margin: 20px;
}

.calculator-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
    padding: 0 5px;
    position: relative;
}

.calc-close-btn {
    position: absolute;
    top: 10px;
    right: 10px;
    background: #c05030;
    border: none;
    color: #fff;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    cursor: pointer;
    font-size: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 8px rgba(0,0,0,0.4);
    z-index: 10;
    transition: all 0.2s ease;
}

.calc-close-btn:hover {
    background: #d06040;
    transform: scale(1.15);
    box-shadow: 0 3px 12px rgba(0,0,0,0.5);
}

.calc-brand {
    color: #fff;
    font-size: 16px;
    font-weight: bold;
    letter-spacing: 1.5px;
    text-shadow: 0 1px 2px rgba(0,0,0,0.3);
}

.calc-model {
    color: #ffd700;
    font-size: 9px;
    font-weight: bold;
    letter-spacing: 0.5px;
}

.calc-solar-panel {
    background: #0a0a0a;
    height: 16px;
    width: 65px;
    border-radius: 2px;
    display: flex;
    gap: 1px;
    padding: 2px;
    border: 1px solid #333;
    box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.9);
}

.calc-solar-cell {
    flex: 1;
    background: linear-gradient(180deg, #1a3a5c 0%, #0d1f33 100%);
    border-right: 1px solid #0a0a0a;
}

.calc-solar-cell:last-child {
    border-right: none;
}

.calc-display-frame {
    background: #1a1a2e;
    border-radius: 6px;
    padding: 3px;
    margin-bottom: 12px;
    box-shadow: inset 0 2px 5px rgba(0, 0, 0, 0.6), 0 1px 0 rgba(255, 255, 255, 0.1);
}

.calc-display {
    background: linear-gradient(180deg, #e8f0d8 0%, #d8e8c8 50%, #c8d8b8 100%);
    border-radius: 4px;
    padding: 10px 12px;
    min-height: 75px;
    box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.15);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.calc-display-indicators {
    display: flex;
    justify-content: space-between;
    font-size: 8px;
    color: #6a7a5a;
    margin-bottom: 4px;
    font-weight: bold;
}

.calc-ind-left, .calc-ind-right {
    display: flex;
    gap: 6px;
}

.calc-indicator {
    opacity: 0.2;
    transition: opacity 0.2s;
}

.calc-indicator.active {
    opacity: 1;
    color: #2a3a1a;
}

.calc-expression {
    font-family: 'Courier New', monospace;
    font-size: 13px;
    color: #4a5a3a;
    min-height: 18px;
    text-align: left;
    width: 100%;
    margin-bottom: 6px;
    overflow-x: auto;
    white-space: nowrap;
}

.calc-result {
    font-family: 'Courier New', monospace;
    font-size: 24px;
    font-weight: bold;
    color: #1a2a0a;
    text-align: right;
    width: 100%;
    min-height: 30px;
}

.calc-fraction-result {
    display: none;
    align-items: center;
    justify-content: flex-end;
    margin-top: 4px;
}

.calc-fraction-result.active {
    display: flex;
}

.calc-frac {
    display: inline-flex;
    flex-direction: column;
    align-items: center;
    vertical-align: middle;
}

.calc-frac-num {
    font-family: 'Courier New', monospace;
    font-size: 13px;
    font-weight: bold;
    color: #1a2a0a;
    border-bottom: 2px solid #1a2a0a;
    padding: 0 6px 2px;
    text-align: center;
}

.calc-frac-den {
    font-family: 'Courier New', monospace;
    font-size: 13px;
    font-weight: bold;
    color: #1a2a0a;
    padding: 2px 6px 0;
    text-align: center;
}

.calc-buttons-container {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.calc-btn-row {
    display: grid;
    gap: 5px;
}

.calc-btn-row-5 {
    grid-template-columns: repeat(5, 1fr);
}

.calc-btn-row-6 {
    grid-template-columns: repeat(6, 1fr);
}

.calc-btn {
    padding: 10px 4px;
    font-size: 11px;
    font-weight: bold;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.1s ease;
    font-family: Arial, sans-serif;
    min-height: 32px;
    position: relative;
    box-shadow: 0px 4px 0px 0px rgba(0, 0, 0, 0.4);
}

.calc-btn:active {
    transform: translateY(4px) !important;
    box-shadow: 0px 0px 0px 0px transparent !important;
}

.calc-shift-label {
    display: block;
    font-size: 9px;
    color: #ffd700;
    font-weight: bold;
    margin-bottom: 2px;
    line-height: 1;
    text-align: center;
}

.btn-shift { background: linear-gradient(180deg, #8b7a3e 0%, #6b5a2e 100%); color: #fff; font-size: 10px; }
.btn-shift:hover { background: linear-gradient(180deg, #9b8a4e 0%, #7b6a3e 100%); }
.btn-alpha { background: linear-gradient(180deg, #a84a3a 0%, #883a2a 100%); color: #fff; font-size: 10px; }
.btn-alpha:hover { background: linear-gradient(180deg, #b85a4a 0%, #984a3a 100%); }
.btn-mode { background: linear-gradient(180deg, #5a6a7a 0%, #4a5a6a 100%); color: #fff; font-size: 9px; }
.btn-mode:hover { background: linear-gradient(180deg, #6a7a8a 0%, #5a6a7a 100%); }
.btn-nav { background: linear-gradient(180deg, #6a7a8a 0%, #5a6a7a 100%); color: #fff; font-size: 10px; }
.btn-nav:hover { background: linear-gradient(180deg, #7a8a9a 0%, #6a7a8a 100%); }
.btn-function { background: linear-gradient(180deg, #5a6a7a 0%, #4a5a6a 100%); color: #fff; font-size: 10px; }
.btn-function:hover { background: linear-gradient(180deg, #6a7a8a 0%, #5a6a7a 100%); }
.btn-trig { background: linear-gradient(180deg, #4a5a6a 0%, #3a4a5a 100%); color: #fff; font-size: 11px; }
.btn-trig:hover { background: linear-gradient(180deg, #5a6a7a 0%, #4a5a6a 100%); }
.btn-number { background: linear-gradient(180deg, #4a4a5a 0%, #3a3a4a 100%); color: #fff; font-size: 14px; }
.btn-number:hover { background: linear-gradient(180deg, #5a5a6a 0%, #4a4a5a 100%); }
.btn-operator { background: linear-gradient(180deg, #5a6a7a 0%, #4a5a6a 100%); color: #fff; font-size: 14px; }
.btn-operator:hover { background: linear-gradient(180deg, #6a7a8a 0%, #5a6a7a 100%); }
.btn-del { background: linear-gradient(180deg, #c05030 0%, #a04020 100%); color: #fff; font-size: 10px; }
.btn-del:hover { background: linear-gradient(180deg, #d06040 0%, #b05030 100%); }
.btn-ac { background: linear-gradient(180deg, #c05030 0%, #a04020 100%); color: #fff; font-size: 10px; }
.btn-ac:hover { background: linear-gradient(180deg, #d06040 0%, #b05030 100%); }
.btn-equals { background: linear-gradient(180deg, #2060a0 0%, #105090 100%); color: #fff; font-size: 16px; }
.btn-equals:hover { background: linear-gradient(180deg, #3070b0 0%, #2060a0 100%); }
.btn-fraction { background: linear-gradient(180deg, #6a5a8a 0%, #5a4a7a 100%); color: #fff; font-size: 11px; }
.btn-fraction:hover { background: linear-gradient(180deg, #7a6a9a 0%, #6a5a8a 100%); }
.btn-sd { background: linear-gradient(180deg, #6a5a8a 0%, #5a4a7a 100%); color: #fff; font-size: 10px; }
.btn-sd:hover { background: linear-gradient(180deg, #7a6a9a 0%, #6a5a8a 100%); }

@media (max-width: 768px) {
    .calculator-modal-content {
        max-width: 95%;
        width: calc(100% - 20px);
        margin: 10px auto;
        padding: 15px 8px 10px;
        border-radius: 14px;
        max-height: 85vh;
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
    }

    .calc-close-btn {
        top: 8px;
        right: 8px;
        width: 32px;
        height: 32px;
        font-size: 14px;
    }

    .calc-brand {
        font-size: 13px;
    }

    .calc-model {
        font-size: 7px;
    }

    .calc-solar-panel {
        height: 12px;
        width: 50px;
    }

    .calc-display-frame {
        margin-bottom: 10px;
        padding: 2px;
    }

    .calc-display {
        min-height: 60px;
        padding: 8px 10px;
    }

    .calc-display-indicators {
        font-size: 7px;
        gap: 4px;
    }

    .calc-expression {
        font-size: 11px;
        min-height: 15px;
        margin-bottom: 4px;
    }

    .calc-result {
        font-size: 20px;
        min-height: 26px;
    }

    .calc-frac-num,
    .calc-frac-den {
        font-size: 11px;
    }

    .buttons-container {
        gap: 4px;
    }

    .calc-btn-row {
        gap: 4px;
    }

    .calc-btn {
        padding: 14px 3px;
        font-size: 13px;
        min-height: 44px;
        border-radius: 6px;
        -webkit-tap-highlight-color: rgba(255, 255, 255, 0.2);
        touch-action: manipulation;
    }

    .calc-btn:active {
        transform: translateY(4px) !important;
        box-shadow: 0px 0px 0px 0px transparent !important;
    }

    .calc-shift-label {
        font-size: 9px;
        margin-bottom: 1px;
    }

    .btn-number {
        font-size: 16px;
    }

    .btn-operator,
    .btn-function,
    .btn-trig {
        font-size: 12px;
    }

    .btn-equals {
        font-size: 18px;
    }

    .btn-del,
    .btn-ac,
    .btn-shift,
    .btn-alpha,
    .btn-mode,
    .btn-nav {
        font-size: 11px;
    }
}

@media (max-width: 480px) {
    .calculator-modal-content {
        max-width: 100%;
        width: 100%;
        margin: 0;
        border-radius: 0;
        max-height: 100vh;
        height: 100vh;
        padding: 20px 6px 10px;
        display: flex;
        flex-direction: column;
    }

    .calc-close-btn {
        top: 10px;
        right: 10px;
        width: 36px;
        height: 36px;
    }

    .calc-buttons-container {
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: space-evenly;
        gap: 3px;
    }

    .calc-btn-row {
        gap: 3px;
    }

    .calc-btn {
        padding: 8px 2px;
        min-height: 40px;
        font-size: 12px;
        touch-action: manipulation;
    }

    .btn-number {
        font-size: 15px;
    }

    .btn-operator,
    .btn-function,
    .btn-trig {
        font-size: 11px;
    }

    .btn-equals {
        font-size: 16px;
    }

    .calc-shift-label {
        font-size: 8px;
    }

    .calc-display {
        min-height: 70px;
    }

    .calc-expression {
        font-size: 12px;
    }

    .calc-result {
        font-size: 22px;
    }
}

@media (max-height: 600px) and (orientation: landscape) {
    .calculator-modal-content {
        max-height: 95vh;
        padding: 10px 5px;
    }

    .calc-display {
        min-height: 50px;
        padding: 6px 8px;
    }

    .calc-expression {
        font-size: 10px;
    }

    .calc-result {
        font-size: 16px;
    }

    .calc-btn {
        padding: 6px 2px;
        min-height: 32px;
        font-size: 10px;
    }

    .btn-number {
        font-size: 12px;
    }

    .btn-operator,
    .btn-function {
        font-size: 9px;
    }
}

/* Improve touch interactions */
@media (hover: none) and (pointer: coarse) {
    .calc-btn {
        min-height: 44px;
        -webkit-tap-highlight-color: rgba(255, 255, 255, 0.3);
    }

    .calc-btn:hover {
        transform: none;
    }

    .calc-btn:active {
        transform: translateY(4px) !important;
        box-shadow: 0px 0px 0px 0px transparent !important;
        opacity: 0.8;
    }
}
</style>
