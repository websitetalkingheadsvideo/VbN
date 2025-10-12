const fs = require('fs');

// Read the CSS file
let cssContent = fs.readFileSync('css/style.css', 'utf8');

// Extract all mobile CSS sections
const mobileSections = [];

// Find and extract tablet CSS (lines 19-42)
const tabletMatch = cssContent.match(/\/\* Tablet Responsive Design \*\/\s*@media \(max-width: 1024px\) and \(min-width: 769px\) \{[^}]+\}/s);
if (tabletMatch) {
    mobileSections.push(tabletMatch[0]);
    cssContent = cssContent.replace(tabletMatch[0], '');
}

// Find and extract main mobile CSS (lines 2644-2878)
const mainMobileMatch = cssContent.match(/@media \(max-width: 768px\) \{[^}]+\}/s);
if (mainMobileMatch) {
    mobileSections.push(mainMobileMatch[0]);
    cssContent = cssContent.replace(mainMobileMatch[0], '');
}

// Find and extract other mobile sections
const mobileMatches = cssContent.match(/@media \(max-width: 768px\) \{[^}]+\}/g);
if (mobileMatches) {
    mobileSections.push(...mobileMatches);
    mobileMatches.forEach(match => {
        cssContent = cssContent.replace(match, '');
    });
}

// Clean up any extra whitespace
cssContent = cssContent.replace(/\n\s*\n\s*\n/g, '\n\n');

// Add all mobile CSS to the bottom
const reorganizedCSS = cssContent + '\n\n/* ========================================\n   RESPONSIVE DESIGN\n   ======================================== */\n\n' + mobileSections.join('\n\n');

// Write the reorganized CSS
fs.writeFileSync('css/style_reorganized_final.css', reorganizedCSS);

console.log('CSS reorganized successfully!');
console.log('Mobile sections found:', mobileSections.length);
console.log('Original file size:', cssContent.length);
console.log('Reorganized file size:', reorganizedCSS.length);
