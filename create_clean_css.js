const fs = require('fs');

// Read the current CSS file
let cssContent = fs.readFileSync('css/style.css', 'utf8');

// Extract the CSS variables (already at the top)
const variablesMatch = cssContent.match(/\/\* ========================================\s*CSS VARIABLES\s*======================================== \*\/[\s\S]*?(?=\/\*|$)/);
const variablesSection = variablesMatch ? variablesMatch[0] : '';

// Extract all media queries
const mediaQueries = cssContent.match(/@media[^{]*\{[^}]*\}/g) || [];

// Remove variables and media queries from main content
let mainContent = cssContent;
if (variablesMatch) {
    mainContent = mainContent.replace(variablesMatch[0], '');
}
mainContent = mainContent.replace(/@media[^{]*\{[^}]*\}/g, '');

// Clean up extra whitespace
mainContent = mainContent.replace(/\n\s*\n\s*\n/g, '\n\n');

// Create a clean, organized CSS structure
let cleanCSS = `/* ========================================
   CSS VARIABLES
   ======================================== */
${variablesSection}

/* ========================================
   RESET & BASE STYLES
   ======================================== */

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: var(--font-body);
    background: linear-gradient(135deg, #000713, #2E5740, #17212D, #1D2523);
    color: #e0e0e0;
    padding: 8px;
    display: flex;
    justify-content: center;
    gap: 20px;
    min-height: 100vh;
}

/* ========================================
   LAYOUT COMPONENTS
   ======================================== */

.container {
    flex: 0 1 1200px;
    max-width: 1200px;
    background: linear-gradient(145deg, #1a1a1a, #2a2a2a, #1a1a1a);
    border: 2px solid #780606;
    border-radius: 8px;
    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.8), 0 0 20px rgba(120, 6, 6, 0.4), inset 0 1px 0 rgba(120, 6, 6, 0.1);
}

.sidebar {
    width: 300px;
    background: linear-gradient(135deg, #0a0a0a, #1a1a1a, #0a0a0a);
    border: 2px solid #780606;
    border-radius: 8px;
    padding: 8px;
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.6), 0 0 15px rgba(120, 6, 6, 0.3);
}

/* ========================================
   TYPOGRAPHY
   ======================================== */

.header h1 {
    font-family: var(--font-brand);
    color: #780606;
    font-size: 28px;
    font-weight: bold;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.8);
    margin: 0;
}

.header .subtitle {
    font-family: var(--font-title);
    color: #c0c0c0;
    font-size: 14px;
    font-style: italic;
    margin-top: 4px;
}

/* ========================================
   FORM ELEMENTS
   ======================================== */

.form-group {
    margin-bottom: 8px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

label {
    display: block;
    margin-bottom: 8px;
    color: #c0c0c0;
    font-weight: bold;
    font-size: 14px;
    font-family: var(--font-title);
}

input, select, textarea {
    width: 100%;
    padding: 8px 12px;
    background: #2a2a2a;
    border: 1px solid #555;
    border-radius: 4px;
    color: #e0e0e0;
    font-family: var(--font-body);
    font-size: 14px;
}

/* ========================================
   UI COMPONENTS
   ======================================== */

.tab {
    background: linear-gradient(145deg, #2a2a2a, #1a1a1a);
    border: 1px solid #555;
    border-bottom: none;
    padding: 6px 18px;
    cursor: pointer;
    font-family: var(--font-title);
    font-size: 13px;
    color: #ccc;
    transition: all 0.3s ease;
}

.tab.active {
    background: linear-gradient(145deg, #780606, #5a0202);
    color: #fff;
    border-color: #8a0202;
}

/* ========================================
   INTERACTIVE ELEMENTS
   ======================================== */

.tab:hover {
    background: linear-gradient(145deg, #3a3a3a, #2a2a2a);
    color: #fff;
}

button:hover {
    background: linear-gradient(145deg, #8a0202, #6a0101);
    box-shadow: 0 4px 8px rgba(120, 6, 6, 0.4);
}

/* ========================================
   RESPONSIVE DESIGN
   ======================================== */

${mediaQueries.join('\n\n')}
`;

// Write the clean CSS
fs.writeFileSync('css/style_clean.css', cleanCSS);

console.log('Clean CSS structure created!');
console.log('Sections: Variables, Reset, Layout, Typography, Forms, UI, Interactive, Responsive');
console.log('Media queries:', mediaQueries.length);
