/**
 * Chemistry Calculator - Equation & Reaction Builder (AI Enhanced)
 */

class ChemistryCalculator extends BaseCalculator {
    constructor() {
        super('Chemistry', 'chem-calc-panel', 'chem-calc-trigger');
        this.variableData = {
            'P': { name: 'Pressure', unit: 'atm' },
            'V': { name: 'Volume', unit: 'L' },
            'n': { name: 'Amount (moles)', unit: 'mol' },
            'T': { name: 'Temperature', unit: 'K' },
            'R': { name: 'Gas Constant', unit: 'L·atm/(mol·K)' },
            'C': { name: 'Concentration', unit: 'mol/L' },
            'm': { name: 'Mass', unit: 'g' },
            'M': { name: 'Molar Mass', unit: 'g/mol' },
            'pH': { name: 'pH', unit: '' },
            'pOH': { name: 'pOH', unit: '' },
            '[H^+]': { name: 'H+ Conc', unit: 'M' },
            '[OH^-]': { name: 'OH- Conc', unit: 'M' },
            'Q': { name: 'Heat Energy', unit: 'J' },
            'c': { name: 'Specific Heat', unit: 'J/g°C' },
            '\\Delta T': { name: 'Temp Change', unit: '°C' }
        };
    }

    // Legacy methods removed in favor of BaseCalculator's dynamic solving
}

// Initialize when DOM is ready
function initChemistryCalculator() {
    if (window.chemistryCalculator) return;
    window.chemistryCalculator = new ChemistryCalculator();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initChemistryCalculator);
} else {
    initChemistryCalculator();
}
