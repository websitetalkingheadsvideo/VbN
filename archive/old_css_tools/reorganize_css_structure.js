const fs = require('fs');

// Read the CSS file
let cssContent = fs.readFileSync('css/style.css', 'utf8');

// Define the new structure sections
const sections = {
    'CSS Variables': [],
    'Reset & Base Styles': [],
    'Typography': [],
    'Layout Components': [],
    'UI Components': [],
    'Form Elements': [],
    'Interactive Elements': [],
    'Utility Classes': [],
    'Responsive Design': []
};

// Extract CSS rules and categorize them
const rules = cssContent.split(/\n(?=\/\*|\.|\#|@media)/);

// Process each rule
rules.forEach(rule => {
    const trimmedRule = rule.trim();
    if (!trimmedRule) return;

    // CSS Variables
    if (trimmedRule.includes(':root') || trimmedRule.includes('--font-') || trimmedRule.includes('--color-')) {
        sections['CSS Variables'].push(trimmedRule);
    }
    // Reset & Base
    else if (trimmedRule.includes('* {') || trimmedRule.includes('body {') || trimmedRule.includes('html {')) {
        sections['Reset & Base Styles'].push(trimmedRule);
    }
    // Typography
    else if (trimmedRule.includes('font-') || trimmedRule.includes('h1') || trimmedRule.includes('h2') || 
             trimmedRule.includes('h3') || trimmedRule.includes('h4') || trimmedRule.includes('h5') || 
             trimmedRule.includes('h6') || trimmedRule.includes('p {') || trimmedRule.includes('label {')) {
        sections['Typography'].push(trimmedRule);
    }
    // Layout Components
    else if (trimmedRule.includes('.container') || trimmedRule.includes('.sidebar') || 
             trimmedRule.includes('.tab-') || trimmedRule.includes('.card-') || 
             trimmedRule.includes('grid-') || trimmedRule.includes('flex-') ||
             trimmedRule.includes('display:') || trimmedRule.includes('position:')) {
        sections['Layout Components'].push(trimmedRule);
    }
    // Form Elements
    else if (trimmedRule.includes('.form-') || trimmedRule.includes('input') || 
             trimmedRule.includes('select') || trimmedRule.includes('textarea') || 
             trimmedRule.includes('button') || trimmedRule.includes('option')) {
        sections['Form Elements'].push(trimmedRule);
    }
    // Interactive Elements
    else if (trimmedRule.includes(':hover') || trimmedRule.includes(':focus') || 
             trimmedRule.includes(':active') || trimmedRule.includes(':checked') ||
             trimmedRule.includes('transition') || trimmedRule.includes('transform') ||
             trimmedRule.includes('animation')) {
        sections['Interactive Elements'].push(trimmedRule);
    }
    // Utility Classes
    else if (trimmedRule.includes('.hidden') || trimmedRule.includes('.visible') || 
             trimmedRule.includes('.text-') || trimmedRule.includes('.bg-') ||
             trimmedRule.includes('.margin-') || trimmedRule.includes('.padding-')) {
        sections['Utility Classes'].push(trimmedRule);
    }
    // Responsive Design
    else if (trimmedRule.includes('@media')) {
        sections['Responsive Design'].push(trimmedRule);
    }
    // Default to UI Components
    else {
        sections['UI Components'].push(trimmedRule);
    }
});

// Build the reorganized CSS
let reorganizedCSS = '';

// Add each section with proper headers
Object.entries(sections).forEach(([sectionName, rules]) => {
    if (rules.length > 0) {
        reorganizedCSS += `\n/* ========================================\n   ${sectionName.toUpperCase()}\n   ======================================== */\n\n`;
        rules.forEach(rule => {
            reorganizedCSS += rule + '\n\n';
        });
    }
});

// Write the reorganized CSS
fs.writeFileSync('css/style_reorganized_structure.css', reorganizedCSS);

console.log('CSS structure reorganized successfully!');
console.log('Sections created:');
Object.entries(sections).forEach(([name, rules]) => {
    console.log(`- ${name}: ${rules.length} rules`);
});
