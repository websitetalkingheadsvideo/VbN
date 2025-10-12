const fs = require('fs');

// Read the CSS file
let cssContent = fs.readFileSync('css/style.css', 'utf8');

// Function to reduce vertical padding by 50%
function reduceVerticalPadding(match, p1, p2, p3, p4) {
    // p1 = top, p2 = right, p3 = bottom, p4 = left
    const top = Math.max(1, Math.round(parseInt(p1) / 2));
    const right = p2;
    const bottom = Math.max(1, Math.round(parseInt(p3) / 2));
    const left = p4;
    
    return `padding: ${top}px ${right}px ${bottom}px ${left}px;`;
}

// Function to reduce single padding value by 50%
function reduceSinglePadding(match, value) {
    const num = parseInt(value);
    const reduced = Math.max(1, Math.round(num / 2));
    return `padding: ${reduced}px;`;
}

// Function to reduce two-value padding (vertical horizontal) by 50% on vertical
function reduceTwoValuePadding(match, vertical, horizontal) {
    const vert = Math.max(1, Math.round(parseInt(vertical) / 2));
    const horiz = horizontal;
    return `padding: ${vert}px ${horiz}px;`;
}

// Apply reductions
// 1. Four-value padding: top right bottom left
cssContent = cssContent.replace(/padding:\s*(\d+)px\s+(\d+)px\s+(\d+)px\s+(\d+)px;/g, reduceVerticalPadding);

// 2. Two-value padding: vertical horizontal (reduce vertical by 50%)
cssContent = cssContent.replace(/padding:\s*(\d+)px\s+(\d+)px;/g, reduceTwoValuePadding);

// 3. Single padding value (reduce by 50%)
cssContent = cssContent.replace(/padding:\s*(\d+)px;/g, reduceSinglePadding);

// Write the modified CSS
fs.writeFileSync('css/style.css', cssContent);

console.log('Vertical padding reduced by 50% throughout the page!');
console.log('All padding values have been halved (minimum 1px maintained)');
