const fs = require('fs');

// Read the CSS file
const cssContent = fs.readFileSync('css/style.css', 'utf8');

// Split into lines
const lines = cssContent.split('\n');

// Arrays to store different sections
let mainCSS = [];
let tabletCSS = [];
let mobileCSS = [];
let currentSection = 'main';

// Track if we're inside a media query
let inMediaQuery = false;
let mediaQueryContent = [];

// Process each line
for (let i = 0; i < lines.length; i++) {
    const line = lines[i];
    
    // Check for media queries
    if (line.includes('@media (max-width: 1024px) and (min-width: 769px)')) {
        // Tablet media query
        if (mediaQueryContent.length > 0) {
            mainCSS.push(...mediaQueryContent);
        }
        mediaQueryContent = [line];
        inMediaQuery = true;
        currentSection = 'tablet';
    } else if (line.includes('@media (max-width: 768px)')) {
        // Mobile media query
        if (mediaQueryContent.length > 0) {
            if (currentSection === 'tablet') {
                tabletCSS.push(...mediaQueryContent);
            } else {
                mainCSS.push(...mediaQueryContent);
            }
        }
        mediaQueryContent = [line];
        inMediaQuery = true;
        currentSection = 'mobile';
    } else if (inMediaQuery && line.trim() === '}') {
        // End of media query
        mediaQueryContent.push(line);
        if (currentSection === 'tablet') {
            tabletCSS.push(...mediaQueryContent);
        } else if (currentSection === 'mobile') {
            mobileCSS.push(...mediaQueryContent);
        }
        mediaQueryContent = [];
        inMediaQuery = false;
        currentSection = 'main';
    } else if (inMediaQuery) {
        // Inside media query
        mediaQueryContent.push(line);
    } else {
        // Regular CSS
        if (mediaQueryContent.length > 0) {
            mainCSS.push(...mediaQueryContent);
            mediaQueryContent = [];
        }
        mainCSS.push(line);
    }
}

// Add any remaining content
if (mediaQueryContent.length > 0) {
    if (currentSection === 'tablet') {
        tabletCSS.push(...mediaQueryContent);
    } else if (currentSection === 'mobile') {
        mobileCSS.push(...mediaQueryContent);
    } else {
        mainCSS.push(...mediaQueryContent);
    }
}

// Combine all sections
const reorganizedCSS = [
    ...mainCSS,
    '',
    '/* ========================================',
    '   RESPONSIVE DESIGN',
    '   ======================================== */',
    '',
    ...tabletCSS,
    '',
    ...mobileCSS
].join('\n');

// Write the reorganized CSS
fs.writeFileSync('css/style_reorganized.css', reorganizedCSS);

console.log('CSS reorganized successfully!');
console.log('Main CSS lines:', mainCSS.length);
console.log('Tablet CSS lines:', tabletCSS.length);
console.log('Mobile CSS lines:', mobileCSS.length);
