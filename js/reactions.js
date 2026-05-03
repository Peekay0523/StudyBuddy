let currentReactionIndex = 0;
const reactions = [
    {
        title: "Formation of Water (Covalent)",
        description: "Two Hydrogen atoms share electrons with one Oxygen atom to form stable covalent bonds (H<sub>2</sub>O).",
        svg: `
            <svg width="400" height="200" viewBox="0 0 400 200">
                <!-- Oxygen -->
                <circle cx="200" cy="100" r="45" fill="#ffcccb" stroke="#333" stroke-width="2"/>
                <text x="188" y="110" font-weight="bold" font-size="24">O</text>
                
                <!-- Hydrogens (Overlapping to show sharing) -->
                <circle cx="145" cy="70" r="25" fill="#add8e6" stroke="#333" stroke-width="2" opacity="0.9"/>
                <circle cx="255" cy="70" r="25" fill="#add8e6" stroke="#333" stroke-width="2" opacity="0.9"/>
                <text x="135" y="75" font-weight="bold">H</text>
                <text x="245" y="75" font-weight="bold">H</text>
                
                <!-- Shared Electrons -->
                <circle cx="168" cy="82" r="4" fill="blue"/>
                <circle cx="174" cy="88" r="4" fill="blue"/>
                <circle cx="226" cy="82" r="4" fill="blue"/>
                <circle cx="232" cy="88" r="4" fill="blue"/>
                
                <text x="150" y="180" font-size="14" fill="#666">Shared electron pairs form covalent bonds</text>
            </svg>`
    },
    {
        title: "Table Salt (Ionic Bonding)",
        description: "Sodium (Na) transfers its valence electron to Chlorine (Cl), creating a lattice of Na<sup>+</sup> and Cl<sup>-</sup> ions.",
        svg: `
            <svg width="400" height="200" viewBox="0 0 400 200">
                <!-- Sodium Ion -->
                <circle cx="120" cy="100" r="30" fill="#ced6e0" stroke="#333" stroke-width="2"/>
                <text x="105" y="108" font-weight="bold">Na+</text>
                
                <!-- Chlorine Ion -->
                <circle cx="250" cy="100" r="50" fill="#2ed573" stroke="#333" stroke-width="2" opacity="0.8"/>
                <text x="235" y="108" font-weight="bold" font-size="20">Cl-</text>
                
                <!-- Electron Transfer Path -->
                <path d="M150,100 L190,100" stroke="#007bff" stroke-width="2" stroke-dasharray="5,5">
                    <animate attributeName="stroke-dashoffset" from="10" to="0" dur="1s" repeatCount="indefinite" />
                </path>
                <circle cx="195" cy="100" r="4" fill="#007bff">
                    <animate attributeName="cx" values="150;200" dur="1s" repeatCount="indefinite" />
                </circle>
                
                <text x="110" y="180" font-size="14" fill="#666">Opposite charges attract to form a crystal lattice</text>
            </svg>`
    },
    {
        title: "Methane Combustion",
        description: "CH<sub>4</sub> + 2O<sub>2</sub> &rarr; CO<sub>2</sub> + 2H<sub>2</sub>O. Hydrocarbons react with oxygen to release energy.",
        svg: `
            <svg width="450" height="220" viewBox="0 0 450 220">
                <!-- Methane Molecule (CH4) -->
                <g transform="translate(40, 60) scale(0.6)">
                    <circle cx="50" cy="50" r="30" fill="#2f3542"/> <!-- C -->
                    <circle cx="50" cy="10" r="15" fill="#add8e6"/> <!-- H -->
                    <circle cx="50" cy="90" r="15" fill="#add8e6"/> <!-- H -->
                    <circle cx="10" cy="50" r="15" fill="#add8e6"/> <!-- H -->
                    <circle cx="90" cy="50" r="15" fill="#add8e6"/> <!-- H -->
                    <text x="42" y="58" fill="white" font-weight="bold">C</text>
                </g>
                <text x="100" y="95" font-size="24">+</text>
                
                <!-- Oxygen Molecules (2 x O2) -->
                <g transform="translate(130, 60) scale(0.5)">
                    <circle cx="30" cy="30" r="25" fill="#ff4757"/>
                    <circle cx="70" cy="30" r="25" fill="#ff4757"/>
                </g>
                <g transform="translate(130, 110) scale(0.5)">
                    <circle cx="30" cy="30" r="25" fill="#ff4757"/>
                    <circle cx="70" cy="30" r="25" fill="#ff4757"/>
                </g>
                <text x="135" y="160" font-size="14" font-weight="bold">2O2</text>
                
                <text x="200" y="95" font-size="24">→</text>
                
                <!-- Products -->
                <!-- CO2 -->
                <g transform="translate(240, 75) scale(0.6)">
                    <circle cx="25" cy="30" r="20" fill="#ff4757"/>
                    <circle cx="65" cy="30" r="30" fill="#2f3542"/>
                    <circle cx="105" cy="30" r="20" fill="#ff4757"/>
                    <text x="35" y="85" font-size="18">CO2</text>
                </g>
                <text x="320" y="95" font-size="24">+</text>
                <!-- H2O -->
                <g transform="translate(350, 50) scale(0.4)">
                    <circle cx="50" cy="50" r="35" fill="#ffcccb"/>
                    <circle cx="20" cy="80" r="15" fill="#add8e6"/>
                    <circle cx="80" cy="80" r="15" fill="#add8e6"/>
                </g>
                <g transform="translate(350, 100) scale(0.4)">
                    <circle cx="50" cy="50" r="35" fill="#ffcccb"/>
                    <circle cx="20" cy="80" r="15" fill="#add8e6"/>
                    <circle cx="80" cy="80" r="15" fill="#add8e6"/>
                </g>
                <text x="355" y="160" font-size="14" font-weight="bold">2H2O</text>
                
                <!-- Energy/Heat -->
                <path d="M210,40 L210,10" stroke="orange" stroke-width="4">
                    <animate attributeName="stroke-width" values="4;10;4" dur="0.4s" repeatCount="indefinite"/>
                </path>
                <text x="190" y="25" fill="orange" font-weight="bold">ENERGY</text>
            </svg>`
    },
    {
        title: "Neutralization (HCl + NaOH)",
        description: "Acid and Base react to form Water and a Salt (NaCl). Ions rearrange to form stable products.",
        svg: `
            <svg width="400" height="200" viewBox="0 0 400 200">
                <!-- Reactants -->
                <g transform="translate(30, 60)">
                    <circle cx="20" cy="20" r="15" fill="#add8e6"/> <!-- H+ -->
                    <circle cx="50" cy="20" r="25" fill="#2ed573"/> <!-- Cl- -->
                    <text x="10" y="70" font-size="12">HCl (Acid)</text>
                </g>
                <text x="110" y="85" font-size="24">+</text>
                <g transform="translate(140, 60)">
                    <circle cx="20" cy="20" r="18" fill="#ced6e0"/> <!-- Na+ -->
                    <circle cx="55" cy="20" r="22" fill="#ffcccb"/> <!-- OH- -->
                    <text x="0" y="70" font-size="12">NaOH (Base)</text>
                </g>
                
                <text x="230" y="85" font-size="24">→</text>
                
                <!-- Products -->
                <g transform="translate(270, 60)">
                    <circle cx="20" cy="20" r="18" fill="#ced6e0"/> <!-- Na -->
                    <circle cx="45" cy="20" r="25" fill="#2ed573"/> <!-- Cl -->
                    <text x="15" y="70" font-size="12">NaCl (Salt)</text>
                    
                    <g transform="translate(70, 10)">
                         <circle cx="30" cy="40" r="20" fill="#ffcccb"/>
                         <circle cx="15" cy="55" r="10" fill="#add8e6"/>
                         <circle cx="45" cy="55" r="10" fill="#add8e6"/>
                         <text x="15" y="85" font-size="12">H2O</text>
                    </g>
                </g>
            </svg>`
    }
];

function updateReactionDisplay() {
    const reaction = reactions[currentReactionIndex];
    const container = document.getElementById('reaction-display-area');
    
    container.innerHTML = `
        <div class="text-center animate-fade">
            <h5 class="fw-bold text-primary mb-3">${reaction.title}</h5>
            <div class="bg-white p-4 rounded shadow-inner mb-3 d-flex justify-content-center align-items-center" style="min-height: 250px;">
                ${reaction.svg}
            </div>
            <p class="text-muted small px-5 lead" style="font-size: 0.95rem;">${reaction.description}</p>
            <div class="mt-3 text-secondary font-monospace" style="font-size: 0.8rem;">
                Step ${currentReactionIndex + 1} / ${reactions.length}
            </div>
        </div>
    `;
}

document.addEventListener('DOMContentLoaded', () => {
    const prevBtn = document.getElementById('prev-reaction');
    const nextBtn = document.getElementById('next-reaction');
    
    if (prevBtn && nextBtn) {
        prevBtn.onclick = () => {
            currentReactionIndex = (currentReactionIndex - 1 + reactions.length) % reactions.length;
            updateReactionDisplay();
        };
        
        nextBtn.onclick = () => {
            currentReactionIndex = (currentReactionIndex + 1) % reactions.length;
            updateReactionDisplay();
        };
        
        updateReactionDisplay();
    }
});
