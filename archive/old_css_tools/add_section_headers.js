const fs = require('fs');

// Read the current CSS file
let cssContent = fs.readFileSync('css/style.css', 'utf8');

// Add section headers to organize the CSS better
let organizedCSS = cssContent.replace(/\* {/, `/* ========================================
   RESET & BASE STYLES
   ======================================== */

* {`);

// Add header before body styles
organizedCSS = organizedCSS.replace(/body {/, `/* ========================================
   LAYOUT COMPONENTS
   ======================================== */

body {`);

// Add header before form styles
organizedCSS = organizedCSS.replace(/\.form-group {/, `/* ========================================
   FORM ELEMENTS
   ======================================== */

.form-group {`);

// Add header before tab styles
organizedCSS = organizedCSS.replace(/\.tab {/, `/* ========================================
   UI COMPONENTS
   ======================================== */

.tab {`);

// Add header before hover styles
organizedCSS = organizedCSS.replace(/\.tab:hover {/, `/* ========================================
   INTERACTIVE ELEMENTS
   ======================================== */

.tab:hover {`);

// Write the organized CSS
fs.writeFileSync('css/style.css', organizedCSS);

console.log('CSS organized with clear section headers!');
